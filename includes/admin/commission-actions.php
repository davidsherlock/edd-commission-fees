<?php
/**
 * Commissions Actions
 *
 * @package     EDD_Commission_Fees
 * @subpackage  Admin
 * @copyright   Copyright (c) 2018, Sell Comet
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Process commission fee actions for single view
 *
 * @since       1.0.0
 * @return      void
 */
function eddcf_process_commission_update() {
	if ( empty( $_GET['commission'] ) || empty( $_GET['action'] ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_shop_payments' ) ) {
		return;
	}

	if ( isset( $_GET['_wpnonce'] ) && ! wp_verify_nonce( $_GET['_wpnonce'], 'eddcf_commission_nonce' ) ) {
		return;
	}

	$action = sanitize_text_field( $_GET['action'] );
	$id     = absint( $_GET['commission'] );

	switch ( $action ) {
		case 'revoke_fee':
			eddcf_revoke_commission_fee( $id );
			break;
		case 'recalculate_fee':
			eddcf_relculate_commission_fee( $id );
			break;
	}

	do_action( 'eddcf_process_commission_update', $action, $id );

	wp_redirect( add_query_arg( array( 'action' => false, '_wpnonce' => false, 'edd-message' => $action ) ) );
	exit;
}
add_action( 'admin_init', 'eddcf_process_commission_update', 1 );


/**
 * Update commission fee data
 *
 * @since       1.0.0
 * @return      void
 */
function eddcf_update_commission() {
	if ( ! current_user_can( 'edit_shop_payments' ) ) {
		return;
	}

	if ( ! isset( $_POST['eddcf_amount'] ) && ! isset( $_POST['eddcf_rate'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['eddcf_update_commission_nonce'], 'eddcf_update_commission' ) ) {
		wp_die( __( 'Nonce verification failed', 'edd-commission-fees' ), __( 'Error', 'edd-commission-fees' ), array( 'response' => 403 ) );
	}

	$commission_id   = (int) $_POST['commission_id'];
	$commission      = new EDD_Commission( $commission_id );

	$meta 			 = $commission->get_meta( '_edd_commission_fees', true );

	$base_amount 	 = isset( $meta['base'] ) 	? $meta['base']   : $commission->amount;
	$fee_type		 = isset( $meta['type'] ) 	? $meta['type']   : 'flat';
	$fee_status      = isset( $meta['status'] ) ? $meta['status'] : $commission->status;

	// Santize the fee amount
	$amount = sanitize_text_field( $_POST['eddcf_amount'] );
	$amount = str_replace( '%', '', $amount );
	$amount = str_replace( '$', '', $amount );
	$amount = $amount < 0 || ! is_numeric( $amount ) ? '' : $amount;
	$amount = round( $amount, edd_currency_decimal_filter() );

	// Santize the fee rate
	$rate = sanitize_text_field( $_POST['eddcf_rate'] );
	$rate = str_replace( '%', '', $rate );
	$rate = str_replace( '$', '', $rate );

	switch ( $fee_type ) {
		case 'percentage':
			if ( $rate < 0 || ! is_numeric( $rate ) ) {
				$rate = '';
			}

			$rate = ( is_numeric( $rate ) && $rate < 1 ) ? $rate * 100 : $rate;
			if ( is_numeric( $rate ) ) {
				$rate = round( $rate, 2 );
			}

			break;
		case 'flat':
		default:
			$rate = $rate < 0 || ! is_numeric( $rate ) ? '' : $rate;
			$rate = round( $rate, edd_currency_decimal_filter() );
			break;
	}

	$args = apply_filters( 'eddcf_update_commission', array(
		'fee'		=> (float) $amount,
		'rate' 		=> (float) $rate,
		'type'		=> $fee_type,
		'status'	=> $fee_status,
		'base'		=> $base_amount
	) );

	$commission->update_meta( '_edd_commission_fees', $args );

	wp_redirect( add_query_arg( array( 'edd-message' => 'update' ) ) );
	exit;
}
add_action( 'admin_init', 'eddcf_update_commission', 1 );


/**
 * Update commission fee status on commission status change
 *
 * @since       1.0.0
 * @return      void
 */
function eddcf_set_commission_status( $commission_id, $new_status, $old_status ) {
	eddcf_set_commission_fee_status( $commission_id, $new_status );
}
add_action( 'eddc_set_commission_status', 'eddcf_set_commission_status', 10, 3 );
