<?php


function my_acf_custom_post_publish_hook($post_id, $post, $update) {

    // Check if it's an ACF custom post type
    if ($post->post_type === 'send-sms' && $post->post_status === 'publish' && $post->post_date == $post->post_modified) {

        $post = get_post($post_id);
        $strTemplateName = get_field('ctmsg_templateid', $post->ID);
        $intBookingId = get_field('ctmsg_bookingid', $post->ID);

    	if($strTemplateName == '67e53411d6fc0534f26ee112')
    	{
    		handlingsmsfile('bookingconfirmsms', $intBookingId, $post->ID);
    	}
    	else if($strTemplateName == '67e53336d6fc0504f662a3d2')
    	{
    		handlingsmsfile('cancelbookingsms', $intBookingId, $post->ID);
    	}
    }
}
add_action('save_post', 'my_acf_custom_post_publish_hook', 200, 3);


function handlingsmsfile($filename, $bookingId, $postID)
{	
    global $smsSettingsData;
    
	$getSmsSettingsOption = getSmsSettingsData();
	
    if($getSmsSettingsOption[$smsSettingsData['ctmsg_settings_template_bookconfirm_on_off']] != 'On')
    {
        include_once('bookingconfirmsms.php');
    }
    if($getSmsSettingsOption[$smsSettingsData['ctmsg_settings_template_aptcancel_on_off']] != 'On')
    {
        include_once('cancelbookingsms.php');
    }

    if($filename == 'bookingconfirmsms')
    {
        setbookingConfirmSMS($bookingId, true, $postID);
    }
    else if($filename == 'cancelbookingsms')
    {
        setCancelbookingSMS($bookingId, true, $postID);
    }
}

function SMSSuccessErrorMsg($smsSuccessFailureCode)
{
    switch ($smsSuccessFailureCode) {
        case 7001:
            return "SMS Success";
            break;
        case 8001:
            return "SMS Failed";
            break;
        case 8002:
            return "SMS Failed - Invalid mobile number";
            break;
        case 8003:
            return "SMS Failed - Invalid booking id";
            break;
        default:
            // code...
            break;
    }

}