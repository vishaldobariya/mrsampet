<?php
    /* Product Page Pop Up */
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" ></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>

<div class="modal fade petinfomodal" id="addEditPetInfo" tabindex="-1" role="dialog" aria-labelledby="addEditPetInfo" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title flex-item" id="headerPetInfo">Add Pet Info</h5>
        <button type="button" class="close  flex-item" data-dismiss="modal" aria-label="Close" onclick="closeModal('addEditPetInfo')">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <?php echo do_shortcode('[forminator_form id="'.$intSinglePetInfoFormId.'"]'); ?>
      </div>
      <!-- <div class="modal-footer">
      </div> -->
    </div>
  </div>
</div>

<script type="text/javascript">
        var arrayPetOptions = JSON.parse('<?php echo $arrayPetOptions; ?>');
        var javascriptArrayPetBreedOptions = JSON.parse('<?php echo $jsonArrayPetOptions; ?>');
        jQuery(document).ready(function(){

          var petSelect = jQuery(".wpcpo-options").find("select:first").attr('id');
          jQuery('#' + petSelect).empty();

          jQuery('#' + petSelect).append(jQuery('<option>', { 
              value: "", // Value attribute of the option
              text : "Select pet", // Displayed text of the option  
          }));

          var urlParams = new URLSearchParams(window.location.search);
          var petParam = urlParams.get('pet');
           
    
          jQuery.each(arrayPetOptions, function(index, item) {

            /*if(petParam != '')
            {
              if(petParam != 'null')
              {
                petParam = petParam.toLowerCase();
                if(petParam != item.strlowertype)
                {
                  return true;
                }
              }
            }*/
            if (petParam !== '' && petParam !== null && petParam !== undefined && petParam !== 'null') {
                petParam = petParam.toLowerCase();
                if (petParam !== item.strlowertype) {
                    return true;
                }
            }

            jQuery('#' + petSelect).append(jQuery('<option>', { 
                value: item.id, // Value attribute of the option
                text : item.value, // Displayed text of the option
                    'data-title': "Pet",
                    'data-label': item.id,
                    'data-enable-price': "1",
                    'data-price-type': "flat",
                    'data-pet-type-lower': item.strlowertype,
            }));
        });

        if(jQuery(".wpcpo-options").find(".wpcpo-option-select:first").length > 0) 
        {
          jQuery(".wpcpo-options").find(".wpcpo-option-select:first").after(jQuery('<button>',{
            id: 'addpet',  // Set an ID for the button
              text: 'Add New Pet',  // Set button text
              click: function() {  // Add a click event to the button

                jQuery('#addEditPetInfo').modal({backdrop: 'static', keyboard: false});
                jQuery('#addEditPetInfo').modal('show');
                setPetBreedOptions();
                  
              }
          }));
        }
        else if(jQuery("select[name='drts[field_selectpet]']").length > 0)
        {
          jQuery("select[name='drts[field_selectpet]']").each(function(index) {
                jQuery(this).after(jQuery('<a>',{
                  id: 'userNewPet',  // Set an ID for the button
                  text: 'Add New Pet',  // Set button text
                  href: 'javascript:void(0)',
                  class: 'btn btn-success mt-3',
                    click: function() {  // Add a click event to the button

                      jQuery('#addEditPetInfo').modal({backdrop: 'static', keyboard: false});
                      jQuery('#addEditPetInfo').modal('show');
                      setPetBreedOptions();
                    }
                }));
          });
        }

        jQuery(document).on('forminator:form:submit:success', function() {
                // Reload the page after form submission
                location.reload();
              });
        });

       

      function closeModal(id) {
            jQuery('#'+id).modal('hide');
        }

        function setPetBreedOptions(){
          let objPetTypeSelect = jQuery("#addEditPetInfo select[name='select-1']").val();
                let objPetBreedOption = jQuery("#addEditPetInfo select[name='select-2']");
                objPetBreedOption.empty(); // Removes all options from the <select>
                objPetBreedOption.append('<option value="">Select Pet Breeds</option>');
                jQuery.each(javascriptArrayPetBreedOptions[objPetTypeSelect], function (index, option) {
                    objPetBreedOption.append('<option value="' + index + '">' + option + '</option>');
                });


            jQuery("#addEditPetInfo select[name='select-1']").on('change', function(){

                    let objPetSelect = jQuery(this).val();
                    let objPetBreedOption = jQuery("#addEditPetInfo select[name='select-2']");
                    objPetBreedOption.empty(); // Removes all options from the <select>
                    objPetBreedOption.append('<option value="">Select Pet Breeds</option>');
                    jQuery.each(javascriptArrayPetBreedOptions[objPetSelect], function (index, option) {
                        objPetBreedOption.append('<option value="' + index + '">' + option + '</option>');
                    });
                });

        }
      </script>