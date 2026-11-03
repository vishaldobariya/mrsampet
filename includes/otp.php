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
        //$mobile = "9998050924";
        $name = $_POST['name'];
        $email = $_POST['email'];
        $otp = rand(100000, 999999); // Generate a 6-digit OTP.
        set_transient('otp_' . $mobile, $otp, 5 * MINUTE_IN_SECONDS); // Store OTP for 5 minutes.

        send_user_otp_email( $email, $otp ); // send otp on email

        $mobile = str_replace(' ', '', $mobile);
        $smsSettingsVal = getSmsSettingsData();

        $recipients[] = ["mobiles" => "91".$mobile, "NAME" => $name,  "OTP" => $otp ];
        $postData = ['template_id' => $smsSettingsVal['ctmsg_settings_template_otp'], "recipients" => $recipients];
        sendsms($postData);
        wp_send_json_success('OTP sent successfully.');
    }
    
    // Verify OTP.
    add_action('wp_ajax_verify_otp', 'verify_otp');
    add_action('wp_ajax_nopriv_verify_otp', 'verify_otp');
    function verify_otp() {
        $otp = sanitize_text_field($_POST['otp']);
        $mobile = sanitize_text_field($_POST['mobile']);
        //$mobile = "9998050924";
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

    

?>