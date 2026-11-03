<?php
//--- Customer Signup form - username as mobile number space issue
    function remove_spaces_from_phone_field($prepared_data, $form) {
        $form_id = get_forminator_form_id_by_name('user-registration');
        if ($form_id == 3939) {  // Use your correct Form ID here
    
            // Check if the 'phone-1' field exists in the prepared data
            if (isset($prepared_data['phone-1'])) {
                // Get the phone number (which is mapped to username)
                $mobile_number = $prepared_data['phone-1'];
    
                // Remove spaces from the phone number
                $cleaned_mobile_number = str_replace(' ', '', $mobile_number);
    
                // If spaces were removed, update the phone-1 field and the username field
                if ($cleaned_mobile_number !== $mobile_number) {
                    // Update the fields with the cleaned phone number
                    $prepared_data['phone-1'] = $cleaned_mobile_number; // Update the phone-1 field
                    $prepared_data['username'] = $cleaned_mobile_number; // Update the username field
                }
            }
        }
    
        // Return the modified prepared data
        return $prepared_data;
    }
    
    add_filter('forminator_prepared_data', 'remove_spaces_from_phone_field', 10, 2);













?>