<?php

global $wpdb;

 $smsSettingsData = [
        'ctmsg_settings_template' => 'ctmsg_settings_smstemplateids', // for sending otp template id
        'ctmsg_settings_otp_on_off' => 'ctmsg_settings_signupotp', // for sending otp on off
        'ctmsg_settings_template_otp' => 'ctmsg_settings_template_otp', // for sending otp template id
        'ctmsg_settings_template_bookconfirm' => 'ctmsg_settings_template_bookconfirm', // for sending  template booking confirm
        'ctmsg_settings_template_bookconfirm_on_off' => 'ctmsg_settings_bkgconfirm', // for sending  template booking confirm on off
        'ctmsg_settings_template_aptcancel' => 'ctmsg_settings_template_aptcancel', // for sending  template cancel appointment
        'ctmsg_settings_template_aptcancel_on_off' => 'ctmsg_settings_bkgcancel', // for sending  template cancel appointment on off
        'ctmsg_settings_template_booking_reminder_on_off' => 'ctmsg_settings_bkgreminder', // for sending  template booking reminder appointment on off
        'ctmsg_settings_template_booking_reminder_time' => 'ctmsg_settings_bkgreminderfreq', // for sending  template booking reminder appointment on off
    ];

    $getSmsSettingsOption = getSmsSettingsData();

$intMinute = $getSmsSettingsOption[$smsSettingsData['ctmsg_settings_template_booking_reminder_time']];

if($intMinute > 0)
{
    error_log("cron file work");
	$objBookings = get_dynamic_booking_reminders( $intMinute );

	/*error_log(print_r($objBookings, true));*/
	foreach ( $objBookings as $objBooking ) {
		error_log(print_r($objBooking->ID, true));
		setbookingConfirmSMS($objBooking->ID, true);
		update_post_meta( $objBooking->ID, '_reminder_sent_' . $intMinute, current_time( 'mysql' ) );
	}
}


function get_dynamic_booking_reminders( $offset_minutes = 60 ) {
    global $wpdb;

    $posts_table = $wpdb->prefix . 'posts';    // sm_posts
    $meta_table  = $wpdb->prefix . 'postmeta'; // sm_postmeta

    // +/- 5-minute buffer window
    $buffer_seconds   = 5 * 60;
    $target_timestamp = current_time( 'timestamp' ) + ( $offset_minutes * 60 );

    // Format strictly as YmdHis (e.g. 20260820170000)
    $start_time = date( 'YmdHis', $target_timestamp - $buffer_seconds );
    $end_time   = date( 'YmdHis', $target_timestamp + $buffer_seconds );

    /*error_log($start_time);
    error_log($end_time);*/

    $sql = $wpdb->prepare(
        "SELECT p.ID, p.post_status, m_start.meta_value AS booking_start
         FROM {$posts_table} AS p
         INNER JOIN {$meta_table} AS m_start 
            ON (p.ID = m_start.post_id AND m_start.meta_key = '_booking_start')
         LEFT JOIN {$meta_table} AS m_sent 
            ON (p.ID = m_sent.post_id AND m_sent.meta_key = %s)
         WHERE p.post_type = 'wc_booking'
           AND p.post_status IN ('paid', 'confirmed')
           AND m_start.meta_value BETWEEN %s AND %s
           AND m_sent.meta_id IS NULL",
        '_reminder_sent_' . intval( $offset_minutes ),
        $start_time,
        $end_time
    );

    return $wpdb->get_results( $sql );
}

if (!function_exists( 'getSmsSettingsData' ) ) {
    function getSmsSettingsData() {
        global $smsSettingsData;
        $smsSettings = [];
        $tempCheckarrayOrString = get_field($smsSettingsData['ctmsg_settings_template'], 'option');
        if(gettype($tempCheckarrayOrString) == "array")
        {
            $smsSettings = $tempCheckarrayOrString;
            $smsSettings[$smsSettingsData['ctmsg_settings_otp_on_off']] = get_field($smsSettingsData['ctmsg_settings_otp_on_off'], 'option');
            $smsSettings[$smsSettingsData['ctmsg_settings_template_bookconfirm_on_off']] = get_field($smsSettingsData['ctmsg_settings_template_bookconfirm_on_off'], 'option');
            $smsSettings[$smsSettingsData['ctmsg_settings_template_aptcancel_on_off']] = get_field($smsSettingsData['ctmsg_settings_template_aptcancel_on_off'], 'option');
            $smsSettings[$smsSettingsData['ctmsg_settings_template_booking_reminder_on_off']] = get_field($smsSettingsData['ctmsg_settings_template_booking_reminder_on_off'], 'option');
            $smsSettings[$smsSettingsData['ctmsg_settings_template_booking_reminder_time']] = get_field($smsSettingsData['ctmsg_settings_template_booking_reminder_time'], 'option');

            return $smsSettings;
        }
        return $smsSettings;
    }
}

if (!function_exists( 'setbookingConfirmSMS' ) ) {
    function setbookingConfirmSMS($order_id,  $directBookingId = false, $postId = 0)
    {
        $arrayBookingIds = $recipients = [];
        $smsSettingsVal = getSmsSettingsData();
        if($directBookingId)
        {
            $arrayBookingIds = [$order_id]; // while directBookingId is true then considered here order_id as booking id;
        }
        else
        {
            $booking_data = new WC_Booking_Data_Store();
            $arrayBookingIds  = $booking_data->get_booking_ids_from_order_id( $order_id );
        }
        /*error_log(print_r($arrayBookingIds, true));*/
        foreach ($arrayBookingIds as $key => $intBookingId) {
            $booking = get_wc_booking($intBookingId);
            if(!empty($booking))
            {
                $objCustomerId = $booking->get_customer_id();
                $objOrderItemId = $booking->get_order_item_id();
                $item = new WC_Order_Item_Product($objOrderItemId);
                $storeId = wc_get_order_item_meta( $item->get_id(), '_vendor_id');
                $arrayDateTime = explode(",", $booking->get_start_date( null, null,  $booking));
                $mobile = str_replace(' ', '', get_user_meta($objCustomerId, 'billing_phone', true));
                //$mobile = "9998050924";
                if(strlen($mobile) == 10)
                {
                    $recipients[] = [
                        'mobiles' => "91".$mobile,
                        'NAME' => get_user_meta( $objCustomerId, 'first_name', true ),
                        'STORENAME'  => get_user_meta($storeId, 'wcfmmp_store_name', true),
                        'STORELOC'  => get_user_meta($storeId, '_wcfm_city', true),
                        'DATE'  =>  date('d-m-Y', strtotime($arrayDateTime[0])),
                        'TIME'  => $arrayDateTime[1],
                    ];
                    $postData = ['template_id' => $smsSettingsVal['ctmsg_settings_template_bookconfirm'], "recipients" => $recipients];
                    /*error_log(print_r($postData, true));*/
                    if(!empty($recipients))
                    {
                        sendsms($postData);
                        if($postId > 0)
                        {
                            $strSMSSuccessMsg = SMSSuccessErrorMsg(7001);
                            update_field('ctmsg_smssuccess',$strSMSSuccessMsg,  $postId);
                        }
                    }
                    else
                    {
                        if($postId > 0)
                        {
                            $strSMSFailurMsg = SMSSuccessErrorMsg(8001);
                            update_field('ctmsg_smssuccess',$strSMSFailurMsg,  $postId);
                        }
                    }
                }
                else
                {
                    if($postId > 0)
                    {
                        $strSMSFailurMsg = SMSSuccessErrorMsg(8002);
                        update_field('ctmsg_smssuccess',$strSMSFailurMsg,  $postId);
                    }

                }

            }
            else
            {
                if($postId > 0)
                {
                    $strSMSFailurMsg = SMSSuccessErrorMsg(8003);
                    update_field('ctmsg_smssuccess',$strSMSFailurMsg,  $postId);
                }
            }
        }

    }
}

if (!function_exists( 'sendsms' ) ) {
    function sendsms($postData){

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://control.msg91.com/api/v5/flow',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => json_encode($postData),
          CURLOPT_HTTPHEADER => array(
            'authkey: 437497Aruq6BlwFDvg68087de1P1',
            'Content-Type: application/json',
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
}

if (!function_exists( 'SMSSuccessErrorMsg' ) ) {
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
}
?>