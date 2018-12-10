<?php
/**
 * Admin View: Notice - WPML Multicurrency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="notice notice-warning notice-pbc pbc-is-dismissible">	
	<a class="notice-dismiss pbc-hide-notice" data-nonce="<?php echo wp_create_nonce( 'pbc-hide-notice' )?>" data-notice="maxmind_geoip_database" href="#"><span class="screen-reader-text"><?php _e( 'Dismiss this notice.' ); ?></span></a>	
	<p><strong>WooCommerce Price Based on Country:</strong> <?php printf( __( 'The MaxMind GeoIP Database does not exist, geolocation will not function. Read the instructions on how to fix it in the "Database" section of your %sSystem Status report%s.', 'wc-price-based-country' ), '<a href="' . admin_url('admin.php?page=wc-status') . '">', '</a>' ); ?></p>		
</div>