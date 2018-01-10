<?php
/**
 * Metabox Functions
 *
 * @package     EDD\CommissionFees
 * @subpackage  Functions
 * @copyright   Copyright (c) 2017, Sell Comet
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Get the download commission fee rate
 *
 * @since 1.0.0
 * @return float
 */
function edd_commission_fees_get_download_rate( $download_id = 0 ) {
	$ret = (float) get_post_meta( $download_id, '_edd_commission_fee_rate', true );
	return apply_filters( 'edd_commission_fees_get_download_rate', $ret, $download_id );
}


/**
 * Are commission fees disabled on this download?
 *
 * @since 1.0.0
 * @return bool
 */
function edd_commission_fees_download_fee_disabled( $download_id = 0 ) {
	$ret = (bool) get_post_meta( $download_id, '_edd_commission_fee_disabled', true );
	return apply_filters( 'edd_commission_fees_download_fee_disabled', $ret, $download_id );
}


/**
 * Are commission fees disabled on this download?
 *
 * @since 1.0.0
 * @return bool
 */
function edd_commission_fees_user_fee_disabled( $user_id = 0 ) {
	$ret = (bool) get_user_meta( $user_id, 'eddcf_disable_user_commission_fees', true );
	return apply_filters( 'edd_commission_fees_user_fee_disabled', $ret, $user_id );
}


/**
 * Are fees disabeld on the download, user or global settings?
 *
 * @since 1.0.0
 * @return bool
 */
function edd_commission_fees_disabled( $download_id = 0, $user_id = 0 ) {
	$download_fees_disabled = edd_commission_fees_download_fee_disabled( $download_id );
	$user_fee_disabled			=	edd_commission_fees_user_fee_disabled ( $user_id );
	$global_disabled				= edd_commission_fees_global_fee_disabled();

	if ( true === $download_fees_disabled || true === $user_fee_disabled || true === $global_disabled ) {
		$ret = true;
	} else {
		$ret = false;
	}

	return apply_filters( 'edd_commission_fees_disabled', (bool) $ret, $download_id, $user_id );
}


/**
 * Are fees disabled globally?
 *
 * @since 1.0
 * @return bool $ret True if disabled, false otherwise
 */
function edd_commission_fees_global_fee_disabled() {
	$ret = edd_get_option( 'edd_commission_fees_disabled', false );
	return (bool) apply_filters( 'edd_commission_fees_global_fee_disabled', $ret );
}


/**
 * Show fees on frontend commissinos shortcodes?
 *
 * @since 1.0
 * @return bool $ret True if disabled, false otherwise
 */
function edd_commission_fees_global_enable_frontend() {
	$ret = edd_get_option( 'edd_commission_fees_enable_frontend_fees', false );
	return (bool) apply_filters( 'edd_commission_fees_global_enable_frontend', $ret );
}





/**
 * Is commission amount adjustment disabled?
 *
 * @since 1.0
 * @return bool $ret True if disabled, false otherwise
 */
function edd_commission_fees_fee_adjustment_disabled() {
	$ret = edd_get_option( 'edd_commission_fees_fee_adjustment_disabled', false );
	return (bool) apply_filters( 'edd_commission_fees_fee_adjustment_disabled', $ret );
}
