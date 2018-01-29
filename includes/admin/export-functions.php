<?php
/**
 * Export Functions
 *
 * Helper functions for the bulk export process
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
 * Loads the commissions fees report batch process if needed
 *
 * @access    	public
 * @since       1.0.0
 * @param       string $class The class being requested to run for the batch export for details
 * @return      void
 */
function eddcf_include_commission_fees_report_batch_processor( $class ) {
	if ( 'EDD_Batch_Commission_Fees_Report_Export' === $class ) {
		require_once EDD_COMMISSION_FEES_DIR . 'includes/admin/classes/class-batch-export-commission-fees-report.php';
	}
}
