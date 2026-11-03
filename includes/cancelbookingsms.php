<?php

    function setCancelbookingSMS($order_id, $directBookingId = false, $postId = 0) // while directBookingId is true then considered here order_id as booking id;
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
        foreach ($arrayBookingIds as $key => $intBookingId) {
            $booking = get_wc_booking($intBookingId);
            if(!empty($booking))
            {
                $objCustomerId = $booking->get_customer_id();
                $mobile = str_replace(' ', '', get_user_meta($objCustomerId, 'billing_phone', true));
                // $mobile = "9998050924";
                if(strlen($mobile) == 10)
                {
                    $recipients[] = [
                        'mobiles' => "91".$mobile,
                        'ORDERID' => $intBookingId,
                    ];
                    $postData = ['template_id' => $smsSettingsVal['ctmsg_settings_template_aptcancel'], "recipients" => $recipients];
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

