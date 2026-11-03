    <div>
        <div class="icon-box icon-box-side woocommerce-MyAccount-garrage justify-content-start mb-4">
            <span class="icon-box-icon text-grey mr-2">
                <i class="fas fa-pet"></i>
            </span>
            <div class="icon-box-content">
                <h4 class="icon-box-title text-normal">Pets Details</h4>
            </div>
        </div>

        <form method="get" action="">
            <input type="hidden" name="page" value="petinfo-admin" />
            <label for="filter_key">Filter: </label>
            <select name="user" id="user">
                <option value="">Select User Name</option>
                <?php
                    foreach ($arrayUserInfos as $intUserId => $arrayUserName) {
                        $selected = "";
                        if(isset($_GET['user'])){ 
                            if($_GET['user'] == $intUserId){
                             $selected =  'selected';
                            } 
                        }

                ?>
                    <option value="<?php echo $intUserId ?>" <?php echo $selected; ?> ><?php echo $arrayUserName ?></option>        
                <?php    } ?>

            </select>

            <select name="pet_type" id="pet_type">
                <option value="">Select Pet Type</option>
                <?php
                    foreach ($arrayPettypeOptions as $strPettypeKey => $arrayPettypeOption) {
                        $selected = "";
                        if(isset($_GET['pet_type'])){ 
                            if($_GET['pet_type'] == $strPettypeKey){
                             $selected =  'selected';
                            } 
                        }
                ?>
                    <option value="<?php echo $strPettypeKey ?>" <?php echo $selected; ?> ><?php echo $arrayPettypeOption ?></option>        
                <?php    } ?>

            </select>
            <?php
                $strDisplayNone = 'hidden';
                if(isset($_GET['pet_type']) && $_GET['pet_type'] != '')
                {
                    if(array_key_exists($_GET['pet_type'],$arrayPetBreedOptions) && $arrayPetBreedOptions[$_GET['pet_type']])
                    {
                        $strDisplayNone = "";
                    }
                }
                else if(isset($_GET['pet_breed']) && $_GET['pet_breed'] != null)
                {
                    $strDisplayNone = "";
                }
                
            ?>
            <select class="<?php echo $strDisplayNone;  ?>" name="pet_breed" id="pet_breed">
                <option value="">Select Pet Breed</option>
                <?php
                    foreach ($arrayPetBreedOptionsDirectFromUrlGet as $strPetbreedKey => $arrayPetBreedOption) {
                         $selected = "";
                        if(isset($_GET['pet_breed'])){ 
                            if($_GET['pet_breed'] == $strPetbreedKey){
                             $selected =  'selected';
                            } 
                        }
                ?>
                    <option value="<?php echo $strPetbreedKey ?>" <?php echo $selected; ?> ><?php echo $arrayPetBreedOption ?></option>        
                <?php    } ?>

            </select>

            <input type="submit" value="Filter" class="button button-primary" />
        </form>

            <div class="mt-5">
                <table class="table" id="petInfoTable">
                    <thead>
                        <th>No</th>
                        <th>User Name</th>
                        <th>Pet Type</th>
                        <th>Pet Breed</th>
                        <th>Gender</th>
                        <th>Name</th>
                        <th>Pet DOB</th>
                        <th>Pet Vaccinated</th>
                        <th>Action</th>
                    </thead>
                    <tbody>
                        <?php
                        if(!empty($pet_info))
                        {   
                            $arrayRepeaterValues = ['pet_type','pet_breed','pet_gender','pet_name', 'pet_dob', 'pet_vaccination'];
                            $intCountNo = 1;
                            foreach ($pet_info as $pet_index => $pet) {
                                $editQueryString = '/wp-admin/user-edit.php?user_id='.$pet['user_id'].'&wp_http_referer=%2Fwp-admin%2Fadmin.php?page=petinfo-admin';
                            echo '<tr>';
                                echo  '<td>'.$intCountNo++ . '</td>';
                                echo  '<td>'.$pet['user_name'] . '</td>';
                                foreach ($arrayRepeaterValues as $key => $repeaterSubKey) {
                                    echo '<td>'.$pet[$repeaterSubKey].'</td>';
                                }
                                    echo '<td><a href="'.$editQueryString.'">Edit</td>';

                            echo '</tr>';
                            }
                        }
                        else
                        {
                           /* echo '<tr style="text-align: center"><td>No Record Found</td></tr>';*/
                        }
                       
                        ?>
                    </tbody>
                </table>
            </div>
            
    </div>




<script type="text/javascript">
        var javascriptArrayPetBreedOptions = JSON.parse('<?php echo $jsonArrayPetOptions; ?>');
        jQuery(document).ready(function(){
          
           jQuery("#pet_type").on('change', function(){
                let objPetSelect = jQuery(this).val();
                if(typeof javascriptArrayPetBreedOptions[objPetSelect] !== 'undefined')
                {
                    if(jQuery("#pet_breed").hasClass('hidden'))
                    {
                        jQuery("#pet_breed").removeClass('hidden');
                    }
                }
                else
                {
                    if(!jQuery("#pet_breed").hasClass('hidden'))
                    {
                        jQuery("#pet_breed").addClass('hidden');
                    }
                }

                let objPetBreedOption = jQuery("#pet_breed");
                objPetBreedOption.empty(); // Removes all options from the <select>
                objPetBreedOption.append('<option value="">Select Pet Breeds</option>');
                jQuery.each(javascriptArrayPetBreedOptions[objPetSelect], function (index, option) {
                    objPetBreedOption.append('<option value="' + index + '">' + option + '</option>');
                });

           });

        });

        
      </script>