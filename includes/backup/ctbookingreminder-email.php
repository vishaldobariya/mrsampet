<?php

/**
 * Custom WooCommerce Booking Reminder
 * Runs every 30 minutes
 */


/**
 * Add 30 minute cron schedule
 */
add_filter( 'cron_schedules', 'custom_booking_reminder_add_schedule' );

function custom_booking_reminder_add_schedule( $schedules ) {

    $schedules['thirty_minutes'] = array(
        'interval' => 1800,
        'display'  => __( 'Every 30 minutes' ),
    );

    return $schedules;
}


/**
 * Register cron event
 */
add_action( 'init', 'custom_booking_reminder_register_cron' );

function custom_booking_reminder_register_cron() {

    if ( ! wp_next_scheduled( 'custom_booking_reminder_worker' ) ) {

        wp_schedule_event(
            time(),
            'thirty_minutes',
            'custom_booking_reminder_worker'
        );

    }
}


/**
 * Cron worker
 */
add_action(
    'custom_booking_reminder_worker',
    'custom_booking_reminder_process'
);


function custom_booking_reminder_process() {


    $current_time = current_time( 'timestamp' );


    /**
     * 30 minute cron means we need a window.
     * Target: send reminder 2 hours before booking start.
     *
     * Example:
     * Cron runs at 10:00
     * It will pick bookings between 11:45 - 12:15
     */

    $from = $current_time + (105 * MINUTE_IN_SECONDS);
    $to   = $current_time + (135 * MINUTE_IN_SECONDS);



    $bookings = get_posts(
        array(
            'post_type'      => 'wc_booking',
            'post_status'    => array(
                'confirmed',
                'paid'
            ),
            'posts_per_page' => -1,

            'meta_query' => array(

                // Booking start time
                array(
                    'key'     => '_booking_start',
                    'value'   => array( $from, $to ),
                    'compare' => 'BETWEEN',
                    'type'    => 'NUMERIC',
                ),

                // Only send once
                array(
                    'key'     => '_booking_reminder_sent',
                    'compare' => 'NOT EXISTS',
                ),

            ),
        )
    );


    if ( empty( $bookings ) ) {
        return;
    }



    foreach ( $bookings as $booking_post ) {


        $booking_id = $booking_post->ID;


        $booking = get_wc_booking( $booking_id );


        if ( ! $booking ) {
            continue;
        }


        // Safety check
        if ( ! $booking->is_active() ) {
            continue;
        }



        /**
         * Trigger WooCommerce default Booking Reminder email
         */
        $mailer = WC()->mailer();


        if (
            isset( $mailer->emails['WC_Email_Booking_Reminder'] )
        ) {

            $email = $mailer->emails['WC_Email_Booking_Reminder'];

            $email->trigger( $booking_id );


            /**
             * Mark as sent
             */
            update_post_meta(
                $booking_id,
                '_booking_reminder_sent',
                current_time( 'timestamp' )
            );

        }

    }

}
?>