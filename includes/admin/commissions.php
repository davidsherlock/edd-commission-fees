<?php
/**
 * Commissions
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
 * View a commission fee
 *
 * @since       1.0.0
 * @param       object $commission The commission object being displayed
 * @return      void
 */
function edd_commission_fees_single_view( $commission ) {
	if ( ! $commission ) {
		echo '<div class="info-wrapper item-section">' . __( 'Invalid commission specified.', 'edd-commission-fees' ) . '</div>';
		return;
	}

	$base = admin_url( 'edit.php?post_type=download&page=edd-commissions&view=edd-commission-fees&commission=' . $commission->id );
	$base = wp_nonce_url( $base, 'eddcf_commission_nonce' );

	$commission_fees = $commission->get_meta( '_edd_commission_fees', true );
	$commission_status = isset( $commission_fees['status'] ) ? $commission_fees['status'] : 'unpaid';
	$base_amount = eddcf_get_commission_fee_base_amount( $commission->id );

	// Check commission record has meta data assigned
	if( empty( $commission_fees ) ){
	    ?><div id="customer-tables-wrapper" class="customer-section">
		<h3><?php echo __( 'Commission Fees', 'edd-commission-fees' ); ?></h3>
	    <p><?php echo __( 'This commission record does not have fees.', 'edd-commission-fees' ); ?></p>
	    </div>
	    <?php return;
	}

	do_action( 'eddcf_commission_card_top', $commission->id );
	?>
	<div class="info-wrapper item-section">
		<form id="edit-item-info" method="post" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-commissions&view=edd-commission-fees&commission=' . $commission->id ); ?>">
			<div class="item-info">
				<table class="widefat striped">
					<tbody>
						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Commission ID', 'edd-commission-fees' ); ?></label>
							</td>
							<td style="word-wrap: break-word">
								<?php echo esc_attr( $commission->id ); ?>
							</td>
						</tr>
						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Amount', 'edd-commission-fees' ); ?></label>
							</td>
							<td style="word-wrap: break-word">
								<?php echo esc_attr( edd_currency_filter( edd_format_amount( $commission->amount ) ) ); ?>
							</td>
						</tr>
						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Fee Amount', 'edd-commission-fees' ); ?></label>
							</td>
							<td style="word-wrap: break-word">
								<?php echo esc_attr( edd_currency_filter( edd_format_amount( $commission_fees['fee'] ) ) ); ?>
								<input type="text" name="eddcf_amount" class="hidden eddc-commission-amount" value="<?php echo esc_attr( edd_format_amount( $commission_fees['fee'] ) ); ?>" />
							</td>
						</tr>
						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Fee Status', 'edd-commission-fees' ); ?></label>
							</td>
							<td style="word-wrap: break-word">
								<?php echo esc_attr( ucfirst( $commission_status ) ); ?>
							</td>
						</tr>
						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Fee Rate', 'edd-commission-fees' ); ?></label>
							</td>
							<td style="word-wrap: break-word">
								<?php echo esc_attr( eddc_format_rate( $commission_fees['rate'], $commission_fees['type'] ) ); ?>
								<input type="text" name="eddcf_rate" class="hidden eddc-commission-rate" value="<?php echo esc_attr( $commission_fees['rate'] ); ?>" />
							</td>
						</tr>
						<tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Base Amount', 'edd-commission-fees' ); ?></label>
							</td>
							<td style="word-wrap: break-word">
								<?php echo esc_attr( edd_currency_filter( edd_format_amount( $commission_fees['base'] ) ) ); ?>
							</td>
						</tr>
							<td class="row-title">
								<label for="tablecell"><?php _e( 'Actions:', 'edd-commission-fees' ); ?></label>
							</td>
							<td class="eddc-commission-card-actions">
								<?php
								$actions = array(
									'edit' => '<a href="#" class="eddc-edit-commission">' . __( 'Edit Commission', 'edd-commission-fees' ) . '</a>'
								);
								$base    = admin_url( 'edit.php?post_type=download&page=edd-commissions&view=edd-commission-fees&commission=' . $commission->id );
								$base    = wp_nonce_url( $base, 'eddcf_commission_nonce' );

								if ( 'unpaid' == $commission->status ) {
									$actions['recalculate_fee'] = sprintf( '<a href="%s&action=%s">' . __( 'Recalculate Fee', 'edd-commission-fees' ) . '</a>', $base, 'recalculate_fee' );
								}

								if ( 'revoked' !== $commission_status && 'unpaid' == $commission->status && $base_amount !== $commission->amount ) {
									$actions['revoke_fee'] = sprintf( '<a href="%s&action=%s">' . __( 'Revoke Fee', 'edd-commission-fees' ) . '</a>', $base, 'revoke_fee' );
								}

								$actions = apply_filters( 'eddcf_commission_details_actions', $actions, $commission->id );

								if ( ! empty( $actions ) ) {
									$count = count( $actions );
									$i     = 1;

									foreach ( $actions as $action ) {
										echo $action;

										if ( $i < $count ) {
											echo '&nbsp;|&nbsp;';
											$i++;
										}
									}
								} else {
									_e( 'No actions available for this commission', 'edd-commission-fees' );
								}
								?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div id="item-edit-actions" class="edit-item" style="float: right; margin: 10px 0 0; display: block;">
				<?php wp_nonce_field( 'eddcf_update_commission', 'eddcf_update_commission_nonce' ); ?>
				<input type="submit" name="eddc_update_commission" id="eddc_update_commission" class="button button-primary" value="<?php _e( 'Update Commission', 'edd-commission-fees' ); ?>" />
				<input type="hidden" name="commission_id" value="<?php echo esc_attr( absint( $commission->id ) ); ?>" />
			</div>
			<div class="clear"></div>
		</form>
	</div>

	<?php
	do_action( 'eddcf_commission_card_bottom', $commission->id );
}
