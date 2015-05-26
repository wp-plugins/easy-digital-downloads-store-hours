<?php
/**
 * Plugin Name:     Easy Digital Downloads - Store Hours
 * Description:     Easily handle store hours of operation on your Easy Digital Downloads-powered site
 * Version:         1.0.1
 * Author:          Daniel J Griffiths
 * Author URI:      http://section214.com
 * Text Domain:     edd-store-hours
 *
 * @package         EDD\StoreHours
 * @author          Daniel J Griffiths <dgriffiths@section214.com>
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


if( !class_exists( 'EDD_Store_Hours' ) ) {


    /**
     * Main EDD_Store_Hours class
     *
     * @since       1.0.0
     */
    class EDD_Store_Hours {

        /**
         * @var         EDD_Store_Hours $instance The one true EDD_Store_Hours
         * @since       1.0.0
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      self::$instance The one true EDD_Store_Hours
         */
        public static function instance() {
            if( !self::$instance ) {
                self::$instance = new EDD_Store_Hours();
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
            define( 'EDD_STORE_HOURS_VER', '1.0.1' );

            // Plugin path
            define( 'EDD_STORE_HOURS_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'EDD_STORE_HOURS_URL', plugin_dir_url( __FILE__ ) );
        }


        /**
         * Include necessary files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {
            // Load core files
            require_once EDD_STORE_HOURS_DIR . 'includes/scripts.php';
            require_once EDD_STORE_HOURS_DIR . 'includes/functions.php';

            // Load widgets
            require_once EDD_STORE_HOURS_DIR . 'includes/widgets.php';

            // Load notification bar
            if( edd_get_option( 'edd_store_hours_show_notification_bar', 'true' ) == 'true' ) {
                require_once EDD_STORE_HOURS_DIR . 'includes/notification-bar.php';
            }

            // Load admin bar
            if( edd_get_option( 'edd_store_hours_show_admin_bar', 'true' ) == 'true' ) {
                require_once EDD_STORE_HOURS_DIR . 'includes/admin-bar.php';
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
            // Edit plugin metalinks
            add_filter( 'plugin_row_meta', array( $this, 'plugin_metalinks' ), null, 2 );

            // Add Store Hours tab to EDD settings
            add_filter( 'edd_settings_tabs', array( $this, 'add_settings_tab' ), 1 );

            // Register settings
            add_action( 'admin_init', array( $this, 'register_settings' ) );

            // Register settings
            add_filter( 'edd_settings_hours', array( $this, 'settings' ), 1 );

            // Sanitize hours fields
            add_filter( 'edd_settings_hours_sanitize', array( $this, 'sanitize' ), 1 );
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
            $lang_dir = apply_filters( 'EDD_Store_Hours_language_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale     = apply_filters( 'plugin_locale', get_locale(), '' );
            $mofile     = sprintf( '%1$s-%2$s.mo', 'edd-store-hours', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/edd-store-hours/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/edd-store-hours/ folder
                load_textdomain( 'edd-store-hours', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/edd-store-hours/languages/ folder
                load_textdomain( 'edd-store-hours', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'edd-store-hours', false, $lang_dir );
            }
        }


        /**
         * Modify plugin metalinks
         *
         * @access      public
         * @since       1.0.0
         * @param       array $links The current links array
         * @param       string $file A specific plugin table entry
         * @return      array $links The modified links array
         */
        public function plugin_metalinks( $links, $file ) {
            if( $file == plugin_basename( __FILE__ ) ) {
                $help_link = array(
                    '<a href="http://section214.com/support/forum/edd-store-hours/" target="_blank">' . __( 'Support Forum', 'edd-store-hours' ) . '</a>'
                );

                $docs_link = array(
                    '<a href="http://section214.com/docs/category/edd-store-hours/" target="_blank">' . __( 'Docs', 'edd-store-hours' ) . '</a>'
                );

                $links = array_merge( $links, $help_link, $docs_link );
            }

            return $links;
        }


        /**
         * Add settings tab
         *
         * @access      public
         * @since       1.0.0
         * @param       array $tabs The existing settings tabs
         * @return      array $tabs The modified settings tabs
         */
        public function add_settings_tab( $tabs ) {
            $tabs['hours'] = __( 'Store Hours', 'edd-store-hours' );

            return $tabs;
        }


        /**
         * Register settings helper since EDD
         * doesn't act like I think it should!
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function register_settings() {
            add_settings_section(
                'edd_settings_hours',
                __return_null(),
                '__return_false',
                'edd_settings_hours'
            );

            foreach( $this->settings() as $option ) {

                $name = isset( $option['name'] ) ? $option['name'] : '';

                add_settings_field(
                    'edd_settings_hours[' . $option['id'] . ']',
                    $name,
                    function_exists( 'edd_' . $option['type'] . '_callback' ) ? 'edd_' . $option['type'] . '_callback' : 'edd_missing_callback',
                    'edd_settings_hours',
                    'edd_settings_hours',
                    array(
                        'id'        => isset( $option['id'] ) ? $option['id'] : null,
                        'desc'      => !empty( $option['desc'] ) ? $option['desc'] : '',
                        'name'      => isset( $option['name'] ) ? $option['name'] : null,
                        'section'   => 'hours',
                        'size'      => isset( $option['size'] ) ? $option['size'] : null,
                        'options'   => isset( $option['options'] ) ? $option['options'] : '',
                        'std'       => isset( $option['std'] ) ? $option['std'] : ''
                    )
                );
            }
        }


        /**
         * Add settings
         *
         * @access      public
         * @since       1.0.0
         * @param       array $settings the existing EDD settings array
         * @return      array $settings the filtered EDD settings array
         */
        public function settings() {
            $settings = array(
                array(
                    'id'    => 'edd_store_hours',
                    'name'  => '<strong>' . __( 'Store Hours', 'edd-store-hours' ) . '</strong>',
                    'desc'  => __( 'Configure Store Hours', 'edd-store-hours' ),
                    'type'  => 'header'
                ),
                array(
                    'id'    => 'edd_store_hours_monday',
                    'name'  => __( 'Monday', 'edd-store-hours' ),
                    'desc'  => '',
                    'type'  => 'hours',
                ),
                array(
                    'id'    => 'edd_store_hours_tuesday',
                    'name'  => __( 'Tuesday', 'edd-store-hours' ),
                    'desc'  => '',
                    'type'  => 'hours',
                ),
                array(
                    'id'    => 'edd_store_hours_wednesday',
                    'name'  => __( 'Wednesday', 'edd-store-hours' ),
                    'desc'  => '',
                    'type'  => 'hours',
                ),
                array(
                    'id'    => 'edd_store_hours_thursday',
                    'name'  => __( 'Thursday', 'edd-store-hours' ),
                    'desc'  => '',
                    'type'  => 'hours',
                ),
                array(
                    'id'    => 'edd_store_hours_friday',
                    'name'  => __( 'Friday', 'edd-store-hours' ),
                    'desc'  => '',
                    'type'  => 'hours',
                ),
                array(
                    'id'    => 'edd_store_hours_saturday',
                    'name'  => __( 'Saturday', 'edd-store-hours' ),
                    'desc'  => '',
                    'type'  => 'hours',
                ),
                array(
                    'id'    => 'edd_store_hours_sunday',
                    'name'  => __( 'Sunday', 'edd-store-hours' ),
                    'desc'  => '',
                    'type'  => 'hours',
                ),
                array(
                    'id'    => 'edd_store_hours_closed_now',
                    'name'  => __( 'Close Store', 'edd-store-hours' ),
                    'desc'  => __( 'Override the pre-defined schedule and close the store now', 'edd-store-hours' ),
                    'type'  => 'select',
                    'options' => array(
                        'true'  => __( 'True', 'edd-store-hours' ),
                        'false' => __( 'False', 'edd-store-hours' ),
                    ),
                    'std'   => 'false',
                ),
                array(
                    'id'    => 'edd_store_hours_settings',
                    'name'  => '<strong>' . __( 'Display Settings', 'edd-store-hours' ) . '</strong>',
                    'desc'  => __( 'Configure Store Hours Settings', 'edd-store-hours' ),
                    'type'  => 'header'
                ),
                array(
                    'id'    => 'edd_store_hours_hide_buttons',
                    'name'  => __( 'Hide Purchase Buttons', 'edd-store-hours' ),
                    'desc'  => __( 'Hide purchase buttons instead of simply disabling them', 'edd-store-hours' ),
                    'type'  => 'select',
                    'options' => array(
                        'true'  => __( 'True', 'edd-store-hours' ),
                        'false' => __( 'False', 'edd-store-hours' ),
                    ),
                    'std'   => 'true',
                ),
                array(
                    'id'    => 'edd_store_hours_closed_label',
                    'name'  => __( 'Closed Label', 'edd-store-hours' ),
                    'desc'  => edd_store_hours_get_template_tags(),
                    'type'  => 'text',
                    'std'   => __( 'Store Closed', 'edd-store-hours' ),
                ),
                array(
                    'id'    => 'edd_store_hours_admin_bar_settings',
                    'name'  => '<strong>' . __( 'Admin Bar Settings', 'edd-store-hours' ) . '</strong>',
                    'desc'  => __( 'configure Sttore Hours Admin Bar', 'edd-store-hours' ),
                    'type'  => 'header',
                ),
                array(
                    'id'    => 'edd_store_hours_show_admin_bar',
                    'name'  => __( 'Show Admin Bar Notification', 'edd-store-hours' ),
                    'desc'  => __( 'Displays a notification in the admin bar when the site is closed', 'edd-store-hours' ),
                    'type'  => 'select',
                    'options' => array(
                        'true'  => __( 'True', 'edd-store-hours' ),
                        'false' => __( 'False', 'edd-store-hours' ),
                    ),
                    'std'   => 'true',
                ),
                // Notification bar to be added in 1.1.0
                /*array(
                    'id'    => 'edd_store_hours_notification_bar_settings',
                    'name'  => '<strong>' . __( 'Notification Bar Settings', 'edd-store-hours' ) . '</strong>',
                    'desc'  => __( 'Configure Store Hours Notification Bar', 'edd-store-hours' ),
                    'type'  => 'header',
                ),
                array(
                    'id'    => 'edd_store_hours_show_notification_bar',
                    'name'  => __( 'Show Notification Bar', 'edd-store-hours' ),
                    'desc'  => __( 'Displays a notification bar at the top of all frontend pages when the site is closed', 'edd-store-hours' ),
                    'type'  => 'select',
                    'options' => array(
                        'true'  => __( 'True', 'edd-store-hours' ),
                        'false' => __( 'False', 'edd-store-hours' ),
                    ),
                    'std'   => 'true',
                ),*/
            );

            return $settings;
        }


        /**
         * The hours fields need special sanitization... time
         * formats are a bitch!
         *
         * @access      public
         * @since       1.0.0
         * @param       array $input The settings we are sanitizing
         * @global      array $edd_options The EDD settings array
         * @return      array $input The sanitized settings
         */
        public function sanitize( $input ) {
            global $edd_options;

            $days = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );

            foreach( $days as $day ) {
                if( isset( $input['edd_store_hours_' . $day . '_open'] ) && !empty( $input['edd_store_hours_' . $day . '_open'] ) ) {
                    $input['edd_store_hours_' . $day . '_open'] = date( 'Hi', strtotime( $input['edd_store_hours_' . $day . '_open'] ) );
                }

                if( isset( $input['edd_store_hours_' . $day . '_close'] ) && !empty( $input['edd_store_hours_' . $day . '_close'] ) ) {
                    $input['edd_store_hours_' . $day . '_close'] = date( 'Hi', strtotime( $input['edd_store_hours_' . $day . '_close'] ) );
                }
            }

            return $input;
        }
    }
}


/**
 * The main function responsible for returning the one true EDD_Store_Hours
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      EDD_Store_Hours The one true EDD_Store_Hours
 */
function EDD_Store_Hours_load() {
    if( !class_exists( 'Easy_Digital_Downloads' ) ) {
        if( !class_exists( 'S213_EDD_Activation' ) ) {
            require_once( 'includes/class.s214-edd-activation.php' );
        }

        $activation = new S214_EDD_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
        $activation = $activation->run();
    } else {
        return EDD_Store_Hours::instance();
    }
}
add_action( 'plugins_loaded', 'EDD_Store_Hours_load' );
