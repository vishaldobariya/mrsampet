<?php
    /*Add Cancel button to orders landing page for bookings*/
       //add_filter( 'woocommerce_my_account_my_orders_actions', 'ctt_orderbookingcancel_action', 9999, 2 );
        function ctt_orderbookingcancel_action( $actions, $order ) {
            $booking_data = new WC_Booking_Data_Store();
            $booking_id = $booking_data->get_booking_ids_from_order_id( $order->id )[0];
            $booking = get_wc_booking( $booking_id );
            
            if ( $booking->get_status() !== 'cancelled' && $booking->get_status() !== 'completed' && ! $booking->passed_cancel_day() ) { 
                $actions['cancel_booking'] = array(
                    //'url' => wp_nonce_url( add_query_arg( 'cancel_booking=true&booking_id=', $order->get_id(), wc_get_cart_url() ), 'woocommerce-cancel_booking' ),
                    'url' => $booking->get_cancel_url(),
                    'name' => __( 'Cancel', 'mrsam' ),
                );
                
            }
            
            /*Loop through each order item to get the WooCommerce Booking details from $order
                foreach ( $order->get_items() as $item_id => $item ) {
                    $product = $item->get_product();
                    if ( $product && 'booking' === $product->get_type() ) {
                        $booking_id_meta = esc_html( $item->get_meta( '_booking_id' )[0] ?? 'N/A' );
                        echo $booking_id;
                    }
                }  
            */
             
             return $actions;
        }
    
    /*Rename Orders Tab*/
        function rename_orders_tab( $menu_links ) {
            if ( isset( $menu_links['orders'] ) ) {
                $menu_links['orders'] = 'Orders Details'; // Change the label here
            }
            return $menu_links;
        }
       add_filter( 'woocommerce_account_menu_items', 'rename_orders_tab' );
       

    /**Remove Notes option from My Account Orders page > WCFM**/    
        add_filter( 'wcfm_is_allow_customer_add_note', '__return_false' );

    /**Remove Orders Tab - Enable once Products are about to go live**/
        add_filter( 'woocommerce_account_menu_items', 'remove_orders_from_my_account', 999 );
        function remove_orders_from_my_account( $menu_links ) {
            unset( $menu_links['orders'] ); // Remove the "Orders" tab
            return $menu_links;
        }

    /**Order View - Add Cancel Button per order item**/
        add_action( 'woocommerce_order_item_meta_end', 'cttbookingcancel_before_order_item_meta', 10, 4 );
        function cttbookingcancel_before_order_item_meta( $item_id, $item, $order, $plain_text ) {
            // Example: only for a specific product ID
            $product_id = $item->get_product_id();
            $booking_data = new WC_Booking_Data_Store();
            $booking_id = WC_Booking_Data_Store::get_booking_ids_from_order_item_id( $item_id )[0];
            $booking = get_wc_booking( $booking_id );
            
            if ( $booking->get_status() !== 'cancelled' && $booking->get_status() !== 'completed' && ! $booking->passed_cancel_day() ) { 
                //echo '<a href="' . $booking->get_cancel_url() . '">Cancel Booking</a>';
                echo "<a class='btn btn-xs btn-danger btncancelbooking' href='{$booking->get_cancel_url()}'>Cancel Booking</a>";
            }
        }

    /**Password Strength Changes Globally**/
        //Disable WooCommerce password strength meter on registration page 
            function disable_woocommerce_password_strength_meter() {
                if (is_account_page()) {
                    wp_dequeue_script('wc-password-strength-meter');
                }
            }
            add_action('wp_print_scripts', 'disable_woocommerce_password_strength_meter', 100);

        // Remove password strength validation on reset (if present)
            remove_filter('validate_password_reset', 'wc_strong_passwords_validate_password_reset', 10);

        
    /** Save Mobile number field in My account**/
        //add_action( 'woocommerce_save_account_details_errors', 'validate_mobile_number_field', 10, 2 );
        function validate_mobile_number_field( $args, $user ) {
            if ( empty( $_POST['account_phone'] ) ) {
                $args->add( 'error', __( 'Please enter your Mobile Number.', 'woocommerce' ) );
            }
        }

        add_action( 'woocommerce_save_account_details', 'save_mobile_number_account_field' );
        function save_mobile_number_account_field( $user_id ) {
            if ( isset( $_POST['account_phone'] ) ) {
                update_user_meta( $user_id, 'billing_phone', sanitize_text_field( $_POST['account_phone'] ) );
            }
        }


    
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
?>