<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.6.1
 */

if (!defined('ABSPATH')) {
    exit;
}

global $product;

if (empty($product) || !$product->is_visible()) {
    return;
}

$pageType = $wp->request;

if (!$pageType) {
    echo '<div class="col-md-6 col-sm-6 col-lg-3 col-xs-12">';
        echo '<div class="product-cell">';
            global $product;
            $link = apply_filters('woocommerce_loop_product_link', get_the_permalink(), $product);
            echo '<a href="'.esc_url($link).'" target="_blank" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">';
                echo '<div class="image">';
                    if ($product->is_on_sale()) {
                        echo apply_filters('woocommerce_sale_flash', '<span class="onsale">' . __('Sale!', 'woocommerce') . '</span>', $post, $product);
                    }
                    echo woocommerce_get_product_thumbnail();
                echo '</div>';
                echo '<div class="details">';
                    echo '<div class="title">';
                        echo '<h4 class="woocommerce-loop-product__title">'.get_the_title().'</h4>';
                    echo '</div>';
                    echo '<div class="price">';
                        wc_get_template('loop/price.php');
                    echo '</div>';
                echo '</div>';
            echo '</a>';
        echo '</div>';
    echo '</div>';
} else {
    echo '<div class="col-md-6 col-sm-6 col-lg-4 col-xs-12">';
        echo '<div class="product-cell">';
            global $product;
            $link = apply_filters('woocommerce_loop_product_link', get_the_permalink(), $product);
            echo '<a href="'.esc_url($link).'" target="_blank" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">';
                echo '<div class="image">';
                    if ($product->is_on_sale()) {
                        echo apply_filters('woocommerce_sale_flash', '<span class="onsale">' . __('Sale!', 'woocommerce') . '</span>', $post, $product);
                    }
                    echo woocommerce_get_product_thumbnail();
                echo '</div>';
                echo '<div class="details">';
                    echo '<div class="title">';
                        echo '<h4 class="woocommerce-loop-product__title">'.get_the_title().'</h4>';
                    echo '</div>';
                    echo '<div class="price">';
                        wc_get_template('loop/price.php');
                    echo '</div>';
                echo '</div>';
            echo '</a>';
        echo '</div>';
    echo '</div>';
}
?>
