<?php
/**
 * AffiliateWP Integrations
 *
 * @package     EDD_Commission_Fees
 * @subpackage  Integrations
 * @copyright   Copyright (c) 2018, Sell Comet
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Adjust the commission rate recorded if a referral is present
 *
 * @access  public
 * @since   1.0.0
*/
function eddcf_affiliatewp_commission_rate( $amount, $args ) {

	if ( ! class_exists( 'Affiliate_WP' ) ) {
		return $amount;
	}

    if( ! affiliate_wp()->settings->get( 'edd_adjust_commissions' ) ) {
        return $amount;
    }

    $referral = affiliate_wp()->referrals->get_by( 'reference', $args['payment_id']  );

    if( ! empty( $referral->products ) ) {
        $products = maybe_unserialize( maybe_unserialize( $referral->products ) );
        foreach( $products as $product ) {

            if( (int) $product['id'] !== (int) $args['download_id'] ) {
                continue;
            }

            if( 'flat' == $args['type'] ) {
                return $args['rate'] - $product['referral_amount'];
            }

            $args['price'] -= $product['referral_amount'];

            if ( $args['rate'] >= 1 ) {
                $amount = $args['price'] * ( $args['rate'] / 100 ); // rate format = 10 for 10%
            } else {
                $amount = $args['price'] * $args['rate']; // rate format set as 0.10 for 10%
            }

        }

    }

    return $amount;
}
add_filter( 'eddcf_calc_commission_base_amount', 'eddcf_affiliatewp_commission_rate', 10, 2 );
