jQuery(document).ready(function (jQuery) {

    let formId = '#forminator-module-1';
    let mobileNumber = "";
    let name = "";
    setTimeout(function(){
        let  formIdNumber = jQuery("#main .forminator-custom-form:first").data('form-id');
        formId = '#forminator-module-'+formIdNumber; // Replace with your Forminator form ID.
        jQuery(formId +" .forminator-button-submit").on('click', function (event) {
            event.preventDefault();
            let inputerror = checkValidation();
            if(inputerror)
            {
                return;
            }
            jQuery("#otpModal").trigger('click');
            mobileNumber = jQuery("input[name='phone-1']").val();
            mobileNumber = mobileNumber.replace(/\s/g, '');
            name = jQuery("input[name='name-1']").val()+ " "+jQuery("input[name='name-3']").val();
            email = jQuery("input[name='email-1']").val();
            sendOtp(forminatorAjax.ajaxUrl, name, mobileNumber, false, email);
            setTimeout(function(){
                resendOtpJs(forminatorAjax.ajaxUrl, name, mobileNumber, email);
                veryfyOTP(forminatorAjax.ajaxUrl, name, mobileNumber, formId);
            },500);
           
        });

    },1000);
});

function resendOtpJs(forminatorAjaxUrl, name, mobileNumber, email)
{
    let errorMessage = jQuery("#myOtpForm .verify-otp-msg");
    jQuery("#myOtpForm #resend-otp-button").on('click', function(){
        errorMessage.html('');
        sendOtp(forminatorAjax.ajaxUrl, name, mobileNumber, true, email);
    });
}

function veryfyOTP(forminatorAjaxajaxUrl, name, mobileNumber, formId)
{
    // OTP Verification
        jQuery('#myOtpForm #verifyOtp').on('click', function (event) {
            event.preventDefault(); // Prevent default form submission.
            const otp = jQuery('#myOtpForm  #form-field-otpinput').val();
            let errorMessage = jQuery("#myOtpForm .verify-otp-msg");
                errorMessage.html('');
            if(otp == null)
            {
                errorMessage.html("Please enter OTP first");
                return;
            }

            jQuery.ajax({
                url: forminatorAjaxajaxUrl,
                type: 'POST',
                data: {
                    action: 'verify_otp',
                    otp: otp,
                    mobile: mobileNumber,
                    name: name
                },
                success: function (response) {
                    if (response.success) {
                        //closeModal("verifyOtpModal");
                        jQuery(formId).off('submit').submit(); // Submit the form after OTP verification.
                        setTimeout(function(){
                            jQuery("#myOtpForm").closest(".dialog-widget-content").find(".dialog-close-button").trigger('click');
                        },500);
                    } else {
                        errorMessage.html("Invalid otp. Please try again");
                    }
                },
            });
        });
}

function closeModal(id) {
    jQuery('#'+id).modal('hide');
}

function openModal(id) {
    jQuery('#'+id).modal({backdrop: 'static', keyboard: false});
    jQuery('#'+id).modal('show');
}

function sendOtp(sendOtpUrl, name, mobileNumber, resend = false, email) {

    /*if(resend == false)
    {
        openModal('verifyOtpModal');
        jQuery("#verifyOtpModal #otp-input").val('');

    }*/
     // Send OTP via Ajax
    jQuery.ajax({
        url: sendOtpUrl,
        type: 'POST',
        data: {
            action: 'send_otp',
            mobile: mobileNumber,
            name: name,
            email: email,
        },
        success: function (response) {
            if (response.success) {
                if(resend == false)
                {
                    let errorMessage = jQuery("#myOtpForm .verify-otp-msg");
                    errorMessage.html("Enter OTP below that is sent to your mobile number.");
                }
                if(resend)
                {
                     let errorMessage = jQuery("#myOtpForm .verify-otp-msg");
                     errorMessage.html("OTP has been resent to your mobile");
                }
            } else {
                alert('OTP Sending Failed! Try Resend OTP.');
            }
        },
    });
}

function checkValidation(){
    let inputerror = false;
    jQuery(".forminator-error-message").remove();
    let tagNames = ['name-1', 'name-3', 'email-1', 'phone-1', 'password-1'];
    let tagNameInput = ['input', 'input', 'input', 'input', 'input'];
    let tagNameInputError = [
                                'First Name is required.',
                                'Last Name is required.',
                                'This field is required. Please enter email.',
                                'Mobile no is required.',
                                'Your password is required.',

    ];

    let tags = [];
    for(var i=0; i<tagNames.length; i++) {
        tags[i] = tagNames[i];
    }
    let index = 0;
     jQuery.each(tags, function (key, val) {
        let tag = jQuery(tagNameInput[key]+"[name='"+val+"']");
        if(tag.length > 0)
        {
            if(tag.val() == '')
            {
                if(val == 'phone-1')
                {
                    tag.closest(".forminator-iti-input").after('<span class="forminator-error-message d-block">'+tagNameInputError[index]+'</span>');
                }
                else
                {
                    tag.closest(".forminator-input--wrap").after('<span class="forminator-error-message d-block">'+tagNameInputError[index]+'</span>');
                }
                inputerror = true;
            }
            else if(tag.val().length < 10 && val == 'phone-1')
            {
                tag.closest(".forminator-iti-input").after('<span class="forminator-error-message d-block">Please input a valid phone number.</span>');
                inputerror = true;
            }
            else if(tag.val().length <= 7 && val == 'password-1')
            {
                tag.closest(".forminator-input--wrap").after('<span class="forminator-error-message d-block">Your password doesn\'t meet the minimum strength requirement of 8 characters.</span>');
                inputerror = true;
            }
            else if(val == 'email-1')
            {
                if( !validateEmail(tag.val()))
                {
                    tag.closest(".forminator-input--wrap").after('<span class="forminator-error-message d-block">This is not a valid email.</span>');
                    inputerror = true;
                }
            }
        }

        index++;
     });
     return inputerror;
}

 function validateEmail($email) {
  var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
  return emailReg.test( $email );
}