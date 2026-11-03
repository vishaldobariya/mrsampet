<?php
    function forminator_custom_script() {
        global $formCustomerRegistrationName;
    	$strPageSlug = get_current_page_slug_in_footer(); 
    	if($strPageSlug == $formCustomerRegistrationName)
    	{
        	wp_enqueue_script('forminator-otp-script', get_stylesheet_directory_uri() . '/includes/signup-otp.js', array('jquery'), null, true);
    	}
        wp_localize_script('forminator-otp-script', 'forminatorAjax', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
        ));
    }
    add_action('wp_enqueue_scripts', 'forminator_custom_script');
    
    // Generate and send OTP.
    add_action('wp_ajax_send_otp', 'send_otp');
    add_action('wp_ajax_nopriv_send_otp', 'send_otp');
    function send_otp() {
        $mobile = sanitize_text_field($_POST['mobile']);
        $name = $_POST['name'];
        $otp = rand(100000, 999999); // Generate a 6-digit OTP.
        set_transient('otp_' . $mobile, $otp, 5 * MINUTE_IN_SECONDS); // Store OTP for 5 minutes.
    
        // Implement your SMS API to send the OTP.
        $sent = true; // Replace with actual API call status.
    
        if ($sent) {
            $mobile = "9998050924";
            $mobile = str_replace(' ', '', $mobile);
            $recipients = ["mobiles" => "91".$mobile, "NAME" => $name,  "OTP" => $otp ];
            $postData = ['template_id' => '67e53480d6fc05459a3be036', "recipients" => [$recipients]];
            sendsms($postData);
            wp_send_json_success('OTP sent successfully.');
        } else {
            wp_send_json_error('Failed to send OTP.');
        }
    }
    
    // Verify OTP.
    add_action('wp_ajax_verify_otp', 'verify_otp');
    add_action('wp_ajax_nopriv_verify_otp', 'verify_otp');
    function verify_otp() {
        $otp = sanitize_text_field($_POST['otp']);
        $mobile = sanitize_text_field($_POST['mobile']);
        $stored_otp = get_transient('otp_' . $mobile);
    
        if ($stored_otp && $otp === $stored_otp) {
            delete_transient('otp_' . $mobile); // Remove OTP after successful verification.
            wp_send_json_success('OTP verified successfully.');
        } else {
            wp_send_json_error('Invalid OTP.');
        }
    }

    // Prepare post data
    /*$postData = array(
        'authkey' => $authKey,
        'mobiles' => $mobileNumber,
        'message' => $message,
        'sender' => $senderId,
        'route' => $route,
        'country' => '91'
    );*/

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

?>