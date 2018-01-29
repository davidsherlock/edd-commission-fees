<?php
/**
 * Email Functions.
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
 * Parse and render the email tags within the message contents
 *
 * @access      public
 * @since       1.0.0
 * @param		string $message - the email body/message
 * @param		integer $used_id - the commission recipient user_id
 * @param		float $commission_amount - the base commission amount
 * @param		float $rate - the commission rate
 * @param		integer $download_id - the download id
 * @param		integer $commission_id - the commission id
 * @return      string $message - the modified email body/message
 */
function eddcf_parse_template_tags( $message, $user_id, $commission_amount, $rate, $download_id, $commission_id ) {

	$fee_type = eddcf_get_commission_fee_type( $download_id );
	$fee_rate = eddcf_get_recipient_rate( $download_id, $user_id );
	$base_amount = eddcf_calc_base_commission_amount( $commission_id );
	$fee_amount = eddcf_calc_commission_fee( $base_amount, $fee_rate, $fee_type );

	$message = str_replace( '{fee}', html_entity_decode( edd_currency_filter( edd_format_amount( $fee_amount ) ) ), $message );
	$message = str_replace( '{fee_rate}', wp_specialchars_decode( eddc_format_rate( $fee_rate, $fee_type ), ENT_QUOTES ), $message );

	return $message;
}
add_filter( 'eddc_sale_alert_email', 'eddcf_parse_template_tags', 10, 6 );
