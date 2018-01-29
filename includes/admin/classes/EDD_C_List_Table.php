<?php
/**
 * Commission list table
 *
 * @package     EDD_Commission_Fees
 * @subpackage  Admin/Classes
 * @copyright   Copyright (c) 2018, Sell Comet
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Setup bulk actions
 *
 * @since       1.0.0
 * @param       array $actions The actions array
 * @return      array $actions The registered bulk actions
 */
function eddcf_bulk_actions( $actions ) {
	$actions['revoke_fee'] 		= __( 'Revoke Fees', 'edd-commission-fees' );
	$actions['recalculate_fee'] = __( 'Recalculate Fees', 'edd-commission-fees' );

	return $actions;
}
add_filter( 'manage_edd_commissions_bulk_actions', 'eddcf_bulk_actions', 10, 1 );


/**
 * Process bulk actions
 *
 * @since       1.0.0
 * @return      void
 */
function eddcf_process_bulk_action( $id, $current_action ) {

	// Revoke commission fee
	if ( 'revoke_fee' === $current_action ) {
		eddcf_revoke_commission_fee( $id );
	}

	// Recalculate commission fee
	if ( 'recalculate_fee' === $current_action ) {
		eddcf_relculate_commission_fee( $id );
	}

}
add_action( 'edd_commissions_process_bulk_action_end', 'eddcf_process_bulk_action', 10, 2 );
