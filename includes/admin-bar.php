<?php
/**
 * Add Admin Bar
 *
 * @package     EDD\StoreHours\AdminBar
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


function edd_store_hours_show_admin_bar() {
	if( is_admin_bar_showing() && edd_store_hours_is_closed() ) {
		global $wp_admin_bar;

		$classes = apply_filters( 'debug_bar_classes', array() );
		$classes = implode( " ", $classes );

		/* Add the main siteadmin menu item */
		$wp_admin_bar->add_menu( array(
			'id'     => 'edd-store-hours-bar',
			'parent' => 'top-secondary',
			'title'  => apply_filters( 'edd_store_hours_admin_bar_title', __('Store Closed', 'edd-store-hours') ),
		) );
	}
}
add_action( 'admin_bar_menu', 'edd_store_hours_show_admin_bar' );