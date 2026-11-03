<?php
/*
    shortcode for stores with all styles and get all vendors by master product as default do_shortcode does not work
    [mrsam_storesbyproducts id="4461"]
*/
    add_shortcode('mrsam_storesbyproducts', 'customstorespage_shortcode');
    function customstorespage_shortcode($attr) {
         ob_start();
        global $WCFM, $WCFMmp;$wpdb;
        
        //--Get Vendor List By Master Product Id
            if (empty($attr['id']) || !absint($attr['id'])) return '';
            $product_id = absint($attr['id']);
            
            // Call the core function to get the vendors
            $vendors_list = get_vendors_by_product($product_id);
            $vendors = implode(', ', $vendors_list);
            
        //--main stores shortcode    
    
        wp_enqueue_style(
            'wcfmmp_store_list_css',
            $WCFMmp->library->css_lib_url_min . 'store-lists/wcfmmp-style-stores-list.css',
            array(),
            $WCFMmp->version
        );
    
        $upload_dir      = wp_upload_dir();
        $wcfmmp_style_custom = get_option( 'wcfmmp_style_custom' );
        if( $wcfmmp_style_custom && file_exists( trailingslashit( $upload_dir['basedir'] ) . 'wcfm/' . $wcfmmp_style_custom ) ) {
            wp_enqueue_style( 'wcfmmp_store_list_style_custom',  trailingslashit( $upload_dir['baseurl'] ) . 'wcfm/' . $wcfmmp_style_custom, array( 'wcfmmp_store_list_css' ), $WCFMmp->version );
        }
    
        $theme = 'classic'; // Default theme (change if needed)
        if ($theme === 'classic') {
            wp_enqueue_style(
                'wcfmmp_store_list_classic_css',
                $WCFMmp->library->css_lib_url_min . 'store-lists/wcfmmp-style-stores-list-classic.css',
                array('wcfmmp_store_list_css'),
                $WCFMmp->version
            );
            // RTL support
            if (is_rtl()) {
                wp_enqueue_style(
                    'wcfmmp_store_list_classic_rtl_css',
                    $WCFMmp->library->css_lib_url_min . 'store-lists/wcfmmp-style-stores-list-classic-rtl.css',
                    array('wcfmmp_store_list_classic_css'),
                    $WCFMmp->version
                );
            }
        }
        
        $WCFM->library->load_select2_lib();
        wp_enqueue_script( 'wc-country-select' );
        
        $WCFMmp->library->load_map_lib();
        $default_geolocation = isset( $WCFMmp->wcfmmp_marketplace_options['default_geolocation'] ) ? $WCFMmp->wcfmmp_marketplace_options['default_geolocation'] : array();
        $default_lat         = isset( $default_geolocation['lat'] ) ? esc_attr( $default_geolocation['lat'] ) : apply_filters( 'wcfmmp_map_default_lat', 30.0599153 );
        $default_lng         = isset( $default_geolocation['lng'] ) ? esc_attr( $default_geolocation['lng'] ) : apply_filters( 'wcfmmp_map_default_lng', 31.2620199 );
        $default_zoom        =  apply_filters( 'wcfmmp_map_default_zoom_level', 17 );
        $max_radius_to_search = isset( $WCFMmp->wcfmmp_marketplace_options['max_radius_to_search'] ) ? $WCFMmp->wcfmmp_marketplace_options['max_radius_to_search'] : '100';
        $radius_unit          = isset( $WCFMmp->wcfmmp_marketplace_options['radius_unit'] ) ? $WCFMmp->wcfmmp_marketplace_options['radius_unit'] : 'km';
        wp_enqueue_script( 'wcfmmp_store_list_js', $WCFMmp->library->js_lib_url_min . 'store-lists/wcfmmp-script-store-lists.js', array('jquery' ), $WCFMmp->version, true );
        wp_localize_script( 'wcfmmp_store_list_js', 'wcfmmp_store_list_messages', array( 'choose_category' => __( 'Choose Category', 'wc-multivendor-marketplace' ), 'choose_location' => __( 'Choose Location', 'wc-multivendor-marketplace' ), 'choose_state' => __( 'Choose State', 'wc-multivendor-marketplace' ) ) );
        wp_localize_script( 'wcfmmp_store_list_js', 'wcfmmp_store_list_options', array( 'search_location' => __( 'Insert your address ..', 'wc-multivendor-marketplace' ), 'is_geolocate' => apply_filters( 'wcfmmp_is_allow_store_list_by_user_location', true ), 'max_radius' => apply_filters( 'wcfmmp_radius_filter_max_distance', $max_radius_to_search ), 'radius_unit' => ucfirst( $radius_unit ), 'start_radius' => apply_filters( 'wcfmmp_radius_filter_start_distance', 10 ), 'default_lat' => $default_lat, 'default_lng' => $default_lng, 'default_zoom' => absint( $default_zoom ), 'icon_width' => apply_filters( 'wcfmmp_map_icon_width', 40 ), 'icon_height' => apply_filters( 'wcfmmp_map_icon_height', 57 ), 'is_poi' => apply_filters( 'wcfmmp_is_allow_map_poi', true ), 'is_allow_scroll_zoom' => apply_filters( 'wcfmmp_is_allow_map_scroll_zoom', true ), 'is_cluster' => apply_filters( 'wcfmmp_is_allow_map_pointer_cluster', true ), 'cluster_image' => apply_filters( 'wcfmmp_is_cluster_image', 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m' ), 'is_rtl' => is_rtl() ) );
    
        //return do_shortcode('[wcfm_stores store_category="3" has_category="no" has_zip="yes" has_orderby="no"]');
        
        return do_shortcode('[wcfm_stores includes="'. $vendors . '" has_product="yes" has_category="no" has_zip="yes" search_zip="" has_orderby="no"]');
        
        return ob_get_clean();
    }


/*
    Custom Shortcode to get pricing by product options + resources - for vaccines
    ---Not used anymore, as code merged with no_plan SC---
*-/

    //custom function
        function getVaccineNamesAndPrices($results, $vaccinefromURL) {
            $finalData = [];
            
   
            // Loop through each result
            foreach ($results as $result) {
                $vendor_name = $result['vendor_name'];
                $wpcpo_fields = unserialize($result['wpcpo_fields']);
                
                $vaccine_names = [];
                $vaccine_prices = [];
                $resource_price = $result['price']; // Main price as resource_price
                
                $vaccine_from_url_array = array_map('trim', explode(',', $vaccinefromURL)); // trim spaces
        /*
                // Loop through each field in wpcpo_fields to get all vaccine pricing
                foreach ($wpcpo_fields as $field) {
                    // Check if 'options' key exists
                    if (isset($field['options']) && is_array($field['options'])) {
                        // Loop through each option and extract vaccine names and prices
                        foreach ($field['options'] as $option) {
                            if (isset($option['name']) && isset($option['price'])) {
                                $vaccine_names[] = $option['name'];
                                $prices[] = $option['price'];
                            }
                        }
                    }
                }
        
                // Join the vaccine names and prices with commas
                $finalData[$vendor_name] = [
                    'vaccine_name' => implode(', ', $vaccine_names), // Comma-separated vaccine names
                    'prices' => implode(', ', $prices), // Comma-separated vaccine prices
                    'resource_price' => $resource_price // The main price as resource_price
                ];
         *-/       
                
                //to get final total pricing based on vaccines in url + resources    

                    // Loop through each field in wpcpo_fields to extract vaccine names and prices
                        foreach ($wpcpo_fields as $field) {
                            if (isset($field['options']) && is_array($field['options'])) {
                                foreach ($field['options'] as $option) {
                                    // Check if the vaccine name is in the selected list from URL
                                    if (in_array($option['value'], $vaccine_from_url_array)) {
                                        // Add the price of this vaccine to the list
                                        $vaccine_prices[] = $option['price'];
                                    }
                                }
                            }
                        }
                    
                    // Sum up the prices of selected vaccines
                        $total_vaccine_price = array_sum($vaccine_prices);
                        
                    // Calculate the final price (sum of selected vaccine prices + resource price)
                        $final_price = $total_vaccine_price + $resource_price;
                        
                    // Store the result for this vendor
                        $finalData[$vendor_name] = [
                            'vaccine_name' => implode(', ', $vaccine_from_url_array), // Vaccines selected from URL
                            'prices' => implode(', ', $vaccine_prices), // Prices of selected vaccines
                            'resource_price' => $resource_price, // Main price
                            'final_price' => $final_price // Final price
                        ];    
                
            }   //foreach results ends
            return $finalData;
        }

    add_shortcode('getvendorprice_reswpcpo', 'getvendorprice_reswpcpo_shortcode');
    function getvendorprice_reswpcpo_shortcode($attr) {
        ob_start();
        global $WCFM, $WCFMmp, $wpdb, $wp;
        
        //--URL
            $url = end(explode("/", home_url( add_query_arg( array(), $wp->request ) )));
            $strCategoryName = str_replace("stores-", "", $url);
            $categoryName = ucwords(str_replace("-", " ", $strCategoryName));
            $strLastCategoryName = explode("-", $strCategoryName)[1];
        
        
        //--Get Vendor List By Master Product Id
            if (empty($attr['id']) || !absint($attr['id'])) return '';
            $product_id = absint($attr['id']);
            
            // Call the core function to get the vendors
            $vendors_list = get_vendors_by_product($product_id);
            $vendors = implode(', ', $vendors_list);
            
        //--Custom Query to get Pricing
            $vaccinefromURL = isset($_GET['vaccine']) ? sanitize_text_field($_GET['vaccine']) : ''; // selected plan here will come
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "
                    SELECT 
                        {$wpdb->prefix}users.user_nicename as vendor_name, 
                        {$wpdb->prefix}posts.ID as post_id, 
                        {$wpdb->prefix}posts.post_name as post_link, 
                        price_meta.meta_value as price, 
                        wpcpo_meta.meta_value as wpcpo_fields
                    FROM {$wpdb->prefix}users 
                    INNER JOIN {$wpdb->prefix}posts
                        ON {$wpdb->prefix}users.ID = {$wpdb->prefix}posts.post_author
                    INNER JOIN {$wpdb->prefix}postmeta AS price_meta
                        ON {$wpdb->prefix}posts.ID = price_meta.post_id
                        AND price_meta.meta_key = '_price'
                    INNER JOIN {$wpdb->prefix}postmeta AS wpcpo_meta
                        ON {$wpdb->prefix}posts.ID = wpcpo_meta.post_id
                        AND wpcpo_meta.meta_key = 'wpcpo-fields'
                    WHERE {$wpdb->prefix}posts.post_title LIKE %s 
                        AND {$wpdb->prefix}posts.post_status = 'publish'
                    ",
                    '%' . $wpdb->esc_like($categoryName) . '%'
                ),
                ARRAY_A
            );
    
            $results = getResorceWithTaxPrice($results);            
            $jsonVendorResult = json_encode($results);
            
            // Call the function and output the results
                $processedData = getVaccineNamesAndPrices($results, $vaccinefromURL);
                
            
          /* echo "<pre>";
            echo "VaccineName from URL = "; var_dump($vaccinefromURL);
            echo "----";
            print_r($processedData);
            echo "</pre>";
        *-/
        return ob_get_clean();
    }    
        
*/

?>