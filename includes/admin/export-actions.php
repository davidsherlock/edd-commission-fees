<?php
/**
 * Export Actions
 *
 * These are actions related to exporting data from EDD Commissions.
 *
 * @package     EDD_Commission_Fees
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2018, Sell Comet
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Register the commissions fees report details batch exporter
 *
 * @since       1.0.0
 * @return      void
 */
function eddcf_register_commission_fees_report_batch_export() {
	add_action( 'edd_batch_export_class_include', 'eddcf_include_commission_fees_report_batch_processor', 10, 1 );
}
add_action( 'edd_register_batch_exporter', 'eddcf_register_commission_fees_report_batch_export', 1 );
