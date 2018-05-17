<?php
/**
 * Admin View: Notice - Data Updated
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="notice notice-success notice-pbc pbc-is-dismissible">	
	<a class="notice-dismiss pbc-hide-notice" data-nonce="<?php echo wp_create_nonce( 'pbc-hide-notice' )?>" data-notice="updated" href="#"></a>		
	<p><strong>WooCommerce Price Based on Country:</strong> <?php _e( 'Data update complete. Thank you for updating to the latest version!', 'wc-price-based-country' ); ?></p>			
</div>	