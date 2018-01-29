<?php
/**
 * Export Filters
 *
 * These are filters related to exporting data from EDD Commissions.
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
 * Add the "Fees" report type to the commissions reporting options.
 *
 * @access      public
 * @since       1.0.0
 * @param       array $options containing the registered "Fees" report type.
 * @return      array
 */
function eddcf_commissions_export_classes( $options ) {
    $options['EDD_Batch_Commission_Fees_Report_Export'] = __( 'Fees', 'edd-commission-fees' );

    return $options;
}
add_filter( 'eddc_export_classes', 'eddcf_commissions_export_classes', 10, 1 );


/**
 * Append "Fees" report type details to the tooltip description.
 *
 * @access      public
 * @since       1.0.0
 * @param       string $tooltip_desc string
 * @return      string
 */
function eddcf_commissions_export_tooltip_description( $tooltip_desc ) {
    $tooltip_desc  .= __( '<p><strong>Fees Report</strong><br />Provides a list of all commission records for the dates and status selected. Includes fee details for segmentation, reporting and accounting purposes.</p>', 'edd-commission-fees' );

    return $tooltip_desc;
}
add_filter( 'eddc_report_types_tooltip_desc', 'eddcf_commissions_export_tooltip_description' );
