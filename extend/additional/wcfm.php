<?php

//--- Custom CSS For Store Manager pages
    function custom_sm_style_styles() {
        if (!is_super_admin()) {
            wp_enqueue_style( 'custom-sm-style' , WP_STATICASSET_FOLDERNAME . '/css/custom-storemanager.css' );
            //wp_enqueue_script('my-script', get_template_directory_uri() . '/script.js', array('jquery'), '1.0', true);
        }    
    }
    add_action('wp_enqueue_scripts', 'custom_sm_style_styles');

    
//--- Fix vendor documents image path issue
   /* add_filter( 'wp_get_attachment_url', function($myurl) {
       return str_replace( 'https://webworkdemo.com/mrsam/cdn/uploads/https:/webworkdemo.com/mrsam/cdn/uploads/', 'https://webworkdemo.com/mrsam/cdn/uploads/', $myurl );
    } );*/


//--- Don't auto login user on registration (for vendors)
    add_action('template_redirect', function( $template ) {
    	if( function_exists( 'wcfm_membership_registration_current_step' ) && function_exists( 'wcfm_is_vendor' ) ) {
    		$current_step = wcfm_membership_registration_current_step();
    		if( $current_step && ( $current_step == 'thankyou' ) && !wcfm_is_vendor() ) {
    			wp_clear_auth_cookie();
    		}
    	}
    	return $template;
    }, 1 );

//--- Restrict vendors to adding products only via ‘Add to My Store’
    add_filter( 'wcfm_is_allow_add_products', function ($is_allowed) {
    if(current_user_can( 'administrator' )) return $is_allowed;
        global $wp;
        return isset( $wp->query_vars['wcfm-sell-items-catalog'] );
    } );
    

//--- Add To My Store Catalog: Show Only Admin Products
    add_filter( 'wcfm_sell_items_catalog_args', function( $args ) {
    	if( isset( $args['author__not_in']  ) ) unset( $args['author__not_in']  );
    	$args['author'] = 1; 
    	return $args;
    }, 50 );
    add_filter( 'wcfmmp_is_allow_single_product_multivendor_by_product', function( $is_allow, $product_id ) {
    	global $WCFM, $WCFMmp, $product, $post;
    	
    	$product_author = wcfm_get_vendor_id_by_post( $product_id );
    	if( $product_author ) {
    		$is_allow = false;
    	}
    	return $is_allow;
    }, 50, 2 );
    
    
//--- WCFM admin show order status with icon + label instead of just icon
    add_filter( 'wcfm_order_status_display', function( $label, $the_order ) {
        $label .= '<br />' . wc_get_order_status_name( $the_order->get_status() );
        return $label;
    }, 50, 2 );
    

//--- Shortcode for getting all vendors for same parent product 
    //Function to get vendor list by product id
        function get_vendors_by_product($product_id) {
            global $wpdb;
            
            // Ensure valid product ID
            if (empty($product_id) || !absint($product_id)) return '';
            $product_id = absint($product_id);
            $product = wc_get_product($product_id);
            
            if (!$product) return '';
        
            // Get the vendor ID
            $vendor_id = wcfm_get_vendor_id_by_post($product_id);
            
            // Check if multivendor editing is allowed
            if (!apply_filters('wcfm_is_allow_product_multivendor_title_edit_disable', true)) return $vendor_id;
        
            // Check for multi-parent ID
            $multi_parent_id = get_post_meta($product_id, '_is_multi_parent', true);
            if (!$multi_parent_id) {
                $multi_parent_id = get_post_meta($product_id, '_has_multi_selling', true);
            }
        
            // If no multi-parent ID, return the vendor ID
            if (!$multi_parent_id) return $vendor_id;
        
            // Get the list of vendors for the multi-parent ID
            $sql = "SELECT GROUP_CONCAT(vendor_id) as vendors 
                    FROM `{$wpdb->prefix}wcfm_marketplace_product_multivendor` 
                    WHERE `parent_product_id` = $multi_parent_id";
            $results = $wpdb->get_row($sql);
            $vendor_list = isset($results->vendors) ? explode(',', $results->vendors) : array();
        
            // If the current vendor is not already in the list, add it
            if (wcfm_is_vendor($vendor_id) && !in_array($vendor_id, $vendor_list)) {
                array_unshift($vendor_list, $vendor_id);
            }
        
            return $vendor_list;
        }

    //Shortcode for to print vendors
        function wcfm_vendorslist_byproduct_shortcode($attr) {
            if (empty($attr['id']) || !absint($attr['id'])) return '';
            $product_id = absint($attr['id']);
            
            // Call the core function to get the vendors
            $vendors = get_vendors_by_product($product_id);
            
            if (empty($vendors)) return ''; // No vendors found
        
            // Return the list of vendors as a comma-separated string
            return implode(', ', $vendors);
        }
    
        add_shortcode('wcfm_vendorslist_by_product', 'wcfm_vendorslist_byproduct_shortcode');

    /*Original
        add_shortcode('wcfm_vendors_by_product', 'wcfm_vendors_by_product_shortcode');
        function wcfm_vendors_by_product_shortcode($attr) {
            global $wpdb;
            if ( empty( $attr['id'] ) || ! absint( $attr['id'] ) ) return '';
                $product_id = absint( $attr['id'] );
                $product = wc_get_product($product_id);
            
                if(!$product) return '';
                    $vendor_id = wcfm_get_vendor_id_by_post($product_id);
                if(!apply_filters( 'wcfm_is_allow_product_multivendor_title_edit_disable', true )) return $vendor_id;
                    $multi_parent_id = get_post_meta( $product_id, '_is_multi_parent', true );
                    if(!$multi_parent_id) {
                        $multi_parent_id = get_post_meta( $product_id, '_has_multi_selling', true );
                    }
            if(!$multi_parent_id) return $vendor_id;
          	
            $sql = "SELECT GROUP_CONCAT(vendor_id) as vendors FROM `{$wpdb->prefix}wcfm_marketplace_product_multivendor` WHERE `parent_product_id` = $multi_parent_id";
            $results = $wpdb->get_row( $sql );
            $vendor_list = isset($results->vendors) ? explode(',', $results->vendors) : array();
            if(wcfm_is_vendor($vendor_id) && !in_array($vendor_id, $vendor_list)) {
                array_unshift($vendor_list, $vendor_id);
            }
            return implode(', ', $vendor_list);
        }
    */

//--- Show other vendors data after cart
    //This code will remove the More Offers tab from bottom of the product details page
        //add_filter( 'woocommerce_product_tabs', 'remove_wcfm_more_offers_tab', 99 );
        function remove_wcfm_more_offers_tab($tabs) {
            if (isset($tabs['wcfm_product_multivendor_tab'])) {
                unset($tabs['wcfm_product_multivendor_tab']);
            }
            return $tabs;
        }

    //This code will add the More Offers section under the Add To Cart button in product details page
        //add_action( 'woocommerce_after_add_to_cart_button', 'add_more_offers_section', 10 );
        function add_more_offers_section() {
            global $product;
            echo do_shortcode( '[wcfm_more_offers]' );
        }


//--- Store page radius fix
    add_filter('wcfmmp_radius_filter_start_distance', function($start_distance){
        return 1;
    });

//-- shortcode test
    /*add_shortcode('mrsamcatstores_test', 'ct_customstorespagetest_shortcode');
    function ct_customstorespagetest_shortcode() { 
        return do_shortcode('[wcfm_stores store_category="3" has_category="no" has_zip="yes" has_orderby="no"]');

    }*/
    //include_once('wcfm_shortcodes.php');

//-- To get pricing based on Booking Reosurces (Home Visit/ Clinic Visit) #VD
    function getResorceWithTaxPrice($results)
    {
        foreach ($results as $key => $value) {
                $results[$key]['resource'] = "";
                // Get the product ID
                $product_id = $value['post_id']; // Replace with your actual product ID
                $product = wc_get_product( $product_id );
                
                // Get the booking product object
                $resource_costs = get_post_meta($product_id, '_resource_base_costs', true);

                if( $product && is_a( $product, 'WC_Product_Booking' ) )
                {
                    $resources = $product->get_resources();
                    
                      if ( ! empty( $resources ) ) {
                        foreach ( $resources as $resource  ) {
                            $resource_name = $resource->get_name();
                            if(isset($_GET['visit']))
                            {
                                $results[$key]['resource'] = $_GET['visit'];
                                $results[$key]['price'] =  $tax_amount =0;
                                $price = null;
                                
                                if($_GET['visit'] == 'home' && $resource_name == 'Home Visit')
                                {
                                    $resourceId = $resource->get_id();
                                }
                                else if($resource_name == 'Clinic Visit')
                                {
                                    $resourceId = $resource->get_id();
                                }
                                $price = $resource_costs[$resourceId];
                                
                            }
                            else // by default clinic
                            {
                                if($resource_name == 'Clinic Visit')
                                {
                                     $resourceId = $resource->get_id();
                                     $price = $resource_costs[$resourceId];
                                }
                            }


                            if($price > 0)
                            {
                                $prices_include_tax = get_option( 'woocommerce_prices_include_tax' );  // (value = yes/ no)
                                $display_prices_in_shop = get_option( 'woocommerce_tax_display_shop' );  // (value = incl/ excl)

                                /*echo "check price";
                                echo "<pre>";
                                print_r($prices_include_tax);
                                echo "</pre>";

                                echo "<pre>";
                                print_r($display_prices_in_shop);
                                echo "</pre>";*/

                                if ($prices_include_tax == "yes" && $display_prices_in_shop == "incl"){
                                    $results[$key]['price'] = round($price, 2);
                                }else if ($prices_include_tax == "no" && $display_prices_in_shop == "incl"){ 
                                    $gstPer = explode("-",$product->get_tax_class());
                                    $tax_amount = $price * ((float) $gstPer[0]/100);
                                    $results[$key]['price'] = round($price + $tax_amount, 2);
                                }
                            }
                        }
                    }
                }
            }

            /*echo "<pre>";
            print_r($results);
            echo "</pre>";*/

        return $results;
    }


//--- Shortcode for Custom Stores Page based on category (for Services Flow - Grooming, Health Package)
    add_shortcode('mrsamcatstores', 'ct_customstorespage_shortcode');
    function ct_customstorespage_shortcode() { 
        ob_start();
        global $wpdb, $wp;
        $url = end(explode("/", home_url( add_query_arg( array(), $wp->request ) )));
        $strCategoryName = str_replace("stores-", "", $url);
        $categoryName = ucwords(str_replace("-", " ", $strCategoryName));

        // Get the search term from the query string
        $plan = isset($_GET['plan']) ? sanitize_text_field($_GET['plan']) : ''; // selected plan here will come
        $results = $wpdb->get_results("
                SELECT {$wpdb->prefix}users.user_nicename as vendor_name, {$wpdb->prefix}posts.ID as post_id, {$wpdb->prefix}posts.post_name as  post_link, {$wpdb->prefix}postmeta.meta_value as price
                FROM {$wpdb->prefix}users INNER JOIN  {$wpdb->prefix}posts
                ON {$wpdb->prefix}users.ID = {$wpdb->prefix}posts.post_author
                INNER JOIN  {$wpdb->prefix}postmeta
                ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id
                WHERE {$wpdb->prefix}posts.post_title  LIKE '$categoryName %$plan% Package' AND {$wpdb->prefix}posts.post_status = 'publish' AND {$wpdb->prefix}postmeta.meta_key = '_price'  ", ARRAY_A);

            $results = getResorceWithTaxPrice($results);            
            $jsonVendorResult = json_encode($results);

            ?>
               <script type="text/javascript">
                var vendorData = JSON.parse('<?php echo $jsonVendorResult; ?>');
                jQuery(document).ready(function(){

                    // mrsamcatstorescall();

                    const sortSelect = `
                        Sort: <select id="sortPrice" class="ctStoreSortField" style="margin-bottom: 15px;">
                          <option value="asc">Price Low to High</option>
                          <option value="desc">Price High to Low</option>
                        </select>`;

                        


                    jQuery( document ).ajaxComplete(function() {
                      mrsamcatstorescall();
                      if(jQuery("#sortPrice").length == 0){
                        jQuery('.wcfmmp-store-wrap').before(sortSelect);
                        jQuery('#sortPrice').on('change', function() {
                            const sortOrder = jQuery(this).val();
                            sortWithPrice(sortOrder);
                        });
                      }
                    });

                    function mrsamcatstorescall(){
                        
                        // Get the 'pet' parameter from the URL
                        var urlParams = new URLSearchParams(window.location.search);
                        var petParam = urlParams.get('pet');  // Extract 'pet' parameter
                        var vaccineParam = urlParams.get('vaccine'); // Extract 'vaccine' parameter
                        

                        jQuery('.wcfmmp-visit-store').each(function() {
                            var arrayVendorLink = jQuery(this).attr('href').split("/");
                            const strVendorName = arrayVendorLink[arrayVendorLink.length - 1];
                            const key = "vendor_name"; // find vender name 
                            const intVendorIndex = vendorData.findIndex((item) => item[key] === strVendorName);
                            var paths = window.location.pathname.split("/");
                            var strExtraFolder = "";
                            if(paths[1] == "mrsam"){
                                strExtraFolder = "/mrsam"; // if extra folder then set here otherwise you set blank here
                            }
                            if(intVendorIndex > -1)
                            {
                                if(vendorData[intVendorIndex].price == null)
                                {
                                    jQuery(this).closest(".wcfmmp-single-store").remove();
                                }
                                else
                                {

                                    jQuery(this).attr('href',strExtraFolder+'/product/'+vendorData[intVendorIndex].post_id+'/'+vendorData[intVendorIndex].post_link+'/?visit='+vendorData[intVendorIndex].resource + '&pet=' + petParam + '&vaccine=' + vaccineParam);
                                    jQuery(this).html('Book');
                                    jQuery(this).before('<div class="bookPrice"> From ₹ <span class="bookonlyprice">'+vendorData[intVendorIndex].price+'</span></div>');
                                }
                            }
                        });

                        sortWithPrice('asc');
                    }

                    function sortWithPrice(sortname) {


                       let $ul = jQuery('.wcfmmp-store-wrap');
                        let $liItems = $ul.children('li');

                        $liItems.sort(function(a, b) {
                            const aPrice = parseFloat(jQuery(a).find('.bookonlyprice').text().replace(/[^\d.]/g, ''));
                            const bPrice = parseFloat(jQuery(b).find('.bookonlyprice').text().replace(/[^\d.]/g, ''));
                            if(sortname == 'asc')
                            {
                                return aPrice - bPrice;  // ascending order
                            }
                            else if(sortname == 'desc')
                            {
                                return bPrice - aPrice;  // decending order
                            }
                            return 0;
                        });

                        $ul.append($liItems);
                    }
                });
                </script>

        <?php
        
        return ob_get_clean();
    }
    
    


//-- Shorcode for Custom Stores page (non Plan Flow - like Vets, Vaccines)
    add_shortcode('mrsamcatstores_noplan', 'ct_customstorespage_noplan_shortcode');
    function ct_customstorespage_noplan_shortcode() { 

        ob_start();
        global $wpdb, $wp;
        $url = end(explode("/", home_url( add_query_arg( array(), $wp->request ) )));
        $strCategoryName = str_replace("stores-", "", $url);
        $categoryName = ucwords(str_replace("-", " ", $strCategoryName));
        $strLastCategoryName = explode("-", $strCategoryName)[1];

        /*$animalCategoryName = get_title_from_slug(explode("-",$categoryName)[0]);
        $lastCategoryName = get_title_from_slug(explode("-",$categoryName)[1]);
        $likeTitle = $animalCategoryName." ".$lastCategoryName;*/

        // Get the search term from the query string
       if($strCategoryName == "veterinarian")
       {
            $results = $wpdb->get_results("SELECT u.user_nicename as vendor_name, p.ID AS post_id, p.post_name AS post_link, t.name AS store_category , pm.meta_value as price
            FROM {$wpdb->prefix}posts p
            JOIN {$wpdb->prefix}users u ON p.post_author = u.ID
            JOIN {$wpdb->prefix}term_relationships tr ON p.ID = tr.object_id
            JOIN {$wpdb->prefix}term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            JOIN {$wpdb->prefix}terms t ON tt.term_id = t.term_id
            JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_price'
            WHERE p.post_type = 'product'
            AND p.post_status = 'publish'
            AND t.name = 'Veterinarian'", ARRAY_A);

       }
       else
       {

        $results = $wpdb->get_results("
        SELECT {$wpdb->prefix}users.user_nicename as vendor_name, {$wpdb->prefix}posts.ID as post_id, {$wpdb->prefix}posts.post_name as  post_link, {$wpdb->prefix}postmeta.meta_value as price
        FROM {$wpdb->prefix}users INNER JOIN  {$wpdb->prefix}posts
        ON {$wpdb->prefix}users.ID = {$wpdb->prefix}posts.post_author
        INNER JOIN  {$wpdb->prefix}postmeta
        ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id
        WHERE {$wpdb->prefix}posts.post_title  LIKE '$categoryName' AND {$wpdb->prefix}posts.post_status = 'publish' AND {$wpdb->prefix}postmeta.meta_key = '_price'  ", ARRAY_A);
       }

        $results = getResorceWithTaxPrice($results);  
        $jsonVendorResult = json_encode($results);
            ?>
               <script type="text/javascript">
                    var vendorData = JSON.parse('<?php echo $jsonVendorResult; ?>');
                    jQuery(document).ready(function(){
    
                        callShortCode();

                        const sortSelect = `
                        Sort: <select id="sortPrice" style="margin-bottom: 15px;">
                          <option value="asc">Price Low to High</option>
                          <option value="desc">Price High to Low</option>
                        </select>`;
                        
                        if(jQuery("#sortPrice").length == 0){
                            jQuery('.wcfmmp-store-wrap').before(sortSelect);
                            jQuery('#sortPrice').on('change', function() {
                                const sortOrder = jQuery(this).val();
                                sortWithPrice(sortOrder);
                            });
                        }
    
                        jQuery( document ).ajaxComplete(function() {
                          callShortCode();
                        });

                        
                        function callShortCode(){
                            
                            // Get the 'pet' parameter from the URL
                                var urlParams = new URLSearchParams(window.location.search);
                                var petParam = urlParams.get('pet');  // Extract 'pet' parameter
                                var vaccineParam = urlParams.get('vaccine'); // Extract 'vaccine' parameter
                                
                            jQuery('.wcfmmp-visit-store').each(function() {
                                var arrayVendorLink = jQuery(this).attr('href').split("/");
                                const strVendorName = arrayVendorLink[arrayVendorLink.length - 1];
                                const key = "vendor_name"; // find vender name 
                                const intVendorIndex = vendorData.findIndex((item) => item[key] === strVendorName);
                                var paths = window.location.pathname.split("/");
                                var strExtraFolder = "";
                                if(paths[1] == "mrsam"){
                                    strExtraFolder = "/mrsam"; // if extra folder then set here otherwise you set blank here
                                }
                                if(intVendorIndex > -1)
                                {
                                    if(vendorData[intVendorIndex].price == null)
                                    {
                                        jQuery(this).closest(".wcfmmp-single-store").remove();
                                    }
                                    else
                                    {
                                        jQuery(this).attr('href',strExtraFolder+'/product/'+vendorData[intVendorIndex].post_id+'/'+vendorData[intVendorIndex].post_link+'/?visit='+vendorData[intVendorIndex].resource + '&pet=' + petParam + '&vaccine=' + vaccineParam);
                                        jQuery(this).html('Book');
                                        jQuery(this).before('<div class="bookPrice"> From ₹ <span class="bookonlyprice">'+vendorData[intVendorIndex].price+'</span></div>');
                                    }
                                }
                            });
                           sortWithPrice('asc');
                        }

                        function sortWithPrice(sortname) {

                           let $ul = jQuery('.wcfmmp-store-wrap');
                            let $liItems = $ul.children('li');

                            $liItems.sort(function(a, b) {
                                const aPrice = parseFloat(jQuery(a).find('.bookonlyprice').text().replace(/[^\d.]/g, ''));
                                const bPrice = parseFloat(jQuery(b).find('.bookonlyprice').text().replace(/[^\d.]/g, ''));
                                if(sortname == 'asc')
                                {
                                    return aPrice - bPrice;  // ascending order
                                }
                                else if(sortname == 'desc')
                                {
                                    return bPrice - aPrice;  // decending order
                                }
                                return 0;
                            });

                            $ul.append($liItems);
                        }
                    });
                </script>

        <?php
        
        return ob_get_clean();
    }

    /*function get_title_from_slug($slug, $post_type = 'post') {
        $post = get_page_by_path($slug, OBJECT, $post_type);
        
        if ($post) {
            return get_the_title($post->ID);
        } else {
            return null; // Return null if no post is found
        }
    }*/
    
    

?>