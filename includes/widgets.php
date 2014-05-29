<?php
/**
 * Handle Hours Widgets
 *
 * @package     EDD\StoreHours\Widgets
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Hours Widget class
 *
 * @since       1.0.0
 */
class edd_store_hours_widget extends WP_Widget {
	public function edd_store_hours_widget() {
		parent::WP_Widget( false, __( 'Store Hours', 'edd-store-hours' ), array( 'description' => __( 'Display the hours your store is open', 'edd-store-hours' ) ) );
	}


	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$format = $instance['format'];

		global $edd_options;

		echo $before_widget;

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		do_action( 'edd_before_store_hours_widget' );

		if( $instance['status'] == 'true' ) {
			if( edd_store_hours_is_closed() ) {
				echo '<h3 class="edd-store-hours-status edd-store-hours-status-closed">' . __( 'We are currently closed!', 'edd-store-hours' ) . '</h3>';
			} else {
				echo '<h3 class="edd-store-hours-status edd-store-hours-status-open">' . __( 'We are open for business!', 'edd-store-hours' ) . '</h3>';
			}
		}

		if( $instance['hours'] == 'true' ) {
			if( $instance['format'] == 'custom' && !empty( $instance['custom_format'] ) ) {
				$format = $instance['custom_format'];
			}

			$days  = array( 
				'monday' => array(
					'name' => __( 'Monday', 'edd-store-hours' ),
				),
				'tuesday' => array(
					'name' => __( 'Tuesday', 'edd-store-hours' ),
				),
				'wednesday' => array(
					'name' => __( 'Wednesday', 'edd-store-hours' ),
				),
				'thursday' => array(
					'name' => __( 'Thursday', 'edd-store-hours' ),
				),
				'friday' => array(
					'name' => __( 'Friday', 'edd-store-hours' ),
				),
				'saturday' => array(
					'name' => __( 'Saturday', 'edd-store-hours' ),
				),
				'sunday' => array(
					'name' => __( 'Sunday', 'edd-store-hours' ),
				)
			);

			foreach( $days as $day => $details ) {
				$status = edd_get_option( 'edd_store_hours_' . $day . '_status', 'open' );

				if( $status == 'closed' ) {
					$days[$day]['hours'] = __( 'Closed', 'edd-store-hours' );
				} else {
					$days[$day]['hours'] = date( $format, strtotime( edd_get_option( 'edd_store_hours_' . $day . '_open', '0000' ) ) ) . ' - ' . date( $format, strtotime( edd_get_option( 'edd_store_hours_' . $day . '_close', '2359' ) ) );
				}
			}

			if( $instance['display'] == 'block' ) {
				echo '<div class="edd-hours">';

				foreach( $days as $day => $details ) {
					echo '<div class="edd-hours-day">' . $details['name'] . '</div>';
					echo '<div class="edd-hours-time">' . $details['hours'] . '</div>';
				}

		        echo '</div>';
			} else {
				echo '<ul class="edd-hours">';

				foreach( $days as $day => $details ) {
		        	echo '<li><span class="edd-hours-day">' . $details['name'] . '</span><span class="edd-hours-time">' . $details['hours'] . '</span></li>';
		        }
				
				echo '</ul>';
			}
		}

		echo '<div style="clear: both;"></div>';

		do_action( 'edd_after_store_hours_widget' );

		echo $after_widget;
	}


	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']			= strip_tags( $new_instance['title'] );
		$instance['format']			= isset( $new_instance['format'] ) ? $new_instance['format'] : '';
		$instance['custom_format']	= isset( $new_instance['custom_format'] ) ? $new_instance['custom_format'] : '';
		$instance['display']		= isset( $new_instance['display'] ) ? $new_instance['display'] : '';
		$instance['status']			= isset( $new_instance['status'] ) ? $new_instance['status'] : '';
		$instance['hours']			= isset( $new_instance['hours'] ) ? $new_instance['hours'] : '';

		return $instance;
	}


	public function form( $instance ) {
		$title 			= isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$format 		= isset( $instance['format'] ) ? esc_attr( $instance['format'] ) : 'g:i';
		$custom_format 	= isset( $instance['custom_format'] ) ? esc_attr( $instance['custom_format'] ) : '';
		$display 		= isset( $instance['display'] ) ? esc_attr( $instance['display'] ) : 'inline';
		$status 		= isset( $instance['status'] ) ? esc_attr( $instance['status'] ) : 'true';
		$hours 			= isset( $instance['hours'] ) ? esc_attr( $instance['hours'] ) : 'true';
		?>

		<p>
       		<label for="<?php echo esc_attr( $this->get_field_id( 'status' ) ); ?>"><?php _e( 'Display Status:', 'edd-store-hours' ); ?></label><br />
       		<select id="<?php echo esc_attr( $this->get_field_id( 'status' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'status' ) ); ?>">
       			<option value="true" <?php echo selected( 'true', $status, false ); ?>><?php _e( 'True', 'edd-store-hours' ); ?></option>
       			<option value="false" <?php echo selected( 'false', $status, false ); ?>><?php _e( 'False', 'edd-store-hours' ); ?></option>
       		</select>
    	</p>

    	<p>
       		<label for="<?php echo esc_attr( $this->get_field_id( 'hours' ) ); ?>"><?php _e( 'Display Hours:', 'edd-store-hours' ); ?></label><br />
       		<select id="<?php echo esc_attr( $this->get_field_id( 'hours' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hours' ) ); ?>">
       			<option value="true" <?php echo selected( 'true', $hours, false ); ?>><?php _e( 'True', 'edd-store-hours' ); ?></option>
       			<option value="false" <?php echo selected( 'false', $hours, false ); ?>><?php _e( 'False', 'edd-store-hours' ); ?></option>
       		</select>
    	</p>

		<p>
       		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'edd-store-hours' ); ?></label>
     		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo $title; ?>"/>
    	</p>

    	<p>
       		<label for="<?php echo esc_attr( $this->get_field_id( 'format' ) ); ?>"><?php _e( 'Time Format:', 'edd-store-hours' ); ?></label><br />
       		<select id="<?php echo esc_attr( $this->get_field_id( 'format' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'format' ) ); ?>">
       			<option value="g:i a" <?php echo selected( 'g:i a', $format, false ); ?>><?php echo current_time( 'g:i a' ); ?></option>
       			<option value="g:i A" <?php echo selected( 'g:i A', $format, false ); ?>><?php echo current_time( 'g:i A' ); ?></option>
       			<option value="g:i" <?php echo selected( 'g:i', $format, false ); ?>><?php echo current_time( 'g:i' ); ?></option>
       			<option value="H:i" <?php echo selected( 'H:i', $format, false ); ?>><?php echo current_time( 'H:i' ); ?></option>
       			<option value="custom" <?php echo selected( 'custom', $format, false ); ?>><?php _e( 'Custom', 'edd-store-hours' ); ?></option>
       		</select>
    	</p>

		<p>
       		<label for="<?php echo esc_attr( $this->get_field_id( 'custom_format' ) ); ?>"><?php _e( 'Custom Format:', 'edd-store-hours' ); ?></label>
     		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'custom_format' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'custom_format' ) ); ?>" type="text" value="<?php echo $custom_format; ?>"/><br />
     		<span style="font-size: smaller;"><?php _e( 'Custom date formats must be compatible with the PHP <a href="http://php.net/manual/en/function.date.php" target="_blank">date()</a> function!', 'edd-store-hours' ); ?></span>
    	</p>

    	<p>
       		<label for="<?php echo esc_attr( $this->get_field_id( 'display' ) ); ?>"><?php _e( 'Display Format:', 'edd-store-hours' ); ?></label><br />
       		<select id="<?php echo esc_attr( $this->get_field_id( 'display' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display' ) ); ?>">
       			<option value="inline" <?php echo selected( 'inline', $display, false ); ?>><?php _e( 'Inline', 'edd-store-hours' ); ?></option>
       			<option value="block" <?php echo selected( 'block', $display, false ); ?>><?php _e( 'Block', 'edd-store-hours' ); ?></option>
       		</select>
    	</p>
    
   		<?php
	}
}


/**
 * Register Widgets
 *
 * @since		1.0.0
 * @return		void
 */
function edd_store_hours_register_widgets() {
	register_widget( 'edd_store_hours_widget' );
}
add_action( 'widgets_init', 'edd_store_hours_register_widgets' );