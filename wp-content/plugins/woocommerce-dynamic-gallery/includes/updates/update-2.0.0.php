<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Set Settings Default for new features
global $wc_dgallery_admin_init;
$wc_dgallery_admin_init->set_default_settings();

$bg_image_wrapper = get_option( WOO_DYNAMIC_GALLERY_PREFIX . 'bg_image_wrapper', '' );
update_option( WOO_DYNAMIC_GALLERY_PREFIX . 'main_bg_color', array( 'enable' => 1, 'color' => $bg_image_wrapper ) );

$border_image_wrapper_color = get_option( WOO_DYNAMIC_GALLERY_PREFIX . 'border_image_wrapper_color', '' );
update_option( WOO_DYNAMIC_GALLERY_PREFIX . 'main_border', array( 'width' => '1px', 'style' => 'solid', 'color' => $border_image_wrapper_color, 'corner' => 'square' , 'rounded_value' => 0 ) );

$bg_nav_color = get_option( WOO_DYNAMIC_GALLERY_PREFIX . 'bg_nav_color', '' );
update_option( WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_bg_color', array( 'enable' => 1, 'color' => $bg_nav_color ) );

update_option( WOO_DYNAMIC_GALLERY_PREFIX . 'navbar_border', array( 'width' => '1px', 'style' => 'solid', 'color' => $border_image_wrapper_color, 'corner' => 'square' , 'rounded_value' => 0 ) );

$product_gallery_bg_des = get_option( WOO_DYNAMIC_GALLERY_PREFIX . 'product_gallery_bg_des', '' );
update_option( WOO_DYNAMIC_GALLERY_PREFIX . 'caption_bg_color', array( 'enable' => 1, 'color' => $product_gallery_bg_des ) );

// Build sass
global $wc_wc_dynamic_gallery_less;
$wc_wc_dynamic_gallery_less->plugin_build_sass();