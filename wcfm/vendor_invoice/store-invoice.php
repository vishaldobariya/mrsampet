<?php
use Automattic\WooCommerce\Utilities\OrderUtil;

global $WCFM, $WCFMu, $wpdb, $wpo_wcpdf, $order, $order_id, $document, $document_type, $vendor_id, $process_item_ids, $process_product_ids, $process_shipping_items, $wcfm_vendor_invoice_id;

if (WC()->payment_gateways()) {
    $payment_gateways = WC()->payment_gateways->payment_gateways();
} else {
    $payment_gateways = [];
}

if (! is_a($order, 'WC_Order')) {
    $payment_method = '';
} else {
    $payment_method = ! empty($order->get_payment_method()) ? $order->get_payment_method() : '';
}

$order_type_object = get_post_type_object(OrderUtil::get_order_type( $order_id ));

// Get line items
$line_items = $order->get_items(apply_filters('woocommerce_admin_order_item_types', 'line_item'));

$order_taxes = $classes_options = $tax_breakups = $shipping_tax_breakups = [];
if (apply_filters('wcfm_store_invoice_tax_line_item', true)) {
    if (wc_tax_enabled()) {
        $order_taxes         = $order->get_taxes();
        $tax_classes         = WC_Tax::get_tax_classes();
        $classes_options[''] = __('Standard', 'wc-frontend-manager-ultimate');

        if (! empty($tax_classes)) {
            foreach ($tax_classes as $class) {
                $classes_options[sanitize_title($class)] = $class;
            }
        }

        // Older orders won't have line taxes so we need to handle them differently :(
        $tax_data = '';
        if ($line_items) {
            $check_item = current($line_items);
            $tax_data   = maybe_unserialize(isset($check_item['line_tax_data']) ? $check_item['line_tax_data'] : '');
        } else if ($line_items_shipping) {
            $check_item = current($line_items_shipping);
            $tax_data   = maybe_unserialize(isset($check_item['taxes']) ? $check_item['taxes'] : '');
        } else if ($line_items_fee) {
            $check_item = current($line_items_fee);
            $tax_data   = maybe_unserialize(isset($check_item['line_tax_data']) ? $check_item['line_tax_data'] : '');
        }

        $legacy_order     = ! empty($order_taxes) && empty($tax_data) && ! is_array($tax_data);
        $show_tax_columns = ! $legacy_order || sizeof($order_taxes) === 1;
    }//end if
}//end if

if (! empty($order_taxes)) {
    foreach ($order_taxes as $tax_id => $tax_item) {
        $column_label = ! empty($tax_item['label']) ? $tax_item['label'] : __('Tax', 'wc-frontend-manager');
        $tax_breakups[$tax_item->get_rate_id()]          = [
            'label'    => $column_label,
            'subtotal' => 0,
            'total'    => 0,
        ];
        $shipping_tax_breakups[$tax_item->get_rate_id()] = [
            'label'    => $column_label,
            'subtotal' => 0,
            'total'    => 0,
        ];
    }
}

$wcfm_vendor_invoice_options    = get_option('wcfm_vendor_invoice_options', []);
$wcfm_vendor_invoice_active     = isset($wcfm_vendor_invoice_options['enable']) ? 'yes' : '';
$wcfm_vendor_invoice_logo       = isset($wcfm_vendor_invoice_options['logo']) ? 'yes' : '';
$wcfm_vendor_invoice_store      = isset($wcfm_vendor_invoice_options['store']) ? 'yes' : '';
$wcfm_vendor_invoice_address    = isset($wcfm_vendor_invoice_options['address']) ? 'yes' : '';
$wcfm_vendor_invoice_email      = isset($wcfm_vendor_invoice_options['email']) ? 'yes' : '';
$wcfm_vendor_invoice_phone      = isset($wcfm_vendor_invoice_options['phone']) ? 'yes' : '';
$wcfm_vendor_invoice_policies   = isset($wcfm_vendor_invoice_options['policies']) ? 'yes' : '';
$wcfm_vendor_invoice_disclaimer = isset($wcfm_vendor_invoice_options['disclaimer']) ? 'yes' : '';
$wcfm_vendor_invoice_signature  = isset($wcfm_vendor_invoice_options['signature']) ? 'yes' : '';
$wcfm_vendor_invoice_fields     = isset($wcfm_vendor_invoice_options['fields']) ? $wcfm_vendor_invoice_options['fields'] : [];
$wcfm_vendor_invoice_data       = (array) wcfm_get_user_meta($vendor_id, 'wcfm_vendor_invoice_options', true);
$wcfm_vendor_disclaimer         = isset($wcfm_vendor_invoice_data['disclaimer']) ? $wcfm_vendor_invoice_data['disclaimer'] : '';
$wcfm_vendor_signature          = isset($wcfm_vendor_invoice_data['signature']) ? wcfm_get_attachment_url($wcfm_vendor_invoice_data['signature']) : '';
$vendor_gst_number              = get_user_meta($vendor_id, 'gst-registration-number', true);


$shipping_amt = 0;
$shipping_tax = 0;

//--Invoice Suffix
    // Determine current financial year (India: April 1 – March 31)
    $month = date('n');
    $year  = date('Y');
    
    if ($month >= 4) {
        $fy = $year . '-' . substr($year + 1, -2); // e.g., 2025-26
    } else {
        $fy = ($year - 1) . '-' . substr($year, -2); // e.g., 2024-25
    }
    
    // Append as suffix
    $wcfm_vendor_invoice_sufix = '/' . $fy;
//--Invoice Suffix

?>
<?php do_action('wcfm_before_store_invoice_document', $vendor_id, $order_id, $order, $document); ?>

<table class="head container">
  <?php do_action('wcfm_before_store_invoice_header', $vendor_id, $order_id, $order, $document); ?>
    <tr>
        <td class="header">
            <?php
            $store_name = wcfm_get_vendor_store_name($vendor_id);
            $store_logo = wcfm_get_vendor_store_logo_by_vendor($vendor_id);
            /*if ($store_logo && $wcfm_vendor_invoice_logo) {
                printf('<img style="max-width:150px;" src="%1$s" alt="%2$s" />', $store_logo, $store_name);
            } else if ($document->has_header_logo()) {
                $document->header_logo();
            } else {
                echo apply_filters('wcfm_store_invoice_title', __('Store Invoice', 'wc-frontend-manager-ultimate'));
            }*/
            printf('<img style="max-width:150px;" src="https://mymrsam.com/cdn/uploads/MRSAMLOGO_622x250.png" alt="%2$s" />', $store_logo, $store_name);
            ?>
        </td>
    <?php /*    
        <td class="shop-info">
          <?php do_action('wcfm_store_invoice_vendor_info_before', $vendor_id); ?>
          <?php if ($wcfm_vendor_invoice_store) { ?>
              <div class="vendor-shop-name"><h3><?php echo $store_name; ?></h3></div>
          <?php } ?>
            <?php if ($wcfm_vendor_invoice_address) { ?>
                <div class="vendor-shop-address"><?php echo wcfm_get_vendor_store_address_by_vendor($vendor_id); ?></div>
            <?php } ?>
          <?php if ($wcfm_vendor_invoice_email) { ?>
              <div class="vendor-shop-email"><?php echo wcfm_get_vendor_store_email_by_vendor($vendor_id); ?></div>
          <?php } ?>
          <?php if ($wcfm_vendor_invoice_phone) { ?>
              <div class="vendor-shop-phone"><?php echo wcfm_get_vendor_store_phone_by_vendor($vendor_id); ?></div>
          <?php } ?>
    <?php
    if (! empty($wcfm_vendor_invoice_fields)) {
        foreach ($wcfm_vendor_invoice_fields as $wvif_key => $wcfm_vendor_invoice_field) {
            if (isset($wcfm_vendor_invoice_field['is_active']) && $wcfm_vendor_invoice_field['field']) {
                if (isset($wcfm_vendor_invoice_data[$wvif_key]) && $wcfm_vendor_invoice_data[$wvif_key]) {
                    ?>
                            <div class="vendor-shop-phone"><?php _e($wcfm_vendor_invoice_field['field'], 'wc-frontend-manager-ultimate'); ?>: <?php echo $wcfm_vendor_invoice_data[$wvif_key]; ?></div>
                            <?php
                }
            }
        }
    }

            do_action('wcfm_store_invoice_vendor_info_after', $vendor_id, $order_id, $order, $document);
    ?>
        </td>
    */?>    
    </tr>

    <?php do_action('wcfm_after_store_invoice_header', $vendor_id, $order_id, $order, $document); ?>
</table>

<h1 class="document-type-label" style="text-align:center;">
    <?php
        if ($document->has_header_logo()) {
            echo apply_filters('wcfm_store_invoice_title', __('Tax Invoice', 'wc-frontend-manager-ultimate'));
        }
    ?>
    <br><small>ORIGINAL For Recipient</small>
</h1>

<?php do_action('wcfm_after_store_invoice_document_label', $vendor_id, $order_id, $order, $document); ?>

<table class="order-data-addresses storeinfo">
    <!--<tr>
        <td>
            Tax Invoice on behalf of -
        </td>
    </tr>-->
    <tr>
        <td class="shop-info">
            <?php do_action('wcfm_store_invoice_vendor_info_before', $vendor_id); ?>
            <?php if ($wcfm_vendor_invoice_store) { ?>
              <div class="vendor-shop-name"><b>Legal Entity Name :</b> <?php echo $store_name; ?></div>
              <div class="vendor-shop-name"><b>Legal Entity GSTIN :</b> <?php echo $vendor_gst_number ? esc_html($vendor_gst_number) : 'N/A'; ?></div>
            <?php } ?>
            <?php if ($wcfm_vendor_invoice_address) { 
                $address = wcfm_get_vendor_store_address_by_vendor($vendor_id);
                // replace line breaks with comma
                    $address = preg_replace('/<br\s*\/?>/i', ', ', $address);
                // remove any remaining HTML
                    $address = strip_tags($address);
                // clean extra spaces
                    $address_single_line = trim(preg_replace('/\s+/', ' ', $address));
            ?>
                <div class="vendor-shop-address"><b>Address :</b> <?php echo $address_single_line; //wcfm_get_vendor_store_address_by_vendor($vendor_id); ?></div>
            <?php } ?>
            <?php /* if ($wcfm_vendor_invoice_email) { ?>
                <div class="vendor-shop-email"><?php echo wcfm_get_vendor_store_email_by_vendor($vendor_id); ?></div>
            <?php } ?>
            <?php if ($wcfm_vendor_invoice_phone) { ?>
                <div class="vendor-shop-phone"><?php echo wcfm_get_vendor_store_phone_by_vendor($vendor_id); ?></div>
            <?php } */ ?>
            <?php
                if (! empty($wcfm_vendor_invoice_fields)) {
                    foreach ($wcfm_vendor_invoice_fields as $wvif_key => $wcfm_vendor_invoice_field) {
                        if (isset($wcfm_vendor_invoice_field['is_active']) && $wcfm_vendor_invoice_field['field']) {
                            if (isset($wcfm_vendor_invoice_data[$wvif_key]) && $wcfm_vendor_invoice_data[$wvif_key]) {
                                ?>
                                        <div class="vendor-shop-phone"><?php _e($wcfm_vendor_invoice_field['field'], 'wc-frontend-manager-ultimate'); ?>: <?php echo $wcfm_vendor_invoice_data[$wvif_key]; ?></div>
                                        <?php
                            }
                        }
                    }
                }
                do_action('wcfm_store_invoice_vendor_info_after', $vendor_id, $order_id, $order, $document);
            ?>
        </td>
    </tr>
    <tr>
        <td class="order-data">
            <?php do_action('wpo_wcpdf_before_order_data', $document_type, $order); ?>
            
            <?php if ($wcfm_vendor_invoice_id) { ?>
                <div class="invoice-number"><b><?php _e('Invoice No. :', 'wc-frontend-manager-ultimate'); ?></b> <?php echo $wcfm_vendor_invoice_id; ?></div>
            <?php } ?>

            <?php 
                //if (isset($document->settings->template_settings['display_date']) && $document->settings->template_settings['display_date'] == 'invoice_date') { 
                $completed_date = $order->get_date_completed();
                $myinvoicedate = $completed_date;
                if ($completed_date instanceof WC_DateTime) {
                    $myinvoicedate =  wc_format_datetime($completed_date);
                } else {
                    // fallback if not completed
                        ob_start();
                        $document->invoice_date(); // echoes
                        $myinvoicedate = ob_get_clean(); // capture it
                        
                        //$myinvoicedate = $order->get_date_created();
            
                }
            
            
            ?>
                <div class="invoice-date">
                    <b><?php _e('Invoice Date :', 'wc-frontend-manager-ultimate'); ?></b> <?php echo $myinvoicedate; ?>
                </div>
            <?php //} ?>
            
            <div class="order-number"><b><?php _e('Order No. :', 'wc-frontend-manager-ultimate'); ?></b> #<?php echo $order->get_order_number(); ?></div>
            <div class="order-date"><b><?php _e('Order Date :', 'wc-frontend-manager-ultimate'); ?></b> <?php echo date_i18n(wc_date_format(), strtotime($order->get_date_created())); ?> @<?php echo date_i18n(wc_time_format(), strtotime($order->get_date_created())); ?></div>
            <div class="payment-method"><b><?php _e('Payment Method :', 'wc-frontend-manager-ultimate'); ?></b> <?php 
                        $payment_method_title = isset($payment_gateways[ $payment_method ]) ? esc_html($payment_gateways[ $payment_method ]->get_title()) : esc_html($payment_method);
                        printf(__($payment_method_title, 'wc-frontend-manager-ultimate'));
                    ?>
            </div>
            <div class="gst-supply"><b>Supply Attracts Reverse Charge :</b> No</div>
            
            <?php do_action('wpo_wcpdf_after_order_data', $document_type, $order); ?>
        </td>    
    </tr>
    <tr>
        <td style="height:20px;">&nbsp;</td>
    </tr>
    <tr>
        <td class="customer-data">
            <?php
            
                $shipping_first = $order->get_shipping_first_name();
                $shipping_last  = $order->get_shipping_last_name();
                
                if ( !empty($shipping_first) || !empty($shipping_last) ) {
                    $customer_name = trim( $shipping_first . ' ' . $shipping_last );
                } else {
                    $customer_name = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
                }
                
            ?>
            <div class="customer-name"><b><?php _e('Customer Name :', 'wc-frontend-manager-ultimate'); ?></b> <?php echo $customer_name; ?></div>
            
            <?php 
                $shipping = $order->get_formatted_shipping_address();
                // Fallback to billing if shipping is empty
                if ( empty( $shipping ) ) {
                    $shipping = $order->get_formatted_billing_address();
                }
                
                // Remove the customer name from the start
                // Pattern: remove everything until the first <br> (usually the name)
                $shipping = preg_replace('/^.*?<br\s*\/?>/i', '', $shipping);
                
                // Replace **all <br> tags with comma + space**
                $shipping = preg_replace('/<br\s*\/?>/i', ', ', $shipping);
                
                // Remove any remaining HTML tags (if any)
                $shipping = strip_tags($shipping);
                
                // Collapse multiple spaces into one
                $shipping_single_line = preg_replace('/\s+/', ' ', $shipping);
                
                // Trim extra commas or spaces
                $shipping_single_line = trim($shipping_single_line, ', ');
                

            //if (( ( $shipping && $order->needs_shipping_address() ) || apply_filters('wcfm_is_force_shipping_address', false) ) && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability($vendor_id, 'view_shipping_details')) { 
            if (! empty ($shipping_single_line) ) { ?>
                <div class="delivery-address"><b><?php _e('Delivery Address :', 'wc-frontend-manager-ultimate'); ?></b> <?php echo esc_html($shipping_single_line); ?> </div>
            <?php } ?>
            
            <?php
                if (apply_filters('wcfm_is_allow_order_data_after_shipping_address', true)) {
                    do_action('woocommerce_admin_order_data_after_shipping_address', $order);
                }
            ?>
        </td>
    </tr>
</table>
<?php /*
<table class="order-data-addresses">
    <tr>
        <td class="address billing-address">
            <!-- <h3><?php _e('Billing Address:', 'wc-frontend-manager-ultimate'); ?></h3> -->
            <?php if (wcfm_vendor_has_capability($vendor_id, 'view_billing_details')) { ?>
                <?php echo wp_kses($order->get_formatted_billing_address(), [ 'br' => [] ]); ?>
            <?php } ?>
            <?php if ($order->get_billing_email() && wcfm_vendor_has_capability($vendor_id, 'view_email') && apply_filters('wcfm_allow_view_customer_billing_email', true)) { ?>
              <div class="billing-email"><?php echo $order->get_billing_email(); ?></div>
            <?php } ?>
            <?php if ($order->get_billing_phone() && wcfm_vendor_has_capability($vendor_id, 'view_email') && apply_filters('wcfm_allow_view_customer_billing_phone', true)) { ?>
              <div class="billing-phone"><?php echo $order->get_billing_phone(); ?></div>
            <?php } ?>
        </td>
        <td class="address shipping-address">
            <?php if (( ( ( $shipping = $order->get_formatted_shipping_address() ) && $order->needs_shipping_address() ) || apply_filters('wcfm_is_force_shipping_address', false) ) && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability($vendor_id, 'view_shipping_details')) { ?>
                <h3><?php _e('Ship To:', 'wc-frontend-manager-ultimate'); ?></h3>
                <?php echo $shipping; ?>
            <?php } ?>
    <?php
    if (apply_filters('wcfm_is_allow_order_data_after_shipping_address', true)) {
        do_action('woocommerce_admin_order_data_after_shipping_address', $order);
    }
    ?>
        </td>
    
        <td class="order-data">
            <table>
                <?php do_action('wpo_wcpdf_before_order_data', $document_type, $order); ?>
                <tr class="invoice-number">
                    <th><?php _e('Invoice Numberrrr:', 'wc-frontend-manager-ultimate'); ?></th>
                    <td><?php echo $wcfm_vendor_invoice_id; ?></td>
                </tr>
                <?php if (isset($document->settings->template_settings['display_date']) && $document->settings->template_settings['display_date'] == 'invoice_date') { ?>
                <tr class="invoice-date">
                    <th><?php _e('Invoice Date:', 'wc-frontend-manager-ultimate'); ?></th>
                    <td><?php $document->invoice_date(); ?></td>
                </tr>
                <?php } ?>
                <tr class="order-number">
                    <th><?php _e('Order Number:', 'wc-frontend-manager-ultimate'); ?></th>
                    <td>#<?php echo $order->get_order_number(); ?></td>
                </tr>
                <tr class="order-date">
                    <th><?php _e('Order Date:', 'wc-frontend-manager-ultimate'); ?></th>
                    <td><?php echo date_i18n(wc_date_format(), strtotime($order->get_date_created())); ?> @<?php echo date_i18n(wc_time_format(), strtotime($order->get_date_created())); ?></td>
                </tr>
                <tr class="payment-method">
                    <th><?php _e('Payment Method:', 'wc-frontend-manager-ultimate'); ?></th>
                    <td><?php 
                        $payment_method_title = isset($payment_gateways[ $payment_method ]) ? esc_html($payment_gateways[ $payment_method ]->get_title()) : esc_html($payment_method);
                        printf(__($payment_method_title, 'wc-frontend-manager-ultimate'));
                    ?></td>
                </tr>
                <?php do_action('wpo_wcpdf_after_order_data', $document_type, $order); ?>
            </table>            
        </td>
        
    </tr>
</table>
*/ ?>
<?php do_action('wcfm_before_store_invoice_order_details', $vendor_id, $order_id, $order); ?>
<?php //------- New Table ------- ?>
    <table class="order-details">
        <thead>
            <tr>
                <td class="product" colspan="3"><?php _e('Particulars', 'wc-frontend-manager-ultimate'); ?></td> 
                <td class="price"><?php _e('Gross Value', 'wc-frontend-manager-ultimate'); ?></td>
                <td class="discount"><?php _e('Discount', 'wc-frontend-manager-ultimate'); ?></td>
                <td class="line_cost"><?php _e('Net Value', 'wc-frontend-manager-ultimate'); ?></td>
                
                <?php //Tax
                    if (wc_tax_enabled() && apply_filters('wcfm_store_invoice_tax_line_item', true)) { 
                        if (empty($legacy_order) && ! empty($order_taxes)) :
                            foreach ($order_taxes as $tax_id => $tax_item) :
                                $tax_class      = wc_get_tax_class_by_tax_id($tax_item['rate_id']);
                                $tax_class_name = isset($classes_options[$tax_class]) ? $classes_options[$tax_class] : __('Tax', 'wc-frontend-manager');
                                $column_label   = ! empty($tax_item['label']) ? $tax_item['label'] : __('Tax', 'wc-frontend-manager');
                                $column_tip     = $tax_item['name'].' ('.$tax_class_name.')';
                                ?>
                                    <td class="line_tax text_tip" data-tip="<?php echo esc_attr($column_tip); ?>">
                                        <?php echo esc_attr($column_label); ?> (Rate)
                                    </td>
                                    <td class="line_tax text_tip" data-tip="<?php echo esc_attr($column_tip); ?>">
                                        <?php echo esc_attr($column_label); ?>
                                    </td>
                                <?php
                            endforeach;
                        endif;
                    } 
                ?>
                
                <td class="line_cost"><?php _e('Total', 'wc-frontend-manager-ultimate'); ?></td>
            </tr>
        </thead>    
        <?php
            $tax_total     = 0;
            $invoice_total = 0;
        ?>
        <tbody>
            <?php
                foreach ($line_items as $item_id => $item) :
                    if ($process_item_ids && ( in_array($item_id, $process_item_ids) )) {
                        $_product = $item->get_product();
            ?>
            
                <tr class="<?php echo apply_filters('wpo_wcpdf_item_row_class', $item_id, $document_type, $order, $item_id); ?>">
                    <!--product-->
                    <td class="product" colspan="3">
                        <span class="item-name"><?php echo esc_html(apply_filters('wcfm_order_item_name', $item->get_name(), $item)); ?></span>
                        <?php
                            if ($_product && $_product->get_sku()) {
                                echo '<div class="wc-order-item-sku"><strong>'.__('SKU:', 'wc-frontend-manager').'</strong> '.esc_html($_product->get_sku()).'</div>';
                            }
        
                            if (! empty($item->get_variation_id())) {
                                echo '<div class="wc-order-item-variation"><strong>'.__('Variation ID:', 'wc-frontend-manager').'</strong> ';
                                if (! empty($item->get_variation_id()) && 'product_variation' === get_post_type($item->get_variation_id())) {
                                    echo esc_html($item->get_variation_id());
                                } else if (! empty($item->get_variation_id())) {
                                    echo esc_html($item->get_variation_id()).' ('.__('No longer exists', 'wc-frontend-manager').')';
                                }
        
                                echo '</div>';
                            }
                        ?>
                        <div class="view">
                            <?php do_action('woocommerce_before_order_itemmeta', $item_id, $item, $_product); ?>
                            <?php do_action('wcfm_pdf_invoice_before_item_meta', $document_type, $item, $order); ?>
                            <?php //wc_display_item_meta($item); ?>
                            <?php do_action('wcfm_pdf_invoice_after_item_meta', $document_type, $item, $order); ?>
                            <?php
                                if (! class_exists('WC_Deposits_Order_Item_Manager') || ( class_exists('WC_Deposits_Order_Item_Manager') && ! WC_Deposits_Order_Item_Manager::is_deposit($item) )) {
                                                do_action('woocommerce_after_order_itemmeta', $item_id, $item, $_product);
                                }
                            ?>
                            <?php
                                //GET HSN CODE
                                    if ($_product) {
                                        // Handle variation products properly
                                        $product_id = $_product->is_type('variation') 
                                            ? $_product->get_parent_id() 
                                            : $_product->get_id();
                                    
                                        // Get categories (safe method)
                                        $terms = wc_get_product_terms($product_id, 'product_cat', ['orderby' => 'parent', 'order' => 'DESC']);
                                    
                                        if (!empty($terms) && !is_wp_error($terms)) {
                                    
                                            foreach ($terms as $term) {
                                    
                                                // Try ACF first
                                                $hsn_code = function_exists('get_field') 
                                                    ? get_field('gst_hsncode', 'product_cat_' . $term->term_id) 
                                                    : '';
                                    
                                                // Fallback to native term meta (IMPORTANT)
                                                if (!$hsn_code) {
                                                    $hsn_code = get_term_meta($term->term_id, 'gst_hsncode', true);
                                                }
                                    
                                                if ($hsn_code) {
                                                    echo '<div class="wc-order-item-hsn">
                                                            <strong>' . __('HSN Code :', 'wc-frontend-manager') . '</strong> 
                                                            ' . esc_html($hsn_code) . '
                                                          </div>';
                                                    break; // stop after first valid HSN
                                                }
                                            }
                                        }
                                    }
                                //END HSN CODE
                            ?>
                        </div>
                    </td>
                    
                    <!--gross value-->
                    <td class="price">
                        <?php
                            if ($item->get_total()) {
                                echo wc_price($order->get_item_total($item, false, true), [ 'currency' => $order->get_currency() ]);
                                echo '<small class="times">&times;</small> '.( $item->get_quantity() ? esc_html($item->get_quantity()) : '1' );
                            }
                        ?>
                    </td>
                    
                    <!--discount-->
                    <td class="discount">
                        <?php
                            $discount = 0;
                            // WooCommerce stores line item subtotal and total. Subtotal includes original price, total includes discount
                            $line_subtotal = $item->get_subtotal(); // before discount
                            $line_total    = $item->get_total();    // after discount
                    
                            if ($line_subtotal > $line_total) {
                                $discount = $line_subtotal - $line_total;
                            }
                    
                            echo wc_price($discount, ['currency' => $order->get_currency()]);
                        ?>
                    </td>
                    
                    <!--Net Value-->
                    <td class="line_cost" data-sort-value="<?php echo esc_attr(( $item->get_total() ) ? $item->get_total() : ''); ?>">
                        <div class="view">
                            <?php
                                if ($item->get_total()) {
                                    $invoice_total += (float) $item->get_total();
                                    echo wc_price($item->get_total(), [ 'currency' => $order->get_currency() ]);
                                }
                            ?>
                        </div>
                    </td>
                    
                    <!--Taxes-->
                    <?php if (apply_filters('wcfm_store_invoice_tax_line_item', true)) { ?>
                    <?php
                        if (( $tax_data = $item->get_taxes() ) && wc_tax_enabled()) {
                            foreach ($order_taxes as $tax_item) {
                                $tax_item_id       = $tax_item['rate_id'];
                                $tax_item_total    = isset($tax_data['total'][$tax_item_id]) ? $tax_data['total'][$tax_item_id] : '';
                                $tax_item_subtotal = isset($tax_data['subtotal'][$tax_item_id]) ? $tax_data['subtotal'][$tax_item_id] : '';
                                
                                // Get tax rate %
                                $tax_rate_percent = WC_Tax::get_rate_percent($tax_item_id);
                    ?>
                                
                                <td class="line_tax">
                                    <div class="view">
                                        <?php echo esc_html($tax_rate_percent ? $tax_rate_percent : '&ndash;'); ?>
                                    </div>
                                </td>
                                
                                <td class="line_tax">
                                    <div class="view">
                                        <?php
                                            if ('' != $tax_item_total) {
                                                $invoice_total += (float) $tax_item_total;
                                                $tax_total     += (float) $tax_item_total;
                                                $tax_breakups[$tax_item_id]['total'] += (float) $tax_item_total;
                                                echo wc_price(wc_round_tax_total($tax_item_total), [ 'currency' => $order->get_currency() ]);
                                            } else {
                                                echo '&ndash;';
                                            }
                                        ?>
                                    </div>
                                </td>
                    <?php
                            }//end foreach
                        }//end if
                    ?>
                    <?php }//end if ?>
                    
                    
                    <!--total-->
                    <td class="line_cost">
                        <?php
                            $line_total_cost = $item->get_total(); // total price for this product (after discount)
                    
                            // If you want to include tax in total per product, add tax:
                            if (($tax_data = $item->get_taxes()) && wc_tax_enabled()) {
                                foreach ($tax_data['total'] as $tax_amount) {
                                    $line_total_cost += (float) $tax_amount;
                                }
                            }
                    
                            echo wc_price($line_total_cost, ['currency' => $order->get_currency()]);
                        ?>
                    </td>
                    
                </tr>
                
            <?php
                    }//end if
                endforeach;
            ?>
        </tbody>    
        
        <!--Total Values Footer-->
        <?php
            $total_gross    = 0;
            $total_discount = 0;
            $total_tax      = [];
            $total_order    = 0;
            
            // Loop through ALL items
            foreach ($line_items as $item_id => $item) {
                //IMPORTANT: filter only vendor items
                if ($process_item_ids && in_array($item_id, $process_item_ids)) {
                    $line_subtotal = $item->get_subtotal();
                    $line_total    = $item->get_total();
                
                    // Gross
                    $total_gross += $line_subtotal;
                
                    // Discount
                    $line_discount = max(0, $line_subtotal - $line_total);
                    $total_discount += $line_discount;
                
                    // Taxes
                    if (($tax_data = $item->get_taxes()) && wc_tax_enabled()) {
                        foreach ($tax_data['total'] as $tax_id => $tax_amount) {
                            if (!isset($total_tax[$tax_id])) $total_tax[$tax_id] = 0;
                            $total_tax[$tax_id] += (float) $tax_amount;
                        }
                
                        // Add tax to line total
                        $line_total += array_sum($tax_data['total']);
                    }
                
                    // Final total
                    $total_order += $line_total;
                }
            }
            /* works only for single product hence commented
                // Gross value per item (subtotal)
                $line_subtotal = $item->get_subtotal();
                $total_gross += $line_subtotal;
                
                // Discount per item
                $line_discount = max(0, $line_subtotal - $item->get_total());
                $total_discount += $line_discount;
                
                // Tax per tax rate
                if (($tax_data = $item->get_taxes()) && wc_tax_enabled()) {
                    foreach ($tax_data['total'] as $tax_id => $tax_amount) {
                        if (!isset($total_tax[$tax_id])) $total_tax[$tax_id] = 0;
                        $total_tax[$tax_id] += (float) $tax_amount;
                    }
                }
                
                // Total order value per line including taxes
                $line_total_with_tax = $item->get_total();
                if (!empty($tax_data)) {
                    $line_total_with_tax += array_sum($tax_data['total']);
                }
                $total_order += $line_total_with_tax;
            */
        ?>
        
        
        <tfoot>
            <tr class="order-total">
                <!-- Product column (empty) -->
                <td class="product" colspan="3"><strong><?php _e('Total Value', 'wc-frontend-manager-ultimate'); ?></strong></td>

                <!-- Total Gross Value -->
                <td class="price">
                    <?php echo wc_price($total_gross, ['currency' => $order->get_currency()]); ?>
                </td>

                <!-- Total Discount -->
                <td class="discount">
                    <?php echo wc_price($total_discount, ['currency' => $order->get_currency()]); ?>
                </td>

                <!-- Net Value column (sum of line totals before tax) -->
                <td class="line_cost">
                    <?php
                        $total_net = $total_gross - $total_discount;
                        echo wc_price($total_net, ['currency' => $order->get_currency()]);
                    ?>
                </td>

                <!-- Tax columns (loop through order_taxes to keep same column count) -->
                <?php
                if (!empty($order_taxes)) {
                    foreach ($order_taxes as $tax_item) {
                        $tax_id = $tax_item['rate_id'];
                        $tax_rate_percent = WC_Tax::get_rate_percent($tax_id);
                        $tax_amount_total = isset($total_tax[$tax_id]) ? $total_tax[$tax_id] : 0;
                ?>
                        <!-- Tax Rate -->
                        <td class="line_tax"></td>
                        <!-- Tax Amount -->
                        <td class="line_tax"><?php echo wc_price($tax_amount_total, ['currency' => $order->get_currency()]); ?></td>
                <?php
                    }
                }
                ?>

                <!-- Total Order Value -->
                <td class="line_cost">
                    <?php echo wc_price($total_order, ['currency' => $order->get_currency()]); ?>
                </td>
            </tr>    
        </tfoot>
        
    </table>    
<?php //------- New Table ------- ?>

<div class="amount-inwords">
    <?php
        //Function for Amount in Words
        if (!function_exists('convert_number_to_words')) {
            function convert_number_to_words($number) {
                $hyphen      = '-';
                $conjunction = ' and ';
                $separator   = ', ';
                $negative    = 'negative ';
                $decimal     = ' point ';
                $dictionary  = array(
                    0                   => 'Zero',
                    1                   => 'One',
                    2                   => 'Two',
                    3                   => 'Three',
                    4                   => 'Four',
                    5                   => 'Five',
                    6                   => 'Six',
                    7                   => 'Seven',
                    8                   => 'Eight',
                    9                   => 'Nine',
                    10                  => 'Ten',
                    11                  => 'Eleven',
                    12                  => 'Twelve',
                    13                  => 'Thirteen',
                    14                  => 'Fourteen',
                    15                  => 'Fifteen',
                    16                  => 'Sixteen',
                    17                  => 'Seventeen',
                    18                  => 'Eighteen',
                    19                  => 'Nineteen',
                    20                  => 'Twenty',
                    30                  => 'Thirty',
                    40                  => 'Forty',
                    50                  => 'Fifty',
                    60                  => 'Sixty',
                    70                  => 'Seventy',
                    80                  => 'Eighty',
                    90                  => 'Ninety',
                    100                 => 'Hundred',
                    1000                => 'Thousand',
                    100000              => 'Lakh',
                    10000000            => 'Crore'
                );
            
                if (!is_numeric($number)) {
                    return false;
                }
            
                if ($number < 0) {
                    return $negative . convert_number_to_words(abs($number));
                }
            
                $string = '';
                $fraction = '';
            
                if (strpos($number, '.') !== false) {
                    list($number, $fraction) = explode('.', $number);
                }
            
                switch (true) {
                    case $number < 21:
                        $string = $dictionary[$number];
                        break;
                    case $number < 100:
                        $tens   = ((int) ($number / 10)) * 10;
                        $units  = $number % 10;
                        $string = $dictionary[$tens];
                        if ($units) {
                            $string .= $hyphen . $dictionary[$units];
                        }
                        break;
                    case $number < 1000:
                        $hundreds  = (int) ($number / 100);
                        $remainder = $number % 100;
                        $string    = $dictionary[$hundreds] . ' ' . $dictionary[100];
                        if ($remainder) {
                            $string .= $conjunction . convert_number_to_words($remainder);
                        }
                        break;
                    default:
                        foreach ([10000000=>'Crore', 100000=>'Lakh', 1000=>'Thousand', 100=>'Hundred'] as $divisor => $name) {
                            if ($number >= $divisor) {
                                $quotient  = (int) ($number / $divisor);
                                $remainder = $number % $divisor;
                                $string = convert_number_to_words($quotient) . ' ' . $name;
                                if ($remainder) {
                                    $string .= $remainder < 100 ? $conjunction : $separator;
                                    $string .= convert_number_to_words($remainder);
                                }
                                break;
                            }
                        }
                        break;
                }
            
                if ($fraction) {
                    //$string .= ' and ' . convert_number_to_words($fraction) . ' Paisa';
                    // Make sure fraction is two digits
                    $fraction = str_pad(substr($fraction, 0, 2), 2, '0', STR_PAD_RIGHT);
                    if ((int)$fraction > 0) {
                        $string .= ' and ' . convert_number_to_words((int)$fraction) . ' Paisa';
                    }
                }
            
                return $string;
            }
        }    
    
    ?>
    
    <strong><?php _e('Amount (in words):', 'wc-frontend-manager-ultimate'); ?></strong> <?php echo convert_number_to_words(number_format($total_order, 2, '.', '')) . ' Only'; ?>
</div>


<table class="footer" style="position:fixed; bottom: 0; left: 0; right: 0;width:100%; font-size:9pt; padding-top:5px;">
    <tr>
        <td colspan="3"><b>For MR SAM INDIA PRIVATE LIMITED</b></td>
        <td style="text-align:right;">For <b><?php echo $store_name; ?></b></td>
    </tr>
    <tr>
        <td colspan="3">
            PAN : AANCM5714F<br>
            CIN : U74999MH2020PTC341908<br>
            GST : 27AANCM5714F1ZC<br>
            
        </td>
        <td style="text-align:right;font-size:8pt;">
            <img src="https://mymrsam.com/cdn/uploads/img_mrsam_invoicesign-e1775736400751.png" style="height:50px;width:auto;"/>
            <br>Authorised Signatory 
            
            
        </td>
    </tr>
</table>


<?php /* Original Table ?>    
<table class="order-details">
    <thead>
        <tr>
            <td class="product" colspan="2"><?php _e('Product', 'wc-frontend-manager-ultimate'); ?></td>
            <td class="price"><?php _e('Price', 'wc-frontend-manager-ultimate'); ?></td>
            <td class="line_cost"><?php _e('Total', 'wc-frontend-manager-ultimate'); ?></td>
            <?php if (wc_tax_enabled() && apply_filters('wcfm_store_invoice_tax_line_item', true)) { ?>
                <?php
    if (empty($legacy_order) && ! empty($order_taxes)) :
                    foreach ($order_taxes as $tax_id => $tax_item) :
                        $tax_class      = wc_get_tax_class_by_tax_id($tax_item['rate_id']);
                        $tax_class_name = isset($classes_options[$tax_class]) ? $classes_options[$tax_class] : __('Tax', 'wc-frontend-manager');
                        $column_label   = ! empty($tax_item['label']) ? $tax_item['label'] : __('Tax', 'wc-frontend-manager');
                        $column_tip     = $tax_item['name'].' ('.$tax_class_name.')';
                        ?>
                            <td class="line_tax text_tip" data-tip="<?php echo esc_attr($column_tip); ?>">
                            <?php echo esc_attr($column_label); ?>
                            </td>
                            <?php
                    endforeach;
    endif;
                ?>
            <?php } ?>
        </tr>
    </thead>
    <?php
    $tax_total     = 0;
    $invoice_total = 0;
    ?>
    <tbody>
        <?php
        foreach ($line_items as $item_id => $item) :
            if ($process_item_ids && ( in_array($item_id, $process_item_ids) )) {
                $_product = $item->get_product();
                ?>
        <tr class="<?php echo apply_filters('wpo_wcpdf_item_row_class', $item_id, $document_type, $order, $item_id); ?>">
            <td class="product" colspan="2">
                <span class="item-name"><?php echo esc_html(apply_filters('wcfm_order_item_name', $item->get_name(), $item)); ?></span>
                    <?php
                    if ($_product && $_product->get_sku()) {
                        echo '<div class="wc-order-item-sku"><strong>'.__('SKU:', 'wc-frontend-manager').'</strong> '.esc_html($_product->get_sku()).'</div>';
                    }

                    if (! empty($item->get_variation_id())) {
                        echo '<div class="wc-order-item-variation"><strong>'.__('Variation ID:', 'wc-frontend-manager').'</strong> ';
                        if (! empty($item->get_variation_id()) && 'product_variation' === get_post_type($item->get_variation_id())) {
                            echo esc_html($item->get_variation_id());
                        } else if (! empty($item->get_variation_id())) {
                            echo esc_html($item->get_variation_id()).' ('.__('No longer exists', 'wc-frontend-manager').')';
                        }

                        echo '</div>';
                    }
                    ?>
                <div class="view">
                              <?php do_action('woocommerce_before_order_itemmeta', $item_id, $item, $_product); ?>
                  <?php do_action('wcfm_pdf_invoice_before_item_meta', $document_type, $item, $order); ?>
                    <?php wc_display_item_meta($item); ?>
                    <?php do_action('wcfm_pdf_invoice_after_item_meta', $document_type, $item, $order); ?>
                <?php
    if (! class_exists('WC_Deposits_Order_Item_Manager') || ( class_exists('WC_Deposits_Order_Item_Manager') && ! WC_Deposits_Order_Item_Manager::is_deposit($item) )) {
                    do_action('woocommerce_after_order_itemmeta', $item_id, $item, $_product);
    }
                ?>
                </div>
            </td>
            <td class="price">
                <?php
                if ($item->get_total()) {
                    echo wc_price($order->get_item_total($item, false, true), [ 'currency' => $order->get_currency() ]);
                    echo '<small class="times">&times;</small> '.( $item->get_quantity() ? esc_html($item->get_quantity()) : '1' );
                }
                ?>
            </td>
            <td class="line_cost" data-sort-value="<?php echo esc_attr(( $item->get_total() ) ? $item->get_total() : ''); ?>">
                <div class="view">
                    <?php
                    if ($item->get_total()) {
                        $invoice_total += (float) $item->get_total();
                        echo wc_price($item->get_total(), [ 'currency' => $order->get_currency() ]);
                    }
                    ?>
                </div>
            </td>
            
                            <?php if (apply_filters('wcfm_store_invoice_tax_line_item', true)) { ?>
    <?php
                                if (( $tax_data = $item->get_taxes() ) && wc_tax_enabled()) {
                                    foreach ($order_taxes as $tax_item) {
                                        $tax_item_id       = $tax_item['rate_id'];
                                        $tax_item_total    = isset($tax_data['total'][$tax_item_id]) ? $tax_data['total'][$tax_item_id] : '';
                                        $tax_item_subtotal = isset($tax_data['subtotal'][$tax_item_id]) ? $tax_data['subtotal'][$tax_item_id] : '';
                                        ?>
                            <td class="line_tax">
                                <div class="view">
                                    <?php
                                        if ('' != $tax_item_total) {
                                            $invoice_total += (float) $tax_item_total;
                                            $tax_total     += (float) $tax_item_total;
                                            $tax_breakups[$tax_item_id]['total'] += (float) $tax_item_total;
                                            echo wc_price(wc_round_tax_total($tax_item_total), [ 'currency' => $order->get_currency() ]);
                                        } else {
                                            echo '&ndash;';
                                        }
                                        ?>
                                </div>
                            </td>
                                <?php
                                    }//end foreach
                                }//end if
                                ?>
                                <?php
                            }//end if
                            ?>
            
        </tr>
            <?php
            }//end if
        endforeach;
        ?>
    </tbody>

    
    <?php if (! empty($process_shipping_items) && apply_filters('wcfm_is_allow_invoice_order_shipping_line_items', true)) { ?>
        <tbody id="order_shipping_line_items">
          <?php
          $shipping_methods = WC()->shipping() ? WC()->shipping->load_shipping_methods() : [];
            foreach ($process_shipping_items as $item_id => $item) {
                ?>
                <tr class="shipping <?php echo ( ! empty($class) ) ? $class : ''; ?>" data-order_item_id="<?php echo $item_id; ?>">
                    <td class="name" colspan="3">
                        <div class="view">
                            <?php echo ! empty($item->get_name()) ? wc_clean($item->get_name()) : __('Shipping', 'wc-frontend-manager-ultimate'); ?>
                        </div>
                
                        <div class="view">
                          <?php do_action('woocommerce_before_order_itemmeta', $item_id, $item, null); ?>
                            <?php wc_display_item_meta($item); ?>
                            <?php do_action('woocommerce_after_order_itemmeta', $item_id, $item, null); ?>
                        </div>
                    </td>
                    
                    <?php do_action('woocommerce_admin_order_item_values', null, $item, absint($item_id)); ?>
                
                    <td class="line_cost">
                        <div class="view">
                            <?php
                            $shipping_amt += wc_round_tax_total($item['cost']);
                                echo ( isset($item['cost']) ) ? wc_price(wc_round_tax_total($item['cost']), [ 'currency' => $order->get_currency() ]) : '';

                            if ($refunded = $order->get_total_refunded_for_item($item_id, 'shipping')) {
                                echo '<small class="refunded">-'.wc_price($refunded, [ 'currency' => $order->get_currency() ]).'</small>';
                            }
                            ?>
                        </div>
                    </td>
                
                    <?php
                    if (( $tax_data = $item->get_taxes() ) && wc_tax_enabled() && apply_filters('wcfm_store_invoice_tax_line_item', true)) {
                        foreach ($order_taxes as $tax_item) {
                            $tax_item_id    = $tax_item->get_rate_id();
                            $tax_item_total = isset($tax_data['total'][$tax_item_id]) ? $tax_data['total'][$tax_item_id] : '';
                            ?>
                                    <td class="line_tax no_ipad no_mob" >
                                        <div class="view">
                                        <?php
                                        $shipping_tax += wc_round_tax_total($tax_item_total);
                                            $shipping_tax_breakups[$tax_item_id]['total'] += wc_round_tax_total($tax_item_total);
                                            echo ( '' != $tax_item_total ) ? wc_price(wc_round_tax_total($tax_item_total), [ 'currency' => $order->get_currency() ]) : '&ndash;';

                                        if ($refunded = $order->get_tax_refunded_for_item($item_id, $tax_item_id, 'shipping')) {
                                            echo '<small class="refunded">-'.wc_price($refunded, [ 'currency' => $order->get_currency() ]).'</small>';
                                        }
                                        ?>
                                        </div>
                                    </td>
                
                                <?php
                        }
                    }//end if
                    ?>
                </tr>
                <?php
            }//end foreach

            do_action('woocommerce_admin_order_items_after_shipping', $order->get_id());
            ?>
        </tbody>
        <?php
    }//end if
    ?>
</table>


<table class="notes-totals" style="width:100%">
    <tbody>
        <tr class="no-borders">
            <td class="no-borders" style="width:60%">
                <div class="customer-notes">
                    <?php do_action('wcfm_pdf_invoice_before_customer_notes', $document_type, $order); ?>
                    <?php if ($document->get_shipping_notes()) : ?>
                        <h3><?php _e('Customer Notes', 'wc-frontend-manager-ultimate'); ?></h3>
                        <?php $document->shipping_notes(); ?>
                    <?php endif; ?>
                    <?php do_action('wcfm_pdf_invoice_after_customer_notes', $document_type, $order); ?>
                </div>                
            </td>
          
            <td class="no-borders" style="width:40%">
                <table class="totals">
                    <tfoot>
                
                        <?php if (wc_tax_enabled()) : ?>
    <?php
                            if (apply_filters('wcfm_is_allow_vendor_order_details_tax_breakup', false)) {
                                if (! empty($tax_breakups)) {
                                    foreach ($tax_breakups as $tax_breakup_is => $tax_breakup) {
                                        ?>
                                        <tr>
                                            <th class="label description" colspan="2" style="text-align:right;"><?php echo $tax_breakup['label']; ?>:</th>
                                            <td class="total price" style="text-align:center;">
                                            <?php
                                            echo wc_price($tax_breakup['total'], [ 'currency' => $order->get_currency() ]);
                                            ?>
                                            </td>
                                        </tr>
                                            <?php
                                    }
                                }
                            }
                            ?>
                            <?php if (apply_filters('wcfm_is_allow_vendor_order_details_tax_breakup_total', true)) { ?>
                                <tr>
                                    <th class="label description" colspan="2" style="text-align:right;">
                                        <?php
                                        if (apply_filters('wcfm_is_allow_vendor_order_details_tax_breakup', false)) {
                                            echo __('Total', 'wc-frontend-manager').' ';
                                        }
                                        ?>
                                        <?php _e('Tax', 'wc-frontend-manager-ultimate'); ?>:
                                    </th>
                                    <td class="total price" style="text-align:center;">
                                    <?php
                                    echo wc_price($tax_total, [ 'currency' => $order->get_currency() ]);
                                    ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php endif; ?>
                        
                        <?php if ($order->get_formatted_shipping_address() && apply_filters('wcfm_is_allow_invoice_order_shipping_line_items', true)) { ?>
                            <tr>
                                <th class="label description" colspan="2" style="text-align:right;"><?php _e('Shipping', 'wc-frontend-manager-ultimate'); ?>:</th>
                                <td class="total price" style="text-align:center;">
                                    <?php
                                    if (wcfm_is_marketplace() == 'wcfmmarketplace') {
                                        $invoice_total += $shipping_amt;
                                        echo wc_price($shipping_amt, [ 'currency' => $order->get_currency() ]);
                                    } else {
                                        $invoice_total += (float) $order->get_total_shipping();
                                        echo wc_price($order->get_total_shipping(), [ 'currency' => $order->get_currency() ]);
                                    }
                                    ?>
                                </td>
                            </tr>
                        
                            <?php
                            if (apply_filters('wcfm_is_allow_vendor_order_details_tax_breakup', false)) {
                                if (! empty($shipping_tax_breakups)) {
                                    foreach ($shipping_tax_breakups as $tax_breakup_is => $tax_breakup) {
                                        ?>
                                        <tr>
                                            <th class="label description" colspan="2" style="text-align:right;"><?php echo __('Shipping Tax', 'wc-frontend-manager').' '.$tax_breakup['label']; ?>:</th>
                                            <td class="total price" style="text-align:center;">
                                            <?php
                                            echo wc_price($tax_breakup['total'], [ 'currency' => $order->get_currency() ]);
                                            ?>
                                            </td>
                                        </tr>
                                            <?php
                                    }
                                }
                            }
                            ?>
                            <?php if (apply_filters('wcfm_is_allow_vendor_order_details_tax_breakup_total', true)) { ?>
                                <tr>
                                    <th class="label description" colspan="2" style="text-align:right;">
                                        <?php
                                        if (apply_filters('wcfm_is_allow_vendor_order_details_tax_breakup', false)) {
                                            echo __('Total', 'wc-frontend-manager').' ';
                                        }
                                        ?>
                                        <?php _e('Shipping Tax', 'wc-frontend-manager-ultimate'); ?>:
                                    </th>
                                    <td class="total price" style="text-align:center;">
                                        <?php
                                        if (wcfm_is_marketplace() == 'wcfmmarketplace') {
                                            $invoice_total += $shipping_tax;
                                            echo wc_price($shipping_tax, [ 'currency' => $order->get_currency() ]);
                                        } else {
                                            $invoice_total += (float) $order->get_shipping_tax();
                                            echo wc_price($order->get_shipping_tax(), [ 'currency' => $order->get_currency() ]);
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php
                            }//end if
                            ?>
                            <?php
                        }//end if
                        ?>
                
                        <tr>
                            <th class="label description" colspan="2" style="text-align:right;"><?php _e('Total', 'wc-frontend-manager-ultimate'); ?>:</th>
                            <td class="total price" style="text-align:center;">
                                <div class="view">
                                  <?php
                                  echo wc_price($invoice_total, [ 'currency' => $order->get_currency() ]);
                                    if (apply_filters('wcfm_is_allow_earning_in_words', false)) {
                                        echo '<br/>'.wcfm_number_to_words($invoice_total);
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr>
                
                    </tfoot>
                </table>
            </td>
        </tr>
    </tbody>
</table>
<?php Original Table */ ?> 

<?php do_action('wcfm_after_store_invoice_order_details', $vendor_id, $order_id, $order); ?>

<?php
if (apply_filters('wcfm_is_pref_policies', true) && apply_filters('wcfm_is_allow_policy_under_order_invoice', true) && wcfm_vendor_has_capability($vendor_id, 'policy') && wcfm_vendor_has_capability($vendor_id, 'vendor_policy') && $wcfm_vendor_invoice_policies) {
    foreach ($process_product_ids as $product_id) {
        if (apply_filters('wcfm_is_allow_order_item_policies_by_vendor', false)) {
            $wcfm_policy_vendor_options = (array) wcfm_get_user_meta($vendor_id, 'wcfm_policy_vendor_options', true);
            $shipping_policy            = isset($wcfm_policy_vendor_options['shipping_policy']) ? $wcfm_policy_vendor_options['shipping_policy'] : '';
            $refund_policy              = isset($wcfm_policy_vendor_options['refund_policy']) ? $wcfm_policy_vendor_options['refund_policy'] : '';
            $cancellation_policy        = isset($wcfm_policy_vendor_options['cancellation_policy']) ? $wcfm_policy_vendor_options['cancellation_policy'] : '';
        } else {
            if ($product_id) {
                $shipping_policy     = $WCFM->wcfm_policy->get_shipping_policy($product_id);
                $refund_policy       = $WCFM->wcfm_policy->get_refund_policy($product_id);
                $cancellation_policy = $WCFM->wcfm_policy->get_cancellation_policy($product_id);
            } else {
                continue;
            }
        }

        if (wcfm_empty($shipping_policy) && wcfm_empty($refund_policy) && wcfm_empty($cancellation_policy)) {
            continue;
        }
        ?>
        <br/><br/>
        <h3><u>
        <?php
        if (apply_filters('wcfm_is_allow_order_item_policies_by_vendor', false)) {
            echo wcfm_get_vendor_store_name($vendor_id).' ';
        } else {
            echo get_the_title($product_id).' ';
        }

        _e('Policies:', 'wc-frontend-manager-ultimate');
        ?>
        </u></h3>
        <br/>
        <table width="100%" style="width:100%;">
            <tbody>
                <?php if (! wcfm_empty($shipping_policy)) { ?>
                    <tr>
                        <td colspan="3" style="background-color: #eeeeee;padding: 1em 1.41575em;line-height: 1.5;"><?php echo apply_filters('wcfm_shipping_policies_heading', __('Shipping Policy', 'wc-frontend-manager')); ?></td>
                        <td colspan="5" style="background-color: #f8f8f8;padding: 1em;"><?php echo $shipping_policy; ?></td>
                    </tr>
                <?php } ?>
                <?php if (! wcfm_empty($refund_policy)) { ?>
                    <tr>
                        <td colspan="3" style="background-color: #eeeeee;padding: 1em 1.41575em;line-height: 1.5;"><?php echo apply_filters('wcfm_refund_policies_heading', __('Refund Policy', 'wc-frontend-manager')); ?></td>
                        <td colspan="5" style="background-color: #f8f8f8;padding: 1em;"><?php echo $refund_policy; ?></td>
                    </tr>
                <?php } ?>
                <?php if (! wcfm_empty($cancellation_policy)) { ?>
                    <tr>
                        <td colspan="3" style="background-color: #eeeeee;padding: 1em 1.41575em;line-height: 1.5;"><?php echo apply_filters('wcfm_cancellation_policies_heading', __('Cancellation / Return / Exchange Policy', 'wc-frontend-manager')); ?></td>
                        <td colspan="5" style="background-color: #f8f8f8;padding: 1em;"><?php echo $cancellation_policy; ?></td>
                    </tr>
                <?php } ?>
                
                <?php do_action('wcfm_store_invoice_policy_content_after', $product_id); ?>
                
            </tbody>
        </table>
        <br/><br/>
        <?php
        if (apply_filters('wcfm_is_allow_order_item_policies_by_vendor', false)) {
            break;
        }
    }//end foreach
}//end if
?>

<?php do_action('wcfm_after_store_invoice_policies', $vendor_id, $order_id, $order); ?>

<?php
if ($wcfm_vendor_invoice_disclaimer && $wcfm_vendor_disclaimer) {
    ?>
    <br/><br/>
    <table width="100%" style="width:100%;">
      <tbody>
            <tr>
                <th></th>
                <td colspan="7"><b><?php echo apply_filters('wcfm_store_invoice_disclaimer', $wcfm_vendor_disclaimer, $vendor_id, $order_id, $order); ?></b></td>
            </tr>
        </tbody>
    </table>
    <br/><br/>
    <?php
}
?>

<?php do_action('wcfm_after_store_invoice_disclaimer', $vendor_id, $order_id, $order); ?>

<?php
if ($wcfm_vendor_invoice_signature && $wcfm_vendor_signature) {
    ?>
    <br/><br/>
    <table width="100%" style="width:100%;">
      <tbody>
            <tr>
                <td><div style="width:100%;text-align: right;"><img style="max-width: 250px;" src="<?php echo apply_filters('wcfm_store_invoice_signature', $wcfm_vendor_signature, $vendor_id, $order_id, $order); ?>" /></div></td>
            </tr>
        </tbody>
    </table>
    <br/><br/>
    <?php
}
?>

<?php do_action('wcfm_after_store_invoice', $vendor_id, $order_id, $order); ?>

<?php if ($document->get_footer()) : ?>
<div id="footer">
    <?php $document->footer(); ?>
</div><!-- #letter-footer -->
    <?php
endif;
