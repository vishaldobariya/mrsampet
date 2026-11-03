<?php


	function custom_store_vendor_jquery_footer()
	{
	
		$strPageSlug = get_current_page_slug_in_footer();
		//if(!current_user_can('administrator'))
		if ( !current_user_can('administrator') && !current_user_can('website_admin') )
		{

			if(in_array($strPageSlug, ['store-manager']))
			{
			?>
				<script type="text/javascript">
					var strPageSlug = '<?php echo $strPageSlug; ?>';
					 jQuery(document).ready(function(){
					 	jQuery("#add_new_product_dashboard").remove();
					 	jQuery(".wcfm_sub_menu_items_product_manage").remove();
					 	jQuery(document).ajaxComplete(function(event, xhr, settings) {
		                    jQuery('.wcfm_product_archive').remove();
		                });
		                if(jQuery("#product_type").length > 0)
		                {
		                	if(jQuery("#product_type").val() == 'booking')
		                	{
		                		jQuery("input[name='featured_img_remove_button']").remove();
		                		//2025.05.11 jQuery("#gallery_img").parent('.wcfm_product_manager_gallery_fields').children(".catalog_visibility_ele").remove();
		                		//2025.05.11 jQuery("#gallery_img").parent('.wcfm_product_manager_gallery_fields').children(".screen-reader-text").remove();
		                		//2025.05.11 jQuery(".wcfm-wp-fields-uploader").find('input').remove();
		                		// booking tab
    		                		//jQuery("._wc_booking_duration_type").remove();
    		                		//jQuery("#_wc_booking_duration_type").remove();
    		                		jQuery("#_wc_booking_duration_type").prop('disabled', true);
    		                		//jQuery("#_wc_booking_duration_unit").remove();
    		                		//jQuery("#_wc_booking_duration").remove();
    		                		jQuery("#_wc_booking_min_duration").remove();
    		                		jQuery("#_wc_booking_max_duration").remove();
    		                		jQuery("._wc_booking_min_duration").remove();
    		                		jQuery("._wc_booking_max_duration").remove();
    		                		jQuery("._wc_booking_enable_range_picker").remove();
    		                		jQuery("#_wc_booking_enable_range_picker").remove();
    		                		jQuery("._wc_booking_calendar_display_mode").remove();
    		                		jQuery("#_wc_booking_calendar_display_mode").hide();
    		                		jQuery("#_wc_booking_calendar_display_mode").prop('disabled', true);
    		                		jQuery("._wc_booking_requires_confirmation").remove();
    		                		jQuery("#_wc_booking_requires_confirmation").remove();
    		                		
    		                		//booking-cancel option
        		                		jQuery("#_wc_booking_user_can_cancel").prop('checked', true);
        		                		//jQuery("#_wc_booking_user_can_cancel").prop('disabled', true);
        		                		jQuery("#_wc_booking_user_can_cancel").addClass('d-none');
        		                		jQuery("._wc_booking_user_can_cancel").hide();
        		                		
        		                		jQuery("._wc_booking_cancel_limit").addClass('d-none');
        		                		jQuery("#_wc_booking_cancel_limit").addClass('d-none');
        		                		jQuery("#_wc_booking_cancel_limit").prop('disabled', true);
        		                		jQuery("#_wc_booking_cancel_limit_unit").addClass('d-none');
        		                		jQuery("#_wc_booking_cancel_limit_unit").prop('disabled', true);
        		                		jQuery(".description.can_cancel_ele").addClass('d-none');
		                		// shipping tab
		                		    jQuery("#wcfm_products_manage_form_shipping_head").remove();
		                	
		                		// availibility tab
		                		    jQuery("._wc_booking_qty").remove();
    		                		jQuery("#_wc_booking_qty").remove();
    		                		jQuery("._wc_booking_buffer_period").remove();
    		                		jQuery("#_wc_booking_buffer_period").remove();
    		                		jQuery('span._wc_booking_buffer_period_unit').parent('p.description').remove();
    		                		jQuery("._wc_booking_apply_adjacent_buffer").remove();
    		                		jQuery("#_wc_booking_apply_adjacent_buffer").remove();
    		                		jQuery("._wc_booking_default_date_availability").remove();
    		                		jQuery("#_wc_booking_default_date_availability").remove();
    		                		jQuery("._wc_booking_check_availability_against").remove();
    		                		jQuery("#_wc_booking_check_availability_against").remove();
    		                		jQuery("._wc_booking_has_restricted_days").remove();
    		                		jQuery("#_wc_booking_has_restricted_days").remove();
    		                		jQuery('._wc_booking_restricted_days.wcfm_title.booking strong').text('Available Days');
    		                		
    		                		jQuery("._wc_booking_first_block_time").remove();
    		                		jQuery("#_wc_booking_first_block_time").val('01:00').css('pointer-events', 'none').addClass('d-none');
    		                		
    		                		//jQuery("._wc_booking_availability_rules").remove();
    		                		//jQuery("#_wc_booking_availability_rules").remove();
    		                		jQuery("#_wc_booking_availability_rules_type_0").prop('readonly', true).css('pointer-events', 'none').addClass('d-none');
    		                		jQuery("._wc_booking_availability_rules_type_0").after('<p class="wcfm_title" style="width:75%;font-weight:600;">Operational Hours - Time Range (All Days)</p>');

    		                		
    		                		jQuery("._wc_booking_availability_rules_bookable_0").prop('readonly', true).css('pointer-events', 'none').addClass('d-none');
    		                		jQuery("#_wc_booking_availability_rules_bookable_0").prop('readonly', true).css('pointer-events', 'none').addClass('d-none');
    		                		jQuery("._wc_booking_availability_rules_priority_0").prop('readonly', true).css('pointer-events', 'none').addClass('d-none');
    		                		jQuery("#_wc_booking_availability_rules_priority_0").prop('readonly', true).css('pointer-events', 'none').addClass('d-none');
    		                		//jQuery(".add_multi_input_block").hide();
    		                		jQuery('#_wc_booking_availability_rules .add_multi_input_block').hide().addClass('d-none');
    		                		
    		                		jQuery(".add_multi_input_block").prop('readonly', true);
    		                		jQuery(".avail_rules_desc").remove();
    		                		
    		                		
		                		// cost tab
    		                		jQuery("#wcfm_products_manage_form_costs_head").remove();
    		                		jQuery("._wc_booking_block_cost").remove();
    		                		jQuery("#_wc_booking_block_cost").remove();
    		                		jQuery("._wc_display_cost").remove();
    		                		jQuery("#_wc_display_cost").remove();
    		                		jQuery("._wc_booking_cost_rules").remove();
    		                		jQuery("#_wc_booking_cost_rules").remove();
    		                		jQuery(".cost_rules_desc").remove();
		                		// resources tab
    		                		//jQuery("#wcfm_products_manage_form_resources_head").remove();
    		                		jQuery('#wcfm_products_manage_form_resources_head').contents().filter(function() {
                                        return this.nodeType === 3; // Node type 3 is text node
                                        console.log (this.nodeType === 3);
                                      }).replaceWith('Pricing');
    		                		jQuery("#_wc_booking_resource_label").prop('disabled', true).css('pointer-events', 'none').addClass('d-none');
    		                		jQuery("#_wc_booking_resource_label").prop('disabled', true).css('pointer-events', 'none').addClass('d-none');
    		                		jQuery("._wc_booking_resource_label").remove();
    		                		jQuery("._wc_booking_resources.wcfm_title").remove();
    		                		jQuery("._wc_booking_resources_assignment").hide();
    		                		jQuery("#_wc_booking_resources_assignment").hide();
    		                		jQuery("._wc_booking_all_resources").hide();
    		                		jQuery("#_wc_booking_all_resources").hide();
    		                		jQuery("#_wc_booking_resources_resource_title_0").prop('readonly', true).css('pointer-events', 'none');
    		                		jQuery("._wc_booking_resources_resource_block_cost_0").remove();
    		                		jQuery("#_wc_booking_resources_resource_block_cost_0").remove();
    		                		//jQuery("#_wc_booking_resources_resource_quantity_0").prop('disabled', true);
    		                		jQuery("#_wc_booking_resources_resource_title_1").prop('readonly', true).css('pointer-events', 'none');
    		                		jQuery("._wc_booking_resources_resource_block_cost_1").remove();
    		                		jQuery("#_wc_booking_resources_resource_block_cost_1").remove();
    		                		//jQuery("#_wc_booking_resources_resource_quantity_1").prop('disabled', true);
    		                		//jQuery(".remove_multi_input_block").remove();
    		                		jQuery('#_wc_booking_resources_resource_id_0').next('.remove_multi_input_block').remove();
    		                		//jQuery(".add_multi_input_block").remove();
    		                		jQuery('#_wc_booking_resources').find('.add_multi_input_block').remove().addClass('d-none');	
    		                		jQuery('#_wc_booking_resources').find('.remove_multi_input_block').remove().addClass('d-none');	
    		                		
    		                		
    		                		
		                		//top section
    		                		jQuery("#product_type").hide();
    		                		jQuery("#_wc_booking_has_resources").hide();
    		                		jQuery(".has_resource_ele_title").hide();
    		                		jQuery("#_wc_booking_has_persons").hide();
    		                		jQuery(".has_person_ele_title").hide();
    		                		//2025.05.11 jQuery("#gallery_img").remove();
    		                		jQuery("#catalog_visibility").remove();
    		                		jQuery(".catalog_visibility_ele").remove();
    		                		jQuery(".screen-reader-text").remove();
    		                		
		                	}
		                	else if(jQuery("#product_type").val() == 'simple')
		                	{
		                		jQuery("input[name='featured_img_remove_button']").remove();
		                		jQuery("#gallery_img").parent('.wcfm_product_manager_gallery_fields').children(".catalog_visibility_ele").remove();
		                		jQuery("#gallery_img").parent('.wcfm_product_manager_gallery_fields').children(".screen-reader-text").remove();
		                		jQuery(".wcfm-wp-fields-uploader").find('input').remove();
		                		jQuery(".remove_multi_input_block").remove();
		                		jQuery(".add_multi_input_block").remove();
		                		// Inventory tab
		                		jQuery(".backorders").remove();
		                		jQuery("#backorders").remove();
		                		jQuery(".sold_individually").remove();
		                		jQuery("#sold_individually").remove();
		                		// Shipping tab
		                		jQuery(".weight").remove();
		                		jQuery("#weight").remove();
		                		jQuery(".length").remove();
		                		jQuery("#length").remove();
		                		jQuery("#width").remove();
		                		jQuery("#height").remove();
		                		jQuery(".shipping_class").remove();
		                		jQuery("#shipping_class").remove();
		                		jQuery("#product_type").hide();
		                	}
		                	
		                	//vaccine tab - show only if title has vaccine in it
		                	    var titleInput = document.getElementById("pro_title");
                                //var vaccineDiv = document.getElementById("wcfm_products_manage_form_vaccine-price_head");
                                
                                function updateVaccineDivVisibility() {
                                    var title = titleInput.value.toLowerCase();
                                    var catDiv = document.getElementById("wcfm_products_manage_form_cat-vaccine-price_head");
                                    var dogDiv = document.getElementById("wcfm_products_manage_form_dog-vaccine-price_head");
                                
                                    
                                
                                    // Hide all vaccine-related divs
                                        [catDiv, dogDiv].forEach(function(div) {
                                            if (div) div.style.display = "none";
                                        });
                                    /*var allVaccineDivs = document.querySelectorAll("[id*='vaccine']");
                                    allVaccineDivs.forEach(function(div) {
                                        div.style.display = "none";
                                    });*/
                                
                                    // Show the appropriate div
                                    if (title === "cat vaccine") {
                                        var catDiv = document.getElementById("wcfm_products_manage_form_cat-vaccine-price_head");
                                        if (catDiv) catDiv.style.display = "block";
                                        if (catDiv) dogDiv.style.display = "none";
                                    } else if (title === "dog vaccine") {
                                        var dogDiv = document.getElementById("wcfm_products_manage_form_dog-vaccine-price_head");
                                        if (dogDiv) dogDiv.style.display = "block";
                                        if (dogDiv) catDiv.style.display = "none";
                                    }
                                }
                                
                                if (titleInput) {
                                    updateVaccineDivVisibility();
                                
                                    // Attach dynamic update on title change
                                    titleInput.addEventListener("input", updateVaccineDivVisibility);
                                }
		                	
		                }
		                //store settings page
		                    jQuery('.wcfm_vendor_settings_heading').each(function(index) {
                                const $headingDiv = jQuery(this); // wrap this in jQuery
                                const headingText = $headingDiv.find('h2').text().trim();
                            
                                //console.log(`Heading ${index}: "${headingText}"`);
                            
                                if (headingText === 'Store Visibility Setup') {
                                //  console.log('✅ Match found — hiding this div.');
                                  $headingDiv.hide();
                                }
                            });
                            jQuery("#wcfm_settings_form_vendor_invoice_head").remove();
                        
                        //profile page
                            jQuery('#wcfm_profile_address_expander .wcfm_profile_heading').each(function(index) {
                                const $headingDiv = jQuery(this); // wrap this in jQuery
                                const headingText = $headingDiv.find('h3').text().trim();
                            
                                if (headingText === 'Shipping') {
                                  $headingDiv.hide();
                                  jQuery('#wcfm_profile_address_expander .same_as_billing').hide();
                                  jQuery('#wcfm_profile_address_expander #same_as_billing').hide();
                                }
                            });
                        //order-details
                            //jQuery("#order_quick_actions .wcfm_pdf_invoice.order_quick_action.add_new_wcfm_ele_dashboard ").remove();
                            

					 });

				</script>
	<?php
			}
		}	
	}

	add_action('wp_footer', 'custom_store_vendor_jquery_footer');
	
	
//-- Custom menus in store vendor for classifieds
    add_filter('wcfm_menus','ws_get_wcfm_menus', 30, 1);
    function ws_get_wcfm_menus($wcfm_menus) {
    
        global $wpdb;
    
        $vendor_id = get_current_user_id();
    
        $mapping_table = $wpdb->prefix . 'wcfm_store_categories_mapping';
        $categories_table = $wpdb->prefix . 'wcfm_store_categories';
    
        // Get all active categories for vendor
        $categories = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT c.ID, c.name
                FROM $mapping_table m
                JOIN $categories_table c ON m.store_category_id = c.ID
                WHERE m.vendor_id = %d
                AND c.is_active = 1",
                $vendor_id
            )
        );
        
        
        // Categories to exclude by exact name
            $exclude_names = [
                'Health Checkup',
                'Veterinarian',
                'Vaccine',
                'Pet Groomer',
                'Product Selling'
            ];
            
        // Filter categories: remove excluded ones
            $categories = array_filter($categories, function($category) use ($exclude_names) {
                return !in_array($category->name, $exclude_names);
            });
        
        
        if(!empty($categories)){
            foreach($categories as $category){
                $menu_slug = 'ws-cf-' . $category->ID;
                $menu_label = 'Add ' . $category->name;
                // Convert category name → lowercase + remove spaces
                    $cat_slug = sanitize_title($category->name);
                    $cat_slug = str_replace('-', '', $cat_slug); // remove hyphens if you want no separators
                    
                //Custom Overwrites
                    $slug_map = [
                        'petphotography'  => 'photography',
                        'petmealsservice'  => 'petmeals',
                        'petedoctor'  => 'edoctor',
                        'petoldagehome'  => 'oldagehome',
                        'petcommunicator' => 'communicator'
                    ];
                    
                    if (isset($slug_map[$cat_slug])) {
                        $cat_slug = $slug_map[$cat_slug];
                    }
                    
                    
                // Example external URL
                    //$external_url = site_url('/add-directory-listing/?bundle=petwalker_dir_ltg' . $category->name);
                    $external_url = site_url('/add-directory-listing/?bundle=' . $cat_slug . '_dir_ltg');
    
                $wcfm_menus[$menu_slug] = array(
                    'label' => $menu_label,
                    'url'   => $external_url,
                    'icon'  => 'arrow-right'
                );
            }
    
        } else {
    
            // fallback menu
            /*$wcfm_menus['ws-cf-default'] = array(
                'label' => 'Add Classified',
                'url'   => site_url('/add-classified'),
                'icon'  => 'arrow-right'
            );*/
    
        }
    
        return $wcfm_menus;
    }
    
    /*function get_wcfm_cf_url() {
        $wcfm_page = get_wcfm_page();
        return wcfm_get_endpoint_url('wfcmct-classifieds', '', $wcfm_page);
    }*/