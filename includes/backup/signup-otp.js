jQuery(document).ready(function (jQuery) {

    let formId = '#forminator-module-1';
    let mobileNumber = "";
    let name = "";
    setTimeout(function(){
        let  formIdNumber = jQuery("#main .forminator-custom-form:first").data('form-id');
        formId = '#forminator-module-'+formIdNumber; // Replace with your Forminator form ID.
        jQuery(formId +" .forminator-button-submit").on('click', function (event) {
             event.preventDefault();
            jQuery("#otpModal").trigger('click');
            mobileNumber = jQuery("input[name='phone-1']").val();
            name = jQuery("input[name='name-1']").val()+ " "+jQuery("input[name='name-3']").val();
            veryfyOTP(forminatorAjax.ajaxUrl,mobileNumber, name);
            sendOtp(forminatorAjax.ajaxUrl, name, mobileNumber);
           
        });

        

         let errorMessage = jQuery("#myOtpForm .verify-otp-msg");
        jQuery("#resend-otp-button").on('click', function(){
            errorMessage.html('');
            sendOtp(forminatorAjax.ajaxUrl, name, mobileNumber, true);
        });
    
    },1000);
});

function veryfyOTP(forminatorAjaxajaxUrl, mobileNumber, name)
{
    // OTP Verification
        jQuery('#myOtpForm #verifyOtp').on('click', function (event) {
            event.preventDefault(); // Prevent default form submission.
            alert("here");
            const otp = jQuery('#otp-input').val();
            let errorMessage = jQuery("#myOtpForm .verify-otp-msg");
                errorMessage.html('');
            if(otp == null)
            {
                errorMessage.html("Please enter otp first");
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

function sendOtp(sendOtpUrl, name, mobileNumber, resend = false) {

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
        },
        success: function (response) {
            if (response.success) {
                if(resend == false)
                {
                    let errorMessage = jQuery("#myOtpForm .verify-otp-msg");
                    errorMessage.html("Send otp successfully in your mobile");
                }
                if(resend)
                {
                     let errorMessage = jQuery("#myOtpForm .verify-otp-msg");
                     errorMessage.html("Resend otp successfully");
                }
            } else {
                alert('Failed to send OTP. Please try again.');
            }
        },
    });
}