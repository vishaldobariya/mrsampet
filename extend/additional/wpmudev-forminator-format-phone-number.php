<?php
/**
* Plugin Name: [Forminator Pro] - Format phone number
* Plugin URI: https://premium.wpmudev.org/
* Description: Format US phone numbers to format (000) 000-0000 (as of 1.9.1)
* Author: Alessandro Kaounas @ WPMUDEV
* Author URI: https://premium.wpmudev.org/
* License: GPLv2 or later
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPMUDEV_Forminator_Format_Multiple_Phone_Number' ) ) {
    
    class WPMUDEV_Forminator_Format_Multiple_Phone_Number {

        private static $_instance = null;

		private $form_data = array( 
            '3939' => array(
                'phone-1'
                
            ),
            /*'2200' => array(
                'phone-2',
                'phone-3'
            )*/
        );

        private $phone_pattern = '($1) $2-$3'; // Matches on 10 digits inputs

        public static function get_instance() {

            if( is_null( self::$_instance ) ){
                self::$_instance = new WPMUDEV_Forminator_Format_Multiple_Phone_Number();
            }

            return self::$_instance;
            
        }

        private function __construct() {
            $this->init();
        }

        public function init(){

            add_filter( 'forminator_custom_form_submit_field_data', array( $this, 'wpmudev_format_phone_us'), 999, 2 );

        }
	
        public function wpmudev_format_phone_us( $field_data_array, $form_id ) {

            if( ! in_array( $form_id, array_keys( $this->form_data ) ) ){
                return $field_data_array;
            }

            foreach( $field_data_array as &$field ){

                if( in_array( $field['name'], $this->form_data[$form_id] ) ){
                    
                    $field['value'] = $this->wpmudev_format_phone_number( $field['value'] );
                    
                }

            }
            
            return $field_data_array;

        }

        public function wpmudev_format_phone_number( $number ){
            
            $number = preg_replace( "/[^\d]/", "", $number );
         
            if( strlen( $number ) == 10 ) {

                $number = preg_replace( "/^1?(\d{3})(\d{3})(\d{4})$/", $this->phone_pattern, $number );

            }
        
            return $number;

        }

	}
	
    if ( ! function_exists( 'wpmudev_forminator_avoid_duplicate_entries' ) ) {
        
        function wpmudev_forminator_avoid_duplicate_entries() {
            return WPMUDEV_Forminator_Format_Multiple_Phone_Number::get_instance();
        };

        add_action( 'plugins_loaded', 'wpmudev_forminator_avoid_duplicate_entries', 99 );
    }

}