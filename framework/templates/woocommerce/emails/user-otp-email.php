<?php
defined( 'ABSPATH' ) || exit;
?>
<p><?php echo esc_html__( 'Hello! Your one-time password is:', 'woocommerce' ); ?></p>
<p><strong style="font-size: 24px;"><?php echo esc_html( $otp ); ?></strong></p>
