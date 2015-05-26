<?php
/**
 * Scripts
 *
 * @package     EDD\StoreHours\Scripts
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Load frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function edd_store_hours_load_scripts() {
    wp_enqueue_style( 'edd_store-hours-css', EDD_STORE_HOURS_URL . 'assets/css/style.css' );
}
add_action( 'wp_enqueue_scripts', 'edd_store_hours_load_scripts' );


/**
 * Load admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function edd_store_hours_load_admin_scripts() {
    if( $hook == $edd_settings_page ) {
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'jquery-ui-slider' );
        wp_enqueue_script( 'edd-store-hours-timepicker', EDD_STORE_HOURS_URL . 'assets/js/jquery-ui-timepicker-addon.js', array( 'jquery-ui-datepicker', 'jquery-ui-slider' ) );
        wp_enqueue_script( 'edd-store-hours-clearable', EDD_STORE_HOURS_URL . 'assets/js/jquery.clearable.js', array( 'edd-store-hours-timepicker' ) );
        wp_enqueue_script( 'edd-store-hours-admin', EDD_STORE_HOURS_URL . 'assets/js/admin.js' );
    
        if( get_user_option( 'admin_color' ) == 'classic' ) {
            wp_enqueue_style( 'jquery-ui', EDD_STORE_HOURS_URL . 'assets/css/jquery-ui-classic.css' );
        } else {
            wp_enqueue_style( 'jquery-ui', EDD_STORE_HOURS_URL . 'assets/css/jquery-ui-fresh.css' );
        }

        wp_enqueue_style( 'edd_store-hours-admin', EDD_STORE_HOURS_URL . 'assets/css/admin.css' );
    }
}
add_action( 'admin_enqueue_scripts', 'edd_store_hours_load_admin_scripts' );