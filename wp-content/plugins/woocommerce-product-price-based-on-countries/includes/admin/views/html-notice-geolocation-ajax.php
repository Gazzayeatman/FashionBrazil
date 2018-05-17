<?php
/**
 * Admin View: Notice - Geolocation Ajax
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="notice notice-warning notice-pbc">		
	<p><strong>WooCommerce Price Based on Country:</strong> <?php printf( __( 'You enabled Caching Support option of Price Based on Country. Set %sDefault customer location%s to %sGeolocate%s.', 'wc-price-based-country' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings' ) ) . '">', '</a>', '<strong><em>', '</em></strong>' ); ?></p>			
</div>	