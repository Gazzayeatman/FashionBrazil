<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WCPBC_Frontend
 *
 * WooCommerce Price Based Country Front-End
 *
 * @class 		WCPBC_Frontend
 * @version		1.7.2
 * @author 		oscargare
 */
class WCPBC_Frontend {
	
	/**
	 * Hook actions and filters
	 */
	public static function init(){						

		add_action( 'wp_footer', array( __CLASS__, 'test_store_message' ) );
		
		add_filter( 'woocommerce_customer_default_location_array', array( __CLASS__, 'test_default_location' ) );
		
		add_action( 'wc_price_based_country_before_frontend_init', array( __CLASS__ , 'check_manual_country_widget'), 20 );		

		add_action( 'wc_price_based_country_before_frontend_init', array( __CLASS__ , 'checkout_country_update'), 20 );		
		
		add_action( 'wc_price_based_country_before_frontend_init', array( __CLASS__ , 'calculate_shipping_country_update'), 20 );						

		add_action( 'wp_enqueue_scripts', array( __CLASS__ , 'load_scripts' ), 20 );			
	}	
		
	/**
	 * Print test store message 
	 */
	public static function test_store_message() {
		if ( get_option('wc_price_based_country_test_mode', 'no') === 'yes' && $test_country = get_option('wc_price_based_country_test_country') ) {
			$country = WC()->countries->countries[ $test_country ];		
			echo '<p class="demo_store">' . sprintf( __( '%sPrice Based Country%s test mode enabled for testing %s. You should do tests on private browsing mode. Browse in private with %sFirefox%s, %sChrome%s and %sSafari%s', 'wc-price-based-country'), '<strong>', '</strong>', $country, '<a href="https://support.mozilla.org/en-US/kb/private-browsing-use-firefox-without-history">', '</a>', '<a href="https://support.google.com/chrome/answer/95464?hl=en">', '</a>', '<a href="https://support.apple.com/kb/ph19216?locale=en_US">', '</a>' ) . '</p>';
		}
	}
	
	/**
	 * Return Test country as default location
	 */
	public static function test_default_location( $location ) {	
		if ( get_option('wc_price_based_country_test_mode', 'no') === 'yes' && $test_country = get_option('wc_price_based_country_test_country') ) {	
			$location = wc_format_country_state_string( get_option('wc_price_based_country_test_country') );		
		}
		return $location;
	}	

	/**
	 * Check manual country widget
	 */	
	public static function check_manual_country_widget(){				

		if ( isset( $_REQUEST['wcpbc-manual-country'] ) && $_REQUEST['wcpbc-manual-country'] ) {			
			
			//set WC country
			wcpbc_set_woocommerce_country( wc_clean( $_REQUEST['wcpbc-manual-country'] ) );						

			//set customer session cookie after headers has been send
			add_action( 'send_headers', array( __CLASS__, 'init_session' ), 10 );		
						
		}
	}

	/**
	 * init customer session and refresh cart totals
	 *
	 * @since 1.7.0
	 * @access public
	 */
	public static function init_session(){
		if ( ! WC()->session->has_session() ) {
			WC()->session->set_customer_session_cookie(true);
		}

		// Refresh cart total			
		$cart_content_total = version_compare( WC_VERSION, '3.0', '<' ) ? WC()->cart->cart_contents_total : WC()->cart->get_cart_contents_total();		
		if ( $cart_content_total ) {						
			WC()->cart->calculate_totals();
		}	
	}				

	/**
	 * Add scripts
	 */
	public static function load_scripts( ) {

		if ( ! did_action( 'before_woocommerce_init' ) ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$params = array(
			'wc_ajax_url'  	   => WC_AJAX::get_endpoint( "%%endpoint%%" ),			
			'ajax_geolocation' => ! ( ! WCPBC_Ajax_Geolocation::is_enabled() || is_cart() || is_account_page() || is_checkout() || is_customize_preview() ) ? '1' : '0',
			'country'		   => wcpbc_get_woocommerce_country(),
		);

		$deps = ( '1' == $params['ajax_geolocation'] && wcpbc_is_pro() ? array( 'wc-cart-fragments', 'wc-price-based-country-pro-frontend' ) : array( 'wc-cart-fragments' ) );

		wp_register_script( 'wc-price-based-country-frontend', WCPBC()->plugin_url() . 'assets/js/wcpbc-frontend' . $suffix . '.js', $deps, WCPBC()->version, true );		
		wp_localize_script( 'wc-price-based-country-frontend', 'wc_price_based_country_frontend_params', $params );
		wp_enqueue_script( 'wc-price-based-country-frontend' );
	}	

	/**
	 * Update WooCommerce Customer country on checkout
	 */
	public static function checkout_country_update( $post_data = array() ) {					

		if ( defined( 'WC_DOING_AJAX' ) && WC_DOING_AJAX && isset( $_GET['wc-ajax'] ) && 'update_order_review' == $_GET['wc-ajax'] ) {
			
			check_ajax_referer( 'update-order-review', 'security' );
			
			if ( isset( $_POST['country'] ) ) {
				wcpbc_set_wc_biling_country( wc_clean( $_POST['country'] ) );
			}
			
			if ( wc_ship_to_billing_address_only() ) {
				if ( isset( $_POST['country'] ) ) {
					WC()->customer->set_shipping_country( wc_clean( $_POST['country'] ) );
				}
			} else {
				if ( isset( $_POST['s_country'] ) ) {
					WC()->customer->set_shipping_country( wc_clean( $_POST['s_country'] ) );
				}
			}		
		}				
	}	

	/**
	 * Update WooCommerce Customer country on calculate shipping
	 */
	public static function calculate_shipping_country_update(){

		if ( isset( $_POST['calc_shipping'] ) && $_POST['calc_shipping'] && wp_verify_nonce( $_POST['_wpnonce'], 'woocommerce-cart' ) ) {
			
			if ( isset( $_POST['calc_shipping_country'] ) && $country = wc_clean( $_POST['calc_shipping_country'] ) ) {
				
				wcpbc_set_wc_biling_country( $country );	
				WC()->customer->set_shipping_country( $country );

			} else{
				WC()->customer->set_to_base();
				WC()->customer->set_shipping_to_base();
			}
		} 
	}	
}

WCPBC_Frontend::init();