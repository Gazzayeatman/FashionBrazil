<?php
/**
 * Variable product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/variable.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.5.0
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $product;

$attribute_keys = array_keys( $attributes );

do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<form class="variations_form cart" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->id ); ?>" data-product_variations="<?php echo htmlspecialchars( json_encode( $available_variations ) ) ?>">
    <?php do_action( 'woocommerce_before_variations_form' ); ?>

    <?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
        <p class="stock out-of-stock"><?php _e( 'This product is currently out of stock and unavailable.', 'woocommerce' ); ?></p>
    <?php else : ?>
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <table class="variations" cellspacing="0">
			                <tbody>
                                <?php foreach ( $attributes as $attribute_name => $options ) : ?>
                                    <tr>
                                        <td class="label"><label for="<?php echo sanitize_title( $attribute_name ); ?>"><?php echo wc_attribute_label( $attribute_name ); ?></label></td>
                                        <td class="value">
                                            <?php
                                                $selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ? wc_clean( urldecode( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ) : $product->get_variation_default_attribute( $attribute_name );
                                                wc_dropdown_variation_attribute_options( array( 'options' => $options, 'attribute' => $attribute_name, 'product' => $product, 'selected' => $selected ) );
                                                echo end( $attribute_keys ) === $attribute_name ? apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . __( 'Clear', 'woocommerce' ) . '</a>' ) : '';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach;?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 text-left">
                        <a href="#size-chart-modal" class="btn color-inverse" data-toggle="modal">
                            Size Chart
                        </a>
                    </div>
                </div>
                <!-- Size Chart Modal -->
                <div class="modal" id="size-chart-modal">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Size Chart</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <?php 
                                    $brand = get_term_by('name', $product->get_attribute('pa_brand'), 'pa_brand');
                                    $sizeChart = get_field('size_chart', $brand->taxonomy . '_' . $brand->term_id);
                                    echo '<img src="'.$sizeChart['url'].'" />';
                                ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn woocommerce button" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

        <div class="single_variation_wrap">
            <?php
                /**
                 * woocommerce_before_single_variation Hook.
                 */
                do_action( 'woocommerce_before_single_variation' );

                /**
                 * woocommerce_single_variation hook. Used to output the cart button and placeholder for variation data.
                 * @since 2.4.0
                 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
                 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
                 */
                do_action( 'woocommerce_single_variation' );

                /**
                 * woocommerce_after_single_variation Hook.
                 */
                do_action( 'woocommerce_after_single_variation' );
            ?>
        </div>

        <?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
    <?php endif; ?>

    <?php do_action( 'woocommerce_after_variations_form' ); ?>
    <div class="share">
        <h4>Share this item</h4>
        <ul class="share-social-icons">
            <li><a target="_new" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo SYSTEM_URL; ?><?php echo $_SERVER['REQUEST_URI']; ?>&t=<?php echo get_the_title(); ?>"><i class="fa fa-facebook-square" aria-hidden="true"></i></a></li>
            <li><a target="_new" href="https://plus.google.com/share?url=<?php echo SYSTEM_URL; ?><?php echo $_SERVER['REQUEST_URI']; ?>"><i class="fa fa-google-plus-square" aria-hidden="true"></i></a></li>
            <li><a target="_new" href="https://twitter.com/intent/tweet?text=<?php echo get_the_title(); ?>&url=<?php echo SYSTEM_URL; ?><?php echo $_SERVER['REQUEST_URI']; ?>"><i class="fa fa-twitter-square" aria-hidden="true"></i></a></li>
        </ul>
    </div>
</form>

<?php
do_action( 'woocommerce_after_add_to_cart_form' );
