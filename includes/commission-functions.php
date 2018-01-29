<?php
/**
 * Commission Fee Functions.
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
 * Revokes the commission fee and reverts back to the base commission amount
 *
 * @access      public
 * @since       1.0.0
 * @param       int $commission_id The commission ID to revoke
 * @return      void
 */
function eddcf_revoke_commission_fee( $commission_id = 0 ) {
	if ( empty( $commission_id ) ) {
		return false;
	}

	$commission = new EDD_Commission( $commission_id );

	// Get the commission fee status
	$fee_status = eddcf_get_commission_fee_status( $commission_id );

	// Only process commissions that have meta and a commission status of "unpaid"
	if ( ! empty( $fee_status ) && 'unpaid' == $commission->status && 'revoked' !== $fee_status ) {

		$base_amount = eddcf_get_commission_fee_base_amount( $commission_id );

		if ( $base_amount == $commission->amount ) {
			return;
		}

		// Setup our commission meta args
		$args = apply_filters( 'eddcf_revoke_commission_fee_args', array(
			'fee'		=> '0.00',
			'rate' 		=> '0.00',
			'type'		=> 'flat',
			'status'	=> 'revoked',
			'base'		=> $base_amount
		) );

		// Store the commission meta
		$commission->update_meta( '_edd_commission_fees', $args );

		// Revert back to the base commission amount
		$commission->amount = $base_amount;

		// Save the updated commission record
		$commission->save();
	}
}


/**
 * Recalculate the commission fee from the commission amount
 *
 * @access      public
 * @since       1.0.0
 * @param       int $commission_id The ID for this commission
 * @return      void
 */
function eddcf_relculate_commission_fee( $commission_id = 0 ) {
	if ( empty( $commission_id ) ) {
		return false;
	}

	$commission = new EDD_Commission( $commission_id );

	// If the payment record does not exist, bail and return the current commission amount
	if ( false === edd_check_for_existing_payment( (int) $commission->payment_id ) ) {
		return $commission->amount;
	}

	// Only process commissions with an "unpaid" status
	if ( 'unpaid' == $commission->status ) {

		// Get the commission fee type
		$fee_type = eddcf_get_commission_fee_type( $commission->download_id );

		// Get the commission fee recipient rate
		$fee_rate = eddcf_get_recipient_rate( $commission->download_id, $commission->user_id );

		// Calculate the base commission amount
		$base_amount = eddcf_calc_base_commission_amount( $commission_id );

		// Calculate the commission fee amount
		$fee_amount = eddcf_calc_commission_fee( $base_amount, $fee_rate, $fee_type );

		// Calculate the commission amount
		$new_amount = eddcf_calc_commission_amount( $base_amount, $fee_rate, $fee_type );

		// Bail early if the fee amount is greater (or equal) to the base commission amount AND Allow £0.00 commissions is disabled
		if ( floatval( $fee_amount ) >= $base_amount && edd_get_option( 'edd_commission_fees_allow_zero_value', 'yes' ) == 'no' ) {
			$new_amount = $base_amount;
		} elseif ( floatval( $fee_amount ) >= $base_amount ) {
			$new_amount = round( 0, 2 ); // Return 0.00 if the fee amount is greater (or equal) to the commission amount after the deduction
		}

		// Setup our commission meta args
		$args = apply_filters( 'eddcf_relculate_commission_fee_args', array(
			'fee'		=> $fee_amount,
			'rate' 		=> $fee_rate,
			'type'		=> $fee_type,
			'status'	=> $commission->status,
			'base'		=> $base_amount
		) );

		// Store the commission meta
		$commission->update_meta( '_edd_commission_fees', $args );

		// Set the new commission amount
		$commission->amount = $new_amount;

		// Save the updated commission record
		$commission->save();
	}
}


/**
 * Retrieve the commission fee base amount
 *
 * @access      public
 * @since       1.0.0
 * @param       int $commission_id The post ID for this commission
 * @return      float $amount The commission fee base amount
 */
function eddcf_get_commission_fee_base_amount( $commission_id = 0 ) {
	if ( empty( $commission_id ) ) {
		return false;
	}

	$commission = new EDD_Commission( $commission_id );
	$meta = $commission->get_meta( '_edd_commission_fees', true );
	$amount = isset( $meta['base'] ) ? (float) $meta['base'] : $commission->amount;

	return apply_filters( 'eddcf_get_commission_fee_base_amount', $amount, $commission_id );
}


/**
 * Retrieve the commission fee status
 *
 * @access      public
 * @since       1.0.0
 * @param       int $commission_id The post ID for this commission
 * @return      string $status The commission fee status
 */
function eddcf_get_commission_fee_status( $commission_id = 0 ) {
	if ( empty( $commission_id ) ) {
		return false;
	}

	$commission = new EDD_Commission( $commission_id );
	$meta = $commission->get_meta( '_edd_commission_fees', true );
	$status = isset( $meta['status'] ) ? $meta['status'] : 'flat';

	return apply_filters( 'eddcf_get_commission_fee_status', $status, $commission_id );
}


/**
 * Sets the commission fee status
 *
 * @access      public
 * @since       1.0.0
 * @param       int $commission_id The ID for this commission
 * @param       string $new_status The new status for the commission
 * @return      void
 */
function eddcf_set_commission_fee_status( $commission_id = 0, $new_status = 'unpaid' ) {
	if ( empty( $commission_id ) ) {
		return false;
	}

	$commission = new EDD_Commission( $commission_id );

	$meta = $commission->get_meta( '_edd_commission_fees', true );

	if ( ! empty( $meta ) ) {

		// Get the old commission fee status
		$old_status = isset( $meta['status'] ) ? $meta['status'] : 'unpaid';

		// Setup our commission meta args
		$args = apply_filters( 'eddcf_set_commission_fee_status_args', array(
			'status' => $new_status,
		) );

		// Save the new commission meta
		$commission->update_meta( '_edd_commission_fees', array_merge( $meta, $args ) );

		do_action( 'eddcf_set_commission_fee_status', $commission_id, $new_status, $old_status );
	}
}


/**
 * Helper function to calculate the base (before fees) commission amount
 *
 * @access      public
 * @since       1.0.0
 * @param       integer $commission_id The commission ID to use to look up the payment cart details
 * @return      float of commissions that would need to be paid based on the payment id.
 */
function eddcf_calc_base_commission_amount( $commission_id = 0 ) {
	if ( empty( $commission_id ) ) {
		return false;
	}

	$commission = new EDD_Commission( $commission_id );

	// If we were passed a numeric value as the payment id (which it should be)
	if ( ! is_object( $commission->payment_id ) && is_numeric( $commission->payment_id ) ) {
		$payment = new EDD_Payment( $commission->payment_id );
	} elseif( is_a( $commission->payment_id, 'EDD_Payment' ) ) {
		$payment = $commission->payment_id;
	} else {
		return false;
	}

	// If the payment record does not exist, bail and return the current commission amount
	if ( false === edd_check_for_existing_payment( (int) $commission->payment_id ) ) {
		return $commission->amount;
	}

	// Get the commission type
	$type = eddc_get_commission_type( $commission->download_id );

	// Get the commission rate
	$rate = eddc_get_recipient_rate( $commission->download_id, $commission->user_id );

	// Make sure the array key exists
	if ( array_key_exists( $commission->cart_index, $payment->cart_details ) ) {

		$calc_base = edd_get_option( 'edd_commissions_calc_base', 'subtotal' );

		$recipient_position = eddc_get_recipient_position( $commission->user_id, $commission->download_id );

		if ( ! empty( $payment->cart_details ) ) {
			switch ( $calc_base ) {
				case 'subtotal':
					$price = $payment->cart_details[ $commission->cart_index ]['subtotal'];
					break;
				case 'total_pre_tax':
					$price = $payment->cart_details[ $commission->cart_index ]['price'] - $payment->cart_details[ $commission->cart_index ]['tax'];
					break;
				default:
					$price = $payment->cart_details[ $commission->cart_index ]['price'];
					break;
			}

			if ( 'subtotal' != $calc_base && ! empty( $payment->cart_details[ $commission->cart_index ]['fees'] ) ) {
				foreach ( $payment->cart_details[ $commission->cart_index ]['fees'] as $fee ) {
					$fee_amt = (float) $fee['amount'];
					if ( $fee_amt > 0 ) {
						continue;
					}

					$price = $price + $fee_amt;
				}
			}

			$args = apply_filters( 'eddcf_calc_commission_base_amount_args', array(
				'price'             => $price,
				'rate'              => $rate,
				'type'              => $type,
				'download_id'       => (int) $commission->download_id,
				'cart_item'         => (int) $payment->cart_details[ $commission->cart_index ],
				'recipient'         => (int) $commission->user_id,
				'recipient_counter' => (int) $recipient_position,
				'payment_id'        => (int) $commission->payment_id
			) );

			$amount = eddcf_calc_commission_base_amount( $args ); // calculate the commission amount to award

		}

	} else {

		$amount = $commission->amount;

	}

	return apply_filters( 'eddcf_calc_base_commission_amount', $amount, $rate, $type );
}


/**
 * Retrieve the amount of a commission
 *
 * Note: used by the eddcf_calc_base_commission_amount function to allow third-party filtering
 *
 * @access      public
 * @since       1.0.0
 * @param       array $args Arguments to pass to the query
 * @return      string The amount of the commission
 */
function eddcf_calc_commission_base_amount( $args ) {
	$defaults = array(
		'type' => 'percentage'
	);

	$args = wp_parse_args( $args, $defaults );

	if ( 'flat' == $args['type'] ) {
		return $args['rate'];
	}

	if ( ! isset( $args['price'] ) || $args['price'] == false ) {
		$args['price'] = '0.00';
	}

	if ( $args['rate'] >= 1 ) {
		$amount = $args['price'] * ( $args['rate'] / 100 ); // rate format = 10 for 10%
	} else {
		$amount = $args['price'] * $args['rate']; // rate format set as 0.10 for 10%
	}

	return apply_filters( 'eddcf_calc_commission_base_amount', $amount, $args );
}


/**
 * Calculate the final commission amount minus the fee
 *
 * @access      public
 * @since       1.0.0
 * @param       float $amount The base amount to calculate the fee on
 * @param       float $rate The recipient commission fee rate
 * @param       string $type The commission fee type
 * @return      float $amount The new commission amount
 */
function eddcf_calc_commission_amount( $amount, $rate, $type, $allow_negative = false ) {

	if ( 'percentage' === $type ) {

		if ( $rate >= 1 ) {
			$fee = $amount * ( $rate / 100 ); // rate format = 10 for 10%
		} else {
			$fee = $amount * $rate; // rate format set as 0.10 for 10%
		}

		$amount = $amount - $fee;

	} else {

		$amount = $amount - $rate;

	}

	// Flip negative amounts for comparison purposes
	if ( $allow_negative && $amount < 0 ) {
		$amount = abs( $amount );
	}

	// Set amount to 0.00 for negative amounts
	if ( $amount < 0 ) {
		$amount = round( 0, 2 );
	}

	return apply_filters( 'eddcf_calc_commission_amount', $amount, $rate, $type );
}


/**
 * Calculate the commission fee
 *
 * @access      public
 * @since       1.0.0
 * @param       float $amount The base amount to calculate the fee on
 * @param       float $rate The recipient commission fee rate
 * @param       string $type The commission fee type
 * @return      float $fee The commission fee amount
 */
function eddcf_calc_commission_fee( $amount, $rate, $type ) {

	if ( 'percentage' === $type ) {

		if ( $rate >= 1 ) {
			$fee = $amount * ( $rate / 100 ); // rate format = 10 for 10%
		} else {
			$fee = $amount * $rate; // rate format set as 0.10 for 10%
		}

	} else {

		$fee = $rate;

	}

	return apply_filters( 'eddcf_calc_commission_fee', $fee, $amount, $rate, $type );
}


/**
 *
 * Retrieves the commission fee rate for a product and user
 *
 * If $download_id is empty, the default rate from the user account is retrieved.
 * If no default rate is set on the user account, the global default is used.
 *
 * This function requires very strict typecasting to ensure the proper rates are used at all times.
 *
 * 0 is a permitted rate so we cannot use empty(). We always use NULL to check for non-existent values.
 *
 * @access      public
 * @since       1.0.0
 * @param       int $download_id The ID of the download product to retrieve the commission rate for
 * @param       int $user_id The user ID to retrieve commission rate for
 * @return      float $rate The commission rate
 */
function eddcf_get_recipient_rate( $download_id = 0, $user_id = 0 ) {
	$rate = null;

	// Check for a threshold rate specified on a specific product
	if ( ! empty( $download_id ) ) {
		$settings   = get_post_meta( $download_id, '_edd_commission_fee_settings', true );

		if ( ! empty( $settings ) && is_array( $settings ) ) {

			$rates      = isset( $settings['fee'] ) ? array_map( 'trim', explode( ',', $settings['fee'] ) ) : array();
			$recipients = array_map( 'trim', explode( ',', $settings['user_id'] ) );
			$rate_key   = array_search( $user_id, $recipients );

			if ( ! empty( $rates[ $rate_key ] ) ) {
				$rate = $rates[ $rate_key ];
			}
		}
	}

	// Check for a user specific global threshold rate
	if ( ! empty( $user_id ) && ( null === $rate || '' === $rate ) ) {
		$rate = get_user_meta( $user_id, 'edd_commission_fees_user_rate', true );

		if ( '' === $rate ) {
			$rate = null;
		}
	}

	// Check for an overall global rate
	if ( null === $rate && eddcf_get_default_fee_rate() ) {
		$rate = eddcf_get_default_fee_rate();
	}

	// Set rate to 0 if no rate was found
	if ( null === $rate || '' === $rate ) {
		$rate = 0;
	}

	return apply_filters( 'eddcf_get_recipient_rate', (float) $rate, $download_id, $user_id );
}


/**
 * Retrieve the fee 'type' of a commission for a download
 *
 * @access      public
 * @since       1.0.0
 * @param       int $download_id The download ID
 * @return      string The fee type of the commission
 */
function eddcf_get_commission_fee_type( $download_id = 0 ) {
	$settings = get_post_meta( $download_id, '_edd_commission_fee_settings', true );
	$type     = isset( $settings['type'] ) ? $settings['type'] : 'flat';
	return apply_filters( 'eddcf_get_commission_fee_type', $type, $download_id );
}


/**
 * Get an array containing the user id's entered in the "Users" field in the Commissions metabox.
 *
 * @access      public
 * @since       1.0.0
 * @param       int $download_id The id of the download for which we want the recipients.
 * @return      array An array containing the user ids of the recipients.
 */
function eddcf_get_recipients( $download_id = 0 ) {
	$settings = get_post_meta( $download_id, '_edd_commission_fee_settings', true );

	// If the information for commissions was not saved or this happens to be for a post with commissions currently disabled
	if ( ! isset( $settings['user_id'] ) ){
		return array();
	}

	$recipients = array_map( 'intval', explode( ',', $settings['user_id'] ) );
	return (array) apply_filters( 'eddcf_get_recipients', $recipients, $download_id );
}


/**
 * Check which position a recipient is in for a download's commission fee.
 *
 * @access      public
 * @since       1.0.0
 * @param       int $user_id The user id of the commission recipient (aka vendor).
 * @param       int $download_id The download id being purchased
 * @return      int $position The array position that the recipient is in.
 */
function eddcf_get_recipient_position( $recipient_id, $download_id ) {
	$recipients = eddcf_get_recipients( $download_id );
	return array_search( $recipient_id, $recipients );
}


/**
 * Get the total unpaid commission fees
 *
 * @access      public
 * @since       1.0.0
 * @param       int $user_id The ID of the user to look up
 * @return      string The total of unpaid commission fees
 */
function eddcf_get_unpaid_fee_totals( $user_id = 0 ) {
	$commission_args = array(
		'status' 	=> 'unpaid',
		'number' 	=> -1,
		'user_id' 	=> $user_id,
		'meta_key' 	=> '_edd_commission_fee_settings'
	);

	$commission_args = apply_filters( 'eddcf_get_unpaid_fee_totals', $commission_args, $user_id );

	$commissions = edd_commissions()->commissions_db->get_commissions( $commission_args );

	if ( ! empty( $commissions ) ) {

		$total = array();
		foreach ( $commissions as $commission ) {
			$meta = $commission->get_meta( '_edd_commission_fees', true );
			$total[] = isset( $meta['fee'] ) ? $meta['fee'] : 0;
		}

	}

	$total = isset( $total ) ? array_sum( $total ) : 0;

	return edd_sanitize_amount( $total );
}


/**
 * Get the total paid commission fees
 *
 * @access      public
 * @since       1.0.0
 * @param       int $user_id The ID of the user to look up
 * @return      string The total of paid commission fees
 */
function eddcf_get_paid_fee_totals( $user_id = 0 ) {
	$commission_args = array(
		'status' 	=> 'paid',
		'number' 	=> -1,
		'user_id' 	=> $user_id,
		'meta_key' 	=> '_edd_commission_fee_settings'
	);

	$commission_args = apply_filters( 'eddcf_get_paid_fee_totals', $commission_args, $user_id );

	$commissions = edd_commissions()->commissions_db->get_commissions( $commission_args );

	if ( ! empty( $commissions ) ) {

		$total = array();
		foreach ( $commissions as $commission ) {
			$meta = $commission->get_meta( '_edd_commission_fees', true );
			$total[] = isset( $meta['fee'] ) ? $meta['fee'] : 0;
		}

	}

	$total = isset( $total ) ? array_sum( $total ) : 0;

	return edd_sanitize_amount( $total );
}


/**
 * Get the total revoked commission fees
 *
 * @access      public
 * @since       1.0.0
 * @param       int $user_id The ID of the user to look up
 * @return      string The total of revoked commission fees
 */
function eddcf_get_revoked_fee_totals( $user_id = 0 ) {
	$commission_args = array(
		'status' 	=> 'revoked',
		'number' 	=> -1,
		'user_id' 	=> $user_id,
		'meta_key' 	=> '_edd_commission_fee_settings'
	);

	$commission_args = apply_filters( 'eddcf_get_revoked_fee_totals', $commission_args, $user_id );

	$commissions = edd_commissions()->commissions_db->get_commissions( $commission_args );

	if ( ! empty( $commissions ) ) {

		$total = array();
		foreach ( $commissions as $commission ) {
			$meta = $commission->get_meta( '_edd_commission_fees', true );
			$total[] = isset( $meta['fee'] ) ? $meta['fee'] : 0;
		}

	}

	$total = isset( $total ) ? array_sum( $total ) : 0;

	return edd_sanitize_amount( $total );
}


/**
 * Get the total for a range of commission fees
 *
 * @access      public
 * @since       1.0.0
 * @param       int $day The day to look up
 * @param       int $month The month to look up
 * @param       int $year The year to look up
 * @param       int $hour The hour to look up
 * @param       int $user_id The ID of the user to look up
 * @return      string The total of specified commissions
 */
function eddcf_get_commissions_by_date( $day = null, $month = null, $year = null, $hour = null, $user = 0  ) {
	$commission_args = array(
		'number' 	=> -1,
		'year'   	=> $year,
		'month'  	=> $month,
		'status' 	=> array( 'paid', 'unpaid' ),
		'meta_key' 	=> '_edd_commission_fee_settings'
	);

	if ( ! empty( $day ) ) {
		$commission_args['day'] = $day;
	}

	if ( ! empty( $hour ) ) {
		$commission_args['hour'] = $hour;
	}

	if ( ! empty( $user ) ) {
		$commission_args['user_id'] = absint( $user );
	}

	$commission_args = apply_filters( 'eddcf_get_commissions_by_date', $commission_args, $day, $month, $year, $user );

	$commissions = edd_commissions()->commissions_db->get_commissions( $commission_args );

	if ( ! empty( $commissions ) ) {

		$total = array();

		foreach ( $commissions as $commission ) {
			$meta = $commission->get_meta( '_edd_commission_fees', true );
			$total[] = isset( $meta['fee'] ) ? (float) $meta['fee'] : 0;
		}

	}

	$total = isset( $total ) ? array_sum( $total ) : 0;

	return edd_sanitize_amount( $total );
}


/**
 * Gets the default commission fee rate
 *
 * @access      public
 * @since       1.0.0
 * @return      float
 */
function eddcf_get_default_fee_rate() {
	global $edd_options;

	$rate = isset( $edd_options['edd_commission_fees_default_rate'] ) ? $edd_options['edd_commission_fees_default_rate'] : false;

	return apply_filters( 'eddcf_get_default_fee_rate', $rate );
}


/**
 * Does the user have commission fees disabled?
 *
 * @access      public
 * @since       1.0.0
 * @return      bool
 */
function eddcf_user_fee_disabled( $user_id = 0 ) {
	$ret = (bool) get_user_meta( $user_id, 'eddcf_disable_user_commission_fees', true );
	return apply_filters( 'eddcf_user_fee_disabled', $ret, $user_id );
}
