<?php
/**
 * Extension settings
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
 * Registers the subsection for EDD Settings
 *
 * @since       1.0.0
 * @param       array $sections The sections
 * @return      array Sections with commission fees added
 */
function edd_commission_fees_settings_section_extensions( $sections ) {
	$sections['commission_fees'] = __( 'Commission Fees', 'edd-commission-fees' );
	return $sections;
}
add_filter( 'edd_settings_sections_extensions', 'edd_commission_fees_settings_section_extensions' );


/**
 * Registers the new Commission Fees options in Extensions
 *
 * @since       1.0.0
 * @param       $settings array the existing plugin settings
 * @return      array The new EDD settings array with commissions added
 */
function edd_commission_fees_settings_extensions( $settings ) {
	$type_options = apply_filters( 'edd_commission_fees_settings_type_options', array(
		'percentage'      => __( 'Percentage', 'edd-commission-fees' ),
		'flat'     				=> __( 'Flat amount', 'edd-commission-fees' ),
	) );

	$commission_fees_settings = array(
		array(
			'id'      => 'edd_commission_fees_header',
			'name'    => '<strong>' . __( 'Fee Settings', 'edd-commission-fees' ) . '</strong>',
			'desc'    => '',
			'type'    => 'header',
			'size'    => 'regular',
		),
		array(
			'id'      => 'edd_commission_fees_disabled',
			'name'    => __( 'Disable Fees', 'edd-commission-fees' ),
			'desc'    => __( 'Check this box to disable fees being charged to commission recipients.', 'edd-commission-fees' ),
			'type'    => 'checkbox',
		),
		array(
			'id'      => 'edd_commission_fees_default_rate',
			'name'    => __( 'Default Rate', 'edd-commission-fees' ),
			'desc'    => __( 'Enter the default fee rate recipients should be charged. This can be overwritten on a per-product basis. 10 = 10%', 'edd-commission-fees' ),
			'type'    => 'number',
			'size'    => 'small',
			'step'    => '0.01',
			'min'     => '0',
		),
		array(
			'id'      => 'edd_commission_fees_type',
			'name'    => __( 'Type', 'edd-commission-fees' ),
			'desc'    => __( 'Should the commission fee be charged as a percentage or flat amount fee?', 'edd-commission-fees' ),
			'type'    => 'select',
			'options' => $type_options,
		),
		array(
			'id'      => 'edd_commission_fees_calc_base',
			'name'    => __( 'Calculation Base', 'edd-commission-fees' ),
			'desc'    => __( 'This option determines whether or not fees are calculated on an exclusive or inclusive basis.', 'edd-commission-fees' ),
			'type'    => 'radio',
			'std'     => 'yes',
			'options' => array(
				'yes' => __( 'Yes, calculate fees exclusively', 'edd-commission-fees' ),
				'no'  => __( 'No, calculate fees inclusively', 'edd-commission-fees' ),
			),
			'tooltip_title' => __( 'Calculation Base', 'edd-commission-fees' ),
			'tooltip_desc'  => sprintf( __( 'By default, fees are calculated exclusively based on the recorded commission amount. For example an amount of %s with a 5%% fee added would equal %s ((5 * .05) + 5)), however because a fee is being charged, the original amount (5 - .25) would be decreased leaving %s. Inclusive calculation base functions in the same manner as inclusive taxation, where the fee is subsumed within the original amount (for example %s with a 25%% fee included would result in an adjusted amount of %s). This setting is not used when using the flat amount type.', 'edd-commission-fees' ), edd_currency_filter( edd_format_amount( 5.00 ) ), edd_currency_filter( edd_format_amount( 5.25 ) ), edd_currency_filter( edd_format_amount( 4.75 ) ), edd_currency_filter( edd_format_amount( 5.00 ) ), edd_currency_filter( edd_format_amount( 4.00 ) ) ),
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
			'tooltip_desc'  => sprintf( __( 'By default, to avoid negative commission values being recorded, if the calculated commission fee is greater (or equal) to the base commission amount, the recorded amount is set to %s. By disabling this option, the base commission amount is left untouched.', 'edd-commission-fees' ), edd_currency_filter( edd_format_amount( 0.00 ) ) ),
		),
		array(
			'id'      => 'edd_commission_fees_fee_adjustment_disabled',
			'name'    => __( 'Disable Fee Adjustment', 'edd-commission-fees' ),
			'desc'    => __( 'Check this box to disable the commission amount adjustment based on the calculated fee. Fees will still be calculated and displayed within exported report.', 'edd-commission-fees' ),
			'type'    => 'checkbox',
		),
		array(
			'id'      => 'edd_commission_fees_enable_frontend_fees',
			'name'    => __( 'Enable Frontend Fees', 'edd-commission-fees' ),
			'desc'    => __( 'Check this box to enable the display of the recorded fees within the commissions shortcodes.', 'edd-commission-fees' ),
			'type'    => 'checkbox',
		),
	);

	$commission_fees_settings = apply_filters( 'edd_commission_fees_settings', $commission_fees_settings );

	if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
		$commission_fees_settings = array( 'commission_fees' => $commission_fees_settings );
	}

	return array_merge( $settings, $commission_fees_settings );
}
add_filter( 'edd_settings_extensions', 'edd_commission_fees_settings_extensions' );
