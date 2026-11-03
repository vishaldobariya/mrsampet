<?php


 $smsSettingsData = [
        'ctmsg_settings_template' => 'ctmsg_settings_smstemplateids', // for sending otp template id
        'ctmsg_settings_otp_on_off' => 'ctmsg_settings_signupotp', // for sending otp on off
        'ctmsg_settings_template_otp' => 'ctmsg_settings_template_otp', // for sending otp template id
        'ctmsg_settings_template_bookconfirm' => 'ctmsg_settings_template_bookconfirm', // for sending  template booking confirm
        'ctmsg_settings_template_bookconfirm_on_off' => 'ctmsg_settings_bkgconfirm', // for sending  template booking confirm on off
        'ctmsg_settings_template_aptcancel' => 'ctmsg_settings_template_aptcancel', // for sending  template cancel appointment
        'ctmsg_settings_template_aptcancel_on_off' => 'ctmsg_settings_bkgcancel', // for sending  template cancel appointment on off
        'ctmsg_settings_template_booking_reminder_on_off' => 'ctmsg_settings_bkgreminder', // for sending  template booking reminder appointment on off
        'ctmsg_settings_template_booking_reminder_time' => 'ctmsg_settings_bkgreminderfreq', // for sending  template booking reminder appointment on off
    ];

    $getSmsSettingsOption = getSmsSettingsData();

    include_once('pet-automation.php');
    include_once('extend/wcfm.php');
    include_once('extend/wcfm_shortcodes.php');
    include_once('extend/bookings.php');
    include_once('extend/woo.php');
    include_once('extend/forminator.php');
    include_once('extend/my-account.php');
    include_once('storevendor.php');
    include_once('extend/storemanager.php'); //vaccine code
    include_once('extend/woopdf.php'); //Woo PDF Customisation
    
    //Otp Email
        include_once('includes/class-wc-email-user-otp.php');
        include_once('includes/custom-emails.php');
    include_once('includes/setcustomsms.php');
    if($getSmsSettingsOption[$smsSettingsData['ctmsg_settings_otp_on_off']] == 'On')
    {
        include_once('includes/otp.php');
    }
    if($getSmsSettingsOption[$smsSettingsData['ctmsg_settings_template_bookconfirm_on_off']] == 'On')
    {
        include_once('includes/bookingconfirmsms.php');
    }
   

    if($getSmsSettingsOption[$smsSettingsData['ctmsg_settings_template_booking_reminder_on_off']] == 'On')
    {
        include_once('includes/cancelbookingsms.php');
    }
   

//----- Global Login URL Change
    function custom_login_url( $login_url, $redirect ) {
        // Set your custom login URL here
        $custom_login_url = home_url( '/login' ); // Example: /custom-login
    
        // Optionally, append the redirect URL if it exists (to redirect after login)
        if ( ! empty( $redirect ) ) {
            $custom_login_url = add_query_arg( 'redirect_to', $redirect, $custom_login_url );
        }
    
        return $custom_login_url;
    }
    add_filter( 'login_url', 'custom_login_url', 10, 2 );

//acf options data    
    // Old VD Code Function
   /* if (!function_exists( 'getSmsSettingsData' ) ) {*/
        function getSmsSettingsData() {
            global $smsSettingsData;
            $smsSettings = [];
            $tempCheckarrayOrString = get_field($smsSettingsData['ctmsg_settings_template'], 'option');
            if(gettype($tempCheckarrayOrString) == "array")
            {
                $smsSettings = $tempCheckarrayOrString;
                $smsSettings[$smsSettingsData['ctmsg_settings_otp_on_off']] = get_field($smsSettingsData['ctmsg_settings_otp_on_off'], 'option');
                $smsSettings[$smsSettingsData['ctmsg_settings_template_bookconfirm_on_off']] = get_field($smsSettingsData['ctmsg_settings_template_bookconfirm_on_off'], 'option');
                $smsSettings[$smsSettingsData['ctmsg_settings_template_aptcancel_on_off']] = get_field($smsSettingsData['ctmsg_settings_template_aptcancel_on_off'], 'option');
                $smsSettings[$smsSettingsData['ctmsg_settings_template_booking_reminder_on_off']] = get_field($smsSettingsData['ctmsg_settings_template_booking_reminder_on_off'], 'option');
                $smsSettings[$smsSettingsData['ctmsg_settings_template_booking_reminder_time']] = get_field($smsSettingsData['ctmsg_settings_template_booking_reminder_time'], 'option');

                return $smsSettings;
            }
            return $smsSettings;
        }
    /*}*/
    
      /*if (!function_exists( 'sendsms' ) ) {*/
        // sendsms global function
        function sendsms($postData){

            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://control.msg91.com/api/v5/flow',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS => json_encode($postData),
              CURLOPT_HTTPHEADER => array(
                'authkey: 437497Aruq6BlwFDvg68087de1P1',
                'Content-Type: application/json',
              ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            return $response;
        }
   /* }*/

    
  /*Archive Page - WCFM Map change position to sidebar*/
    add_action( 'wp', 'move_wcfmmp_geo_filter_to_sidebar' );
    function move_wcfmmp_geo_filter_to_sidebar() {
        global $WCFMmp;
    
        if ( isset( $WCFMmp->frontend ) ) {
            // Step 1: Remove from the top of the shop loop
            remove_action( 'woocommerce_before_shop_loop', array( $WCFMmp->frontend, 'wcfmmp_product_list_geo_location_filter' ), 1 );
    
            // Step 2: Add to the WooCommerce sidebar instead
            add_action( 'wolmart_sidebar_content_end', array( $WCFMmp->frontend, 'wcfmmp_product_list_geo_location_filter' ), 5 );
        }
    }

    
    /*To check customers tax status*/
    /*add_action('wp_footer', function () {
        if (is_user_logged_in()) {
            $customer = WC()->customer;
            echo '<!-- TAX DEBUG: ';
            echo 'incl=' . (wc_prices_include_tax() ? 'yes' : 'no');
            echo ' | vat_exempt=' . ($customer->get_is_vat_exempt() ? 'yes' : 'no');
            echo ' | country=' . $customer->get_billing_country();
            echo ' -->';
        }
    });*/
    
//----Financial Year Function for Invoice Generation
    //used in > /wc-frontend-manager-ultimate/core/class-wcfmu-vendor-invoice.php
    if (!function_exists('wcfm_get_financial_year')) {
        function wcfm_get_financial_year() {
            $year = date('Y');
            $month = date('n');
    
            if ($month >= 4) {
                return $year . '-' . substr($year + 1, -2);
            } else {
                return ($year - 1) . '-' . substr($year, -2);
            }
        }
    }

//----DRTS Field Mapping
    add_action( 'wp_footer', function() {
        
        //--- Google Analytics 29.09
    ?>
        <!-- Google Tag Manager -->
            <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','GTM-PMD6BHBZ');</script>
        <!-- End Google Tag Manager -->
        
    <?php    
        //----DRTS Field Mapping
            // Only for logged-in users
            if ( ! is_user_logged_in() ) return;
        
            // Check if current page is /add-directory-listing/ with a bundle query
            $current_path   = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
            $query_args     = [];
            parse_str( $_SERVER['QUERY_STRING'] ?? '', $query_args );
        
            if ( strpos( $current_path, '/add-directory-listing/' ) === false || empty( $query_args['bundle'] ) ) {
                return; // Not a directory form page
            }
        
            $user = wp_get_current_user();
            $first_name = esc_js( $user->first_name );
            $last_name  = esc_js( $user->last_name );
        
            // Get phone from common meta keys
            $phone = get_user_meta( $user->ID, 'phone', true );
            if ( empty( $phone ) ) $phone = get_user_meta( $user->ID, 'billing_phone', true );
            if ( empty( $phone ) ) $phone = get_user_meta( $user->ID, 'mobile', true );
        
            $phone = esc_js( $phone );
        
            ?>
            <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', function() {
    
                    // Prefill Owner Name
                    var firstNameField = document.querySelector('input[name="drts[field_owner_name][0][first_name]"]');
                    var lastNameField  = document.querySelector('input[name="drts[field_owner_name][0][last_name]"]');
                    if(firstNameField) {
                        firstNameField.value = '<?php echo $first_name; ?>';
                       // firstNameField.readOnly = true;
                    }
                    if(lastNameField) {
                        lastNameField.value = '<?php echo $last_name; ?>';
                       // lastNameField.readOnly = true;
                    }
        
                    // Prefill Phone
                    var phoneField = document.querySelector('input[name="drts[field_phone][0]"]');
                    if(phoneField) {
                        phoneField.value = '<?php echo $phone; ?>';
                     //   phoneField.readOnly = true;
                    }
        
                });
            </script>
        <?php
    
    });



//--- 404 Redirect to homepage
    add_action('template_redirect', function () {
        if (is_404()) {
            wp_redirect(home_url());
            exit;
        }
    });

//--- Cron for Booking Reminder (VD)
    /*add_filter('cron_schedules', function ($schedules) {
        $schedules['every_minute'] = [
            'interval' => 60,
            'display'  => __('Every Minute'),
        ];
    
    return $schedules;
    });
    
    add_action('calculation_reminder_cron', function () {
        include_once ABSPATH . 'design/mrsam/includes/calculationreminder.php';
    });
    
    if (!wp_next_scheduled('calculation_reminder_cron')) {
        wp_schedule_event(time(), 'every_minute', 'calculation_reminder_cron');
    }*/
    
/*2026.09.11*/    
    //--- Vaccine selection validation
    function require_vaccine_checkbox_shortcode() {
        ob_start();
        ?>
        <script>
        jQuery(function($) {
    
            $(document).on('click', '#formpetvacc_cat button[type="submit"], #formpetvacc_cat input[type="submit"]', function(e) {
    
                var $form = $('#formpetvacc_cat');
    
                var $checkboxes = $form.find(
                    'input[name="form_fields[field_615cd3a][]"]'
                );
    
                if ($checkboxes.length && !$checkboxes.is(':checked')) {
    
                    e.preventDefault();
                    e.stopImmediatePropagation();
    
                    alert('Please select at least one vaccine.');
    
                    return false;
                }
    
            });
    
        });
        </script>
        <?php
    
        return ob_get_clean();
    }
    
    add_shortcode('require_vaccine_checkbox', 'require_vaccine_checkbox_shortcode');

    //--- Google Analytics 29.09 (after body)
    add_action('wp_body_open', function () {
    ?>
        <!-- Google Tag Manager (noscript) --><noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PMD6BHBZ" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript><!-- End Google Tag Manager (noscript) -->
    <?php
    });
    

?>