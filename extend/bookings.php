<?php
/*Rename Persons to Pets*/
    add_filter( 'booking_form_fields', 'ctt_booking_form_fields' );
    function ctt_booking_form_fields( $fields ) {
    	$fields['wc_bookings_field_persons']['label'] = 'Pets';
    	return $fields;
    }
    
//----- Will make the Bookings calender default to the month with the first available booking.
    add_filter( 'wc_bookings_calendar_default_to_current_date', '__return_false' );    


//----- Will change the minutes it takes an In Cart booking to expire.
    function change_incart_bookings_expiry_minutes_20170825( $minutes ) {
	    return 2880;
    }
    add_filter( 'woocommerce_bookings_remove_inactive_cart_time', 'change_incart_bookings_expiry_minutes_20170825' );



//----- Will enable big selects when the host has a low threshold for MAX_JOIN_SIZE.
    function enable_big_selects_for_bookings_20170825() {
    	global $wpdb;
    	$wpdb->query( 'SET SQL_BIG_SELECTS=1' );
    }
    add_action( 'init', 'enable_big_selects_for_bookings_20170825' );



//----- Will put bookings into a Confirmed status if they were paid for via COD.
    function set_cod_bookings_confirmed_20170825( $order_id ) {
    	
    	// Get the order, then make sure its payment method is COD.
    	$order = wc_get_order( $order_id );
    	if ( 'cod' !== $order->get_payment_method() ) {
    		return;
    	}
    	// Call the data store class so we can get bookings from the order.
    	$booking_data = new WC_Booking_Data_Store();
    	$booking_ids  = $booking_data->get_booking_ids_from_order_id( $order_id );
    	// If we have bookings go through each and update the status.
    	if ( is_array( $booking_ids ) && count( $booking_ids ) > 0 ) {
    		foreach ( $booking_ids as $booking_id ) {
    			$booking = get_wc_booking( $booking_id );
    			$booking->update_status( 'confirmed' );
    		}
    	}
    }
    add_action( 'woocommerce_order_status_processing', 'set_cod_bookings_confirmed_20170825', 20 );

//----- Bookable product after login

    add_action('woocommerce_single_product_summary', 'restrict_booking_for_logged_in_users_message', 25);
    add_action('woocommerce_after_add_to_cart_button', 'restrict_booking_for_logged_in_users_message', 25);
        function restrict_booking_for_logged_in_users_message() {
        // Check if the user is logged in
            if (! is_user_logged_in() ) {
                global $product, $post; 
                $current_path = wp_parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                $query_string = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
                $redirect_path = $current_path . $query_string;
                

               // $redirectURL = urlencode( $_SERVER['REQUEST_URI'] ); // Get the current URL
                $redirectURL = wp_login_url($redirect_path);
                
                
                if ( $product->get_type() == 'booking' ){
                    echo '<div class="bookmsgnologin"><p class="woocommerce-info">You must be logged in to book appointment. <a class="login inline-type btn btn-block btn-success btn-small" href="' . $redirectURL . '">Log in/ Sign Up</a></p></div>';
                ?>
                <style>
                    /*.form-field.wc_bookings_field_resource, .wc-bookings-date-picker.wc_bookings_field_start_date{display:none !important;visibility:hidden !important;height:0px;}*/
                    form.cart #wc-bookings-booking-form, form.cart .wpcpo-wrapper.wpcpo-wrap, form.cart .wc-bookings-booking-form-button {display:none !important;visibility:hidden !important;height:0px;}
                    form.cart .bookmsgnologin{display:block !important; visibility:visible !important;height:auto !important;}
                </style> 
                
                <?php
                }
                    
            }

            //Get all bookable products
                /*$products = wc_get_products( array(
                    'status' => 'publish',
                    'type' => 'booking',
                    'return' => 'ids',
                    'limit' => -1,
                ) );*/
            
            
        }

        add_action('woocommerce_bookings_cancelled_booking', 'set_sms_cancelled_booking_id');

        function set_sms_cancelled_booking_id($booking_id) {
            if (!$booking_id) return;

            // Optionally, you can fetch more booking info like this:
            $booking = get_wc_booking($booking_id);
            if ($booking) {
                if (function_exists('setCancelbookingSMS')) {
                    setCancelbookingSMS($booking_id, true);
                }
            }
        }

        add_action('woocommerce_order_status_cancelled', 'set_sms_cancelled_order_id');
        function set_sms_cancelled_order_id($order_id) {
            if (!$order_id) return;

            // Optionally, you can fetch more booking info like this:
            $order = wc_get_order($order_id);
            if ($order) {
                if (function_exists('setCancelbookingSMS')) {
                    setCancelbookingSMS($order_id);
                }
            }
        }
    
//2025.11.28 not to consider in-cart/unpaid bookings
    add_filter( 'wc_bookings_hold_duration_minutes', '__return_zero' );
    


   


    
?>