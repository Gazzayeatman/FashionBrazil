<?php
/**
 * Plugin Name: Afterpay Gateway for WooCommerce
 * Description: Provide Afterpay as a payment option for WooCommerce orders.
 * Author: Afterpay
 * Author URI: https://www.afterpay.com/
 * Version: 2.0.0
 * Text Domain: afterpay-gateway-for-woocommerce
 * WC requires at least: 2.6.0
 * WC tested up to: 3.2.0
 * 
 * Copyright: (c) 2017 Afterpay
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('Afterpay_Plugin')) {
	class Afterpay_Plugin
	{
		/**
		 * @var		Afterpay_Plugin		$instance	A static reference to an instance of this class.
		 */
		protected static $instance;

		/**
		 * @var		int					$version	A reference to the plugin version, which will match
		 *											the value in the comments above.
		 */
		public static $version = '2.0.0';

		/**
		 * Import required classes.
		 *
		 * @since	2.0.0
		 * @used-by	self::init()
		 * @used-by	self::deactivate_plugin()
		 */
		public static function load_classes()
		{
			if (!function_exists('woothemes_queue_update')) {
				require_once dirname(__FILE__) . '/class/WC/woo-functions.php';
			}
			require_once dirname(__FILE__) . '/class/Cron/Afterpay_Plugin_Cron.php';
			require_once dirname(__FILE__) . '/class/Cron/Afterpay_Plugin_Idempotency_Cron.php';
			require_once dirname(__FILE__) . '/class/Merchant/Afterpay_Plugin_Merchant.php';
			if (class_exists('WC_Payment_Gateway')) {
				require_once dirname(__FILE__) . '/class/WC_Gateway_Afterpay.php';
			}
		}

		/**
		 * Class constructor. Called when an object of this class is instantiated.
		 * 
		 * @since	2.0.0
		 * @see		Afterpay_Plugin::init()						For where this class is instantiated.
		 * @see		WC_Settings_API::process_admin_options()
		 * @uses	version_compare()							Available in PHP since 4.1
		 */
		public function __construct()
		{
			$gateway = WC_Gateway_Afterpay::getInstance();

			/**
			 * Actions.
			 */
			add_action( 'init', array($gateway, 'register_post_types'), 10, 0 );
			add_action( 'admin_notices', array($gateway, 'render_admin_notices'), 10, 0 );
			add_action( 'admin_enqueue_scripts', array($this, 'init_admin_assets'), 10, 0 );
			add_action( 'afterpay_do_cron_jobs', array('Afterpay_Plugin_Cron', 'fire_jobs'), 10, 0 );
			add_action( 'afterpay_do_idempotency_cron_jobs', array('Afterpay_Plugin_Idempotency_Cron', 'fire_jobs'), 10, 0 );
			add_action( "woocommerce_update_options_payment_gateways_{$gateway->id}", array($gateway, 'process_admin_options'), 10, 0 ); # process_admin_options() is defined in WC_Gateway_Afterpay's grandparent class: WC_Settings_API.
			add_action( "woocommerce_update_options_payment_gateways_{$gateway->id}", array('Afterpay_Plugin_Cron', 'fire_jobs'), 11, 0 ); # Manually fire the cron jobs when our gateway settings are saved.
			// add_action( "woocommerce_update_options_payment_gateways_{$gateway->id}", array('Afterpay_Plugin_Idempotency_Cron', 'fire_jobs'), 11, 0 ); # Manually fire the cron jobs when our gateway settings are saved.
			add_action( "woocommerce_receipt_{$gateway->id}", array($gateway, 'receipt_page'), 10, 1 );
			add_action( 'woocommerce_single_product_summary', array($gateway, 'print_info_for_product_detail_page'), 15, 0 );
			add_action( 'woocommerce_after_shop_loop_item_title', array($gateway, 'print_info_for_listed_products'), 15, 0 );
			add_action( 'woocommerce_cart_totals_after_order_total', array($gateway, 'render_schedule_on_cart_page'), 10, 0 );
			add_action( 'template_redirect', array($gateway, 'override_single_post_template_for_afterpay_quotes'), 10, 0 );
			add_action( 'template_redirect', array($gateway, 'afterpay_check_for_cancelled_payment'), 10, 0);
			add_action( 'wp_head', array($gateway, 'inject_preauth_html'), 10, 0 );
			add_action( 'wp_footer', array($gateway, 'inject_preauth_html'), 10, 0 );
			add_action( 'shutdown', array($gateway, 'inject_preauth_html'), 10, 0 );
			add_action( 'wp_enqueue_scripts', array($this, 'init_website_assets'), 10, 0 );
			add_action( 'wp', array($gateway, 'afterpay_retry_capture_call'), 7 ); # Handle Capture Retry AJAX Call

			/**
			 * Filters.
			 */
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array($this, 'filter_action_links'), 10, 1 );
			add_filter( 'cron_schedules', array('Afterpay_Plugin_Cron', 'edit_cron_schedules'), 10, 1 );
			add_filter( 'cron_schedules', array('Afterpay_Plugin_Idempotency_Cron', 'edit_cron_schedules'), 10, 1 );
			add_filter( 'woocommerce_payment_gateways', array($gateway, 'add_afterpay_gateway'), 10, 1 );
			add_filter( 'woocommerce_available_payment_gateways', array($gateway, 'check_cart_within_limits'), 99, 1 );
			add_filter( 'woocommerce_get_price_html', array($gateway, 'filter_woocommerce_get_price_html'), 10, 2 );
			if (version_compare( WC_VERSION, '3.0.0', '<' )) {
				add_filter( 'woocommerce_variation_price_html', array($gateway, 'filter_woocommerce_variation_price_html'), 10, 2);
				add_filter( 'woocommerce_variation_sale_price_html', array($gateway, 'filter_woocommerce_variation_price_html'), 10, 2);
			}
			add_filter( 'woocommerce_gateway_icon', array($gateway, 'filter_woocommerce_gateway_icon'), 10, 2 );
			add_filter( 'woocommerce_create_order', array($gateway, 'override_order_creation'), 10, 2 );
			add_filter( 'woocommerce_new_order_data', array($gateway, 'filter_woocommerce_new_order_data'), 10, 1 );
			add_filter( 'woocommerce_thankyou_order_id', array($gateway, 'payment_callback'), 10, 1 );

			/**
			 * Shortcodes.
			 */
			add_shortcode( 'afterpay_product_logo', array($this, 'shortcode_afterpay_product_logo') );
		}

		/**
		 * Note: Hooked onto the "plugin_action_links_woocommerce-gateway-afterpay/woocommerce-afterpay.php" Action.
		 *
		 * @since	2.0.0
		 * @see		self::__construct()		For hook attachment.
		 * @param	array	$links
		 * @return	array
		 */
		public function filter_action_links($links)
		{
			$additional_links = array(
				'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=afterpay' ) . '">' . __( 'Settings', 'woo_afterpay' ) . '</a>',
			);

			return array_merge($additional_links, $links);
		}

		/**
		 * Note: Hooked onto the "wp_enqueue_scripts" Action to avoid the Wordpress Notice warnings
		 *
		 * @since	2.0.0
		 * @see		self::__construct()		For hook attachment.
		 */
		public function init_website_assets()
		{
			/**
			 * Register & Enqueue JS.
			 * Note: Admin assets are registered in self::init_admin_assets()
			 */
			//wp_enqueue_script( 'fancybox_js', 'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.3.5/jquery.fancybox.min.js', array('jquery') );
			wp_enqueue_script( 'afterpay_js', plugins_url( 'js/afterpay.js', __FILE__ ), array('jquery') );
			
			/**
			 * Register & Enqueue CSS.
			 */
			wp_enqueue_style( 'fancybox_css', 'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.3.5/jquery.fancybox.min.css' );
			wp_enqueue_style( 'afterpay_css', plugins_url( 'css/afterpay.css', __FILE__ ) );
		}

		/**
		 * Note: Hooked onto the "admin_enqueue_scripts" Action.
		 *
		 * @since	2.0.0
		 * @see		self::__construct()		For hook attachment.
		 */
		public function init_admin_assets()
		{
			wp_enqueue_script( 'afterpay_admin_js', plugins_url( 'js/afterpay-admin.js', __FILE__ ) );
		}

		/**
		 * Provide a shortcode for rendering the standard Afterpay logo on individual product pages.
		 *
		 * E.g.:
		 * 	- [afterpay_product_logo] OR [afterpay_product_logo theme="colour"]
		 * 	- [afterpay_product_logo theme="black"]
		 * 	- [afterpay_product_logo theme="white"]
		 *
		 * @since	2.0.0
		 * @see		self::__construct()		For shortcode definition.
		 * @param	array	$atts			Array of shortcode attributes.
		 * @uses	shortcode_atts()
		 * @return	string
		 */
		public function shortcode_afterpay_product_logo($atts) {
			$atts = shortcode_atts( array(
				'theme' => 'colour'
			), $atts );

			if (!in_array($atts['theme'], array('colour', 'black', 'white'))) {
				$atts['theme'] = 'colour';
			}

			ob_start();

			?><img style="vertical-align:middle;" src="https://static.afterpay.com/integration/product-page/logo-afterpay-<?php echo $atts['theme']; ?>.png" srcset="https://static.afterpay.com/integration/product-page/logo-afterpay-<?php echo $atts['theme']; ?>.png 1x, https://static.afterpay.com/integration/product-page/logo-afterpay-<?php echo $atts['theme']; ?>@2x.png 2x, https://static.afterpay.com/integration/product-page/logo-afterpay-<?php echo $atts['theme']; ?>@3x.png 3x" width="100" height="21" alt="Afterpay" /><?php

			return ob_get_clean();
		}

		/**
		 * Initialise the class and return an instance.
		 *
		 * Note:	Hooked onto the "plugins_loaded" Action.
		 *
		 * @since	2.0.0
		 * @uses	self::load_classes()
		 * @return	Afterpay_Plugin
		 * @used-by	self::activate_plugin()
		 */
		public static function init()
		{
			self::load_classes();
			if (!class_exists('WC_Gateway_Afterpay')) {
				return false;
			}
			if (is_null(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Callback for when this plugin is activated. Schedules the cron jobs.
		 *
		 * @since	2.0.0
		 * @uses	set_transient()							Available in WordPress core since 2.8.0
		 * @uses	self::init()
		 * @uses	Afterpay_Plugin_Cron::create_jobs()
		 */
		public static function activate_plugin()
		{
			if (!current_user_can( 'activate_plugins' )) {
				return;
			}

			if (function_exists('set_transient')) {
				set_transient( 'afterpay-admin-activation-notice', true, 300 );
			}

			self::init(); # Can't just use load_classes() here because the cron schedule is setup in the filter, which attaches inside the class constructor. Have to do a full init.
			Afterpay_Plugin_Cron::create_jobs();
			Afterpay_Plugin_Idempotency_Cron::create_jobs();
		}

		/**
		 * Callback for when this plugin is deactivated. Deletes the scheduled cron jobs.
		 *
		 * @since	2.0.0
		 * @uses	self::load_classes()
		 * @uses	Afterpay_Plugin_Cron::delete_jobs()
		 */
		public static function deactivate_plugin()
		{
			if (!current_user_can( 'activate_plugins' )) {
				return;
			}

			self::load_classes();
			Afterpay_Plugin_Cron::delete_jobs();
			Afterpay_Plugin_Idempotency_Cron::delete_jobs();
		}

		/**
		 * Callback for when the plugin is uninstalled. Remove all of its data.
		 *
		 * Note:	This function is called when this plugin is uninstalled.
		 *
		 * @since	2.0.0
		 */
		public static function uninstall_plugin()
		{
			if (!current_user_can( 'activate_plugins' )) {
				return;
			}
		}
	}

	register_activation_hook( __FILE__, array('Afterpay_Plugin', 'activate_plugin') );
	register_deactivation_hook( __FILE__, array('Afterpay_Plugin', 'deactivate_plugin') );
	register_uninstall_hook( __FILE__, array('Afterpay_Plugin', 'uninstall_plugin') );

	add_action( 'plugins_loaded', array('Afterpay_Plugin', 'init'), 10, 0 );
}
