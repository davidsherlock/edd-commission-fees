<?php
/**
 * Plugin Name:     Easy Digital Downloads - Commission Fees
 * Plugin URI:      https://sellcomet.com/downloads/commission-fees/
 * Description:     Charge vendors an additional flat rate or percentage fee on each transaction.
 * Version:         1.0.0
 * Author:          Sell Comet
 * Author URI:      https://sellcomet.com
 * Text Domain:     edd-commission-fees
 *
 * @package         EDD\CommissionFees
 * @author          Sell Comet
 * @copyright       Copyright (c) 2017, Sell Comet
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'EDD_Commission_Fees' ) ) {

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

        public static $edd_commissions;

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

                self::$edd_commissions = new EDD_Commission_Fees_Commissions();
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
            define( 'EDD_COMMISSION_FEES_VER', '1.0.0' );

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

            // Include user meta fields
            require_once EDD_COMMISSION_FEES_DIR . 'includes/functions/user-meta.php';

            // Include helper functions
            require_once EDD_COMMISSION_FEES_DIR . 'includes/functions/helper-functions.php';

            // Admin only requires
            if ( is_admin() ) {
              // Include admin settings
              require_once EDD_COMMISSION_FEES_DIR . 'includes/admin/settings.php';

              // Include the download admin metabox
              require_once EDD_COMMISSION_FEES_DIR . 'includes/admin/metabox.php';
            }

            // Include the commissions plugin integrations
            require_once EDD_COMMISSION_FEES_DIR . 'includes/integrations/plugin-commissions.php';

        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function hooks() {

            // Handle licensing
            if( class_exists( 'EDD_License' ) && is_admin() ) {
                $license = new EDD_License( __FILE__, 'Commission Fees', EDD_COMMISSION_FEES_VER, 'Sell Comet', null, 'https://sellcomet.com/', 22 );
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
            $mofile_global  = WP_LANG_DIR . '/edd-plugin-name/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/edd-plugin-name/ folder
                load_textdomain( 'edd-commission-fees', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/edd-plugin-name/languages/ folder
                load_textdomain( 'edd-commission-fees', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'edd-commission-fees', false, $lang_dir );
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
    if( ! class_exists( 'Easy_Digital_Downloads' ) ) {
        if( ! class_exists( 'EDD_Extension_Activation' ) ) {
            require_once 'includes/classes/class.extension-activation.php';
        }

        $activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
        $activation = $activation->run();
    } else {
        return EDD_Commission_Fees::instance();
    }
}
add_action( 'plugins_loaded', 'EDD_Commission_Fees_load' );
