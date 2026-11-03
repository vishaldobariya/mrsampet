<?php
    /** Custom Redirect to Login page instead of My Account **/
        add_action('template_redirect','woo_check_loggedin');
        function woo_check_loggedin(){
            global $wp;
            $myurlcheck = 'my-account';
            $myurl = $_SERVER["REQUEST_URI"];
            $myurl_array = explode('/',$myurl);
            
            //if ( is_page('my-account') && !is_user_logged_in() ) {
            if ( (basename($myurl) == $myurlcheck) && !is_user_logged_in() ) {
                wp_redirect( home_url('/login') );
                die();
          }
        }
    
    /** Custom Redirect after Logins @ WooCommerce My Account **/
       // add_filter( 'woocommerce_login_redirect', 'bbloomer_customer_login_redirect', 9999 );
        function bbloomer_customer_login_redirect( $redirect_url ) {
            $redirect_url = get_site_url() . '/my-account';
            return $redirect_url;
        }
    
    /** Deny Login Upon Registration @ WooCommerce My Account **/
       // add_filter( 'woocommerce_registration_auth_new_customer', '__return_false' );
    
    /** Add Short Description in Cart & Checkout & Order **/
        //Display product short description in the Cart
            /*add_filter( 'woocommerce_cart_item_name', 'add_short_description_to_cart_item', 10, 3 );
            function add_short_description_to_cart_item( $product_name, $cart_item, $cart_item_key ) {
                $product = $cart_item['data'];
                
                // Check if the product has a short description
                if ( $short_description = $product->get_short_description() ) {
                    
                   /-* // Add a "Show Details" button
                    $product_name .= '<button class="btn btn-sm btn-primary ckoutdescription-btn">Show Details</button>';
                    
                    // Add the short description inside a container that will be toggled
                    $product_name .= '<div class="product-short-description-container d-none">';
                    $product_name .= '<p class="product-short-description">' . $short_description . '</p>';
                    $product_name .= '</div>';*-/
                    
                    $product_name .= '<p class="product-short-description">' . $short_description . '</p>';
                }
                
                return $product_name;
            }*/
        
        //Display product short description in the checkout
            /* add_filter( 'woocommerce_checkout_cart_item_quantity', 'add_short_description_to_checkout', 10, 3 );
            function add_short_description_to_checkout( $item_quantity, $cart_item, $cart_item_key ) {
                $product = $cart_item['data'];
                
                // Check if the product has a short description
                if ( $short_description = $product->get_short_description() ) {
                    $item_quantity .= '<p class="product-short-description">' . $short_description . '</p>';
                }
                
                return $item_quantity;
            }*/
        
        //Display short description in the order details
            add_action( 'woocommerce_order_item_meta_end', 'add_short_description_to_order', 10, 4 );
            function add_short_description_to_order( $item_id, $item, $order, $plain_text ) {
                $product = $item->get_product();
                
                // Check if the product has a short description
                if ( $short_description = $product->get_short_description() ) {
                    echo '<p class="product-short-description">' . $short_description . '</p>';
                }
            }
    
    /** Across Site show products added by wcfm_vendors only **/    
        function restrict_products_by_wcfm_vendor_role( $query ) {
            // Check if we are on the front-end and on the main query (not admin area)
            if ( !is_admin() && $query->is_main_query() && ( is_shop() || is_product_category() || is_search() ) ) {
                
                // Get users with the 'wcfm_vendor' role
                $vendor_users = get_users( array(
                    'role'    => 'wcfm_vendor',  // Only users with the 'wcfm_vendor' role
                    'fields'  => 'ID'            // We only need user IDs
                ) );
        
                // Ensure only products from 'wcfm_vendor' users are shown
                if ( !empty( $vendor_users ) ) {
                    $query->set( 'author__in', $vendor_users );
                }
        
                // Get users with the store name 'gpt' and exclude their products
                $store_name = 'mrsam'; // The store name to exclude
                $stores_to_exclude = get_users( array(
                    'meta_key'   => 'store_name', // Correct meta key for store name
                    'meta_value' => $store_name,  // The store name to exclude
                    'fields'     => 'ID',         // Only get user IDs of these vendors
                ) );
        
                // Exclude products from these users (vendors with store name 'gpt')
                if ( !empty( $stores_to_exclude ) ) {
                    $query->set( 'author__not_in', $stores_to_exclude );
                }
        
                // Exclude products from admins explicitly
                $admins = get_users( array(
                    'role'    => 'administrator', // Get administrators
                    'fields'  => 'ID'             // We only need user IDs
                ) );
        
                // If we have admin users, exclude their products
                if ( !empty( $admins ) ) {
                    $query->set( 'author__not_in', array_merge( $query->get( 'author__not_in', array() ), $admins ) );
                }
            }
        }
        add_action( 'pre_get_posts', 'restrict_products_by_wcfm_vendor_role' );
        
        
        /* Working but shows from admin also - above code is working
            
        function restrict_products_by_wcfm_vendor_role( $query ) {
            // Check if we're on the front-end and it's the main query
            if ( !is_admin() && $query->is_main_query() && ( is_shop() || is_product_category() || is_search() ) ) {
        
                // Exclude products from users whose role is not "wcfm_vendor"
                $query->set( 'role', 'wcfm_vendor' );
        
                // Exclude products from the vendor whose store name is "gpt"
                $store_name = 'mrsam';  // The store name to exclude
                $stores = get_users( array(
                    'meta_key'   => 'store_name', // Correct meta key for store name
                    'meta_value' => $store_name,  // The store name to exclude
                    'fields'     => 'ID', // Only get user IDs of these vendors
                ) );
        
                if ( !empty( $stores ) ) {
                    // Exclude products from these vendors
                    $query->set( 'author__not_in', $stores );
                }
            }
        }
        add_action( 'pre_get_posts', 'restrict_products_by_wcfm_vendor_role' );*/




    /**Single Product Page - home visit or clinic visit select by default > change dropdown & price**/
        function add_visit_parameter_script_to_bookable_productnew() {
            global $product, $post; 
            // Check if we are on a product page and it's a bookable product
            if (is_product() && $product->get_type() == 'booking') {
                //Get tax settings
                    $prices_include_tax = get_option( 'woocommerce_prices_include_tax' );
                    $display_prices_in_shop = get_option( 'woocommerce_tax_display_shop' );
                    $display_prices_in_cart = get_option( 'woocommerce_tax_display_cart' );
                
                // Ensure the tax class is available
                $taxClass = $product->get_tax_class();
                if (empty($taxClass)) {
                    $taxClass = '18-gst'; // Default tax class
                }
        
                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function($) {
                       // console.log('Checking URL parameters...');
                        
                        // Check if the 'visit' parameter exists in the URL
                        var urlParams = new URLSearchParams(window.location.search);
                        var visitParam = urlParams.get('visit');
                        
                        if (!visitParam) {
                            visitParam = "clinic";
                        }
                        
                        
                       // console.log('visitParam:', visitParam);  // Debugging the URL parameter
        
                        // Only run the script if 'visit' parameter is available
                        if (visitParam) {
                            // Check if the resource dropdown exists
                            var resourceDropdown = $('#wc_bookings_field_resource');
                            
                            if (resourceDropdown.length > 0) {
                                //console.log('Resource dropdown found.');
                                
                                // Loop through the options to find the one matching the 'visit' parameter
                                resourceDropdown.find('option').each(function() {
                                    var optionValue = $(this).val();
                                    var optionText = $(this).text();
                                    
                                   // console.log('Checking option value:', optionValue);  // Debugging option values
        
                                    // If the 'visit' parameter matches the option value
                                    if ((visitParam === 'home' && optionValue === '4065') || (visitParam === 'clinic' && optionValue === '4066')) {
                                        console.log('Match found, selecting option:', optionText);
        
                                        // Select the matching option
                                        $(this).prop('selected', true);
        
                                        // Manually trigger the price update by calling the price update function directly
                                        updatePriceBasedOnDropdownSelection();
                                    } 
                                });
                            }
                        } else {
                            console.log('No visit parameter found in the URL');
                        }
        
                        // This function will update the price based on the dropdown selection
                        function updatePriceBasedOnDropdownSelection() {
                            var selectedOption = resourceDropdown.find('option:selected');
                            var priceText = selectedOption.text(); // e.g. "Home Visit (+₹120.00)"
                            //var priceMatch = priceText.match(/\+₹([\d\.]+)/); // Match the price part
                            var priceMatch = priceText.match(/\+₹([\d,\.]+)/);
                            
                            if (priceMatch && priceMatch[1]) {
                                //var price = parseFloat(priceMatch[1]); // Price from the option
                                var price = parseFloat(priceMatch[1].replace(/,/g, ''));
                                //console.log('Price extracted:', price);
                                
                                // Get the tax rates for the product using the tax class
                                var taxClass = '<?php echo $taxClass; ?>';
                                var taxRates = <?php echo json_encode(WC_Tax::get_rates($taxClass)); ?>;
                                
                                //console.log('Tax Rates:', taxRates); // Debugging tax rates
        
                                // Initialize total tax rate
                                var totalTaxRate = 0;
        
                                // Iterate over all the tax rates and add them together
                                $.each(taxRates, function(taxKey, tax) {
                                    totalTaxRate += tax.rate;  // Add the tax rate
                                });
        
                                //console.log('Total Tax Rate:', totalTaxRate); // Debugging total tax rate
        
                                // Calculate the final price with tax
                                var finalPrice = price; //+ (price * totalTaxRate / 100);
                                
                                // Update the price in the HTML (Product price)
                                var priceElement = $('.elementor-widget-wolmart_sproduct_price .woocommerce-Price-amount.amount');
                                if (priceElement.length > 0) {
                                    var formattedPrice = finalPrice.toLocaleString('en-IN', { style: 'decimal', maximumFractionDigits: 2 });

                                    //priceElement.html('<bdi><span class="woocommerce-Price-currencySymbol">₹</span>' + finalPrice.toFixed(2) + '</bdi>');
                                    priceElement.html('<bdi><span class="woocommerce-Price-currencySymbol">₹</span>' + formattedPrice + '</bdi>');
                                }
        
                               //console.log('Final price with tax:', finalPrice.toFixed(2));
                            }
                        }
        
                        // Event listener for dropdown change
                        $(document).on('change', '#wc_bookings_field_resource', function() {
                            console.log('Dropdown value changed.');
                            
                            // Call the price update function when the dropdown value changes
                            updatePriceBasedOnDropdownSelection();
                        });
        
                    });
                </script>
                <?php
            }
        }
        add_action('wp_footer', 'add_visit_parameter_script_to_bookable_productnew');

        /* Only updates dropdown and not the price of product
        function add_visit_parameter_script_to_bookable_product() {
            global $product, $post; 
            // Check if we are on a product page and it's a bookable product
            if (is_product() && $product->get_type() == 'booking') {
                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function($) {
                        console.log('here');
                        // Check if the 'visit' parameter exists in the URL
                        var urlParams = new URLSearchParams(window.location.search);
                        var visitParam = urlParams.get('visit');
                        
                        // Only run the script if 'visit' parameter is available
                        if (visitParam) {
                            // Check if the resource dropdown exists
                            var resourceDropdown = $('#wc_bookings_field_resource');
        
                            if (resourceDropdown.length > 0) {
                                // Loop through the options to find the one matching the 'visit' parameter
                                resourceDropdown.find('option').each(function() {
                                    var optionValue = $(this).val();
        
                                    if ((visitParam === 'home' && optionValue === '4065') || 
                                        (visitParam === 'clinic' && optionValue === '4066')) {
                                        // Select the matching option
                                        $(this).prop('selected', true);
                                        
                                        // Trigger change event on the dropdown to update the price
                                        resourceDropdown.trigger('change');
                                        
                                       
                                    }
                                });
                            }
                        }
                    });
                </script>
                <?php
            }
        }
        add_action('wp_footer', 'add_visit_parameter_script_to_bookable_product');*/


    /**Open cart on product add to cart**/
        function open_fly_cart_on_add_to_cart() {
            if ( is_product() ){
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    console.log("Fly cart script is loaded.");
        
                    // Function to check for the 'alert-success' notification
                    function checkNotification() {
                        // Only trigger once if the notification is found
                        if ($('.woocommerce-notices-wrapper').find('.alert-success').length > 0) {
                            console.log("Product added to cart notification found. Opening the fly cart.");
        
                            // Check if the cart is already opened by checking for the 'opened' class
                            var cartWrapper = $('.dropdown.mini-basket-dropdown.cart-dropdown.block-type.badge-type.cart-offcanvas');
                            if (!cartWrapper.hasClass('opened')) {
                                console.log("Fly cart is not open, opening the fly cart.");
                                
                                // Open the fly cart by triggering a click event on the cart-toggle button
                                var cartToggle = $('.cart-toggle');
                                if (cartToggle.length) {
                                    cartToggle.click();
                                } else {
                                    console.log("Cart toggle element not found.");
                                }
                            } else {
                                console.log("Fly cart is already open.");
                            }
        
                            // Stop further checks once the cart has been opened
                            clearInterval(notificationCheckInterval);
                        }
                    }
        
                    // Perform an initial check immediately
                    checkNotification();
        
                    // Check every second for the notification
                    var notificationCheckInterval = setInterval(function() {
                        checkNotification();
                    }, 3000);
                });
            </script>
            <?php
        }
        }
        add_action('wp_footer', 'open_fly_cart_on_add_to_cart');

    /*Toggle Item Data in cart (Show/ Hide)*/
        function customCkOut_enqueue_scripts() {
            if (is_checkout() || is_cart()) {
                wp_enqueue_script('customCkOut-toggle', WP_STATICASSET_FOLDERNAME . '/js/checkout-toggle-ItemData.js', array('jquery'), null, true);
        
            }
        }
        add_action('wp_enqueue_scripts', 'customCkOut_enqueue_scripts');

  
    /**Hide Shipping Address if cart has only bookable products**/
        add_action( 'woocommerce_checkout_fields', 'conditionally_remove_shipping_fields_for_booking_products' );
        function conditionally_remove_shipping_fields_for_booking_products( $fields ) {
            if( is_checkout() ){
                // Flag to check if cart contains only booking products
                $only_booking_products = true;
                // Loop through each product in the cart
                foreach ( WC()->cart->get_cart() as $cart_item ) {
                    $product = $cart_item['data'];
                    // If a non-booking product is found, set the flag to false
                    if ( 'booking' !== $product->get_type() ) {
                        $only_booking_products = false;
                        break; // Exit loop if any non-booking product is found
                    }
                }
            
                // If the cart contains only booking products, remove the shipping fields
                if ( $only_booking_products ) {
                    // Unset the entire shipping section, including the checkbox and address fields
                    unset( $fields['shipping'] );
                    echo "<style>#customer_details .woocommerce-shipping-fields{display:none !important;}</style>";
                }
            
                return $fields;
            }    
        }

    
    /**Check Terms & Conditions by default on Checkout page**/
        add_action( 'wp_footer', 'custom_default_terms_checked_script' );
        function custom_default_terms_checked_script() {
            if ( is_checkout() && ! is_order_received_page() ) : ?>
                <script type="text/javascript">
                    document.addEventListener('DOMContentLoaded', function() {
                        const termsCheckbox = document.querySelector('#terms');
                        if (termsCheckbox && !termsCheckbox.checked) {
                            termsCheckbox.checked = true;
                        }
                    });
                </script>
            <?php
            endif;
        }










?>