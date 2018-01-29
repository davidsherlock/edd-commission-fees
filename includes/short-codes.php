<?php
/**
 * Short Code Functions.
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
 * Callback function for the [edd_commission_fees_overview] shortcode
 *
 * @access      public
 * @since       1.0.0
 * @param       array $atts Attributes from the Shotcode
 * @return      string The HTML markup for the commissions overview shortcode
 */
function eddcf_user_commission_fees_overview_shortcode( $atts ) {
	$user_id = eddc_userid_from_shortcode_atts( $atts );

	return eddcf_user_commission_fees_overview( $user_id );
}
add_shortcode( 'edd_commission_fees_overview', 'eddcf_user_commission_fees_overview_shortcode' );


/**
 * Given a User ID, return the markup for the [edd_commission_fees_overview] shortcode
 *
 * @access      public
 * @since       1.0.0
 * @param       integer $user_id User ID to get the commissions overview for
 * @return      string HTML markup for the overview
 */
function eddcf_user_commission_fees_overview( $user_id = 0 ) {
	$user_id = empty ( $user_id ) ? get_current_user_id() : $user_id;

	// If still empty, exit
	if ( empty( $user_id ) ) {
		return;
	}

	$unpaid_commission_fees  = eddcf_get_unpaid_fee_totals( array( 'user_id' => $user_id ) );
	$paid_commission_fees    = eddcf_get_paid_fee_totals( array( 'user_id' => $user_id ) );
	$revoked_commission_fees = eddcf_get_revoked_fee_totals( array( 'user_id' => $user_id ) );

	$stats = '';

	ob_start(); ?>
		<div id="edd_user_commission_fees_overview">

			<?php do_action( 'eddcf_before_commission_fees_overview', $user_id ); ?>

			<h3><?php _e( 'Commission Fees Overview', 'edd-commission-fees' ); ?></h3>
			<table>
				<thead>
					<?php do_action( 'eddcf_before_commission_fees_overview_table_head', $user_id ); ?>
					<th><?php _e( 'Unpaid Fees', 'edd-commission-fees' ); ?></th>
					<th><?php _e( 'Paid Fees', 'edd-commission-fees' ); ?></th>
					<th><?php _e( 'Revoked Fees', 'edd-commission-fees' ); ?></th>
					<?php do_action( 'eddcf_after_commission_fees_overview_table_head', $user_id ); ?>
				</thead>
				<tbody>
					<?php if ( eddc_user_has_commissions( $user_id ) ) : ?>
					<tr>
						<?php do_action( 'eddcf_before_commission_fees_overview_table_row', $user_id ); ?>
						<td><?php echo edd_currency_filter( edd_format_amount( $unpaid_commission_fees ) ); ?></td>
						<td><?php echo edd_currency_filter( edd_format_amount( $paid_commission_fees ) ); ?></td>
						<td><?php echo edd_currency_filter( edd_format_amount( $revoked_commission_fees ) ); ?></td>
						<?php do_action( 'eddcf_after_commission_fees_overview_table_row', $user_id ); ?>
					</tr>
					<?php else: ?>
					<tr>
						<td colspan="3"><?php _e( 'No commissions found', 'edd-commission-fees' ); ?></td>
					</tr>
					<?php endif; ?>
				</tbody>
			</table>

			<?php do_action( 'eddcf_after_commission_fees_overview', $user_id ); ?>

		</div>
	<?php

	$stats = apply_filters( 'eddcf_user_commission_fees_overview_display', ob_get_clean() );
	return $stats;
}


/**
 * Commissions [edd_commissions] shortcode - table header
 *
 * @access      public
 * @since       1.0.0
 * @access      public
 * @return      void
 */
function eddcf_user_commissions_fee_table_header() {
	?>

	<th class="edd_commission_fee"><?php _e('Fee', 'edd-commission-fees'); ?></th>

	<?php
}


/**
 * Commissions [edd_commissions] shortcode - table header
 *
 * @access      public
 * @since       1.0.0
 * @access      public
 * @param		object $commission commission object which use to get the meta
 * @return      void
 */
function eddcf_user_commissions_fee_table_row( $commission ) {
	$meta = $commission->get_meta( '_edd_commission_fees', true ); ?>

	<td class="edd_commission_fee"><?php echo html_entity_decode( edd_currency_filter( edd_format_amount( $meta['fee'] ) ) ); ?></td>

	<?php
}
