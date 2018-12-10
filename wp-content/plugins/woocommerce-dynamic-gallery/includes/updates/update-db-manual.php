<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Get all products
global $wpdb;

@set_time_limit(86400);
@ini_set("memory_limit","1000M");

/*
 * Clearn DB from version 1.7.0 after big change on gallery of variation
 */
delete_post_meta_by_key( '_woocommerce_exclude_image' );
delete_post_meta_by_key( '_wc_dgallery_in_variations' );

/*
 * Update DB to version 2.1.0
 *
 */

// Get all products
$all_products = $wpdb->get_results( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' " );
if ( is_array( $all_products ) && count( $all_products ) > 0 ) {
	foreach ( $all_products as $current_product ) {

		// First need to backup Woo Default Gallery meta field - '_product_image_gallery'
		$product_image_gallery = get_post_meta( $current_product->ID, '_product_image_gallery', true );
		if ( ! empty( $product_image_gallery ) && '' != trim( $product_image_gallery ) ) {
			add_post_meta( $current_product->ID, '_product_image_gallery_bk', $product_image_gallery );
		}

		// Get Dynamic Gallery and Convert it to Woo Default Gallery
		$a3_dgallery = get_post_meta( $current_product->ID, '_a3_dgallery', true );
		if ( ! empty( $a3_dgallery ) && '' != trim( $a3_dgallery ) ) {
			update_post_meta( $current_product->ID, '_product_image_gallery', $a3_dgallery );
		}
	}
}


// Set DB version to latest version and set DB updated is yes
update_option( 'a3_dynamic_gallery_db_updated', 'yes' );
update_option( 'a3_dynamic_gallery_db_version', WOO_DYNAMIC_GALLERY_DB_VERSION );
