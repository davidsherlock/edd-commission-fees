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
function eddcf_process_commission_fee( $amount, $args ) {
	$disable_fee_adjustment = (bool) edd_get_option( 'edd_commission_fees_fee_adjustment_disabled', false );

	// Return the base commission amount if disable fee adjustments are enabled
	if ( true === $disable_fee_adjustment ) {
		return $amount;
	}

	// Return the base commission amount if user has fees disabled
	if ( eddcf_user_fee_disabled( $args['recipient'] ) ) {
		return $amount;
	}

	// Add filter to override the function
	$should_process_commission_fee = apply_filters( 'eddcf_should_process_commission_fee', true, $amount, $args );
	if ( false === $should_process_commission_fee ) {
		return $amount;
	}

	// Get the commission recipient fee rate
	$rate = eddcf_get_recipient_rate( (int) $args['download_id'], (int) $args['recipient'] );

	// Bail early if commission rate is 0 (empty) or NULL
	if ( 0 == $rate || NULL == $rate ) {
		return $amount;
	}

	// Get the commission fee 'type' for the download
	$type = eddcf_get_commission_fee_type( (int) $args['download_id'] );

	// Get the commission fee amount
	$fee = eddcf_calc_commission_fee( $amount, $rate, $type );

	// Calculate the commission fee amount
	$new_amount = eddcf_calc_commission_amount( $amount, $rate, $type );

	// If the fee is greater (or equal) to the original commission amount and "Allow 0.00 Values" is set to no, return the original amount
	if ( floatval( $fee ) >= $amount && edd_get_option( 'edd_commission_fees_allow_zero_value', 'yes' ) == 'no' ) {
		return $amount;
	} elseif ( floatval( $fee ) >= $amount ) {
		$new_amount = round( 0, 2 );
	}

	return $new_amount;
}
add_filter( 'eddc_calc_commission_amount', 'eddcf_process_commission_fee', 10, 2 );
