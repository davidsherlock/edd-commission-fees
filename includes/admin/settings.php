<?php
/**
 * Extension settings
 *
 * @package     EDD_Commission_Fees
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2018, Sell Comet
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add settings under "Downloads" > "Extensions" > "Commissions" for Commission Fees.
 *
 * @access    	public
 * @since     	1.0.0
 * @param     	array $commission_settings The array of settings for the Commissions settings page.
 * @return 		array $commission_settings The merged array of settings for the Commissions settings page.
 */
function eddcf_settings_commissions( $commission_settings ) {

	$commission_fee_settings = array(
		array(
			'id'      => 'edd_commission_fees_header',
			'name'    => '<strong>' . __( 'Fee Settings', 'edd-commission-fees' ) . '</strong>',
			'desc'    => '',
			'type'    => 'header',
			'size'    => 'regular',
		),
		array(
			'id'      => 'edd_commission_fees_default_rate',
			'name'    => __( 'Default rate', 'edd-commission-fees' ),
			'desc'    => sprintf( __( 'Enter the default fee rate recipients should be charged. This can be overwritten on a per-product basis. 0.25 = %s', 'edd-commission-fees' ), edd_currency_filter( edd_format_amount( 0.25 ) ) ),
			'type'    => 'text',
			'size'    => 'small',
		),
		array(
			'id'      => 'edd_commission_fees_allow_zero_value',
			'name'    => sprintf( __( 'Allow %s Commissions', 'edd-commission-fees' ), edd_currency_filter( edd_format_amount( 0.00 ) ) ),
			'desc'    => __( 'This option determines whether or not zero-value commissions are recorded if the commission fee is greater (or equal) to the original commission amount.', 'edd-commission-fees' ),
			'type'    => 'radio',
			'std'     => 'yes',
			'options' => array(
				'yes' => __( 'Yes, record zero value commissions', 'edd-commission-fees' ),
				'no'  => __( 'No, do not record zero value commissions', 'edd-commission-fees' ),
			),
			'tooltip_title' => __( 'Allow zero value commissions', 'edd-commission-fees' ),
			'tooltip_desc'  => sprintf( __( 'By default, to avoid negative commission values being recorded, if the calculated commission fee is greater (or equal) to the original commission amount, the recorded amount is set to %s. By disabling this option, the original commission amount is left untouched.', 'edd-commission-fees' ), edd_currency_filter( edd_format_amount( 0.00 ) ) ),
		),
		array(
			'id'      => 'edd_commission_fees_fee_adjustment_disabled',
			'name'    => __( 'Disable Fee Adjustment', 'edd-commission-fees' ),
			'desc'    => __( 'Check this box to disable the commission amount adjustment based on the calculated fee. Fees will still be calculated and recorded.', 'edd-commission-fees' ),
			'type'    => 'checkbox',
		),
		array(
			'id'      => 'edd_commission_fees_enable_shortcode_fees',
			'name'    => __( 'Enable Shortcode Fees', 'edd-commission-fees' ),
			'desc'    => __( 'Check this box to enable the display of the recorded fees within the commission shortcodes.', 'edd-commission-fees' ),
			'type'    => 'checkbox',
		),
  );

  return array_merge( $commission_settings, $commission_fee_settings );
}
add_filter( 'eddc_settings', 'eddcf_settings_commissions', 10, 1 );


/**
 * Adds the {fee} and {fee_amount} email tags to the template tag list
 *
 * @since       1.0.0
 * @access    	public
 * @param       array $tags The email template tags
 * @return      array $tags The merged email template tags
 */
function eddcf_settings_emails( $tags ) {
	$email_tags = array(
		array(
			'tag'         => 'fee',
			'description' => __( 'The fee charged on the commission amount', 'edd-commission-fees' ),
		),
		array(
			'tag'         => 'fee_rate',
			'description' => __( 'The commission fee rate', 'edd-commission-fees' ),
		),
	);

	return array_merge( $tags, $email_tags );
}
add_filter( 'eddc_email_template_tags', 'eddcf_settings_emails', 10, 1 );
