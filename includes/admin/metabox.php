<?php
/**
 * Metabox functions
 *
 * @package     EDD\CommissionFees
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2017, Sell Comet
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

 // Exit if accessed directly
 if ( ! defined( 'ABSPATH' ) ) {
 	exit;
 }


/**
 * Register the commission fees settings metabox
 *
 * @since 1.0.0
 * @return void
 */
function edd_commission_fees_add_download_meta_box() {

	$post_types = apply_filters( 'edd_commission_fees_metabox_post_types' , array( 'download' ) );

	foreach ( $post_types as $post_type ) {

		/** Commission Fees Settings **/
		add_meta_box( 'edd_commission_fees_settings', __( 'Commission Fees', 'edd-commission-fees' ),  'edd_render_commission_fees_settings_meta_box', $post_type, 'side', 'default' );

	}
}
add_action( 'add_meta_boxes', 'edd_commission_fees_add_download_meta_box' );


/**
 * Commission Fees Settings Metabox
 *
 * @since 1.0.0
 * @return void
 */
function edd_render_commission_fees_settings_meta_box() {
	global $post;

	/*
	 * Output the files fields
	 * @since 1.0.0
	 */
	do_action( 'edd_commission_fees_meta_box_settings_fields', $post->ID );
}

/**
 * Commission Fees Rate Row
 *
 * @since 1.0.0
 * @param int $post_id Download (Post) ID
 * @return void
 */
function edd_commission_fees_render_download_fee_rate_row( $post_id ) {
	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	$commission_rate = edd_commission_fees_get_download_rate( $post_id );

?>
	<div id="edd_download_limit_wrap">
		<p><strong><?php _e( 'Fee Rate:', 'edd-commission-fees' ); ?></strong></p>
		<label for="edd_commission_fees_rate">
			<?php echo EDD()->html->text( array(
				'name'  => '_edd_commission_fee_rate',
				'value' => $commission_rate,
				'class' => 'small-text'
			) ); ?>
			<?php _e( 'Leave blank or 0 for global setting', 'edd-commission-fees' ); ?>
		</label>
		<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Commission Fee Rate</strong>: Enter the default fee rate recipients should be charged for this product (10 = 10%). This field inherits the fee type defined in the global settings.', 'edd-commission-fees' ); ?>"></span>
	</div>
<?php
}
add_action( 'edd_commission_fees_meta_box_settings_fields', 'edd_commission_fees_render_download_fee_rate_row', 20 );



/**
 * Disable commission fees row
 *
 * Outputs the option to disable fees on a product
 *
 * @since 1.0.0
 * @param int $post_id Download (Post) ID
 * @return void
 */
function edd_commission_fees_render_download_disable_row( $post_id = 0 ) {
	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	$fee_disabled = edd_commission_fees_download_fee_disabled( $post_id );
?>
	<p><strong><?php _e( 'Disable Fees:', 'edd-commission-fees' ); ?></strong></p>
	<label for="edd_commission_fees_disabled">
		<?php echo EDD()->html->checkbox( array(
			'name'    => '_edd_commission_fee_disabled',
			'current' => $fee_disabled
		) ); ?>
		<?php _e( 'Check this box to disable fees being charged for this download', 'edd-commission-fees' ); ?>
	</label>
<?php
}
add_action( 'edd_commission_fees_meta_box_settings_fields', 'edd_commission_fees_render_download_disable_row', 30 );


/**
 * Add our fields above to the $fields save array
 *
 * @since 1.0.0
 * @return array $fields Array of fields.
 */
function edd_commission_fees_settings_metabox_fields_save( $fields ) {
  $fields[] = '_edd_commission_fee_rate';
	$fields[] = '_edd_commission_fee_disabled';

  return $fields;
}
add_filter( 'edd_metabox_fields_save', 'edd_commission_fees_settings_metabox_fields_save', 10, 1 );
