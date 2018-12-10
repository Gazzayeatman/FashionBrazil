<?php
/**
 * Plugin Uninstall
 *
 * Uninstalling deletes options, tables, and pages.
 *
 */
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) 
	exit();

$plugin_key = 'woo_dynamic_gallery';

// Delete Google Font
delete_option( $plugin_key . '_google_api_key' . '_enable' );
delete_transient( $plugin_key . '_google_api_key' . '_status' );
delete_option( $plugin_key . '_google_font_list' );

if ( get_option( $plugin_key . '_clean_on_deletion' ) == 'yes' ) {
	delete_option( $plugin_key . '_google_api_key' );
	delete_option( $plugin_key . '_toggle_box_open' );
	delete_option( $plugin_key . '-custom-boxes' );

	delete_metadata( 'user', 0,  $plugin_key . '-' . 'plugin_framework_global_box' . '-' . 'opened', '', true );

	delete_option( 'wc_dgallery_product_gallery_width' );
	delete_option( 'wc_dgallery_width_type' );
	delete_option( 'wc_dgallery_gallery_height_type' );
	delete_option( 'wc_dgallery_product_gallery_height' );
	delete_option( 'wc_dgallery_product_gallery_auto_start' );
	delete_option( 'wc_dgallery_product_gallery_speed' );
	delete_option( 'wc_dgallery_product_gallery_effect' );
	delete_option( 'wc_dgallery_product_gallery_animation_speed' );
	delete_option( 'wc_dgallery_stop_scroll_1image' );

	delete_option( 'wc_dgallery_auto_feature_image' );

	delete_option( 'wc_dgallery_bg_image_wrapper' );
	delete_option( 'wc_dgallery_border_image_wrapper_color' );

	delete_option( 'wc_dgallery_main_bg_color' );
	delete_option( 'wc_dgallery_main_border' );
	delete_option( 'wc_dgallery_main_shadow' );
	delete_option( 'wc_dgallery_main_margin_top' );
	delete_option( 'wc_dgallery_main_margin_bottom' );
	delete_option( 'wc_dgallery_main_margin_left' );
	delete_option( 'wc_dgallery_main_margin_right' );
	delete_option( 'wc_dgallery_main_padding_top' );
	delete_option( 'wc_dgallery_main_padding_bottom' );
	delete_option( 'wc_dgallery_main_padding_left' );
	delete_option( 'wc_dgallery_main_padding_right' );

	delete_option( 'wc_dgallery_hide_woo_gallery' );
	delete_option( 'wc_dgallery_popup_gallery' );
	delete_option( 'wc_dgallery_activate' );
	delete_option( 'wc_dgallery_reset_galleries_activate' );
	delete_option( 'wc_dgallery_show_variation' );
	delete_option( 'wc_dgallery_reset_variation_activate' );

	delete_option( 'wc_dgallery_caption_font' );
	delete_option( 'wc_dgallery_caption_font_size' );
	delete_option( 'wc_dgallery_caption_font_style' );
	delete_option( 'wc_dgallery_product_gallery_text_color' );
	delete_option( 'wc_dgallery_product_gallery_bg_des' );

	delete_option( 'wc_dgallery_caption_bg_color' );
	delete_option( 'wc_dgallery_caption_bg_transparent' );

	delete_option( 'wc_dgallery_product_gallery_nav' );
	delete_option( 'wc_dgallery_navbar_font' );
	delete_option( 'wc_dgallery_navbar_font_size' );
	delete_option( 'wc_dgallery_navbar_font_style' );
	delete_option( 'wc_dgallery_bg_nav_color' );
	delete_option( 'wc_dgallery_bg_nav_text_color' );
	delete_option( 'wc_dgallery_navbar_height' );

	delete_option( 'wc_dgallery_navbar_bg_color' );
	delete_option( 'wc_dgallery_navbar_border' );
	delete_option( 'wc_dgallery_navbar_shadow' );
	delete_option( 'wc_dgallery_navbar_margin_top' );
	delete_option( 'wc_dgallery_navbar_margin_bottom' );
	delete_option( 'wc_dgallery_navbar_margin_left' );
	delete_option( 'wc_dgallery_navbar_margin_right' );
	delete_option( 'wc_dgallery_navbar_padding_top' );
	delete_option( 'wc_dgallery_navbar_padding_bottom' );
	delete_option( 'wc_dgallery_navbar_padding_left' );
	delete_option( 'wc_dgallery_navbar_padding_right' );
	delete_option( 'wc_dgallery_navbar_separator' );

	delete_option( 'wc_dgallery_lazy_load_scroll' );
	delete_option( 'wc_dgallery_transition_scroll_bar' );

	delete_option( 'wc_dgallery_enable_gallery_thumb' );
	delete_option( 'wc_dgallery_hide_thumb_1image' );
	delete_option( 'wc_dgallery_thumb_width' );
	delete_option( 'wc_dgallery_thumb_height' );
	delete_option( 'wc_dgallery_thumb_spacing' );
	delete_option( 'wc_dgallery_thumb_columns' );
	delete_option( 'wc_dgallery_thumb_border_color' );
	delete_option( 'wc_dgallery_thumb_current_border_color' );
	delete_option( 'wc_dgallery_thumb_show_type' );

	delete_option( 'wc_dgallery_product_gallery_width_responsive' );
	delete_option( 'wc_dgallery_product_gallery_width_fixed' );

	delete_option( $plugin_key . '_clean_on_deletion' );

	delete_post_meta_by_key('_actived_d_gallery');
	delete_post_meta_by_key('_wc_dgallery_show_variation');
	delete_post_meta_by_key('_woocommerce_exclude_image');
	delete_post_meta_by_key('_wc_dgallery_in_variations');
	delete_post_meta_by_key('_product_image_gallery_bk');
	delete_post_meta_by_key('_a3_dgallery');
	delete_post_meta_by_key('_wc_dgallery_enable_gallery_thumb');
}
