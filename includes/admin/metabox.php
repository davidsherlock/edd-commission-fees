<?php
/**
 * Metabox functions
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
 * Add Commissions meta box verification nonce
 *
 * @since       1.0.0
 * @param       int $post_id The ID of the post being saved
 * @global      object $post The WordPress post object for this download
 * @return      void
 */
function eddcf_commissions_meta_box_nonce( $post_id ) {
	?>
	<input type="hidden" name="edd_download_commission_meta_box_fees_nonce" value="<?php echo wp_create_nonce( basename( __FILE__ ) ); ?>" />
	<?php
}
add_action( 'eddc_metabox_before_options', 'eddcf_commissions_meta_box_nonce', 10, 1 );


/**
 * Add filterable "Fee Type" options to Commissions Meta Box
 *
 * @since       1.0.0
 * @param       int $post_id The ID of the post being saved
 * @global      object $post The WordPress post object for this download
 * @return      void
 */
function eddcf_commissions_meta_box_fee_type_options( $post_id ) {
	$enabled = get_post_meta( $post_id, '_edd_commisions_enabled', true ) ? true : false;
	$meta    = get_post_meta( $post_id, '_edd_commission_fee_settings', true );
	$type    = isset( $meta['type'] ) ? $meta['type'] : 'flat';
	$display = $enabled ? '' : ' style="display:none";';

	?>
	  <tr <?php echo $display; ?> class="eddc_toggled_row" id="edd_commission_fees_type_wrapper">
			<td class="edd_field_type_select">
				<label for="edd_commission_fee_settings[type]"><strong><?php _e( 'Fee Type:', 'edd-commission-fees' ); ?></strong></label>
				<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<strong><?php _e( 'Type', 'edd-commission-fees' ); ?></strong>: <?php _e( 'With commissions enabled, you will need to specify who to assign commission fees to. Commission fees can ether be a flat amount or percentage based.', 'edd-commission-fees' ); ?>"></span><br/>
				<p><?php

				// Filter in the types of commission fees there could be.
				$commission_types = apply_filters( 'edd_commission_fee_types', array(
					'flat'             => __( 'Flat', 'edd-commission-fees' ),
					'percentage'       => __( 'Percentage', 'edd-commission-fees' ),
				) );

				foreach ( $commission_types as $commission_type => $commission_pretty_string ) {
					?>
					<span class="edd-commission-type-wrapper" id="edd_commission_fee_type_<?php echo $commission_type; ?>_wrapper">
						<input id="edd_commission_fee_type_<?php echo esc_attr( $commission_type ); ?>" type="radio" name="edd_commission_fee_settings[type]" value="<?php echo esc_attr( $commission_type ); ?>" <?php checked( $type, $commission_type, true ); ?>/>
						<label for="edd_commission_fee_type_<?php echo esc_attr( $commission_type ); ?>"><?php echo esc_attr( $commission_pretty_string ); ?></label>
					</span>
					<?php
				}
				?>
				</p>
				<p><?php _e( 'Select the type of commission(s) fees to record.', 'edd-commission-fees' ); ?></p>
			</td>
		</tr>
	<?php
}
add_action( 'eddc_metabox_options_table_after', 'eddcf_commissions_meta_box_fee_type_options', 10, 1 );


/**
 * Add "Fee" table header and tooltip description
 *
 * @since       1.0.0
 * @param       int $post_id The ID of the post being saved
 * @global      object $post The WordPress post object for this download
 * @return      void
 */
function eddcf_commissions_meta_box_fee_table_header( $post_id ) {
	?>
	<th class="eddc-commission-rate-fee">
	<?php _e( 'Fee', 'edd-commission-fees' ); ?>
	<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<strong> <?php _e( 'Fee', 'edd-commission-fees' ); ?></strong>:&nbsp;
		<?php _e( 'Enter the flat or percentage rate fee for each user. If no rate is entered, the default rate for the user will be used. If no user rate is set, the global default rate will be used. Currency and percent symbols are not required.', 'edd-commission-fees' ); ?>">
	</span>
	</th>
	<?php
}
add_action( 'eddc_meta_box_table_header_after', 'eddcf_commissions_meta_box_fee_table_header', 10, 1 );


/**
 * Add 'initialization' fee field/cell for when _edd_commission_settings meta is empty
 *
 * @since       1.0.0
 * @param       int $post_id The ID of the post being saved
 * @global      object $post The WordPress post object for this download
 * @return      void
 */
function eddcf_commissions_meta_box_empty_fee_field( $post_id ) {
	?>
	<td>
		<input type="text" name="edd_commission_fee_settings[fees][1][fee]" id="edd_commission_fee_1" placeholder=" <?php _e( 'Fee for this user', 'edd-commission-fees' ); ?>"/>
	</td>
	<?php
}
add_action( 'eddc_meta_box_table_cell_remove_before', 'eddcf_commissions_meta_box_empty_fee_field', 10, 1 );


/**
 * Add fee table fields/cells when _edd_commission_threshold_settings meta is not empty
 *
 * @since       1.0.0
 * @param       int $post_id The ID of the post being saved
 * @param       int $key The commissions meta box table row key
 * @global      array $value The array containing the threshold value
 * @return      void
 */
function eddcf_commissions_meta_box_fee_fields( $post_id, $key, $value ) {
	?>
	<td>
		<input type="text" class="edd-commissions-rate-field" name="edd_commission_fee_settings[fees][<?php echo esc_attr( $key ); ?>][fee]" id="edd_commission_fee_<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value['fee'] ); ?>" placeholder="<?php _e( 'Fee for this user', 'edd-commission-fees' ); ?>"/>
	</td>
	<?php
}
add_action( 'eddc_meta_box_table_cell_rates_remove_before', 'eddcf_commissions_meta_box_fee_fields', 10, 3 );


/**
 * Add "fee" rates to original rates array so the table can be rendered correctly.
 *
 * @since       1.0.0
 * @param       int $post_id The ID of the post being saved
 * @global      object $post The WordPress post object for this download
 * @return      void
 */
function eddcf_filter_commissions_meta_box_rates_query_args( $rates, $users, $i ) {
	global $post;

	$meta = get_post_meta( $post->ID, '_edd_commission_fee_settings', true );
	$fees = isset( $meta['fee'] ) ? $meta['fee'] : '';
	$fees = ! empty( $fees ) ? array_map( 'trim', explode( ',', $fees ) ) : array();
	$rates['fee'] = array_key_exists( $i, $fees ) ? $fees[ $i ] : '';

	return $rates;
}
add_filter( 'eddc_render_commissions_meta_box_rates_args', 'eddcf_filter_commissions_meta_box_rates_query_args', 10, 3 );


/**
 * Save data when save_post is called
 *
 * @since       1.0.0
 * @param       int $post_id The ID of the post being saved
 * @global      object $post The WordPress post object for this download
 * @return      void
 */
function eddcf_save_commissions_meta_box_fee_fields( $post_id ) {
	global $post;

	// Verify nonce
	if ( ! isset( $_POST['edd_download_commission_meta_box_fees_nonce'] ) || ! wp_verify_nonce( $_POST['edd_download_commission_meta_box_fees_nonce'], basename( __FILE__ ) ) ) {
		   return $post_id;
	}

	// Check for auto save / bulk edit
	if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
		return $post_id;
	}

	// Verify the post type is 'download'
	if ( isset( $_POST['post_type'] ) && 'download' != $_POST['post_type'] ) {
		return $post_id;
	}

	// Verify the user has premission to edit the product
	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return $post_id;
	}


	if ( isset( $_POST['edd_commisions_enabled'] ) ) {

		$new  = isset( $_POST['edd_commission_fee_settings'] ) ? $_POST['edd_commission_fee_settings'] : false;
		$type = ! empty( $_POST['edd_commission_fee_settings']['type'] ) ? $_POST['edd_commission_fee_settings']['type'] : 'flat';

		// Recursively santize fields
		if ( ! empty( $_POST['edd_commission_fee_settings'] ) && is_array( $_POST['edd_commission_fee_settings'] ) ) {
			$new = $_POST['edd_commission_fee_settings'];
			array_walk_recursive( $new, 'sanitize_text_field', wp_unslash( $_POST['edd_commission_fee_settings'] ) );
		} else {
			$new = false;
		}

		if ( ! empty( $_POST['edd_commission_fee_settings']['fees'] ) && is_array( $_POST['edd_commission_fee_settings']['fees'] ) ) {
			$users = array();
			$fees  = array();

			// Get the fee values
			foreach( $_POST['edd_commission_fee_settings']['fees'] as $rate ) {
				$fees[] = $rate['fee'];
			}

			// Get the user ids
			foreach( $_POST['edd_commission_settings']['rates'] as $rate ) {
				$users[] = $rate['user_id'];
			}

			$new['user_id'] = implode( ',', $users );
			$new['fee']  	= implode( ',', $fees );

			// No need to store this value since we're saving as a string
			unset( $new['fees'] );
		}

		if ( $new ) {
			if ( ! empty( $new['user_id'] ) ) {
				$new['fee'] = str_replace( '%', '', $new['fee'] );
				$new['fee'] = str_replace( '$', '', $new['fee'] );

				$values           = explode( ',', $new['fee'] );
				$sanitized_values = array();

				foreach ( $values as $key => $value ) {

					switch ( $type ) {
						case 'flat':
							$value = $value < 0 || ! is_numeric( $value ) ? '' : $value;
							$value = round( $value, edd_currency_decimal_filter() );
							break;
						case 'percentage':
						default:
							if ( $value < 0 || ! is_numeric( $value ) ) {
								$value = '';
							}

							$value = ( is_numeric( $value ) && $value < 1 ) ? $value * 100 : $value;
							if ( is_numeric( $value ) ) {
								$value = round( $value, 2 );
							}

							break;
					}

					$sanitized_values[ $key ] = $value;

				}

				$new_values    	= implode( ',', $sanitized_values );
				$new['fee'] 	= trim( $new_values );
			}
		}
		update_post_meta( $post_id, '_edd_commission_fee_settings', $new );
	}
}
add_action( 'save_post', 'eddcf_save_commissions_meta_box_fee_fields', 10, 1 );
