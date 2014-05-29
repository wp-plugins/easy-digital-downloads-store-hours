<?php
/**
 * Add Helper Functions
 *
 * @package     EDD\StoreHours\Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Hours Callback
 *
 * Renders hours fields.
 *
 * @since		1.0.0
 * @param 		array $args Arguments passed by the setting
 * @global 		$edd_options Array of all the EDD Options
 * @return 		void
 */
function edd_hours_callback( $args ) {
	global $edd_options;
	
	$status = ( isset( $edd_options[$args['id'] . '_status'] ) ? $edd_options[$args['id'] . '_status'] : 'open' );
	$open 	= ( isset( $edd_options[$args['id'] . '_open'] ) && !empty( $edd_options[$args['id'] . '_open'] ) ? date( 'g:i a', strtotime( $edd_options[$args['id'] . '_open'] ) ) : '' );
	$close  = ( isset( $edd_options[$args['id'] . '_close'] ) && !empty( $edd_options[$args['id'] . '_close'] ) ? date( 'g:i a', strtotime( $edd_options[$args['id'] . '_close'] ) ) : '' );

	$html  = '<input type="text" class="edd-store-hours" id="edd_settings[' . $args['id'] . '_open]" name="edd_settings[' . $args['id'] . '_open]" value="' . esc_attr( stripslashes( $open ) ) . '" />';
	$html .= ' - ';
	$html .= '<input type="text" class="edd-store-hours" id="edd_settings[' . $args['id'] . '_close]" name="edd_settings[' . $args['id'] . '_close]" value="' . esc_attr( stripslashes( $close ) ) . '" />';
	$html .= '<label for="edd_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
	$html .= '<select id="edd_settings[' . $args['id'] . '_status]" name="edd_settings[' . $args['id'] . '_status]" />';
	$html .= '<option value="open" ' . selected( 'open', $status, false ) . '>' . __( 'Open', 'edd-store-hours' ) . '</option>';
	$html .= '<option value="closed" ' . selected( 'closed', $status, false ) . '>' . __( 'Closed', 'edd-store-hours' ) . '</option>';
	$html .= '</select>';

	echo $html;
}


/**
 * Determine whether or not the store is open
 *
 * @since		1.0.0
 * @return		bool True if closed, False otherwise
 */
function edd_store_hours_is_closed() {
	$today    = strtolower( current_time( 'l' ) );
    $now      = current_time( 'Hi' );
   	$status   = edd_get_option( 'edd_store_hours_' . $today . '_status', 'open' );
   	$open     = edd_get_option( 'edd_store_hours_' . $today . '_open', '0000' );
   	$close    = edd_get_option( 'edd_store_hours_' . $today . '_close', '2359' );
   	$override = edd_get_option( 'edd_store_hours_closed_now', 'false' );

   	if( $status == 'closed' || $override == 'true' || $now < $open || $now > $close ) {
   		return true;
   	}

   	return false;
}


/**
 * Override the add to cart button if the store is closed
 *
 * @since       1.0.0
 * @param       string $purchase_form the actual purchase form code
 * @param       array $args the info for the specific download
 * @return      string $purchase_form if store is open
 * @return      string $closed if store is closed
 */
function edd_store_hours_override_purchase_button( $purchase_form, $args ) {
	$closed_label = edd_get_option( 'edd_store_hours_closed_label' ) ? edd_get_option( 'edd_store_hours_closed_label' ) : __( 'Store Closed', 'edd-store-hours' );
	$closed_label = edd_store_hours_parse_template_tags( $closed_label );
    $form_id      = !empty( $args['form_id'] ) ? $args['form_id'] : 'edd_purchase_' . $args['download_id'];
    $hide_buttons = edd_get_option( 'edd_store_hours_hide_buttons', 'false' );

    if( edd_store_hours_is_closed() ) {
    	if( $hide_buttons == 'false' ) {
		    $purchase_form  = '<form id="' . $form_id . '" class="edd_download_purchase_form">';
		    $purchase_form .= '<div class="edd_purchase_submit_wrapper">';

		    if( edd_is_ajax_enabled() ) {
		        $purchase_form .= sprintf(
		            '<div class="edd-add-to-cart %1$s"><span>%2$s</span></a>',
		            implode( ' ', array( $args['style'], $args['color'], trim( $args['class'] ) ) ),
		            esc_attr( $closed_label )
		        );
		        $purchase_form .= '</div>';
		    } else {
		        $purchase_form .= sprintf(
		            '<input type="submit" class="edd-add-to-cart edd-no-js %1$s" name="edd_purchase_download" value="%2$s" disabled />',
		            implode( ' ', array( $args['style'], $args['color'], trim( $args['class'] ) ) ),
		            esc_attr( $closed_label )
		        );
		    }

		    $purchase_form .= '</div></form>';
		} else {
			$purchase_form = '';
		}
    }

    return $purchase_form;
}
add_filter( 'edd_purchase_download_form', 'edd_store_hours_override_purchase_button', 200, 2 );


/**
 * Override edd_pre_add_to_cart so users can't add through direct linking
 *
 * @since       1.0.0
 * @param       int $download_id The ID of a specific download
 * @param       array $options The options for this downloads
 * @return      void
 */
function edd_store_hours_override_add_to_cart( $download_id, $options ) {
    // Get options
    $closed_label = edd_get_option( 'edd_store_hours_closed_label' ) ? edd_get_option( 'edd_store_hours_closed_label' ) : __( 'Store Closed', 'edd-store-hours' );
    $closed_label = edd_store_hours_parse_template_tags( $closed_label );
    $cart_items = edd_get_cart_contents();
    
    if( edd_store_hours_is_closed() ) wp_die( $closed_label );
}
add_action( 'edd_pre_add_to_cart', 'edd_store_hours_override_add_to_cart', 200, 2 );


/**
 * Get available template tags
 *
 * @since       1.0.0
 * @return      array $tags The available template tags
 */
function edd_store_hours_template_tags() {

    $tags = array(
        'sitename'       => __( 'Your site name', 'edd-site-hours' ),
        'open_today'	 => __( 'The time your store opened today', 'edd-site-hours' ),
        'close_today'    => __( 'The time your store closed today', 'edd-site-hours' ),
        'open_tomorrow'  => __( 'The time your store opens tomorrow', 'edd-site-hours' ),
        'close_tomorrow' => __( 'The time your store closes tomorrow', 'edd-site-hours' ),
    );

    return apply_filters( 'edd_site_hours_template_tags', $tags );
}


/**
 * Get the template tags
 *
 * @since       1.0.0
 * @return      array $tags The formatted template tags
 */
function edd_store_hours_get_template_tags() {
    $template_tags  = edd_store_hours_template_tags();
    $tags            = __( '<br/>This field allows several template tags:', 'edd-store-hours' );

    foreach( $template_tags as $tag => $desc ) {
        $tags .= '<br /><span class="edd-store-hours-tag-name">{' . $tag . '}</span><span class="edd-store-hours-tag-desc">' . $desc . '</span>';
    }

    return $tags;
}


/**
 * Handle template tags
 *
 * @since       1.0.0
 * @param       string $template Text before tag replacement
 * @return      string $template Text after tag replacement
 */
function edd_store_hours_parse_template_tags( $template ) {
	$has_tags = ( strpos( $template, '{' ) !== false );
    if( !$has_tags ) return $template;

	$today          = strtolower( current_time( 'l' ) );
	$tomorrow       = strtolower( date( 'l', current_time( 'timestamp' ) + 86400 ) );
   	$open_today     = edd_get_option( 'edd_store_hours_' . $today . '_open', '0000' );
   	$close_today    = edd_get_option( 'edd_store_hours_' . $today . '_close', '2359' );
   	$open_tomorrow  = edd_get_option( 'edd_store_hours_' . $tomorrow . '_open', '0000' );
   	$close_tomorrow = edd_get_option( 'edd_store_hours_' . $tomorrow . '_close', '2359' );

    $template = str_replace( '{sitename}', get_bloginfo( 'name' ), $template );
    $template = str_replace( '{open_today}', date( 'g:i a', strtotime( $open_today ) ), $template );
    $template = str_replace( '{close_today}', date( 'g:i a', strtotime( $close_today ) ), $template );
    $template = str_replace( '{open_tomorrow}', date( 'g:i a', strtotime( $open_tomorrow ) ), $template );
    $template = str_replace( '{close_tomorrow}', date( 'g:i a', strtotime( $close_tomorrow ) ), $template );

    $template = apply_filters( 'edd_store_hours_parse_template_tags', $template );

    return $template;
}