<?php
/**
 * Plugin Name:     Comment Controller
 * Plugin URI:      http://section214.com
 * Description:     Selectively disable comments on a per-user basis
 * Version:         1.0.3
 * Author:          Daniel J Griffiths
 * Author URI:      http://section214.com
 * Text Domain:     comment-controller
 *
 * @package         CommentController
 * @author          Daniel J Griffiths <dgriffiths@section214.com>
 * @copyright       Copyright (c) 2014, Daniel J Griffiths
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


if( ! class_exists( 'CommentController' ) ) {


    /**
     * Main CommentController class
     *
     * @since       1.0.0
     */
    class CommentController {


        /**
         * @since       1.0.0
         * @var         CommentController $instance The one true CommentController
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      self::$instance The one true CommentController
         */
        public static function instance() {
            if( ! self::$instance ) {
                self::$instance = new CommentController();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
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
            // Plugin path
            define( 'COMMENTCONTROLLER_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'COMMENTCONTROLLER_URL', plugin_dir_url( __FILE__ ) );
        }


        /**
         * Include required files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {
            global $commentcontroller_options;

            require_once COMMENTCONTROLLER_DIR . 'includes/admin/settings/register.php';
            $commentcontroller_options = commentcontroller_get_settings();

            require_once COMMENTCONTROLLER_DIR . 'includes/functions.php';
            require_once COMMENTCONTROLLER_DIR . 'includes/profile.php';

            if( is_admin() ) {
                require_once COMMENTCONTROLLER_DIR . 'includes/admin/pages.php';
                require_once COMMENTCONTROLLER_DIR . 'includes/admin/settings/display.php';
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
            $lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
            $lang_dir = apply_filters( 'commentcontroller_language_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), '' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'comment-controller', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/comment-controller/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/comment-controller/ folder
                load_textdomain( 'comment-controller', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/comment-controller/languages/ folder
                load_textdomain( 'comment-controller', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'comment-controller', false, $lang_dir );
            }
        }
    }
}


/**
 * The main function responsible for returning the one true CommentController
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      CommentController The one true CommentController
 */
function commentcontroller_load() {
    return CommentController::instance();
}
add_action( 'plugins_loaded', 'commentcontroller_load' );
