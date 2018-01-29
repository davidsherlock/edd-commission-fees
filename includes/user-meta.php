<?php
/**
 * User Meta Functions.
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
 * Add user profile "Fee Rate" field
 *
 * @access      public
 * @since       1.0.0
 * @param       object $user The user object
 * @global      object $post The WordPress post object for this download
 * @return      void
 */
function eddcf_add_user_profile_fee_field( $user ) {
	?>
	<?php if ( current_user_can( 'manage_shop_settings' ) ) : ?>
	<tr>
	  	<th><label><?php _e('User\'s Fee Rate', 'edd-commission-fees'); ?></label></th>
		<td>
			<input type="text" name="eddcf_user_fee_rate" id="eddcf_user_fee_rate" class="small-text" value="<?php echo esc_attr( get_user_meta( $user->ID, 'eddcf_user_fee_rate', true ) ); ?>" />
			<span class="description"><?php _e('Enter a global commission fee rate for this user. If a rate is not specified for a product, this rate will be used.', 'edd-commission-fees'); ?></span>
		</td>
	</tr>
	<tr>
		<th><label><?php _e( 'Disable Fees', 'edd-commission-fees' ); ?></label></th>
		<td>
			<input type="checkbox" name="eddcf_disable_user_commission_fees" id="eddcf_disable_user_commission_fees" value="1"<?php checked( get_user_meta( $user->ID, 'eddcf_disable_user_commission_fees', true ) ); ?> />
			<span class="description"><?php _e( 'Check this box if you wish to prevent commission fees from being charged to this user.', 'edd-commission-fees' ); ?></span>
		</td>
	</tr>
	<?php endif; ?>
	<?php
}
add_action( 'eddc_user_profile_table_end', 'eddcf_add_user_profile_fee_field', 10, 1 );


/**
 * Santize and save user data when save_post is called
 *
 * @access      public
 * @since       1.0.0
 * @param       int $post_id The ID of the post being saved
 * @global      object $post The WordPress post object for this download
 * @return      void
 */
function eddcf_save_user_profile_fee_field( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}

	if ( current_user_can( 'manage_shop_settings' ) ) {
		if ( ! empty( $_POST['edd_commission_fees_user_rate'] ) ) {
			update_user_meta( $user_id, 'eddcf_user_fee_rate', sanitize_text_field( $_POST['edd_commission_fees_user_rate'] ) );
		} else {
			delete_user_meta( $user_id, 'eddcf_user_fee_rate' );
		}

		if ( isset( $_POST['eddcf_disable_user_commission_fees'] ) ) {
			update_user_meta( $user_id, 'eddcf_disable_user_commission_fees', true );
		} else {
			delete_user_meta( $user_id, 'eddcf_disable_user_commission_fees' );
		}
	}
}
add_action( 'personal_options_update', 'eddcf_save_user_profile_fee_field', 10, 1 );
add_action( 'edit_user_profile_update', 'eddcf_save_user_profile_fee_field', 10, 1 );
