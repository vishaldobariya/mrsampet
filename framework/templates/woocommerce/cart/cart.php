<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.9.0
 */

defined( 'ABSPATH' ) || die;

wp_enqueue_script( 'wolmart-sticky-lib' );

do_action( 'woocommerce_before_cart' );

?>

<div class="row gutter-lg">
	<div class="col-lg-8 pr-lg-4">
		<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
			<?php do_action( 'woocommerce_before_cart_table' ); ?>

			<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
				<thead>
					<tr>
						<th class="product-thumbnail"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
						<th class="product-name">&nbsp;</th>
						<th class="product-price"><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
						<th class="product-quantity"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
						<th class="product-subtotal"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php do_action( 'woocommerce_before_cart_contents' ); ?>

					<?php
					foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
						$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
						$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

						/**
						 * Filter the product name.
						 *
						 * @since 2.1.0
						 * @param string $product_name Name of the product in the cart.
						 * @param array $cart_item The product in the cart.
						 * @param string $cart_item_key Key for the product in the cart.
						 */
						$product_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );

						if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
							$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
							?>
							<tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

								<td class="product-thumbnail">
								<?php
								$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

								if ( ! $product_permalink ) {
									echo wolmart_strip_script_tags( $thumbnail ); // PHPCS: XSS ok.
								} else {
									printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // PHPCS: XSS ok.
								}
								echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									'woocommerce_cart_item_remove_link',
									sprintf(
										'<a href="%s" class="remove fas fa-times" aria-label="%s" data-product_id="%s" data-product_sku="%s"></a>',
										esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
										/* translators: %s is the product name */
										esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
										esc_attr( $product_id ),
										esc_attr( $_product->get_sku() )
									),
									$cart_item_key
								);
								?>
								</td>

								<td class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
								<div class="CkoutpdtNaQt">    
    						<?php
    								if ( ! $product_permalink ) {
    									echo wp_kses_post( $product_name . '&nbsp;' );
    								} else {
    									/**
    									 * This filter is documented above.
    									 *
    									 * @since 2.1.0
    									 */
    									echo wolmart_strip_script_tags( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
    								}
    						?>
    							</div>
    						<?php
                                /*starts CT 2025.04.05*/
                                $short_description = wc_get_product( $cart_item['product_id'] )->get_short_description();
                                $short_description = wp_strip_all_tags($short_description);
                            ?>    
                                <div class="CkoutPdtItemData-container d-none" id="product-<?php echo esc_attr($cart_item_key); ?>">
                                    <p class="product-short-description"><?php echo esc_html($short_description); ?></p>
							<?php
							    
							    do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );
                            
								// Meta data.
								echo wc_get_formatted_cart_item_data( $cart_item ); // PHPCS: XSS ok.
                            ?>
                                </div>
                                <a href="javascript:void(0)" class="btn-link btn-primary CkoutItemDetailsToggle-btn" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">+ Show Item Details</a>
                            <?php
                                /* ends CT 2025.04.05*/
                                
								// Backorder notification.
								if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
									echo wolmart_strip_script_tags( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
								}
								?>
								</td>

								<td class="product-price" data-title="<?php esc_attr_e( 'Price', 'woocommerce' ); ?>">
									<?php
										echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
									?>
								</td>

								<td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
								<?php
                                    if ($cart_item['data']->get_type() != 'booking'){ //CT 2024.04.07
        								if ( $_product->is_sold_individually() ) {
        									$min_quantity = 1;
        									$max_quantity = 1;
        								} else {
        									$min_quantity = 0;
        									$max_quantity = $_product->get_max_purchase_quantity();
        								}
        
        								$product_quantity = woocommerce_quantity_input(
        									array(
        										'input_name'   => "cart[{$cart_item_key}][qty]",
        										'input_value'  => $cart_item['quantity'],
        										'max_value'    => $max_quantity,
        										'min_value'    => $min_quantity,
        										'product_name' => $product_name,
        									),
        									$_product,
        									false
        								);
        
        								echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // PHPCS: XSS ok.
                                    }else{ //CT 2024.04.07
                                        
                                        echo ($cart_item['data']->qty);
                                        
                                        
                                    }	
								?>
								</td>

								<td class="product-subtotal" data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce' ); ?>">
									<?php
										echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
									?>
								</td>
							</tr>
							<?php
						}
					}
					?>

					<?php do_action( 'woocommerce_cart_contents' ); ?>

					<tr>
						<td colspan="6" class="actions pr-0 pt-4 pl-0 pb-0">
							<div class="cart-actions mb-8">
								<?php /* <a href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>" class="btn btn-dark btn-rounded btn-icon-left continue-shopping mb-4 <?php echo is_rtl() ? 'ml-auto' : 'mr-auto'; ?>"><?php echo is_rtl() ? '' : '<i class="w-icon-long-arrow-left"></i>'; ?><?php esc_html_e( 'Continue Shopping', 'wolmart' ); ?><?php echo is_rtl() ? '<i class="w-icon-long-arrow-right"></i>' : ''; ?></a>*/ ?>
								<a href="<?php echo custom_continue_shopping_redirect(); ?>" class="btn btn-dark btn-rounded btn-icon-left continue-shopping mb-4 <?php echo is_rtl() ? 'ml-auto' : 'mr-auto'; ?>"><?php echo is_rtl() ? '' : '<i class="w-icon-long-arrow-left"></i>'; ?><?php esc_html_e( 'Continue Shopping', 'wolmart' ); ?><?php echo is_rtl() ? '<i class="w-icon-long-arrow-right"></i>' : ''; ?></a>
								<?php if ( wolmart_get_option( 'cart_show_clear' ) ) : ?>
									<button type="submit" class="btn btn-rounded btn-outline btn-default btn-border-thin mb-4 ml-2 mr-2 clear-cart-button" name="clear_cart" value="<?php esc_attr_e( 'Clear cart', 'wolmart' ); ?>"><?php esc_html_e( 'Clear cart', 'wolmart' ); ?></button>
								<?php endif; ?>
								
								<button type="submit" class="btn btn-rounded btn-outline btn-default btn-border-thin wc-action-btn
								<?php
								echo is_rtl() ? ' mr-2' : '';
								echo wolmart_get_option( 'cart_auto_update' ) ? ' d-none' : '';
								?>
								 mb-4" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'wolmart' ); ?>"><?php esc_html_e( 'Update cart', 'wolmart' ); ?></button>
							</div>

									<?php if ( wc_coupons_enabled() ) { ?>
								<div id="cart_coupon_box" class="expanded mb-2" style="display: block;">
									<h5 class="text-uppercase font-weight-semi-bold ls-normal"><?php echo esc_html__( 'Coupon Discount', 'wolmart' ); ?></h5>
									<div class="form-row form-coupon">
										<input type="text" name="coupon_code" class="input-text form-control mb-4" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Enter coupon code here...', 'wolmart' ); ?>">
										<button type="submit" name="apply_coupon" class="btn btn-rounded btn-border-thin btn-outline btn-dark button" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>
										<?php do_action( 'woocommerce_cart_coupon' ); ?>
									</div>
								</div>
							<?php } ?>
									<?php do_action( 'woocommerce_cart_actions' ); ?>

									<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
						</td>
					</tr>

					<?php do_action( 'woocommerce_after_cart_contents' ); ?>
				</tbody>
			</table>
			<?php do_action( 'woocommerce_after_cart_table' ); ?>
		</form>
	</div>
	<div class="col-lg-4">
		<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

		<div class="cart-collaterals sticky-sidebar">
			<?php
			/**
			 * Cart collaterals hook.
			 *
			 * @removed woocommerce_cross_sell_display
			 * @hooked woocommerce_cart_totals - 10
			 */
			do_action( 'woocommerce_cart_collaterals' );
			?>
		</div>
	</div>
</div>

									<?php
									/**
									 * After Cart Action
									 *
									 * @hooked woocommerce_cross_sell_display
									 */
									do_action( 'woocommerce_after_cart' );
									?>