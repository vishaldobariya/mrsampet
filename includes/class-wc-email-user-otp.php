<?php
    //Custom Email Class
    add_filter( 'woocommerce_email_classes', 'register_wc_user_otp_email' );
    function register_wc_user_otp_email( $email_classes ) {
    
        if ( ! class_exists( 'WC_Email_User_OTP' ) ) {
    
            class WC_Email_User_OTP extends WC_Email {
    
                public function __construct() {
                    $this->id             = 'user_otp_email';
                    $this->title          = 'User OTP Email';
                    $this->description    = 'Sends a custom OTP email to users.';
                    $this->template_html  = 'emails/user-otp-email.php';
                    $this->template_plain = 'emails/plain/user-otp-email.php';
                    $this->template_base  = get_stylesheet_directory() . '/woocommerce/';
    
                    $this->recipient = '';
                    $this->subject   = 'Your One-Time Password (OTP)';
                    $this->heading   = 'Login OTP';
    
                    $this->init_form_fields();
                    $this->init_settings();
    
                    add_action( 'woocommerce_update_options_email_' . $this->id, array( $this, 'process_admin_options' ) );
    
                    parent::__construct();
                }
    
                public function init_form_fields() {
                    $this->form_fields = array(
                        'enabled' => array(
                            'title'   => 'Enable/Disable',
                            'type'    => 'checkbox',
                            'label'   => 'Enable this email notification.',
                            'default' => 'yes'
                        ),
                        'subject' => array(
                            'title'       => 'Email Subject',
                            'type'        => 'text',
                            'default'     => $this->subject,
                            'desc_tip'    => true,
                        ),
                        'heading' => array(
                            'title'       => 'Email Heading',
                            'type'        => 'text',
                            'default'     => $this->heading,
                            'desc_tip'    => true,
                        )
                    );
                }
    
                public function trigger( $user_email, $otp ) {
                    $this->recipient = $user_email;
                    $this->otp       = $otp;
    
                    if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
                        return;
                    }
    
                    $this->send(
                        $this->get_recipient(),
                        $this->get_subject(),
                        $this->get_content(),
                        $this->get_headers(),
                        $this->get_attachments()
                    );
                }
    
                public function get_content_html() {
                    ob_start();
                    wc_get_template( $this->template_html, array(
                        'otp'           => $this->otp,
                        'email_heading' => $this->get_heading(),
                        'email'         => $this,
                    ), '', $this->template_base );
                    return ob_get_clean();
                }
    
                public function get_content_plain() {
                    ob_start();
                    wc_get_template( $this->template_plain, array(
                        'otp'           => $this->otp,
                        'email_heading' => $this->get_heading(),
                        'email'         => $this,
                    ), '', $this->template_base );
                    return ob_get_clean();
                }
            }
        }
    
        $email_classes['WC_Email_User_OTP'] = new WC_Email_User_OTP();
        return $email_classes;
    }







?>