<?php

add_action( 'template_redirect', 'detect_order_received_page' );

function detect_order_received_page() {
    if ( is_wc_endpoint_url( 'order-received' ) ) {
        if (!isset($_SESSION['code_ran'])) {
            $order_id = absint( get_query_var( 'order-received' ) );
            if ( $order_id > 0 ) {
                $order = wc_get_order( $order_id );

                if ( $order ) {
                    $_SESSION['code_ran'] = ["One time sms went"];
                    setbookingConfirmSMS($order->get_id());
                }
            }
        }
    }
}

// You can call function while booking confirm
// while directBookingId is true then considered here order_id as booking id
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