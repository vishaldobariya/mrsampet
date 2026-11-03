<?php
/*This file contains extra codes that are not in use*/

//--- Shortcode for getting all vendors for same product >> Works reverse - add latest product id, it gives all data.
    add_shortcode('wcfm_vendors_by_product', function($attr) {
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
    });
    
    
/* List of vendors selling a particular product (SPMV), sorted by product price ASC. Shows Just the cart
 * 
 * Shortcode: [wcfm_vendors_card_by_product id="123"]
 * The shortcode also supports receiving the product ID via a GET request, so you can place the shortcode on a page without the id attribute and pass the product ID through the page URL instead, e.g., https://yourdomain.com/{shortcode-page-slug}/?id=273
 * @param array $attr Shortcode attributes.
 * @return string HTML output of vendor cards.
 */
    add_shortcode('wcfm_vendors_card_by_product', function ($attr) {
        global $wpdb, $WCFMmp;
    
        $defaults = array(
            'id' => 0,
        );
        $atts = shortcode_atts($defaults, $attr);
    
        $product_id = absint(filter_input(INPUT_GET, 'id'));
        if (!$product_id && $atts['id'] && absint($atts['id'])) {
            $product_id = absint($atts['id']);
        }
        if (!$product_id) {
            return '';
        }
    
        $product = wc_get_product($product_id);
        if (!$product) {
            return '';
        }
    
        $vendor_id_single = wcfm_get_vendor_id_by_post($product_id);
        $vendor_list = [];
        $is_multi_vendor_enabled = apply_filters('wcfm_is_allow_product_multivendor_title_edit_disable', true);
    
        if (!$is_multi_vendor_enabled) {
            if(wcfm_is_vendor($vendor_id_single)) {
                $vendor_list[] = $vendor_id_single;
            }
        } else {
            $multi_parent_id = get_post_meta($product_id, '_is_multi_parent', true) ?: get_post_meta($product_id, '_has_multi_selling', true);
    
            if ($multi_parent_id) {
                $sql = $wpdb->prepare(
                    "SELECT GROUP_CONCAT(product_id) as products
                    FROM `{$wpdb->prefix}wcfm_marketplace_product_multivendor`
                    WHERE `parent_product_id` = %d",
                    $multi_parent_id
                );
                $results = $wpdb->get_row($sql);
                $product_list = isset($results->products) ? explode(',', $results->products) : array();
    
                if (!in_array($multi_parent_id, $product_list)) {
                    array_unshift($product_list, $multi_parent_id);
                }
    
                $product_ids = implode(',', $product_list);
                $sql = "SELECT product_id, stock_status, stock_quantity
                        FROM {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup
                        WHERE product_id IN (%1s)
                        ORDER BY wc_product_meta_lookup.min_price ASC";
                $prepare = $wpdb->prepare($sql, $product_ids);
                $product_metas = $wpdb->get_results($prepare);
    
                if (!empty($product_metas)) {
                    foreach ($product_metas as $product_meta) {
                        if ($product_meta->stock_status === 'outofstock') {
                            continue;
                        }
                        $post_status = get_post_status($product_meta->product_id);
                        if ($post_status !== 'publish') {
                            continue;
                        }
                        $vendor_id = wcfm_get_vendor_id_by_post($product_meta->product_id);
                        if (wcfm_is_vendor($vendor_id) && !in_array($vendor_id, $vendor_list)) {
                            $vendor_list[] = $vendor_id;
                        }
                    }
                }
            } else {
                if (wcfm_is_vendor($vendor_id_single) && !in_array($vendor_id_single, $vendor_list)) {
                    $vendor_list[] = $vendor_id_single;
                }
            }
        }
    
        if (empty($vendor_list)) {
            return '';
        }
    
        ob_start();
        ?>
        <div id="wcfmmp-stores-wrap" class="top-vendor-container">
            New Code
            <div class="wcfmmp-stores-content">
                <ul class="top-vendor-list wcfmmp-store-wrap">
                    <?php
                    $args = array('per_row' => 3);
                    foreach ($vendor_list as $vendor_id) {
                        $args['store_id'] = $vendor_id;
                        $WCFMmp->template->get_template('store-lists/wcfmmp-view-store-lists-card.php', $args);
                    }
                    ?>
                </ul>
            </div>
        </div>
        <?php
        $list = ob_get_contents();
        ob_end_clean();
        return $list;
    });


    /*Custom Comission calculation*/
       /* add_action( 'wcfmmp_order_item_processed', function( $commission_id, $order_id, $order, $vendor_id, $product_id, $order_item_id, $grosse_total, $total_commission, $is_auto_withdrawal ) {
    	global $WCFM, $WCFMmp, $wpdb;
    	
    	if( apply_filters( 'wcfm_is_admin_fee_mode', false ) ) {
    		$commission_rule   = unserialize( $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta( $commission_id, 'commission_rule' ) );
    		$gross_sale_order  = (float) $WCFM->wcfm_vendor_support->wcfm_get_gross_sales_by_vendor( $vendor_id, '', '', $order_id );
    		$commission_tax    = (float) $WCFMmp->wcfmmp_commission->wcfmmp_get_commission_meta( $commission_id, 'commission_tax' );
    		$total_commission += $commission_tax;
    		
    		$admin_fee = (float) $gross_sale_order - (float) $total_commission;
    		
    		if( isset( $commission_rule['tax_enable'] ) && ( $commission_rule['tax_enable'] == 'yes' ) ) {
    			$commission_tax = $admin_fee * ( (float)$commission_rule['tax_percent'] / 100 );
    			$total_commission -= (float) $commission_tax;
    			
    			$WCFMmp->wcfmmp_commission->wcfmmp_delete_commission_meta( $commission_id, 'commission_tax' );
    			$WCFMmp->wcfmmp_commission->wcfmmp_update_commission_meta( $commission_id, 'commission_tax', round($commission_tax, 2) );
    			$wpdb->update("{$wpdb->prefix}wcfm_marketplace_orders", array('total_commission' => $total_commission), array('ID' => $commission_id), array('%s'), array('%d'));
    		}
    	}
    }, 50, 9 );*/




?>