<?php
/**
 * Commission Fee Filters.
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
 * The main function for processing the commission fee, returns the update commission amount minus the fee
 *
 * @access      public
 * @since       1.0.0
 * @param       float $amount The base (original) commission amount
 * @param       array $args The commission args
 * @return      float $new_amount
 */
function eddcf_process_commission_fee_testing( $args, $commission_id, $payment_id, $download_id ) {
	$disable_fee_adjustment = (bool) edd_get_option( 'edd_commission_fees_fee_adjustment_disabled', false );

	// Return the base commission amount if disable fee adjustments are enabled
	if ( true === $disable_fee_adjustment ) {
		return $args['amount'];
	}

	// Return the base commission amount if user has fees disabled
	if ( eddcf_user_fee_disabled( $args['user_id'] ) ) {
		return $args['amount'];
	}

	// Add filter to override the function
	$should_process_commission_fee = apply_filters( 'eddcf_should_process_commission_fee', true, $args, $commission_id, $payment_id, $download_id );
	if ( false === $should_process_commission_fee ) {
		return $args['amount'];
	}

	// Get the commission recipient fee rate
	$rate = eddcf_get_recipient_rate( (int) $download_id, (int) $args['user_id'] );

	// Bail early if commission rate is 0 (empty) or NULL
	if ( 0 == $rate || NULL == $rate ) {
		return $args['amount'];
	}

	// Get the commission fee 'type' for the download
	$type = eddcf_get_commission_fee_type( (int) $download_id );

	// Get the commission fee amount
	$fee = eddcf_calc_commission_fee( $args['amount'], $rate, $type );

	// Calculate the commission fee amount
	$args['amount'] = eddcf_calc_commission_amount( $args['amount'], $rate, $type );

	// If the fee is greater (or equal) to the original commission amount and "Allow 0.00 Values" is set to no, return the original amount
	if ( floatval( $fee ) >= $args['amount'] && edd_get_option( 'edd_commission_fees_allow_zero_value', 'yes' ) == 'no' ) {
		return $args['amount'];
	} elseif ( floatval( $fee ) >= $args['amount'] ) {
		$args['amount'] = round( 0, 2 );
	}

	return $args;
}
add_filter( 'edd_commission_info', 'eddcf_process_commission_fee_testing', 10, 4 );
