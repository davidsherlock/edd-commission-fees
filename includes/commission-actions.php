<?php
/**
 * Commission Fee Actions.
 *
 * @package     EDD_Commission_Fees
 * @subpackage  Core
 * @copyright   Copyright (c) 2018, Sell Comet
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


 /**
  * Store a payment note about this commission fee
  *
  * @access      public
  * @since       1.0.0
  * @param       int $recipient The commission recipient user ID
  * @param       float $commission_amount The commission amount
  * @param       float $rate The commission recipient rate
  * @param       int $download_id The commission download ID
  * @param       int $commission_id The commission ID
  * @param       int $payment_id The commission payment ID
  * @return      void
  */
function eddcf_record_commission_note( $recipient, $commission_amount, $rate, $download_id, $commission_id, $payment_id ) {
	$commission = new EDD_Commission( $commission_id );
	$download = new EDD_Download( $download_id );

	// Get download name (including variable pricing name)
	$item_purchased = $download->get_name();
	if ( $download->has_variable_prices() ) {
		$prices = $download->get_prices();
		if ( isset( $prices[ $commission->price_id ] ) ) {
			$item_purchased .= ' - ' . $prices[ $commission->price_id ]['name'];
		}
	}

	// Get the commission fee type
	$fee_type = eddcf_get_commission_fee_type( $download_id );

	// Get the commission fee recipient rate
	$fee_rate = eddcf_get_recipient_rate( $download_id, $recipient );

	// Recalculate the base commission amount, we can't use $commission_amount since it might be 0.00
	$base_amount = eddcf_calc_base_commission_amount( $commission_id );

	// Use the correct wording for the note if disable fee adjustment is enabled
	if ( true === (bool) edd_get_option( 'edd_commission_fees_fee_adjustment_disabled', false ) ) {
		$action = __( 'recorded', 'edd-commission-fees' );
	} else {
		$action = __( 'charged', 'edd-commission-fees' );
	}

	// Setup our note
	$note = sprintf(
		__( 'Commission fee of %s %s to %s for %s &ndash; <a href="%s">View</a>', 'edd-commission-fees' ),
		edd_currency_filter( edd_format_amount( eddcf_calc_commission_fee( $base_amount, $fee_rate, $fee_type ) ) ),
		$action,
		get_userdata( $recipient )->display_name,
		$item_purchased,
		admin_url( 'edit.php?post_type=download&page=edd-commissions&payment=' . $payment_id )
	);

	// Store the payment note
	edd_insert_payment_note( $payment_id, $note );
}
add_action( 'eddc_insert_commission', 'eddcf_record_commission_note', 10, 6 );


/**
 * Store the commission fee meta
 *
 * @since       1.0.0
 * @param       int $recipient The commission recipient user ID
 * @param       float $commission_amount The commission amount
 * @param       float $rate The commission recipient rate
 * @param       int $download_id The commission download ID
 * @param       int $commission_id The commission ID
 * @param       int $payment_id The commission payment ID
 * @return      void
 */
function eddcf_record_commission_meta( $recipient, $commission_amount, $rate, $download_id, $commission_id, $payment_id ) {

	// Get the commission fee type
	$fee_type = eddcf_get_commission_fee_type( $download_id );

	// Get the commission fee recipient rate
	$fee_rate = eddcf_get_recipient_rate( $download_id, $recipient );

	// Bail early if rate is 0 or null
	if ( 0 == $fee_rate || NULL == $fee_rate ) {
		return;
	}

	// Get the commission object
	$commission = eddc_get_commission( $commission_id );

	// Recalculate the base commission amount, we can't use $commission_amount since it might be 0.00
	$base_amount = eddcf_calc_base_commission_amount( $commission_id );

	// Calculate the commission fee amount
	$fee_amount = eddcf_calc_commission_fee( $base_amount, $fee_rate, $fee_type );

	// Setup our commission meta args
	$args = apply_filters( 'eddcf_record_commission_meta_args', array(
		'fee'		=> $fee_amount,
		'rate' 		=> $fee_rate,
		'type'		=> $fee_type,
		'status'	=> 'unpaid',
		'base'		=> $base_amount
	) );

	// Store the commission meta
	$commission->update_meta( '_edd_commission_fees', $args );
}
add_action( 'eddc_insert_commission', 'eddcf_record_commission_meta', 10, 6 );


/**
 * Revoke commission fees when the payment record associated is refunded.
 *
 * @since       1.0.0
 * @param       $payment EDD_Payment object.
 * @return      void
 */
function eddcf_revoke_fees_on_refund( $payment ) {
	$revoke_on_refund = edd_get_option( 'edd_commissions_revoke_on_refund', false );

	if ( false === $revoke_on_refund ) {
		return;
	}

	// Due to action priorities, we need to check for 'revoked' instead of 'unpaid'
	$commissions = eddc_get_commissions( array(
		'payment_id' => $payment->ID,
		'status'     => 'revoked',
		'meta_key'   => '_edd_commission_fees'
	) );

	if ( ! empty( $commissions ) ) {
		foreach ( $commissions as $commission ) {

			$note  = sprintf(
				__( 'Commission fee revoked for %s due to refunded payment &ndash; <a href="%s">View</a>', 'eddc' ),
				get_userdata( $commission->user_id )->display_name,
				admin_url( 'edit.php?post_type=download&page=edd-commissions&payment=' . $payment->ID )
			);

			$payment->add_note( $note );
		}
	}
}
add_action( 'edd_post_refund_payment', 'eddcf_revoke_fees_on_refund', 10, 1 );
