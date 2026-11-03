<?php 
    /* Customer My Account Pet Info Table */
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


    <div id="MyAcPetInfo">
        <div class="icon-box icon-box-side woocommerce-MyAccount-PetInfo justify-content-start mb-4">
            <span class="icon-box-icon text-grey mr-2">
                <i class="fas fa-paw"></i>
            </span>
            <div class="icon-box-content">
                <h4 class="icon-box-title text-normal">My Pets</h4>
            </div>
        </div>
            <?php   
                $arrayRepeaterValues = ['pet_type','pet_breed','pet_gender','pet_dob','pet_name','pet_vaccination'];
                if(!empty($pet_info))
                {
            ?>
                <div>
                    <table class="table mypetinfo-table" border="1">
                        <thead>
                            <th>Pet Type</th>
                            <th>Pet Breed</th>
                            <th>Gender</th>
                            <th>Pet DOB</th>
                            <th>Pet Name</th>
                            <th>Vaccinated</th>
                            <th>Action</th>
                        </thead>
                        <tbody>
                            <?php
                           // $arrayRepeaterValues = ['pet_type','pet_breed','pet_gender','pet_dob','pet_name','pet_vaccination'];
                            /*if(!empty($pet_info))
                            {*/   
                                foreach ($pet_info as $pet_index => $pet) {
                                    $editQueryString = '?status=update&pet_index=pet_'.$pet_index;
                                echo '<tr>';
                                
                                    foreach ($arrayRepeaterValues as $key => $repeaterSubKey) {
                                        /*if($pet['pet_type'] != 'Dog')
                                        {
                                            $pet['pet_breed'] = "";
                                        }*/
                                        echo '<td>'.$pet[$repeaterSubKey].'</td>';
                                        $editQueryString .= '&'.$repeaterSubKey."=".$pet[$repeaterSubKey];
                                    }
                                        echo '<td class="ctactions"><a href="'.$editQueryString.'" class="ctaction-edit">Edit </a>';
                                        echo '<a href=?status=delete&pet_id='.$pet['unique_pet_id'].'&pet_index='.$pet_index.' class="ctaction-delete">Delete</a></td>';
    
                                echo '</tr>';
                                }
                            /*}*/
                           
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php 
                }else{ 
            ?>
                <div class="woocommerce-info alert alert-simple alert-btn alert-info">
		             No Pet has been added yet! 
		        </div>
            <?php    
            }
                       
            ?>
            <div class="mypetinfobtn">
                <button type="button" class="btn btn-sm btn-dark btn-rounded myaccountpetinfobtn" onclick="enabledPetType();editHeaderPetInfo('Add pet info');openModal('addEditPetInfo')">Add New Pet </button>
                
            </div>
    </div>
    <input type="hidden" id="petInfoKey" value="<?php if (isset($_GET['pet_index'])) echo $_GET['pet_index']; ?>">
    <input type="hidden" id="petId" value="<?php if (isset($_GET['pet_id'])) echo $_GET['pet_id']; ?>">
    <input type="hidden" id="petInfoFormId" value="<?php echo $intSinglePetInfoFormId; ?>">

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" ></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

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

    <div class="modal fade petinfomodal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Confirmation!</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"  onclick="closeModal('deleteModal')">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            Are you sure ? You want to delete Pet information
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-primary" id='confirmDelete'>Yes</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade petinfomodal" id="deleteSuccessModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Confirmation!</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeModal('deleteSuccessModal')">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            Pet is deleted successfully
          </div>
        </div>
      </div>
    </div>

<script type="text/javascript">
    var javascriptArrayPetBreedOptions = JSON.parse('<?php echo $jsonArrayPetOptions; ?>');
    var intSinglePetInfoFormId = '<?php echo $intSinglePetInfoFormId; ?>';
    var strSelectPetBreed = '<?php echo isset($_GET['pet_breed'])?  $_GET['pet_breed'] : "" ?>';

    function openModal(id) {
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

        jQuery('#'+id).modal({backdrop: 'static', keyboard: false});
        jQuery('#'+id).modal('show');
    }

    function editHeaderPetInfo(textvalue)
    {
        jQuery('#headerPetInfo').html(textvalue)
    }

    function closeModal(id) {
        jQuery('#'+id).modal('hide');
    }

    function disabledPetType()
    {
        

        if (jQuery('#forminator-module-'+ jQuery("#petInfoFormId").val()).length) {
            var pet_type = jQuery('#addEditPetInfo select[name="select-1"]').val();
             jQuery("<input>").attr({ 
                name: "select-1",
                type: "hidden", 
                value: pet_type 
            }).appendTo(jQuery('#forminator-module-'+ jQuery("#petInfoFormId").val())); 
            jQuery('#addEditPetInfo select[name="select-1"]').prop('disabled', true);


        }
    }

    function enabledPetType()
    {

        if (jQuery('#forminator-module-'+ jQuery("#petInfoFormId").val()).length) {
            jQuery('#addEditPetInfo select[name="select-1"]').prop('disabled', false);
        }
    }

    function getQueryParam(key) {
        const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(key);
    }

    function setPetBreedOptions()
    {
        let objPetTypeSelect = jQuery("#addEditPetInfo select[name='select-1']").val();
        let objPetBreedOption = jQuery("#addEditPetInfo select[name='select-2']");
        objPetBreedOption.empty(); // Removes all options from the <select>
        objPetBreedOption.append('<option value="">Select Pet Breeds</option>');
        jQuery.each(javascriptArrayPetBreedOptions[objPetTypeSelect], function (index, option) {
            var newOption = jQuery('<option>', {
                value: index,   // Option value
                text: option,   // Option text
                selected: (strSelectPetBreed == index)?true:false             // Make it selected
            });

            objPetBreedOption.append(newOption);

        });
    }

    jQuery(document).ready(function($) {

        var key = 'status';
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        var paramValue = getQueryParam(key);
        if (paramValue !== null && paramValue ===  'update') {
            disabledPetType();
            editHeaderPetInfo('Edit pet info');
            openModal('addEditPetInfo');
            setPetBreedOptions();
        }
        else if (paramValue !== null && paramValue ===  'delete') {
            jQuery('#deleteModal').modal('show');
        }

        jQuery('#confirmDelete').click(function(){

            jQuery.ajax({
                url: ajaxurl,
                type: 'post', 
                data: {
                    'key': jQuery("#petInfoKey").val(),
                    'pet_id': jQuery("#petId").val(),
                    'action':"delete_pet_info",
                },
                                       
                success:function(response) { 
                    jQuery('#deleteModal').modal('hide');
                    jQuery('#deleteSuccessModal').modal('show');

                    setTimeout(function(){
                        var paths = window.location.pathname.split("/");
                        var strExtraFolder = "";
                        /*if(paths[1] == "mrsam"){
                            strExtraFolder = "/mrsam"; // if extra folder then set here otherwise you set blank here
                        }*/
                            window.location.href = strExtraFolder+ "/my-account/pet-info";
                    }, 2000)
                    
                } 
            });
        });

        jQuery(document).ajaxComplete(function (event, xhr, settings) {
            let response = xhr.responseJSON;
            if (response && response.success) {
                if(response.data.form_id == intSinglePetInfoFormId)
                {
                    var paths = window.location.pathname.split("/");
                    var strExtraFolder = "";
                    /*if(paths[1] == "mrsam"){
                        strExtraFolder = "/mrsam"; // if extra folder then set here otherwise you set blank here
                    }*/
                    window.location.href = strExtraFolder+ "/my-account/pet-info";
                }
            }
        });

    });

    
</script>