<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! wcpbc_is_pro() && ! class_exists( 'WCPBC_Product_Addons_Basic' ) ) :

/** 
 *
 * @class WCPBC_Product_Addons
 * @version	1.7.0
 */
class WCPBC_Product_Addons_Basic {
	
	/**
	 * Hook actions and filters
	 *
	 * @since 1.6.14
	 */
	public static function init() {		
		add_action( 'product_page_global_addons', array( __CLASS__, 'global_addons_admin' ), 20 );		
		add_action( 'woocommerce_product_write_panel_tabs', array( __CLASS__, 'tab' ), 11 );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'panel' ) );
	}	

	/**
	 * Controls the global addons admin page.
	 */
	public static function global_addons_admin() {		

		if ( ! empty( $_GET['add'] ) || ! empty( $_GET['edit'] ) ) {
			$utm_source = 'product-addons';
			$name 		 = 'WooCommerce Product Add-ons';
			ob_start();			
			include( WCPBC()->plugin_path() . 'includes/admin/views/html-notice-pro-product-type.php' );			
			$get_pro = ob_get_clean();
			include( WCPBC()->plugin_path() . 'includes/admin/views/html-global-product-addon.php' );	
		}
	}

	/**
	 * Add product tab.
	 */
	public static function tab() {
		?><li class="addons_tab product_addons"><a href="#wcpbc_product_addons_data"><span><?php _e( 'Add-ons zone pricing', 'wc-price-based-country' ); ?></span></a></li><?php
	}

	/**
	 * Add product panel.
	 */
	public static function panel() {
		echo '<div id="wcpbc_product_addons_data" class="panel woocommerce_options_panel wc-metaboxes-wrapper">';
		$utm_source = 'product-addons';
		$name 		 = 'WooCommerce Product Add-ons';
		include( WCPBC()->plugin_path() . 'includes/admin/views/html-notice-pro-product-type.php' );	
		echo '</div>';
	}

}

WCPBC_Product_Addons_Basic::init();

endif;
