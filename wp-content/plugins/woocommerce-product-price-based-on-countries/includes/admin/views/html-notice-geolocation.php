<?php
/**
 * Admin View: Notice - Geolocation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="notice notice-warning notice-pbc pbc-is-dismissible">	
	<a class="notice-dismiss pbc-hide-notice" data-nonce="<?php echo wp_create_nonce( 'pbc-hide-notice' )?>" data-notice="geolocation" href="#"></a>		
	<p><strong>WooCommerce Price Based on Country:</strong> <?php printf( __( 'Geolocation is required. Set %sDefault customer location%s to %sGeolocate%s.', 'wc-price-based-country' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings' ) ) . '">', '</a>', '<strong><em>', '</em></strong>' ); ?></p>			
</div>	