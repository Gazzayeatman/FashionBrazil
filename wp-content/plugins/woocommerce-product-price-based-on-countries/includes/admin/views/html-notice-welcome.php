<?php
/**
 * Admin View: Notice - Welcome
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="notice notice-info notice-pbc pbc-welcome-panel">
	<div class="pbc-welcome-panel-body">
		<a class="notice-dismiss pbc-hide-notice" data-nonce="<?php echo wp_create_nonce( 'pbc-hide-notice' )?>" data-notice="welcome" href="#"><?php _e( 'Dismiss', 'wc-price-based-country' ); ?></a>		
		<h2><?php printf( __( 'Welcome to WooCommerce Price Based on Country %s', 'wc-price-based-country' ), WCPBC()->version) ; ?></h2>
		<p class="about-description"><?php _e( 'Where do you want to start?', 'wc-price-based-country' ); ?></p>			
		<p class="">
			<a class="button-secondary" target="_blank" rel="noopener noreferrer" href="https://www.pricebasedcountry.com/docs/"><span class="dashicons dashicons-book"></span><?php _e( 'Getting Started Guide', 'woocommerce' ); ?></a>
			<a class="button-secondary" href="<?php echo admin_url( wp_nonce_url('admin.php?page=wc-settings&tab=price-based-country&welcome=1', 'pbc-hide-notice' ) ); ?>"><span class="dashicons dashicons-admin-generic"></span><?php _e( 'Settings', 'woocommerce' ); ?></a>
		</p>
	</div>
</div>
