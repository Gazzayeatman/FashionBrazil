<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Get all products
global $wpdb;

@set_time_limit(86400);
@ini_set("memory_limit","1000M");

$all_products = $wpdb->get_results( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish' " );
if ( is_array( $all_products ) && count( $all_products ) > 0 ) {
	foreach ( $all_products as $current_product ) {

		// Check if _a3_dgallery meta key is existed then don't upgrade
		$dgallery_ids = get_post_meta( $current_product->ID, '_a3_dgallery', true );
		if ( ! empty( $dgallery_ids ) ) continue;

		$featured_is_excluded = 1;
		$featured_img_id      = (int) get_post_meta( $current_product->ID, '_thumbnail_id', true );
		if ( ! empty( $featured_img_id ) && $featured_img_id > 0 ) {
			$featured_is_excluded = get_post_meta( $featured_img_id, '_woocommerce_exclude_image', true );
		}

		$dgallery_ids = array();

		if ( 1 != $featured_is_excluded ) {
			$dgallery_ids[]    = $featured_img_id;
		}

		$attached_images      = (array) get_posts( array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'numberposts'    => -1,
			'post_status'    => null,
			'post_parent'    => $current_product->ID,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'exclude'        => array( $featured_img_id ),
		) );

		if ( is_array( $attached_images ) && count( $attached_images ) > 0 ) {
			foreach ( $attached_images as $item_thumb ) {
				$is_excluded   = get_post_meta( $item_thumb->ID, '_woocommerce_exclude_image', true );

				// Don't get if this image is excluded on main gallery
				if ( 1 == $is_excluded ) continue;

				$dgallery_ids[]    = $item_thumb->ID;
			}
		}

		if ( count( $dgallery_ids ) > 0 ) {
			add_post_meta( $current_product->ID, '_a3_dgallery', implode( ',', $dgallery_ids ) );
		}
	}
}
