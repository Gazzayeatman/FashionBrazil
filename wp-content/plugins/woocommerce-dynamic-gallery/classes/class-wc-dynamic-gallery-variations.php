<?php
/**
 * WooCommerce Dynamic Gallery Variations Class
 *
 * Class Function into woocommerce plugin
 *
 * Table Of Contents
 *
 * media_fields()
 * save_media_fields()
 */
class WC_Dynamic_Gallery_Variations
{

	public static function change_image_in_cart_page( $product_image, $values, $cart_item_key ) {
		if ( is_array( $values ) && isset( $values['variation_id'] ) && $values['variation_id'] > 0 ) {
			return $product_image;

		} elseif ( isset( $values['product_id'] ) && $values['product_id'] > 0 ) {
			$product_id = $values['product_id'];
			// Don't change the image if product has featured image
			if ( has_post_thumbnail( $product_id ) ) return $product_image;

			$dgallery_ids = WC_Dynamic_Gallery_Functions::get_gallery_ids( $product_id );
			if ( is_array( $dgallery_ids ) && count( $dgallery_ids ) > 0 ) {
				// Use first image from variation gallery
				$img_id = (int) array_shift( $dgallery_ids );

				if ( version_compare( WC_VERSION, '3.3.0', '<' ) ) {
					// bw compat. for less than WC 3.3.0
					$product_image = wp_get_attachment_image( $img_id, 'shop_thumbnail' );
				} else {
					$product_image = wp_get_attachment_image( $img_id, 'woocommerce_thumbnail' );
				}
			}
		}

		return $product_image;
	}
}
?>
