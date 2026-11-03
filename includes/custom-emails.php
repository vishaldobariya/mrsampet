<?php
/*
    moved inside class
    //Register the Custom Email
        add_filter( 'woocommerce_email_classes', 'add_user_otp_woocommerce_email' );
        function add_user_otp_woocommerce_email( $email_classes ) {
            require_once 'class-wc-email-user-otp.php';
            $email_classes['WC_Email_User_OTP'] = new WC_Email_User_OTP();
            return $email_classes;
        }
    
    //Add woocommerce_email_actions If Needed
        add_filter( 'woocommerce_email_actions', function( $actions ) {
            $actions[] = 'send_user_otp_email'; // Add a dummy action
            return $actions;
        });
    
*/  
    //Trigger the OTP Email

    /*function send_user_otp_email( $user_email ) {
        $otp = rand(100000, 999999);
    
        // Send the OTP email
        WC()->mailer()->emails['WC_Email_User_OTP']->trigger( $user_email, $otp );
    }*/
    
    // Show in Woo Settings
    add_filter( 'woocommerce_email_actions', function( $actions ) {
        $actions[] = 'send_user_otp_email';
        return $actions;
    });

    
    //function to send mail
    function send_user_otp_email( $user_email, $otp ) {
        if ( empty( $user_email ) || empty( $otp ) ) {
            return;
        }
    
        $mailer = WC()->mailer();
        
        if ( ! isset( $mailer->emails['WC_Email_User_OTP'] ) ) {
            error_log('❌ OTP email class not found when trying to send.');
            return;
        }
    
        $mailer->emails['WC_Email_User_OTP']->trigger( $user_email, $otp );
    }


    
    
    /*add_action('init', function() {
        $emails = WC()->mailer()->get_emails();
        if ( isset( $emails['WC_Email_User_OTP'] ) ) {
            error_log('✅ Custom OTP email class is registered.');
        } else {
            error_log('❌ OTP email class NOT registered.');
        }
    });*/

?>