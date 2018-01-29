<?php
/**
 * Add Commission Fees to the EDD Commissions Customer Interface
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
 * Customer commission view fees addition
 *
 * @access		public
 * @since       1.0.0
 * @param       object $customer The customer object
 * @return      void
 */
function eddcf_customer_commissions_view( $customer ) {
	$unpaid_commission_fees  = eddcf_get_unpaid_fee_totals( array( 'user_id' => $customer->user_id ) );
	$paid_commission_fees    = eddcf_get_paid_fee_totals( array( 'user_id' => $customer->user_id ) );
	$revoked_commission_fees = eddcf_get_revoked_fee_totals( array( 'user_id' => $customer->user_id ) );

	?>

	<div id="edd-item-tables-wrapper" class="customer-section">

		<h3><?php _e( 'Commission Fees', 'edd' ); ?></h3>
		<table class="wp-list-table widefat striped payments">
			<thead>
				<tr>
					<?php do_action( 'eddcf_before_customer_commissions_view_table_head', $user_id ); ?>
					<th><?php _e( 'Unpaid Fees', 'edd-commission-fees' ); ?></th>
					<th><?php _e( 'Paid Fees', 'edd-commission-fees' ); ?></th>
					<th><?php _e( 'Revoked Fees', 'edd-commission-fees' ); ?></th>
					<?php do_action( 'eddcf_after_customer_commissions_view_table_head', $user_id ); ?>
				</tr>
			</thead>
			<tbody>
				<?php if ( eddc_user_has_commissions( $customer->user_id ) ) : ?>
				<tr>
					<?php do_action( 'eddcf_before_customer_commissions_view_table_row', $customer->user_id ); ?>
					<td><?php echo esc_attr( edd_currency_filter( edd_format_amount( $unpaid_commission_fees ) ) ); ?></td>
					<td><?php echo esc_attr( edd_currency_filter( edd_format_amount( $paid_commission_fees ) ) ); ?></td>
					<td><?php echo esc_attr( edd_currency_filter( edd_format_amount( $revoked_commission_fees ) ) ); ?></td>
					<?php do_action( 'eddcf_after_customer_commissions_view_table_row', $customer->user_id ); ?>
				</tr>
				<?php else: ?>
				<tr>
					<td colspan="3"><?php _e( 'No commission fees found', 'edd-commission-fees' ); ?></td>
				</tr>
				<?php endif; ?>
			</tbody>
		</table>

	</div>

	<?php
}
add_action( 'eddc_customer_commissions_view_unpaid_table_after', 'eddcf_customer_commissions_view', 10, 1 );
