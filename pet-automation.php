<?php
//--- ACF Group Keys
    $acfGroupId = 'group_66c98b449a9cd';
    $acfPetBreedGroupId =  'group_6794bb0a4b7d7';
    $arrayPetBreedOptions = [];
    $formCustomerRegistrationName = 'customer-registration'; // forminator customer-registration form name
    $forminatorUserFormName = 'user-registration'; // forminator user-registration form name
    $forminatorPetInfoFormName = 'pet-info'; // forminator pet-info form name
    $forminatorAddPetInfoFormName = 'add-pet-info'; // forminator add-pet-info form name

//--- Get Breed data from acf using group_id
    function getPetBreedOptions() {
        global $acfPetBreedGroupId, $arrayPetBreedOptions;
        $arrayPetBreedOptions = getChoicePetBreed($acfPetBreedGroupId);
    }

    function getChoicePetBreed($group_id) {
        // Get all fields in the group
            $fields = acf_get_fields($group_id);
            if (!$fields) {
                return null; // No fields found for this group ID
            }
    
        $fields_data = [];
        // Loop through each field to retrieve its details
            foreach ($fields as $field) {
                $choice_data = [];
                if($field['sub_fields'][0]['choices'])
                {
                    $choice_data = $field['sub_fields'][0]['choices'];
                }
                $fields_data[str_replace('Pet ', '', $field['label'])] = $choice_data;     
            }
    
        return $fields_data;
    }


//--- Create Custom Tab In My Account Page
    add_action('init', 'add_pet_info_endpoint');
    function add_pet_info_endpoint() {
        add_rewrite_endpoint('pet-info', EP_ROOT | EP_PAGES);
    }
    
    add_filter('query_vars', 'pet_info_query_vars', 0);
    function pet_info_query_vars($vars) {
        $vars[] = 'pet-info';
        return $vars;
    }
    
    add_filter('woocommerce_account_menu_items', 'add_pet_info_tab');
    function add_pet_info_tab($items) {
        $items['pet-info'] = 'Pet info';
        return $items;
    }
    
    
    function pet_info_tab_content() {
        global $arrayPetBreedOptions, $forminatorPetInfoFormName;
        $intSinglePetInfoFormId = get_forminator_form_id_by_name($forminatorPetInfoFormName);
        $acfGroupId = 'group_66c98b449a9cd';
        list($repeaterFormId, $repeaterFormKey) = getACFFieldDataMainFormId($acfGroupId);
        $user_id = get_current_user_id();
        $pet_info = get_field($repeaterFormKey, 'user_' . $user_id);
        getPetBreedOptions();
        $jsonArrayPetOptions = json_encode($arrayPetBreedOptions);
        include 'pet-info.php';

    }
    add_action('woocommerce_account_pet-info_endpoint', 'pet_info_tab_content');



//--- Get the forminator id by passing forminator form name
    function get_forminator_form_id_by_name( $form_name ) {
    
        if ( class_exists( 'Forminator_API' ) ) {
            $forms = Forminator_API::get_forms();
            // Loop through all forms and find the one with the matching name
            foreach ( $forms as $form ) {
                if ( $form->name === $form_name ) {
                    return $form->id; // Return the form ID if the name matches
                }
            }
        }
        // Return null if no form with the specified name is found
        return null;
    }


//--- get the all pet uuid by passing userid
    function get_PetInfo_Distinct_PetIds($intUserId)
    {
        global $wpdb;
        $arrayPetInfoIds = [];
        $results = $wpdb->get_results(
                    $wpdb->prepare("SELECT DISTINCT(pet_id) FROM {$wpdb->prefix}petinfo WHERE user_id = %d AND deleted_at IS NULL  ORDER BY pet_index DESC",
                $intUserId
                ), ARRAY_A
        );
        foreach ($results as $key => $strPetInfoId) {
            $arrayPetInfoIds[] = $strPetInfoId['pet_id'];
        }
        return $arrayPetInfoIds;
    }

 
//--- get the key and name ACF Form by passing it's group id
    function getACFFieldDataMainFormId($specifications_group_id) {
        $specifications_fields      = [];
        $fields = acf_get_fields( $specifications_group_id );
        foreach ( $fields as $field ) {
            return [$field['key'],  $field['name']];
        }
        return null;
    };

//--- Save the data after save the user registration form and add pet info form data
    add_action( 'forminator_form_after_save_entry', 'process_forminator_entry_data', 10, 2 );
    function process_forminator_entry_data( $form_id, $response ) {
        global $wpdb, $acfGroupId, $forminatorUserFormName, $forminatorPetInfoFormName, $forminatorAddPetInfoFormName;
        $intSinglePetInfoFormId = 0;
        if ( isset($_POST['hidden-1']) ) {
            if($_POST['hidden-1'] == 'product_add_pet')
            {
                $intSinglePetInfoFormId = get_forminator_form_id_by_name($forminatorAddPetInfoFormName); // Get Form id from form name
            }
            else
            {
                
                $intSinglePetInfoFormId = get_forminator_form_id_by_name($forminatorPetInfoFormName); // Get Form id from form name
            }
        }
        else
        {
            $intSinglePetInfoFormId = get_forminator_form_id_by_name($forminatorPetInfoFormName); // Get Form id from form name
        }
    
        $intUserForminatorRegistrationId = get_forminator_form_id_by_name($forminatorUserFormName); // Get Form id from form name
        list($repeaterFormId, $repeaterFormKey) = getACFFieldDataMainFormId($acfGroupId); // Get form key id and form key field
    
        // Retrieve only latest entry with form id
        $entry = Forminator_Form_Entry_Model::get_latest_entry_by_form_id( $form_id );
        // Retrieve all field data
        $field_data_array = $entry->meta_data;
        
        
    
        switch ($form_id) {
            case $intUserForminatorRegistrationId:
                $user_id = 0;
                $user_email = "";
                
                    if ( !is_user_logged_in() ) {
                      foreach ( $field_data_array as $key =>  $field_data) {
                        if ( str_contains($key, 'email-1')) {
                            $user_email= $field_data['value'];
                            break;
                        }
                      }
                        $user = get_user_by( 'email', $user_email);
                        $user_id = $user->ID;
                    }
                    else{
                        $user_id = get_current_user_id();
                    }
                // Initialize an array to hold repeater field data
                $repeater_data = [];
    
                // Process field data
                $arrayRepeaterKeys = ['select-4','select-5','radio-1','date-1','text-9','radio-2'];
                $arrayRepeaterValues = ['pet_type','pet_breed','pet_gender','pet_dob','pet_name','pet_vaccination'];
                $index = 0;
                // create array while submit repeater sub fields
               
                foreach ( $field_data_array as $mainKey =>  $field_data ) {
                    foreach ( $arrayRepeaterKeys as $key =>  $field_key ) {
                        $arrayIndex = explode("-", $mainKey);
                        $index = array_key_exists(2, $arrayIndex)? $arrayIndex[2] : 0;
                        if(str_contains($mainKey, $field_key))
                        {
                            $value = maybe_unserialize($field_data['value']);
                            if($arrayRepeaterValues[$key] == 'pet_dob')
                            {
                                $dateTime = DateTime::createFromFormat('d/m/Y', $value);
                                $value = $dateTime->format('Ymd');
    
                            }
                            $repeater_data[$index][] = ['name' => $arrayRepeaterValues[$key], 'value' => $value];
                        }
                    }   
                }
              
                // create final array for the store acf data
               $ACFRowData = [];
               $finalACFRowDatas = [];
               if(!empty($repeater_data))
               {
                   foreach($repeater_data as $key =>  $repeaterSubfield)
                   {
                        foreach ( $repeaterSubfield as $key =>  $pet_info ) {
                            $ACFRowData[$pet_info['name']] = $pet_info['value'];
                        }
                        $ACFRowData['unique_pet_id'] = uniqid();
                        $finalACFRowDatas[] = $ACFRowData;
                        $ACFRowData = [];
                   }
                    // update field while save sub field data
                    update_field($repeaterFormKey, $finalACFRowDatas, 'user_' . $user_id);
        
                    $finalStoreACFRowDatas = get_field($repeaterFormKey, 'user_' . $user_id);
                    foreach ($finalStoreACFRowDatas as $intPetIndexKey => $objSingleArray) {
        
                        foreach ($objSingleArray as $strMetaKey => $strMetaValue) {
                            
                            if($strMetaKey == 'pet_dob' && $strMetaValue != '')
                            {
                                $dateTime = DateTime::createFromFormat('d/m/Y', $strMetaValue);
                                $strMetaValue = $dateTime->format('Ymd');
                            }
                            if($strMetaKey != 'unique_pet_id')
                            {
                                $wpdb->query(
                                    $wpdb->prepare(
                                    "INSERT INTO {$wpdb->prefix}petinfo ( user_id, pet_id, pet_index, meta_key, meta_value) VALUES ( %d, %s, %d, %s, %s )",
                                        $user_id, $objSingleArray['unique_pet_id'], $intPetIndexKey, $strMetaKey, $strMetaValue
                                    )
                                );
                            }
                        }
                    }          

                }
                break;
    
            case $intSinglePetInfoFormId:
                $user_id = get_current_user_id();
                $arrayKeys = ['select-1','select-2','radio-1','date-1','text-1','radio-2'];
                $arrayValues = ['pet_type','pet_breed','pet_gender','pet_dob','pet_name','pet_vaccination'];
                $arrayCombines = array_combine($arrayKeys,$arrayValues);
                if($_POST['hidden-1'] != 'update') // will work this code while add new data
                {
                    // $intAddUserPetIndex = get_last_pet_index_particular_user_id($user_id) + 1;
                    $index = 0;
                    $finalACFRowData = [];
                    $ExistingACFRowData = get_field($repeaterFormKey, 'user_' . $user_id);
                    /*error_log("staring value");
                    error_log(print_r($field_data_array, true));*/
                    
                    foreach ( $field_data_array as $mainKey =>  $field_data ) {
                        foreach($arrayCombines as $strKey => $strValues)
                        {
                            if($mainKey == $strKey){
                                if($strKey == 'date-1') // Solved while dob occured issue
                                {
                                    $date = DateTime::createFromFormat('d/m/Y', $field_data['value']); 
                                    // 2. Format it into the new format (Ymd)
                                    $newDate = $date->format('Ymd'); 
                                    $finalACFRowData[$strValues] = $newDate;
                                }
                                else
                                {
                                    $finalACFRowData[$strValues] = $field_data['value'];    
                                }
                                
                            }
                        }
                        $finalACFRowData['unique_pet_id'] = uniqid();
                        
                    }
                    /*error_log("new created data");
                    error_log(print_r($finalACFRowData, true));*/
                    $FinalNewExistingACFRowData = [$finalACFRowData];
                    $intCountPetIndex = 0;
                    if($ExistingACFRowData)
                    {
                        $FinalNewExistingACFRowData= array_merge($ExistingACFRowData, [$finalACFRowData]);
                    }
                    /*error_log("before forminaor update");
                    error_log(print_r($FinalNewExistingACFRowData, true));*/
                    update_field($repeaterFormKey, $FinalNewExistingACFRowData, 'user_' . $user_id);
                    $finalStoreACFRowDatas = get_field($repeaterFormKey, 'user_' . $user_id);
                /*  error_log(print_r($finalStoreACFRowDatas, true));*/
                    if($finalStoreACFRowDatas){
                        $intCountPetIndex = count($finalStoreACFRowDatas) - 1;
                    }
                    
                    foreach($finalStoreACFRowDatas[$intCountPetIndex] as $strMetaKey => $strMetaValue)
                    {
                        if($strMetaKey == 'pet_dob' && $strMetaValue != '')
                        {
                            $dateTime = DateTime::createFromFormat('d/m/Y', $strMetaValue);
                            $strMetaValue = $dateTime->format('Ymd');
                        }
                        if($strMetaKey != 'unique_pet_id')
                        {
                            $wpdb->query(
                                $wpdb->prepare(
                                "INSERT INTO {$wpdb->prefix}petinfo ( user_id, pet_id, pet_index, meta_key, meta_value ) VALUES ( %d, %s, %d, %s, %s )",
                                    $user_id,$finalStoreACFRowDatas[$intCountPetIndex]['unique_pet_id'], $intCountPetIndex, $strMetaKey, $strMetaValue
                                )
                            );
    
                        }
                    }
                    
                }
                else if($_POST['hidden-1'] == 'update') //  will work this code while update data
                {
                    
                    $allPosts['select-1'] = $_POST['select-1'];
                    if(isset($_POST['select-2']))
                    {
                        $allPosts['select-2'] = $_POST['select-2'];
                    }
                    $allPosts['radio-1'] = $_POST['radio-1'];
                    $allPosts['date-1'] = $_POST['date-1'];
                    $allPosts['text-1'] = $_POST['text-1'];
                    $allPosts['radio-2'] = $_POST['radio-2'];
    
                    $arrayKeys = ['select-1','select-2','radio-1','date-1','text-1','radio-2'];
                    $arrayValues = ['pet_type','pet_breed','pet_gender','pet_dob','pet_name','pet_vaccination'];
                    $arrayCombines = array_combine($arrayKeys,$arrayValues);
                    $pet_index =  explode("_",$_POST['hidden-2'])[1];
                    $repeater_data = get_field($repeaterFormKey, 'user_' . $user_id);
                    foreach($allPosts as $arrayKey => $arrayValues)
                    {
                        if(isset($allPosts[$arrayKey]))
                        {
                            // Define the subfield name you want to update
                            $sub_field_name = $arrayCombines[$arrayKey];
    
                            // Get the current value of the repeater field
                            $new_value = $arrayValues; // Replace with the actual value you want to set
    
                            if($new_value != ''){
    
                                if($sub_field_name == 'pet_dob')
                                {
                                    $dateTime = DateTime::createFromFormat('d/m/Y', $new_value);
                                    $new_value = $dateTime->format('Ymd');
                                }
    
                                $repeater_data[$pet_index][$sub_field_name] = $new_value;
                                
                                $wpdb->query(
                                    $wpdb->prepare(
                                    "UPDATE  {$wpdb->prefix}petinfo  SET  meta_value = %s WHERE user_id = %d AND meta_key = %s AND pet_id = %s AND deleted_at IS NULL",
                                                $new_value, $user_id, $arrayCombines[$arrayKey], $repeater_data[$pet_index]['unique_pet_id']
                                    )
                                );
                            }
    
                        }
                    }
                    
                    $arrayRepeaterData = $repeater_data;
                    $arrayRepeaterData = array_map(function ($pet) {
                        if (!empty($pet['pet_dob'])) {
                            $date = DateTime::createFromFormat('d/m/Y', $pet['pet_dob']);
                            if ($date !== false) {
                                $pet['pet_dob'] = $date->format('Ymd');
                            }
                        }
                        return $pet;
                    }, $arrayRepeaterData);

                    update_field($repeaterFormKey, $arrayRepeaterData, 'user_' . $user_id);
    
                }
                break;
            
            default:
                // code...
                break;
        }
        
    }

//--- Delete Pet info while user or admin delete the pet
    function delete_pet_info()
    {
        global $wpdb, $acfGroupId;
        switch ($_POST["action"]) {
            case 'delete_pet_info':
                list($repeaterFormId, $repeaterFormKey) = getACFFieldDataMainFormId($acfGroupId);
                $user_id = get_current_user_id();
                $ExistingACFRowData = get_field($repeaterFormKey, 'user_' . $user_id);
                unset($ExistingACFRowData[$_POST['key']]);

                $ExistingACFRowData = array_map(function ($pet) {
                        if (!empty($pet['pet_dob'])) {
                            $date = DateTime::createFromFormat('d/m/Y', $pet['pet_dob']);
                            if ($date !== false) {
                                $pet['pet_dob'] = $date->format('Ymd');
                            }
                        }
                        return $pet;
                    }, $ExistingACFRowData);

                update_field($repeaterFormKey, $ExistingACFRowData, 'user_' . $user_id);
                set_delete_field_same_acf_pet_info($_POST["key"], $_POST['pet_id']);
                break;
            
            default:
                // code...
                break;
        }
            
    }
    add_action( 'wp_ajax_delete_pet_info', 'delete_pet_info' ); 

    //--- Delete the all field while change pet data index or delete pet
        function set_delete_field_same_acf_pet_info($intPetIndexKey, $strPetId)
        {
            global $wpdb;
            $wpdb->query(
                $wpdb->prepare(
                "UPDATE {$wpdb->prefix}petinfo
                SET delete_index = %d,
                deleted_at = %s
                WHERE pet_id = %s AND deleted_at IS NULL",
                $intPetIndexKey, current_time('mysql'), $strPetId
                )
            );
        
            $wpdb->query(
                $wpdb->prepare(
                "UPDATE {$wpdb->prefix}petinfo
                 SET pet_index = pet_index - 1
                 WHERE pet_index > %d AND deleted_at IS NULL",
                 $intPetIndexKey
                )
            );      
        }

// Add js on admin footer while admin handle the pet info particular user
    add_action('acf/input/admin_footer', 'enqueue_custom_js_for_acf_repeater');
    function enqueue_custom_js_for_acf_repeater() {
        global $acfGroupId;
        global $arrayPetBreedOptions;
        getPetBreedOptions();
        list($repeaterFormId, $repeaterFormKey) = getACFFieldDataMainFormId($acfGroupId);
        $jsonArrayPetOptions = json_encode($arrayPetBreedOptions);
        ?>
        <script type="text/javascript">
            var javascriptArrayPetBreedOptions = JSON.parse('<?php echo $jsonArrayPetOptions; ?>');
            (function($) {
                var repeaterFormKey = '<?php echo $repeaterFormKey; ?>';
                
               
    
    
                // ACF ready event when the repeater is ready to be used
                acf.addAction('ready', function($el){
    
                    var repeaterField = $('[data-name="'+repeaterFormKey+'"]');
                    
                    // Bind to the "Add Row" button click event
                    repeaterField.on('click', '.acf-repeater-add-row, [data-event="add-row"]', function(e) {
                        beforeAddRow(e, $(this));
                    });  
    
                    // Set with directly petbreed options based on pet type
                    var fieldPetType = 'pet_type'; 
                    var fieldPetBreed = 'pet_breed'; 
                    $(".acf-table .acf-row").each(function(index,element){
                        let selectPetType = $(this).find('[data-name="' + fieldPetType + '"] select').val();
                        let objPetBreedOption = $(this).find('[data-name="' + fieldPetBreed + '"] select');
                        let objAlreadySelectPetBreed = objPetBreedOption.val();
                        objPetBreedOption.empty(); // Removes all options from the <select>
                        $.each(javascriptArrayPetBreedOptions[selectPetType], function (index, option) {
                            var newOption = jQuery('<option>', {
                                value: index,   // Option value
                                text: option,   // Option text
                                selected: (objAlreadySelectPetBreed == index)?true:false             // Make it selected
                            });
                            objPetBreedOption.append(newOption);
                        });
                    });
    
                    // End with directly petbreed options based on pet type
                    
                    function setDynamicOptions(){
    
                        $(".acf-table .acf-row").each(function(index,element){
                            $(this).find('[data-name="' + fieldPetType + '"] select').on('change', function(){
                                let selectPetType = $(this).val();
                                let objPetBreedOption = $(this).closest('.acf-row').find('[data-name="' + fieldPetBreed + '"] select');
                                objPetBreedOption.empty(); // Removes all options from the <select>
                                $.each(javascriptArrayPetBreedOptions[selectPetType], function (index, option) {
                                    var newOption = jQuery('<option>', {
                                        value: index,   // Option value
                                        text: option,   // Option text
                                    });
                                    objPetBreedOption.append(newOption);
                                });
                            });
                        });
                    }   
    
                        // Function to be called before adding a row
                    function beforeAddRow(e, $el) {
                         var fieldToDisableUniquePetId = 'unique_pet_id'; // Replace with the field name
                         
                        setTimeout(function() {
                            // Get the last row in the repeater (which is the newly added one)
                            setDynamicOptions();
                             $('.acf-repeater').find('.acf-row [data-name="' + fieldToDisableUniquePetId + '"] input').prop('readonly', true);
                        }, 10);
                    }  
    
                });
    
    
    
            })(jQuery);
        </script>
        <?php
    }


// Disabled the field while admin update the pet info.
   // add_filter( 'acf/load_field', 'clicktap_acf_read_only_field' );
    function clicktap_acf_read_only_field( $field ) {
    
      if(in_array($field['name'], ['unique_pet_id', 'pet_type']) ) {
        $field['readonly'] = true;  
      }
      return $field;
    }


// Create unique id while admin handle the add pet.
    add_filter('acf/load_value/name=unique_pet_id', 'my_acf_load_value');
    function my_acf_load_value($value) {
    
        if ($value == NULL) {
            return uniqid();
        }
        return $value;
    }



// Save petinfo data while admin add or update the data
add_action('acf/save_post', 'save_readonly_acf_repeater_fields_to_usermeta', 20);
function save_readonly_acf_repeater_fields_to_usermeta($strWithUserkeyword_user_id) {
    // Check if this is a user profile save

    global $wpdb, $acfGroupId;
    list($repeaterFormId, $repeaterFormKey) = getACFFieldDataMainFormId($acfGroupId);
    $arrayRepeaterData = get_field($repeaterFormKey, $strWithUserkeyword_user_id);
        // Get the repeater field data
    if (have_rows($repeaterFormKey, $strWithUserkeyword_user_id)) {
        $row_index = 0;
        // Loop through each row of the repeater field
        while (have_rows($repeaterFormKey, $strWithUserkeyword_user_id)) {
            the_row();
            
            // Get the readonly field value within the repeater row
            $readonly_field_value = get_sub_field('unique_pet_id');

            // Store the readonly field value in an array or process as needed
            $arrayRepeaterData[$row_index]['unique_pet_id'] = $readonly_field_value;
            $row_index++;
        }
        
        $arrayRepeaterData = array_map(function ($pet) {
            if (!empty($pet['pet_dob'])) {
                $date = DateTime::createFromFormat('d/m/Y', $pet['pet_dob']);
                if ($date !== false) {
                    $pet['pet_dob'] = $date->format('Ymd');
                }
            }
            return $pet;
        }, $arrayRepeaterData);

        /*error_log("check repeater data");
        error_log(print_r($arrayRepeaterData, true));*/
        
        
        // Save the entire repeater field data as usermeta (serialized)
        update_field($repeaterFormKey, $arrayRepeaterData, $strWithUserkeyword_user_id);
    }
    if(!empty($arrayRepeaterData))
    {
        $intUserId = explode("_",$strWithUserkeyword_user_id)[1];
        $arrayBeforeUniqueId = get_PetInfo_Distinct_PetIds($intUserId);
        $arrayAfterUniqueId = array_column($arrayRepeaterData, 'unique_pet_id'); 
        $deleteUniqueID = array_diff($arrayBeforeUniqueId, $arrayAfterUniqueId); //  get those pets delete unique pet Id
        $insertUniqueId = array_diff($arrayAfterUniqueId, $arrayBeforeUniqueId); // get those pets new unique pet Id

        if(!empty($deleteUniqueID))
        {
            $wpdb->query(
                $wpdb->prepare(
                "UPDATE {$wpdb->prefix}petinfo
                SET delete_index = pet_index,
                deleted_at = %s
                WHERE user_id = %d AND pet_id in (%s) AND deleted_at IS NULL",
                current_time('mysql'), $intUserId, implode(",",$deleteUniqueID)
                )
            );
        }

        $finalStoreACFRowDatas = get_field($repeaterFormKey, $strWithUserkeyword_user_id);
        foreach ($finalStoreACFRowDatas as $intPetIndexKey => $objSingleArray) {
            foreach ($objSingleArray as $strMetaKey => $strMetaValue) {
                
                if($strMetaKey == 'pet_dob' && $strMetaValue != '')
                {
                    $dateTime = DateTime::createFromFormat('d/m/Y', $strMetaValue);
                    $strMetaValue = $dateTime->format('Ymd');
                }
                if($strMetaKey != 'unique_pet_id')
                {
                    if(in_array($objSingleArray['unique_pet_id'], $insertUniqueId))
                    {
                        $wpdb->query(
                            $wpdb->prepare(
                            "INSERT INTO {$wpdb->prefix}petinfo ( user_id, pet_id, pet_index, meta_key, meta_value) VALUES ( %d, %s, %d, %s, %s )",
                                $intUserId, $objSingleArray['unique_pet_id'], $intPetIndexKey, $strMetaKey, $strMetaValue
                            )
                        );
                    }
                    else
                    {
                        $wpdb->query(
                            $wpdb->prepare(
                            "UPDATE  {$wpdb->prefix}petinfo  SET pet_index = %d, meta_value = %s WHERE user_id = %d AND meta_key = %s AND pet_id = %s AND deleted_at IS NULL",
                                        $intPetIndexKey, $strMetaValue, $intUserId, $strMetaKey, $objSingleArray['unique_pet_id']
                            )
                        );

                    }
                }
            }
        }   
    }
}


function custom_petinfo_admin_menu() {
    add_menu_page(
        'Pet Info',           // Page title
        'Pet Info',           // Menu title
        'manage_options',     // Capability
        'petinfo-admin',      // Menu slug
        'display_petinfo_data', // Callback function
        'dashicons-pets',     // Icon (optional)
        4                    // Position (optional)
    );
}
add_action('admin_menu', 'custom_petinfo_admin_menu');

// Get the petinfo data and display admin pet info page
function display_petinfo_data() {
    global $wpdb, $acfGroupId, $arrayPetBreedOptions;
    $pet_info = [];
    getPetBreedOptions();
    $stePetInfoTableName = $wpdb->prefix . 'petinfo';
    list($repeaterFormId, $repeaterFormKey) = getACFFieldDataMainFormId($acfGroupId);

    $arrayPettypeOptions = get_options_select_field_from_acf($repeaterFormKey, 'pet_type');
    $arrayPetBreedOptionsDirectFromUrlGet = [];
    if(isset($_GET['pet_breed']) && isset($_GET['pet_type']))
    {
        if($_GET['pet_type'] != "" && array_key_exists($_GET['pet_type'],$arrayPetBreedOptions))
        {
            $arrayPetBreedOptionsDirectFromUrlGet = $arrayPetBreedOptions[$_GET['pet_type']];
        }
    }

    // Fetch data from the wp_petinfo table with optional filtering
    $query = "SELECT * FROM $stePetInfoTableName WHERE deleted_at IS NULL";
    if(isset($_GET['user']))
    {
        if($_GET['user'] > 0)
        {
            $query .= $wpdb->prepare(" AND user_id = %d", $_GET['user']);
        }
    }
    $query .= " ORDER BY pet_index";
    // echo $query;
    
    $arrayPetInfos = $wpdb->get_results($query, ARRAY_A);
    $arrayUserInfos = get_user_id_and_username(); 
    $arrayTempPetInfo = [];
    foreach($arrayPetInfos as $intKey => $arrayPetInfo)
    {
        if($arrayPetInfo['meta_key'] == 'pet_dob' && $arrayPetInfo['meta_value'] != '')
        {
            $dateTime = DateTime::createFromFormat('Ymd', $arrayPetInfo['meta_value']);
            $arrayPetInfo['meta_value'] = $dateTime->format('d/m/Y');
        }
        
        $arrayTempPetInfo[$arrayPetInfo['pet_id']][$arrayPetInfo['meta_key']] =  $arrayPetInfo['meta_value'];
        $arrayTempPetInfo[$arrayPetInfo['pet_id']]['user_name'] =  get_username_from_id($arrayPetInfo['user_id']);
        $arrayTempPetInfo[$arrayPetInfo['pet_id']]['user_id'] =  $arrayPetInfo['user_id'];
    }
    $pet_info = $arrayTempPetInfo;

    if(isset($_GET['pet_type']) && $_GET['pet_type'] != null)
    {
        $arrayNewPetInfo = [];
        if(isset($_GET['pet_breed']) && $_GET['pet_breed'] != "")
        {
            foreach ($arrayTempPetInfo as $key => $arrayPet) {
                if($arrayPet['pet_breed'] == $_GET['pet_breed'])
                {
                    $arrayNewPetInfo[] = $arrayPet;
                }
            }
            $pet_info = $arrayNewPetInfo;
        }
        else
        {
            foreach ($arrayTempPetInfo as $key => $arrayPet) {
                if($arrayPet['pet_type'] == $_GET['pet_type'])
                {
                    $arrayNewPetInfo[] = $arrayPet;
                }
            }
            $pet_info = $arrayNewPetInfo;

        }
    }   
    $jsonArrayPetOptions = json_encode($arrayPetBreedOptions);
    include 'admin-pet-info.php';
    
}
$arrayGlobalUserData = [];

// Get the username from useid
function get_username_from_id($intUserId)
{
    global $wpdb, $arrayGlobalUserData;
    if(in_array($intUserId, array_keys($arrayGlobalUserData)))
    {
        return $arrayGlobalUserData[$intUserId];
    }

    // Query to get the username from the wp_users table
    $username = $wpdb->get_var( $wpdb->prepare("SELECT user_login FROM $wpdb->users WHERE ID = %d", $intUserId ) );
    if ( $username ) {
        $arrayGlobalUserData[$intUserId] = esc_html( $username );
        return  esc_html( $username );
    } 
}

// get the pluck id and login users 
function get_user_id_and_username()
{
    global $wpdb, $arrayGlobalUserData;

    // Query to get the username from the wp_users table
    $arrayUsersInfos = $wpdb->get_results("SELECT ID, user_login FROM $wpdb->users", ARRAY_A );
    foreach ($arrayUsersInfos as $arrayUser) {
        $arrayGlobalUserData[$arrayUser['ID']] = esc_html( $arrayUser['user_login'] );
    }
    return $arrayGlobalUserData;
    
}

// get the label and value from form key and it's fields
function get_options_select_field_from_acf($strACFFormKey, $strACFSubFieldName)
{
    global $wpdb;

    $arrayData = [];
    $admin_id = $wpdb->get_var("SELECT u.ID FROM $wpdb->users u INNER JOIN $wpdb->usermeta um ON u.ID = um.user_id WHERE um.meta_key= 'wp_capabilities' AND um.meta_value LIKE '%administrator%' ORDER BY u.ID ASC LIMIT 1");

    $finalAdminId = 'user_'.$admin_id;
    if (have_rows($strACFFormKey, $finalAdminId)) {
        while (have_rows($strACFFormKey, $finalAdminId)) {
            the_row();
            
            // Get the sub field object for the select field
            $select_field = get_sub_field_object($strACFSubFieldName);
            
            if ($select_field) {
                // Get the choices array from the select field
                $options = $select_field['choices'];
                
                // Loop through the options and display them
                foreach ($options as $value => $label) {
                    $arrayData[$label] = esc_html($value);
                }
            }
        }
    }
    else {
        error_log("No repeater found");
    }
    return $arrayData;
}


// Enqueue external CSS in the WordPress admin area
function enqueue_external_admin_css($hook_suffix) {
    // Replace the URL below with your external CSS file URL
    if (in_array($hook_suffix,['toplevel_page_petinfo-admin'])) {
        wp_enqueue_style('external-admin-css', 'https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css');
    }
}
add_action('admin_enqueue_scripts', 'enqueue_external_admin_css');

// Enqueue external JS in the admin footer
function enqueue_external_admin_js($hook_suffix) {
    // Replace the URL below with your external JS file URL
    if (in_array($hook_suffix,['toplevel_page_petinfo-admin'])) {
        wp_enqueue_script('external-admin-js', 'https://cdn.datatables.net/2.1.5/js/dataTables.js', [], null, true);
    }
}
add_action('admin_enqueue_scripts', 'enqueue_external_admin_js');



// Add inline jQuery to the admin footer
function custom_jquery_footer() {
    $hook_suffix = get_current_screen(); //  get the page slug
    if (in_array($hook_suffix->id,['toplevel_page_petinfo-admin'])) {
    ?>
        <script type="text/javascript">
        jQuery(document).ready(function(){
            jQuery('#petInfoTable').DataTable( {
                language: {
                    emptyTable: "No data available in the table"
                },
                responsive: true,
                lengthMenu: [[30,60,90,150,-1], [30,60,90,150, "All"]],
                pageLength: 30,
            });

        });
    </script>
    <?php
    }
}
add_action('admin_footer', 'custom_jquery_footer');

function update_petinfo_data() {
    // Get the order ID from the AJAX request
    $intOrderId = intval($_POST['intOrderId']);
    
    if( $intOrderId ) {
        // Fetch or update order data here
        
        // Example: Get custom meta data for the order
        $objOrder = wc_get_order($intOrderId);
        $intUserId = $objOrder->get_user_id();
        $arrayPetInfoData = json_encode(getPetInfoFromUser($intUserId));

        
        // Return a response
        wp_send_json_success($arrayPetInfoData);
    }
    
    wp_send_json_error();
}
add_action( 'wp_ajax_update_petinfo_data', 'update_petinfo_data' );

// get the current page slug
function get_current_page_slug_in_footer() {
    $returnPageSlug = "";
    // Get the current page or post ID
    if (is_page() || is_single()) {
        $current_post_id = get_the_ID();
        // Get the slug of the current page/post
        $returnPageSlug = get_post_field('post_name', $current_post_id);

        // Output the slug (you can use it as needed, here we're just printing it in the footer)
       /* echo '<script>console.log("Current page slug: ' . esc_js($slug) . '");</script>';*/
    }
    return $returnPageSlug;
}


add_action( 'wp_footer', 'add_custom_script_to_footer' );
//  add the script on front side footer
function add_custom_script_to_footer() {
    global $arrayPetBreedOptions, $formCustomerRegistrationName, $forminatorAddPetInfoFormName;
    getPetBreedOptions();
    $strPageSlug = get_current_page_slug_in_footer(); 

   // echo $strPageSlug . " ". preg_match('/^' . preg_quote('cat-vaccine', '/') . '(-|$)/', $strPageSlug). " sacvvdfbf dfgb ".preg_match('/^' . preg_quote('dog-vaccine', '/') . '(-|$)/', $strPageSlug) ;
    if (is_user_logged_in() ) 
    {
        $intUserId = get_current_user_id();
        $arrayPetOptions  = json_encode(getPetIdAndPetType($intUserId));

        if ( is_product() ) 
        {
            $intSinglePetInfoFormId = get_forminator_form_id_by_name($forminatorAddPetInfoFormName);
            $jsonArrayPetOptions = json_encode($arrayPetBreedOptions);
            include 'pet-info-modal.php';
        }
        else if($strPageSlug == 'add-directory-listing')
        {
            $intSinglePetInfoFormId = get_forminator_form_id_by_name($forminatorAddPetInfoFormName);
            $jsonArrayPetOptions = json_encode($arrayPetBreedOptions);
            include 'pet-info-modal.php';

        ?>
            <script type="text/javascript">
                var formId = jQuery('[data-form-name="frontendsubmit_addentity_add"]').attr('id');
                var arrayPetOptions = JSON.parse('<?php echo $arrayPetOptions; ?>');
                jQuery(document).ready(function(){

                    let pagOnSelectPets = jQuery("#"+formId).find("select[name='drts[field_selectpet]']")
                    jQuery.each(arrayPetOptions, function(index, item) {
                        pagOnSelectPets.append(jQuery('<option>', { 
                            value: item.id, // Value attribute of the option
                            text : item.value, // Displayed text of the option
                            'data-pet_name': item.name,
                            'data-pet_gender': item.gender,
                            'data-pet_breed': item.breed,
                        }));
                    });

                    jQuery("#"+formId).find("select[name='drts[field_selectpet]']").on('change', function(){
                        var selectPetName = jQuery("#"+formId).find("select[name='drts[field_selectpet]'] option:selected").data('pet_name');
                        var selectPetBreed = jQuery("#"+formId).find("select[name='drts[field_selectpet]'] option:selected").data('pet_breed');
                        var selectPetGender = jQuery("#"+formId).find("select[name='drts[field_selectpet]'] option:selected").data('pet_gender');

                        //jQuery("#"+formId).find("input[name='drts[post_title][0]']").val(selectPetName);
                        jQuery("#"+formId).find("input[name='drts[field_petname][0]']").val(selectPetName);
                        jQuery("#"+formId).find("input[name='drts[field_pet_breed][0]']").val(selectPetBreed);
                        if(selectPetGender == 'Male')
                        {
                            jQuery("#"+formId).find("input[name='drts[field_gender][0]'][value='male']").prop('checked', true);
                        }
                        else
                        {
                            jQuery("#"+formId).find("input[name='drts[field_gender][0]'][value='female']").prop('checked', true);
                        }


                    });

                });

            </script>
        <?php
        }
        

    }
    else
    { ?>
        <script type="text/javascript">
        jQuery(".wpcpo-options").find(".wpcpo-option-select:first").after(jQuery('<button>',{
                    id: 'addpet',  // Set an ID for the button
                    text: 'Add New Pet',  // Set button text
                    click: function() {  // Add a click event to the button
                    var paths = window.location.pathname.split("/");
                    var strExtraFolder = "";
                    /*if(paths[1] == "mrsam"){
                        strExtraFolder = "/mrsam"; // if extra folder then set here otherwise you set blank here
                    }*/
                       window.location.href =  strExtraFolder + "/login";
                    }
                }));
            </script>
    <?php } 
    
    if($strPageSlug == $formCustomerRegistrationName){
        $jsonArrayPetOptions = json_encode($arrayPetBreedOptions);
        ?>
        <script type="text/javascript">
            var javascriptArrayPetBreedOptions = JSON.parse('<?php echo $jsonArrayPetOptions; ?>');
            jQuery(document).ready(function(){
                    let objPetTypeSelect = jQuery("#group-1 .forminator-grouped-fields .forminator-select--field:eq(0)").val();
                    let objPetBreedOption = jQuery("#group-1 .forminator-grouped-fields .forminator-select--field:eq(1)");
                    objPetBreedOption.empty(); 
                    objPetBreedOption.append('<option value="">Select Pet Breeds</option>');
                    jQuery.each(javascriptArrayPetBreedOptions[objPetTypeSelect], function (index, option) {
                        objPetBreedOption.append('<option value="' + index + '">' + option + '</option>');
                    });

                    setChangePetBreed();
                    setLastAddPetAddAction();

                function setLastAddPetAddAction(){
                    jQuery("#group-1 .forminator-grouped-fields").last().find(".forminator-repeater-action-button").on('click', function(){
                        setTimeout(function(){
                            let objPetSelect = jQuery("#group-1").find(".forminator-grouped-fields").last().find(".forminator-select--field:eq(0)").val();
                            let objPetBreedOption = jQuery("#group-1").find(".forminator-grouped-fields").last().find(".forminator-select--field:eq(1)");
                            objPetBreedOption.empty(); 
                            objPetBreedOption.append('<option value="">Select Pet Breeds</option>');
                            jQuery.each(javascriptArrayPetBreedOptions[objPetSelect], function (index, option) {
                                objPetBreedOption.append('<option value="' + index + '">' + option + '</option>');
                            });
                            setChangePetBreed();
                            setLastAddPetAddAction();
                        },500);

                    });
                }
               

                function setChangePetBreed(){
                    jQuery("#group-1 .forminator-grouped-fields").last().find(".forminator-select--field:eq(0)").on('change', function(){
                        let objPetSelect = jQuery(this).val();
                        let objPetBreedOption = jQuery(this).closest(".forminator-grouped-fields").find(".forminator-select--field:eq(1)");
                        objPetBreedOption.empty(); // Removes all options from the <select>
                        objPetBreedOption.append('<option value="">Select Pet Breeds</option>');
                        jQuery.each(javascriptArrayPetBreedOptions[objPetSelect], function (index, option) {
                            objPetBreedOption.append('<option value="' + index + '">' + option + '</option>');
                        });
                    });
                }

            });
        </script>
        <?php

        include 'includes/verify-otp-modal.php';
    }
}

// get pet id and value inside the uuid
function getPetIdAndPetType($intUserId)
{
    global $wpdb, $acfGroupId;
    list($repeaterFormId, $repeaterFormKey) = getACFFieldDataMainFormId($acfGroupId);
    $arrayExistingACFRowData = get_field($repeaterFormKey, 'user_' . $intUserId);
    $arrayPetInfoIds = [];
    if(!empty($arrayExistingACFRowData))
    {

        foreach($arrayExistingACFRowData as $intKey => $arrayPetInfo)
        {
            $arrayPetInfoIds[$arrayPetInfo['unique_pet_id']] = [

                                                                'id' =>  $arrayPetInfo['unique_pet_id'],
                                                                'value' => $arrayPetInfo['pet_type']." - ".$arrayPetInfo['pet_name'],
                                                                'name' => $arrayPetInfo['pet_name'],
                                                                'gender' => $arrayPetInfo['pet_gender'],
                                                                'breed' => $arrayPetInfo['pet_breed'],
                                                                'strlowertype' => strtolower($arrayPetInfo['pet_type'])
                                                               ];
        }

    }
    return $arrayPetInfoIds;
}

// get petinfo from userid
function getPetInfoFromUser($intUserId)
{
    global $wpdb, $acfGroupId;
    list($repeaterFormId, $repeaterFormKey) = getACFFieldDataMainFormId($acfGroupId);
    $arrayExistingACFRowData = get_field($repeaterFormKey, 'user_' . $intUserId);
    $arrayValues = ['pet_type','pet_breed','pet_gender','pet_dob','pet_name','pet_vaccination'];
    $arrayFormatValues = ['Type','Breed','Gender','DOB','Name','Vaccination'];
    $arrayPetInfoIds = [];
    foreach($arrayExistingACFRowData as $intKey => $arrayPetInfo)
    {
        $arrayDisplayString = [];
        foreach($arrayValues as $intKey => $arrayValue)
        {

            if($arrayValue == 'pet_breed'  && isset($arrayPetInfo['pet_breed']))
            {
                if($arrayPetInfo['pet_breed'] != "")
                {
                    $arrayDisplayString[] = $arrayFormatValues[$intKey].": ".$arrayPetInfo['pet_breed'];
                }
            }
            else
            {
                $arrayDisplayString[] = $arrayFormatValues[$intKey].": ".$arrayPetInfo[$arrayValue];
            }
        }

        $arrayPetInfoIds[$arrayPetInfo['unique_pet_id']] =  implode(", ", $arrayDisplayString);

    }
    return $arrayPetInfoIds;
}

function addOptionsInsideForminatorForm() {
    global $arrayPetBreedOptions, $forminatorPetInfoFormName, $forminatorAddPetInfoFormName;
    getPetBreedOptions();
    
    
    $intSingleAddPetInfoFormId = get_forminator_form_id_by_name($forminatorAddPetInfoFormName);
    $intSinglePetInfoFormId = get_forminator_form_id_by_name($forminatorPetInfoFormName);
    $forminatorPetInfoUserRegistrationFormName = $forminatorUserFormName;
    $intSinglePetInfoUserRegistrationFormId = get_forminator_form_id_by_name($forminatorPetInfoUserRegistrationFormName);

    $options = [];
    foreach($arrayPetBreedOptions as $key => $arrayPetBreed)
    {
        foreach($arrayPetBreed as $key => $pet_breed)
        {
            $options[] = ['label' => $pet_breed, 'value' => $key];
        }

    }
    $strPetInfoAndAddPetInfoElementId = 'select-2';
    // Set with simple pet info form inside the my account section
    $select_field = Forminator_API::get_form_field($intSinglePetInfoFormId, $strPetInfoAndAddPetInfoElementId, true);
    if ($select_field) 
    {
        Forminator_API::update_form_field($intSinglePetInfoFormId, $strPetInfoAndAddPetInfoElementId, ['options' => $options]);
    }
    // End with simple pet info form inside the my account section

    // Set with simple add pet info form inside the my account section
    $select_field = Forminator_API::get_form_field($intSingleAddPetInfoFormId, $strPetInfoAndAddPetInfoElementId, true);
    if ($select_field) 
    {
        Forminator_API::update_form_field($intSingleAddPetInfoFormId, $strPetInfoAndAddPetInfoElementId, ['options' => $options]);
    }
    // End with simple add pet info form inside the my account section

    // Set with  user registration forminator field options with set permission temporary

    $form = Forminator_API::get_form($intSinglePetInfoUserRegistrationFormId);
    $form_data = Forminator_API::get_form_wrappers($intSinglePetInfoUserRegistrationFormId);
       
       foreach($form_data as $parentKey => $fields)
       {
            foreach($fields['fields'] as $key => $field)
            {
                if($field['element_id'] == 'select-5')
                {
                    $form_data[$parentKey]['fields'][$key]['options'] = $options;
                }
            }
       }
    $admin_user = get_user_by('ID', 1); // Change to the admin user ID
    if ($admin_user) {
        wp_set_current_user($admin_user->ID);
    }

   $updated_form = Forminator_API::update_form($intSinglePetInfoUserRegistrationFormId, $form_data , $form->settings);
    // End with  user registration forminator field options with set permission temporary
}


function sync_acf_fields_on_group_update($field_group) {
    global $acfPetBreedGroupId, $arrayPetBreedOptions, $acfGroupId;

    if($field_group['key'] == $acfPetBreedGroupId)
    {
        getPetBreedOptions();

        $options = [];
        foreach($arrayPetBreedOptions as $key => $arrayPetBreed)
        {
            foreach($arrayPetBreed as $key => $pet_breed)
            {
                $options[$key] = $pet_breed;
            }

        }
        $arrayAdminUserGroupFields = acf_get_fields($acfGroupId);
        $field_key = "";

        foreach($arrayAdminUserGroupFields[0]['sub_fields'] as $parentKey => $field)
        {
            if($field['name'] == 'pet_breed')
            {
                $field_key = $arrayAdminUserGroupFields[0]['sub_fields'][$parentKey]['key'];
            } 
        }
        $field = acf_get_field($field_key);

        if ($field) {
            $field['choices'] = $options;
        }

        acf_update_field($field);
        addOptionsInsideForminatorForm();
    }

}
add_filter('acf/update_field_group', 'sync_acf_fields_on_group_update', 10, 1);

add_filter('woocommerce_get_item_data', 'display_pet_name_in_cart_and_checkout', 10, 2);
function display_pet_name_in_cart_and_checkout($item_data, $cart_item) {
    
    if (is_user_logged_in() ) 
    {
        $intUserId = get_current_user_id();
        $arrayPetInfoData = getPetInfoFromUser($intUserId);
        $titleName = 'Pet';
        // Check if the WPC product option (pet ID) exists in the cart item data

        $cart_select_pet_index = array_search($titleName, array_column($cart_item['wpcpo-options'], 'title'));

        if (isset($cart_item['wpcpo-options'][$cart_select_pet_index]['value'])) {
            $intPetId = $cart_item['wpcpo-options'][$cart_select_pet_index]['value'];
            $index = array_search($titleName, array_column($item_data, 'name'));

            // Replace the pet ID with the pet name in the cart display
            $item_data[$index] = array(
                'key' => __($titleName, 'woocommerce'), // Label for the pet name
                'value' => $arrayPetInfoData[$intPetId], // Display the pet name
            );
        }
    }

    return $item_data;
}

// Admin wp-order page for the set value select pet
add_filter('woocommerce_order_item_display_meta_value', 'custom_replace_product_option_id', 10, 3);
function custom_replace_product_option_id($meta_value, $meta, $order) {
    // Check if the meta key is the one added by WooCommerce Products Options plugin
    if ($meta->key === 'Pet') {  // Replace with the actual meta key

        $order_id = $order->get_order_id();
        $order = wc_get_order($order_id); // Get the order object
        if ($order) {
            $intUserId = $order->get_user_id();
            $arrayPetInfoData = getPetInfoFromUser($intUserId);
            return $arrayPetInfoData[$meta->value];
        }
    }

    return $meta_value;
}

?>