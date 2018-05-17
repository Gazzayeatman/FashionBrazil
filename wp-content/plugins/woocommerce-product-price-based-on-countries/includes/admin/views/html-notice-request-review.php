<?php
/**
 * Admin View: Notice - Request a review
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="notice notice-info notice-pbc pbc-request-review">		
	<p class="rate-description"><?php printf( __( 'Hi! Do you like %sWooCommerce Price Based on Country%s? Would you mind taking a moment to rate it? It won\'t take more than a minute. Thanks for your support!', 'wc-price-based-country' ), '<strong>', '</strong>' ); ?> <span class="dashicons dashicons-smiley"></span></p>
	<p class="submit">
		<a class="button-primary" target="_blank" rel="noopener noreferrer" href="https://wordpress.org/support/plugin/woocommerce-product-price-based-on-countries/reviews/?filter=5#new-post"><?php _e( 'Yes, rate now!', 'woocommerce' ); ?></a>
		<a class="button-secondary pbc-hide-notice remind-later" data-nonce="<?php echo wp_create_nonce( 'pbc-hide-notice' )?>" data-notice="request_review" href="#"><?php _e( 'Remind me later', 'wc-price-based-country' ); ?></a>
		<a class="button-secondary pbc-hide-notice" data-nonce="<?php echo wp_create_nonce( 'pbc-hide-notice' )?>" data-notice="request_review" href="#"><?php _e( 'No, thanks', 'wc-price-based-country' ); ?></a>
	</p>	
</div>	