<?php
/**
 * Plugin Name:     Easy Digital Downloads - Commission Fees
 * Plugin URI:      https://sellcomet.com/downloads/commission-fees
 * Description:     Charge vendors an additional flat amount or percentage fee on each transaction.
 * Version:         1.0.3
 * Author:          Sell Comet
 * Author URI:      https://sellcomet.com
 * Text Domain:     edd-commission-fees
 * Domain Path:     languages
 *
 * @package         EDD\Commission_Fees
 * @author          Sell Comet
 * @copyright       Copyright (c) Sell Comet
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'EDD_Commission_Fees' ) ) {

    /**
     * Main EDD_Commission_Fees class
     *
     * @since       1.0.0
     */
    class EDD_Commission_Fees {

        /**
         * @var         EDD_Commission_Fees $instance The one true EDD_Commission_Fees
         * @since       1.0.0
         */
        private static $instance;

        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true EDD_Commission_Fees
         */
        public static function instance() {
            if( !self::$instance ) {
                self::$instance = new EDD_Commission_Fees();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
                self::$instance->hooks();
            }

            return self::$instance;
        }

        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {
            // Plugin version
            define( 'EDD_COMMISSION_FEES_VER', '1.0.3' );

            // Plugin path
            define( 'EDD_COMMISSION_FEES_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'EDD_COMMISSION_FEES_URL', plugin_dir_url( __FILE__ ) );
        }

        /**
         * Include necessary files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {

            if ( is_admin() ) {

                // Include admin settings
                require_once EDD_COMMISSION_FEES_DIR . 'includes/admin/settings.php';

                // Include admin download commissions meta box functions
                require_once EDD_COMMISSION_FEES_DIR . 'includes/admin/metabox.php';

                // Include admin customer view functions
                require_once EDD_COMMISSION_FEES_DIR . 'includes/admin/customers.php';

                // Include admin commissions single view functions
                require_once EDD_COMMISSION_FEES_DIR . 'includes/admin/commissions.php';

                // Include admin commssion filters
                require_once EDD_COMMISSION_FEES_DIR . 'includes/admin/commission-filters.php';

                // Include admin commission actions
                require_once EDD_COMMISSION_FEES_DIR . 'includes/admin/commission-actions.php';

                // Include admin export actions
                require_once EDD_COMMISSION_FEES_DIR . 'includes/admin/export-actions.php';

                // Include admin export filters
                require_once EDD_COMMISSION_FEES_DIR . 'includes/admin/export-filters.php';

                // Include admin export functions
                require_once EDD_COMMISSION_FEES_DIR . 'includes/admin/export-functions.php';

                // Include admin reports
                require_once EDD_COMMISSION_FEES_DIR . 'includes/admin/reports.php';

                // Include admin commission list table functions
                require_once EDD_COMMISSION_FEES_DIR . 'includes/admin/classes/EDD_C_List_Table.php';
            }

            // Include user meta fields
            require_once EDD_COMMISSION_FEES_DIR . 'includes/user-meta.php';

            // Include commission functions
            require_once EDD_COMMISSION_FEES_DIR . 'includes/commission-functions.php';

            // Include commission filters
            require_once EDD_COMMISSION_FEES_DIR . 'includes/commission-filters.php';

            // Include commission actions
            require_once EDD_COMMISSION_FEES_DIR . 'includes/commission-actions.php';

            // Include email functions
            require_once EDD_COMMISSION_FEES_DIR . 'includes/email-functions.php';

            // Include short code functions
            require_once EDD_COMMISSION_FEES_DIR . 'includes/short-codes.php';


            if ( true === (bool) edd_get_option( 'edd_commission_fees_enable_shortcode_fees', false ) ) {

                // Commissions Shortcode [edd_commissions] fee addition - Table Headers
                add_action( 'eddc_user_commissions_unpaid_head_row_end', 'eddcf_user_commissions_fee_table_header', 10 );
                add_action( 'eddc_user_commissions_paid_head_row_end', 'eddcf_user_commissions_fee_table_header', 10 );
                add_action( 'eddc_user_commissions_revoked_head_row_end', 'eddcf_user_commissions_fee_table_header', 10 );

                // Commissions Shortcode [edd_commissions] fee addition - Table Rows
                add_action( 'eddc_user_commissions_unpaid_row_end', 'eddcf_user_commissions_fee_table_row', 10, 1 );
                add_action( 'eddc_user_commissions_paid_row_end', 'eddcf_user_commissions_fee_table_row', 10, 1 );
                add_action( 'eddc_user_commissions_revoked_row_end', 'eddcf_user_commissions_fee_table_row', 10, 1 );

            }

        }

        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = EDD_COMMISSION_FEES_DIR . '/languages/';
            $lang_dir = apply_filters( 'edd_commission_fees_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'edd-commission-fees' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'edd-commission-fees', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/edd-commission-fees/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/edd-commission-fees/ folder
                load_textdomain( 'edd-commission-fees', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/edd-commission-fees/languages/ folder
                load_textdomain( 'edd-commission-fees', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'edd-commission-fees', false, $lang_dir );
            }
        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function hooks() {

            // Simple Shipping Integration
            if ( class_exists( 'EDD_Simple_Shipping' ) ) {
                add_filter( 'eddcf_calc_commission_base_amount', 'eddc_include_shipping_calc_in_commission', 10, 2 );
            }

            if ( is_admin() ) {

                // Make sure we are at the minimum version of EDD Commissions - which is 3.3.
                add_action( 'admin_notices', array( $this, 'version_check_notice' ), 10 );

            }

        }


        /**
    	 * Make sure we are at the minimum version of EDD Commissions - which is 3.3.
    	 *
    	 * @since       1.0.0
    	 * @access      public
    	 * @return      void
    	 */
    	public function version_check_notice() {

            if ( defined( 'EDD_COMMISSIONS_VERSION' ) && version_compare( EDD_COMMISSIONS_VERSION, '3.4.5' ) == -1 ) {
            	?>
            	<div class="notice notice-error">
    	        <p><?php echo __( 'EDD Commission Fees: Your version of EDD Commissions must be updated to version 3.4.6 or later to use the Commission Fees extension in conjunction with Commissions.', 'edd-commission-fees' ); ?></p>
            	</div>
            	<?php
            }
    	}

    }
} // End if class_exists check


/**
 * The main function responsible for returning the one true EDD_Commission_Fees
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \EDD_Commission_Fees The one true EDD_Commission_Fees
 */
function EDD_Commission_Fees_load() {
    if ( ! class_exists( 'Easy_Digital_Downloads' ) || ! class_exists( 'EDDC' ) ) {
        if ( ! class_exists( 'EDD_Extension_Activation' ) || ! class_exists( 'EDD_Commissions_Activation' ) ) {
          require_once 'includes/classes/class-activation.php';
        }

        // Easy Digital Downloads activation
		if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
			$edd_activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
			$edd_activation = $edd_activation->run();
		}

        // Commissions activation
		if ( ! class_exists( 'EDDC' ) ) {
			$edd_commissions_activation = new EDD_Commissions_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
			$edd_commissions_activation = $edd_commissions_activation->run();
		}

    } else {

      return EDD_Commission_Fees::instance();
    }
}
add_action( 'plugins_loaded', 'EDD_Commission_Fees_load' );
