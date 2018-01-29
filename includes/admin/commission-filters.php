<?php
/**
 * Commissions Fee Filters
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
 * Register a tab in the single commission view for Commission Fees information.
 *
 * @since  1.0.0
 * @param  array $views An array of existing views
 * @return array $tabs The altered list of views
 */
function eddcf_commissions_tab( $tabs ) {
	$tabs['edd-commission-fees'] = array( 'dashicon' => 'dashicons-chart-pie', 'title' => __( 'Fee', 'edd-commission-fees' ) );

	return $tabs;
}
add_filter( 'eddc_commission_tabs', 'eddcf_commissions_tab' );


/**
 * Register a view in the single commission view for Commission Fees information.
 *
 * @since  1.0.0
 * @param  array $views An array of existing views
 * @return array $views The altered list of views
 */
function eddcf_commissions_view( $views ) {
	$views['edd-commission-fees'] = 'edd_commission_fees_single_view';

	return $views;
}
add_filter( 'eddc_commission_views', 'eddcf_commissions_view' );
