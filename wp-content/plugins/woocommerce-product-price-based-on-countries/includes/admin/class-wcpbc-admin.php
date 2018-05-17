<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WCPBC_Admin
 *
 * WooCommerce Price Based Country Admin 
 *
 * @class 		WCPBC_Admin
 * @version		1.6.6
 * @author 		oscargare
 * @category	Class
 */
class WCPBC_Admin {

	/**
	 * Hook actions and filters
	 */
	public static function init(){
		
		add_action( 'init', array( __CLASS__, 'includes' ) );		
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_scripts' ) );	
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_styles' ) );	
		add_action( 'woocommerce_coupon_options', array( __CLASS__, 'coupon_options' ) );
		add_action( 'woocommerce_coupon_options_save', array( __CLASS__, 'coupon_options_save' ) );		
		add_action( 'woocommerce_system_status_report', array( __CLASS__, 'system_status_report' ) );		
		add_filter( 'woocommerce_get_settings_pages', array( __CLASS__, 'settings_price_based_country' ) );					
		add_filter( 'woocommerce_paypal_supported_currencies', array( __CLASS__, 'paypal_supported_currencies' ) );						
	}

	/**
	 * Include any classes we need within admin.
	 */
	public static function includes() {				

		include_once('class-wcpbc-admin-product-data.php');					
		include_once('class-wcpbc-admin-report.php');
		
		do_action('wc_price_based_country_admin_init');
	}	
	
	/**
	 * Add Price Based Country settings tab to woocommerce settings
	 */
	public static function settings_price_based_country( $settings ) {

		$settings[] = include( 'settings/class-wc-settings-price-based-country.php' );

		return $settings;
	}			
	
	/**
	 * PayPal supported currencies
	 *
	 * @since 1.6.4
	 */
	public static function paypal_supported_currencies( $paypal_currencies ){

		$base_currency = wcpbc_get_base_currency();

		if ( ! in_array( $base_currency, $paypal_currencies ) ) {
			foreach ( WCPBC()->get_regions() as $zone ) {
				if ( in_array( $zone['currency'], $paypal_currencies ) ) {
					$paypal_currencies[] = $base_currency;
					break;
				}
			}	
		}
		
		return $paypal_currencies;
	}

	/**
	 * Enqueue styles.
	 *
	 * @since 1.6
	 */
	public static function admin_styles() {
		// Register admin styles
		wp_enqueue_style( 'wc-price-based-country-admin-styles', WCPBC()->plugin_url() . '/assets/css/admin.css', array(), WCPBC()->version );
	}
	
	/**
	 * Enqueue scripts.	 
	 */	
	public static function admin_scripts( ) {	

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		
		// Register scripts		
		wp_register_script( 'wc-price-based-country-admin', WCPBC()->plugin_url() . 'assets/js/wcpbc-admin' . $suffix . '.js', array( 'jquery' ), WCPBC()->version, true );		
		wp_localize_script( 'wc-price-based-country-admin', 'wc_price_based_country_admin_params', array(
			'ajax_url'				 	 => admin_url( 'admin-ajax.php' ),
			'product_type_supported' 	 => array_keys( wcpbc_product_types_supported() ),
			'i18n_delete_zone_alert' 	 => __( 'Are you sure?', 'wc-price-based-country' ),
			'i18n_caching_support_alert' => __( 'You must clear cache after enabling this option.', 'wc-price-based-country' ),
		) );
		wp_enqueue_script( 'wc-price-based-country-admin' );
	}
	
	
	/**
	 * Display coupon amount options.
	 *
	 * @since 1.6
	 */
	public static function coupon_options(){
		woocommerce_wp_checkbox( array( 'id' => 'zone_pricing_type', 'cbvalue' => 'exchange_rate', 'label' => __( 'Calculate amount by exchange rate', 'wc-price-based-country' ), 'description' => __( 'Check this box if for the countries defined in zone pricing the coupon amount should be calculated using exchange rate.', 'wc-price-based-country' ) ) );	
	}
	
	/**
	 * Save coupon amount options.
	 *
	 * @since 1.6
	 */
	public static function coupon_options_save( $post_id ){
		$type = get_post_meta( $post_id, 'discount_type' , true );
		$zone_pricing_type = in_array( $type, array( 'fixed_cart', 'fixed_product' ) ) && isset( $_POST['zone_pricing_type'] ) ? 'exchange_rate' : 'nothig';
		update_post_meta( $post_id, 'zone_pricing_type', $zone_pricing_type ) ;
	}
	
	/**
	 * Add plugin info to WooCommerce System Status Report
	 *
	 * @since 1.6.3
	 */
	public static function system_status_report(){
		include_once( 'views/html-admin-page-status-report.php' );
	}

}

WCPBC_Admin::init();
