<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>

<?php
    /**
     * woocommerce_before_single_product hook.
     *
     * @hooked wc_print_notices - 10
     */
     do_action( 'woocommerce_before_single_product' );

     if ( post_password_required() ) {
         echo get_the_password_form();
         return;
     }
?>

<div itemscope itemtype="<?php echo woocommerce_get_product_schema(); ?>" id="product-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="row">
        <div class="col-md-5">
            <?php
                /**
                * woocommerce_before_single_product_summary hook.
                *
                * @hooked woocommerce_show_product_sale_flash - 10
                * @hooked woocommerce_show_product_images - 20
                */
                do_action( 'woocommerce_before_single_product_summary' );
            ?>
        </div>
        <div class="col-md-7">
            <div class="row">
                <div class="col-md-12">
                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#home">Buy</a></li>
                        <li><a data-toggle="tab" href="#additional-information">Description</a></li>
                        <li><a data-toggle="tab" href="#delivery">Shipping & Returns</a></li>
                    </ul>
                    <div class="tab-content">
                        <div id="home" class="tab-pane fade in active show">
                            <div class="row">
                                <div class="col-md-12">
                                    <?php
                                        /**
                                        * woocommerce_single_product_summary hook.
                                        *
                                        * @hooked woocommerce_template_single_title - 5
                                        * @hooked woocommerce_template_single_rating - 10
                                        * @hooked woocommerce_template_single_price - 10
                                        * @hooked woocommerce_template_single_excerpt - 20
                                        * @hooked woocommerce_template_single_add_to_cart - 30
                                        * @hooked woocommerce_template_single_meta - 40
                                        * @hooked woocommerce_template_single_sharing - 50
                                        */
                                        do_action('woocommerce_single_product_summary');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div id="description" class="tab-pane fade">
                            <h2>Buy</h2>
                            <?php echo apply_filters( 'woocommerce_short_description', $post->post_excerpt ) ?>
                        </div>
                        <div id="delivery" class="tab-pane fade">
                            <div class="row">
                                <div class="col-md-12">
                                    <h2>Delivery</h2>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <p>
                                        <?php
                                            $args = [
                                                'category_name' => 'delivery'
                                            ];

                                            $myposts = get_posts($args);
                                            foreach ($myposts as $post) : setup_postdata($post); ?>
                                                <?php echo the_content(); ?>
                                            <?php endforeach; 
                                            wp_reset_postdata();
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div id="returns" class="tab-pane fade">
                            <h2>Returns</h2>
                            <div class="row">
                                <div class="col-md-12">
                                    <p>
                                        <?php
                                            $args = [
                                                'category_name' => 'returns'
                                            ];

                                            $myposts = get_posts($args);
                                            foreach ($myposts as $post) : setup_postdata($post); ?>
                                                <?php echo the_content(); ?>
                                            <?php endforeach; 
                                            wp_reset_postdata();
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div id="additional-information" class="tab-pane fade">
                            <div class="row">
                                <div class="col-md-12">
                                    <h2>Description</h2>
                                    <?php echo apply_filters( 'woocommerce_short_description', $post->post_excerpt ) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <?php $tabs = apply_filters('woocommerce_product_tabs', []); ?>
                                    <?php foreach ( $tabs as $key => $tab ) : ?>
                                        <?php 
                                            if ($key == 'additional_information') {
                                                echo '
                                                    <div>
                                                        '.call_user_func($tab['callback'], $key, $tab ).'
                                                    </div>
                                                ';
                                            }
                                        ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div id="reviews" class="tab-pane fade">
                            <?php $tabs = apply_filters('woocommerce_product_tabs', []); ?>
                                <?php foreach ( $tabs as $key => $tab ) : ?>
                                    <?php 
                                        if ($key == 'reviews') {
                                            echo '
                                                <div>
                                                    '.call_user_func($tab['callback'], $key, $tab ).'
                                                </div>
                                            ';
                                        }
                                    ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <div>
    </div>
</div>

    <?php
        /**
         * woocommerce_after_single_product_summary hook.
         *
         * @hooked woocommerce_output_product_data_tabs - 10
         * @hooked woocommerce_upsell_display - 15
         * @hooked woocommerce_output_related_products - 20
         */
        do_action( 'woocommerce_after_single_product_summary' );
    ?>

    <meta itemprop="url" content="<?php the_permalink(); ?>" />

</div><!-- #product-<?php the_ID(); ?> -->

<?php do_action( 'woocommerce_after_single_product' ); ?>
