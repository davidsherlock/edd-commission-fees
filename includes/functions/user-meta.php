<?php
/**
 * User meta functions
 *
 * @package     EDD\CommissionFees
 * @subpackage  Admin/User
 * @copyright   Copyright (c) 2017, Sell Comet
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add fee rate and disable checkbox to user edit form
 *
 * @since  1.0.0
 * @param  object $user The user object
 * @return void
 */
function edd_commission_fees_user_meta( $user ) {
	?>
	<h3><?php _e('Easy Digital Downloads Commission Fees', 'edd-commission-fees'); ?></h3>
	<table class="form-table">
		<?php if ( current_user_can( 'manage_shop_settings' ) ) : ?>
		<tr>
			<th><label><?php _e('User\'s Commission Fee Rate', 'edd-commission-fees'); ?></label></th>
			<td>
				<input type="text" name="eddcf_user_fee_rate" id="eddcf_user_fee_rate" class="small-text" value="<?php echo get_user_meta( $user->ID, 'eddcf_user_fee_rate', true ); ?>" />
				<span class="description"><?php _e('Enter a global commission fee rate for this user. If a rate is not specified for a product, this rate will be used.', 'edd-commission-fees'); ?></span>
			</td>
		</tr>

		<tr>
			<th><label><?php _e('Disable Commission Fees', 'eddcf'); ?></label></th>
			<td>
				<input name="eddcf_disable_user_commission_fees" type="checkbox" id="eddcf_disable_user_commission_fees" value="1"<?php checked( get_user_meta( $user->ID, 'eddcf_disable_user_commission_fees', true ) ); ?> />
				<span class="description"><?php _e( 'Check this box if you wish to prevent commission fees from being charged to this user.', 'edd-commission-fees' ); ?></span>
			</td>
		</tr>
		<?php endif; ?>
	</table>
	<?php
}
add_action( 'show_user_profile', 'edd_commission_fees_user_meta' );
add_action( 'edit_user_profile', 'edd_commission_fees_user_meta' );


/**
 * Save the user meta/fields
 *
 * @since  1.0.0
 * @param  int $user_id The user ID
 * @return void
 */
function edd_commission_fees_save_user_meta( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}

	if ( current_user_can( 'manage_shop_settings' ) ) {

    if ( isset( $_POST['eddcf_disable_user_commission_fees'] ) ) {
      update_user_meta( $user_id, 'eddcf_disable_user_commission_fees', true );
    } else {
      delete_user_meta( $user_id, 'eddcf_disable_user_commission_fees' );
    }

		if ( ! empty( $_POST['eddcf_user_fee_rate'] ) ) {
			update_user_meta( $user_id, 'eddcf_user_fee_rate', sanitize_text_field( $_POST['eddcf_user_fee_rate'] ) );
		} else {
			delete_user_meta( $user_id, 'eddcf_user_fee_rate' );
		}

	}
}
add_action( 'personal_options_update', 'edd_commission_fees_save_user_meta' );
add_action( 'edit_user_profile_update', 'edd_commission_fees_save_user_meta' );
