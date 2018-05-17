<?php
/**
 * This is the Afterpay - WooCommerce Payment Gateway Class.
 */
class WC_Gateway_Afterpay extends WC_Payment_Gateway
{
	/**
	 * Private variables.
	 *
	 * @var		string	$include_path	Path to where this class's includes are located. Populated in the class constructor.
	 * @var		string	$plugin_url		Base URL for this class's assets. Populated in the class constructor.
	 * @var		array	$environments	Keyed array containing the name and API/web URLs for each environment. Populated in the
	 *									class constructor by parsing the values in "environments.ini".
	 * @var		string	$token			The token to render on the preauth page. This is populated by the
	 *									self::override_single_post_template_for_afterpay_quotes() method,
	 *									only if validated. If populated, it will be used to render the Afterpay JS
	 *									somewhere on the page:
	 *										- on "wp_head" (inside the <head></head> block),
	 *										- on "wp_footer" (inside the theme's footer),
	 *										- or if all else fails, on "shutdown" (after the closing </html> tag).
	 */
	private $include_path, $plugin_url, $environments, $token;

	/**
	 * Public variables.
	 *
	 * @var		string	$id							Inherited from WC_Settings_API. Important: The Admin JS assumes this string to be "afterpay".
	 * @var		string	$description				Inherited from WC_Payment_Gateway.
	 * @var		string	$method_title				Inherited from WC_Payment_Gateway.
	 * @var		string	$method_description			Inherited from WC_Payment_Gateway.
	 * @var		string	$icon						Inherited from WC_Payment_Gateway.
	 * @var		array	$supports					Inherited from WC_Payment_Gateway.
	 * @var		array	$form_fields				Inherited from WC_Settings_API.
	 * @var		string	$title						Inherited from WC_Payment_Gateway.
	 * @var		string	$order_button_text			Inherited from WC_Payment_Gateway.
	 */
	public $id, $description, $method_title, $method_description, $icon, $supports, $form_fields, $title, $supported_currencies;

	/**
	 * Protected static variables.
	 *
	 * @var		WC_Gateway_Afterpay	$instance		A static reference to a singleton instance of this class.
	 */
	protected static $instance = null;

	/**
	 * Public static variables.
	 *
	 * @var		bool|null			$log_enabled	Whether or not logging is enabled. Defaults to null.
	 * @var		WC_Logger|null		$log			An instance of the WC_Logger class. Defaults to null.
	 */
	public static $log_enabled = null, $log = null;

	/**
	 * Class constructor. Called when an object of this class is instantiated.
	 *
	 * @since	2.0.0
	 * @uses	plugin_basename()					Available as part of the WordPress core since 1.5.
	 * @uses	WC_Payment_Gateway::init_settings()	If the user has not yet saved their settings, it will extract the
	 *												default values from $this->form_fields defined in an ancestral class
	 *												and overridden below.
	 */
	public function __construct() {
		$this->include_path			= dirname(__FILE__) . '/WC_Gateway_Afterpay';
		$this->plugin_url			= WP_PLUGIN_URL . '/' . plugin_basename( realpath(dirname(__FILE__) . '/../') );
		$this->environments			= parse_ini_file("{$this->include_path}/environments.ini", true);

		$this->id					= 'afterpay';
		$this->description			= __( 'Credit cards accepted: Visa, Mastercard', 'woo_afterpay' );
		$this->method_title			= __( 'Afterpay', 'woo_afterpay' );
		$this->method_description	= __( 'Use Afterpay as a credit card processor for WooCommerce.', 'woo_afterpay' );
		//$this->icon; # Note: This URL is ignored; the WC_Gateway_Afterpay::filter_woocommerce_gateway_icon() method fires on the "woocommerce_gateway_icon" Filter hook and generates a complete HTML IMG tag.
		$this->supports				= array('products', 'refunds');
		$this->supported_currencies = array('AUD', 'NZD', 'USD');

		include "{$this->include_path}/form_fields.php";

		$this->init_settings();

		if (array_key_exists('title', $this->settings)) {
			$this->title = $this->settings['title'];
		}
		if (array_key_exists('api-version', $this->settings) && $this->settings['api-version'] == 'v1') {
			$this->order_button_text	= __( 'Proceed to Afterpay' );
		}
		if (array_key_exists('debug', $this->settings)) {
			self::$log_enabled = ($this->settings['debug'] == 'yes');
		}
	}

	/**
	 * Logging method. Using this to log a string will store it in a file that is accessible
	 * from "WooCommerce > System Status > Logs" in the WordPress admin. No FTP access required.
	 *
	 * @param 	string	$message	The message to log.
	 * @uses	WC_Logger::add()
	 */
	public static function log($message) {
		if (is_null(self::$log_enabled)) {
			# Get the settings key for the plugin
			$gateway = new WC_Gateway_Afterpay;
			$settings_key = $gateway->get_option_key();
			$settings = get_option( $settings_key );
			
			if (array_key_exists('debug', $settings)) {
				self::$log_enabled = ($settings['debug'] == 'yes');
			} else {
				self::$log_enabled = false;
			}
		}
		if (self::$log_enabled) {
			if (is_null(self::$log)) {
				self::$log = new WC_Logger;
			}
			self::$log->add( 'afterpay', $message );
		}
	}

	/**
	 * Instantiate the class if no instance exists. Return the instance.
	 *
	 * @since	2.0.0
	 * @return	WC_Gateway_Afterpay
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Register our custom post types. This will automatically create new top-level menu items
	 * in the admin interface.
	 *
	 * Note:	Hooked onto the "init" Action.
	 *
	 * Note:	The names are limited to 20 characters.
	 * @see		https://codex.wordpress.org/Function_Reference/register_post_type
	 *
	 * @since	2.0.0
	 * @see		AfterpayPlugin::__construct()	For hook attachment.
	 * @uses	register_post_type
	 */
	public function register_post_types() {
		register_post_type( 'afterpay_quote', array(
			'labels' => array(
				'name' => __( 'Afterpay Quotes' ),
				'singular_name' => __( 'Afterpay Quote' ),
				'not_found' => __( 'No quotes found.' ),
				'all_items' => __( 'View All' )
			),
			'supports' => array(
				'custom-fields'
			),
			'public' => true,
			'publicly_queriable' => false,
			'show_ui' => false, # Set to true to render Admin UI for this post type.
			'can_export' => false,
			'exclude_from_search' => true,
			'show_in_nav_menus' => false,
			'has_archive' => false,
			'rewrite' => false
		));
	}

	/**
	 * Is the gateway configured? This method returns true if any of the credentials fields are not empty.
	 *
	 * @since	2.0.0
	 * @return	bool
	 * @used-by	self::render_admin_notices()
	 */
	private function is_configured() {
		if (!empty($this->settings['prod-id'])) return true;
		if (!empty($this->settings['prod-secret-key'])) return true;
		if (!empty($this->settings['test-id'])) return true;
		if (!empty($this->settings['test-secret-key'])) return true;
		return false;
	}

	/**
	 * Add the Afterpay gateway to WooCommerce.
	 *
	 * Note:	Hooked onto the "woocommerce_payment_gateways" Filter.
	 *
	 * @since	2.0.0
	 * @see		AfterpayPlugin::__construct()	For hook attachment.
	 * @param	array	$methods				Array of Payment Gateways.
	 * @return	array							Array of Payment Gateways, with Afterpay added.
	 **/
	public function add_afterpay_gateway($methods) {
		$methods[] = 'WC_Gateway_Afterpay';
		return $methods;
	}

	/**
	 * Check whether the gateway is enabled and the cart amount is within the payment limits for this merchant.
	 * If admin is logged in, this check will be skipped.
	 *
	 * Note:	Hooked onto the "woocommerce_available_payment_gateways" Filter.
	 *
	 * @since	2.0.0
	 * @see		Afterpay_Plugin::__construct()	For hook attachment.
	 * @param	array	$gateways				List of enabled gateways.
	 * @uses	WC()							Available in WooCommerce core since 2.1.0.
	 * @return	array							List of enabled gateways, possibly with Afterpay removed.
	 */
	public function check_cart_within_limits($gateways) {
		if (is_admin()) {
			return $gateways;
		}

		if (!array_key_exists('enabled', $this->settings) || $this->settings['enabled'] != 'yes') {
			unset($gateways[$this->id]);
		} else {
			$total = WC()->cart->total;
			if ($total < $this->settings['pay-over-time-limit-min'] || $total > $this->settings['pay-over-time-limit-max']) {
				unset($gateways[$this->id]);
			}
		}

		return $gateways;
	}

	/**
	 * Display Afterpay Assets on Normal Products 
	 * Note:	Hooked onto the "woocommerce_get_price_html" Filter.
	 *
	 * @since	2.0.0
	 * @see		Afterpay_Plugin::__construct()	For hook attachment.
	 * @param 	float $price
	 * @param 	WC_Product $product
	 * @uses	self::print_info_for_listed_products()
	 * @return	string
	 */
	function filter_woocommerce_get_price_html($price, $product) {
		if (is_object($product) && $product instanceof WC_Product_Variation) {
			ob_start();
			$this->print_info_for_listed_products($product);
			$afterpay_html = ob_get_clean();

			return $price . $afterpay_html;
		}
		return $price;
	}

	/**
	 * Display Afterpay Assets on Variable Products' Variations 
	 *
	 * Note:	Hooked onto the "woocommerce_variation_price_html" Filter.
	 * Note:	Hooked onto the "woocommerce_variation_sale_price_html" Filter.
	 *
	 * @since	2.0.0
	 * @see		Afterpay_Plugin::__construct()	For hook attachment.
	 * @param	float					$price
	 * @param	WC_Product_Variation	$variation
	 * @uses	self::print_info_for_listed_products()
	 * @return	string
	 */
	function filter_woocommerce_variation_price_html($price, $variation) {
		if (is_object($variation)) {
			ob_start();
			$this->print_info_for_listed_products($variation);
			$afterpay_html = ob_get_clean();

			return $price . $afterpay_html;
		}
		return $price;
	 }

	/**
	 * The WC_Payment_Gateway::$icon property only accepts a string for the image URL. Since we want
	 * to support high pixel density screens and specifically define the width and height attributes,
	 * this method attaches to a Filter hook so we can build our own HTML markup for the IMG tag.
	 *
	 * Note:	Hooked onto the "woocommerce_gateway_icon" Filter.
	 *
	 * @since	2.0.0
	 * @see		Afterpay_Plugin::__construct()	For hook attachment.
	 * @param	string 	$icon_html		Icon HTML
	 * @param	string 	$gateway_id		Payment Gateway ID
	 * @return	string
	 */
	public function filter_woocommerce_gateway_icon($icon_html, $gateway_id) {
		if ($gateway_id != 'afterpay') {
			return $icon_html;
		}

		ob_start();

		?><img src="https://static.afterpay.com/integration/checkout/logo-afterpay-colour-120x25.png" srcset="https://static.afterpay.com/integration/checkout/logo-afterpay-colour-120x25.png 1x, https://static.afterpay.com/integration/checkout/logo-afterpay-colour-120x25@2x.png 2x, https://static.afterpay.com/integration/checkout/logo-afterpay-colour-120x25@3x.png 3x" width="120" height="25" alt="Afterpay" /><?php

		return ob_get_clean();
	}

	/**
	 * Render admin notices if applicable. This will print an error on every page of the admin if the cron failed to
	 * authenticate on its last attempt.
	 *
	 * Note:	Hooked onto the "admin_notices" Action.
	 * Note:	This runs BEFORE WooCommerce fires its "woocommerce_update_options_payment_gateways_<gateway_id>" actions.
	 *
	 * @since	2.0.0
	 * @uses	get_transient()			Available in WordPress core since 2.8.0
	 * @uses	delete_transient()		Available in WordPress core since 2.8.0
	 * @uses	admin_url()				Available in WordPress core since 2.6.0
	 * @uses	delete_option()
	 * @uses	self::is_configured()
	 */
	public function render_admin_notices() {
		/**
		 * Also change the activation message to include a link to the plugin settings.
		 *
		 * Note:	We didn't add the "is-dismissible" class here because we continually show another
		 *			message similar to this until the API credentials are entered.
		 *
		 * @see		./wp-admin/plugins.php	For the markup that this replaces.
		 * @uses	get_transient()			Available in WordPress core since 2.8.0
		 * @uses	delete_transient()		Available in WordPress core since 2.8.0
		 */
		if (function_exists('get_transient') && function_exists('delete_transient')) {
			if (get_transient( 'afterpay-admin-activation-notice' )) {
				?>
				<div class="updated notice">
					<p><?php _e( 'Plugin <strong>activated</strong>.' ) ?></p>
					<p><?php _e( 'Thank you for choosing Afterpay.', 'woo_afterpay' ); ?> <a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=checkout&section=afterpay' ); ?>"><?php _e( 'Configure Settings.', 'woo_afterpay' ); ?></a></p>
					<p><?php _e( 'Don&rsquo;t have an Afterpay Merchant account yet?', 'woo_afterpay' ); ?> <a href="https://www.afterpay.com/for-merchants" target="_blank"><?php _e( 'Apply online today!', 'woo_afterpay' ); ?></a></p>
				</div>
				<?php
				if (array_key_exists('activate', $_GET) && $_GET['activate'] == 'true') {
					unset($_GET['activate']); # Prevent the default "Plugin *activated*." notice.
				}
				delete_transient( 'afterpay-admin-activation-notice' );
				# No need to decide whether to render any API errors. We've only just activated the plugin.
				return;
			}
		}

		if (array_key_exists('woocommerce_afterpay_enabled', $_POST)) {
			# Since this runs before we handle the POST, we can clear any stored error here.
			delete_option( 'woocommerce_afterpay_api_error' );

			# If we're posting changes to the Afterpay settings, don't pull anything out of the database just yet.
			# This runs before the POST gets handled by WooCommerce, so we can wait until later.
			# If the updated settings fail, that will trigger its own error later.
			return;
		}

		$show_link = true;
		if (array_key_exists('page', $_GET) && array_key_exists('tab', $_GET) && array_key_exists('section', $_GET)) {
			if ($_GET['page'] == 'wc-settings' && $_GET['tab'] == 'checkout' && $_GET['section'] == 'afterpay') {
				# We're already on the Afterpay gateway's settings page. No need for the circular link.
				$show_link = false;
			}
		}

		$error = get_option( 'woocommerce_afterpay_api_error' );
		if (is_object($error) && $this->settings['enabled'] == 'yes') {
			?>
			<div class="error notice">
				<p>
					<strong><?php _e( "Afterpay API Error #{$error->code}:", 'woo_afterpay' ); ?></strong>
					<?php _e( $error->message, 'woo_afterpay' ); ?>
					<?php if (property_exists($error, 'id') && $error->id): ?>
						<em><?php _e( "(Error ID: {$error->id})", 'woo_afterpay' ); ?></em>
					<?php endif; ?>
					<?php if ($show_link): ?>
						<a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=checkout&section=afterpay' ); ?>"><?php _e( 'Please check your Afterpay Merchant settings here.', 'woo_afterpay' ); ?></a>
					<?php endif; ?>
				</p>
			</div>
			<?php
			return;
		}

		# Also include a link to the plugin settings if they haven't been saved yet,
		# unless they have unchecked the Enabled checkbox in the settings.
		if (!$this->is_configured() && $this->settings['enabled'] == 'yes' && $show_link) {
			?>
			<div class="updated notice">
				<p><?php _e( 'Thank you for choosing Afterpay.', 'woo_afterpay' ); ?> <a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=checkout&section=afterpay' ); ?>"><?php _e( 'Configure Settings.', 'woo_afterpay' ); ?></a></p>
				<p><?php _e( 'Don&rsquo;t have an Afterpay Merchant account yet?', 'woo_afterpay' ); ?> <a href="https://www.afterpay.com/for-merchants" target="_blank"><?php _e( 'Apply online today!', 'woo_afterpay' ); ?></a></p>
			</div>
			<?php
			return;
		}
	}

	/**
	 * Admin Panel Options. Overrides the method defined in the parent class.
	 *
	 * @since	2.0.0
	 * @see		WC_Payment_Gateway::admin_options()			For the method that this overrides.
	 * @uses	WC_Settings_API::generate_settings_html()
	 */
	public function admin_options() {
		?>
		<h3><?php _e( 'Afterpay Gateway', 'woo_afterpay' ); ?></h3>
		
		<table class="form-table">
			<?php $this->generate_settings_html(); ?>
		</table>
		<?php
	}

	/**
	 * Generate WYSIWYG input field. This is a pseudo-magic method, called for each form field with a type of "wysiwyg".
	 *
	 * @since	2.0.0
	 * @see		WC_Settings_API::generate_settings_html()	For where this method is called from.
	 * @param	mixed		$key
	 * @param	mixed		$data
	 * @uses	esc_attr()									Available in WordPress core since 2.8.0.
	 * @uses	wp_editor()									Available in WordPress core since 3.3.0.
	 * @return	string										The HTML for the table row containing the WYSIWYG input field.
	 */
	public function generate_wysiwyg_html($key, $data) {
		$html = '';

		$id = str_replace('-', '', $key);
		$class = array_key_exists('class', $data) ? $data['class'] : '';
		$css = array_key_exists('css', $data) ? ('<style>' . $data['css'] . '</style>') : '';
		$name = "{$this->plugin_id}{$this->id}_{$key}";
		$title = array_key_exists('title', $data) ? $data['title'] : '';
		$value = array_key_exists($key, $this->settings) ? esc_attr( $this->settings[$key] ) : '';
		$description = array_key_exists('description', $data) ? $data['description'] : '';

		ob_start();

		include "{$this->include_path}/wysiwyg.html.php";

		$html = ob_get_clean();

		return $html;
	}

	/**
	 * Get the current API URL based on our user settings. Defaults to the Sandbox URL.
	 *
	 * @since	2.0.0
	 * @return	string
	 * @uses 	get_woocommerce_currency()
	 * @used-by	Afterpay_Plugin_Merchant::__construct()
	 */
	public function get_api_url() {

		$currency = get_woocommerce_currency();
		$target_mode = 'api_url';

		if ($currency == "USD") {
			$target_mode = 'api_us_url';
		}

		$api_url = $this->environments[$this->settings['testmode']][$target_mode];

		if (empty($api_url)) {
			$api_url = $this->environments['sandbox'][$target_mode];
		}

		return $api_url;
	}

	/**
	 * Get the current web URL based on our user settings. Defaults to the Sandbox URL.
	 *
	 * @since	2.0.0
	 * @return	string
	 * @uses 	get_woocommerce_currency() 
	 * @used-by	self::render_js()
	 */
	public function get_web_url() {

		$currency = get_woocommerce_currency();
		$target_mode = 'web_url';

		if ($currency == "USD") {
			$target_mode = 'web_us_url';
		}

		$web_url = $this->environments[$this->settings['testmode']][$target_mode];

		if (empty($web_url)) {
			$web_url = $this->environments['sandbox'][$target_mode];
		}

		return $web_url;
	}

	/**
	 * Get the Merchant ID from our user settings. Uses the Sandbox account for all environments except Production.
	 *
	 * @since	2.0.0
	 * @return	string
	 * @used-by	Afterpay_Plugin_Merchant::get_authorization_header()
	 */
	public function get_merchant_id() {
		if ($this->settings['testmode'] == 'production') {
			return $this->settings['prod-id'];
		}
		return $this->settings['test-id'];
	}

	/**
	 * Get the Secret Key from our user settings. Uses the Sandbox account for all environments except Production.
	 *
	 * @since	2.0.0
	 * @return	string
	 * @used-by	Afterpay_Plugin_Merchant::get_authorization_header()
	 */
	public function get_secret_key() {
		if ($this->settings['testmode'] == 'production') {
			return $this->settings['prod-secret-key'];
		}
		return $this->settings['test-secret-key'];
	}

	/**
	 * Get the API version from our user settings.
	 *
	 * @since	2.0.0
	 * @return	string
	 * @used-by	Afterpay_Plugin_Merchant::__construct()
	 */
	public function get_api_version() {
		return $this->settings['api-version'];
	}

	/**
	 * Convert the global $post object to a WC_Product instance.
	 *
	 * @since	2.0.0
	 * @global	WP_Post	$post
	 * @uses	wc_get_product()	Available as part of the WooCommerce core plugin since 2.2.0.
	 *								Also see:	WC()->product_factory->get_product()
	 *								Also see:	WC_Product_Factory::get_product()
	 * @return	WC_Product
	 * @used-by self::process_and_print_afterpay_paragraph()
	 */
	private function get_product_from_the_post() {
		global $post;

		if (function_exists('wc_get_product')) {
			$product = wc_get_product( $post->ID );
		} else {
			$product = new WC_Product( $post->ID );
		}

		return $product;
	}

	/**
	 * Is the given product supported by the Afterpay gateway?
	 *
	 * Note:	Some products may not be allowed to be purchased with Afterpay unless
	 *			combined with other products to lift the cart total above the merchant's
	 *			minimum. By default, this function will not check the merchant's
	 *			minimum. Set $alone to true to check if the product can be
	 *			purchased on its own.
	 *
	 * @since	2.0.0
	 * @param	WC_Product	$product									The product in question, in the form of a WC_Product object.
	 * @param	bool		$alone										Whether to view the product on its own.
	 *																	This affects whether the minimum setting is considered.
	 * @uses	WC_Product::get_type()									Possibly available as part of the WooCommerce core plugin since 2.6.0.
	 * @uses	wc_get_price_including_tax()							Available as part of the WooCommerce core plugin since 3.0.0.
	 * @uses	WC_Abstract_Legacy_Product::get_price_including_tax()	Possibly available as part of the WooCommerce core plugin since 2.6.0. Deprecated in 3.0.0.
	 * @uses	WC_Product::get_price()									Possibly available as part of the WooCommerce core plugin since 2.6.0.
	 * @uses	WC_Product::get_price_html()							Possibly available as part of the WooCommerce core plugin since 2.6.0.
	 * @uses	apply_filters()											Available in WordPress core since 0.17.
	 * @return	bool													Whether or not the given product is eligible for Afterpay.
	 * @used-by self::process_and_print_afterpay_paragraph()
	 */
	private function is_product_supported($product, $alone = false) {
		if (!isset($this->settings['enabled']) || $this->settings['enabled'] != 'yes') {
			return false;
		}

		$product_type = $product->get_type();
		if (preg_match('/subscription/', $product_type)) {
			# Subscription products are not supported by Afterpay.
			return false;
		}

		if (function_exists('wc_get_price_including_tax')) {
			$price = wc_get_price_including_tax( $product );
		} elseif (method_exists($product, 'get_price_including_tax')) {
			$price = $product->get_price_including_tax();
		} else {
			$price = $product->get_price();
		}

		if ($price < 0.04 || $price > $this->settings['pay-over-time-limit-max']) {
			# Free items are not supported by Afterpay.
			# If the price exceeds the maximum for this merchant, the product is not supported.
			return false;
		}

		if ($alone && $price < $this->settings['pay-over-time-limit-min']) {
			# If the product is viewed as being on its own and priced lower that the merchant's minimum, it will be considered as not supported.
			return false;
		}

		# Allow other plugins to exclude Afterpay from products that would otherwise be supported.
		return (bool)apply_filters( 'afterpay_is_product_supported', true, $product, $alone );
	}

	/**
	 * Is the the website currency supported by the Afterpay gateway?
	 *
	 * Note:	Some products may not be allowed to be purchased with Afterpay unless
	 *			combined with other products to lift the cart total above the merchant's
	 *			minimum. By default, this function will not check the merchant's
	 *			minimum. Set $alone to true to check if the product can be
	 *			purchased on its own.
	 *
	 * @since	2.0.0
	 * @uses	get_woocommerce_currency()									Available in WooCommerce core since 2.6.0.
	 * @used-by self::process_and_print_afterpay_paragraph()
	 */
	private function is_currency_supported() {
		$store_currency = strtoupper(get_woocommerce_currency());
		return in_array($store_currency, $this->supported_currencies);
	}

	/**
	 * Process the HTML for the Afterpay Modal Window
	 *
	 * @since	2.0.0-rc3
	 * @param	string	$html		
	 * @return	string
	 * @uses	parse_ini_file()			parsing the Modal Window Assets
	 * @uses	get_woocommerce_currency()	determine website currency
	 * @used-by	process_and_print_afterpay_paragraph()
	 * @used-by	render_schedule_on_cart_page()
	 * @used-by	payment_fields()
	 */
	private function apply_modal_window($html) {
		$assets					= 	parse_ini_file('WC_Gateway_Afterpay/assets.ini', true);
		$currency				=	get_woocommerce_currency();

		if (!empty($assets[strtolower($currency)])) {
			$region_assets		=	$assets[strtolower($currency)];
			$modal_window_asset = 	$region_assets['modal_window'];
		}
		else {
			$modal_window_asset = 	$assets['aud']['modal_window'];
		}

		return $html . $modal_window_asset;
	}

	/**
	 * Process the HTML from one of the rich text editors and output the converted string.
	 *
	 * @since	2.0.0
	 * @param	string				$html								The HTML with replace tags such as [AMOUNT].
	 * @param	string				$output_filter
	 * @param	WC_Product|null		$product							The product for which to print instalment info.
	 * @uses	self::get_product_from_the_post()
	 * @uses	self::is_product_supported()
	 * @uses	self::apply_modal_window()
	 * @uses	wc_get_price_including_tax()							Available as part of the WooCommerce core plugin since 3.0.0.
	 * @uses	WC_Abstract_Legacy_Product::get_price_including_tax()	Possibly available as part of the WooCommerce core plugin since 2.6.0. Deprecated in 3.0.0.
	 * @uses	WC_Product::get_price()									Possibly available as part of the WooCommerce core plugin since 2.6.0.
	 * @uses	self::display_price_html()
	 * @uses	apply_filters()											Available in WordPress core since 0.17.
	 * @used-by	self::print_info_for_product_detail_page()
	 * @used-by	self::print_info_for_listed_products()
	 */
	private function process_and_print_afterpay_paragraph($html, $output_filter, $product = null) {
		if (is_null($product)) {
			$product = $this->get_product_from_the_post();
		}

		if (!$this->is_product_supported($product, true)) {
			# Don't display anything on the product page if the product is not supported when purchased on its own.
			return;
		}

		if (!$this->is_currency_supported()) {
			# Don't display anything on the product page if the website currency is not within supported currencies.
			return;
		}

		$of_or_from = 'of';
		$price = NAN;

		/**
		 * Note: See also: WC_Product_Variable::get_variation_price( $min_or_max = 'min', $include_taxes = false )
		 */
		if ($product->has_child()) {
			$child_product_ids = $product->get_children();
			if (count($child_product_ids) > 1) {
				$min_child_product_price = NAN;
				$max_child_product_price = NAN;
				foreach ($child_product_ids as $child_product_id) {
					$child_product = wc_get_product( $child_product_id );
					if (function_exists('wc_get_price_including_tax')) {
						$child_product_price = wc_get_price_including_tax( $child_product );
					} elseif (method_exists($child_product, 'get_price_including_tax')) {
						$child_product_price = $child_product->get_price_including_tax();
					} elseif (method_exists($child_product, 'get_price')) {
						$child_product_price = $child_product->get_price();
					} else {
						$child_product_price = 0.00;
					}
					if ($child_product_price >= 0.04 && $child_product_price >= $this->settings['pay-over-time-limit-min'] && $child_product_price <= $this->settings['pay-over-time-limit-max']) {
						if (is_nan($min_child_product_price) || $child_product_price < $min_child_product_price) {
							$min_child_product_price = $child_product_price;
						}
						if (is_nan($max_child_product_price) || $child_product_price > $max_child_product_price) {
							$max_child_product_price = $child_product_price;
						}
					}
				}
				if (!is_nan($min_child_product_price) && $max_child_product_price > $min_child_product_price) {
					$of_or_from = 'from';
					$price = $min_child_product_price;
				}
			}
		}

		if (is_nan($price)) {
			if (function_exists('wc_get_price_including_tax')) {
				$price = wc_get_price_including_tax( $product );
			} elseif (method_exists($product, 'get_price_including_tax')) {
				$price = $product->get_price_including_tax();
			} else {
				$price = $product->get_price();
			}
		}
		$amount = $this->display_price_html( round($price / 4, 2) );

		$html = str_replace(array(
			'[OF_OR_FROM]',
			'[AMOUNT]'
		), array(
			$of_or_from,
			$amount
		), $html);

		# Execute shortcodes on the string after running internal replacements,
		# but before applying filters and rendering.
		$html = do_shortcode( "<p class=\"afterpay-payment-info\">{$html}</p>" );

		# Add the Modal Window to the page
		# Website Admin have no access to the Modal Window codes for data integrity reasons
		$html = $this->apply_modal_window($html);
		

		# Allow other plugins to maniplulate or replace the HTML echoed by this funtion.
		echo apply_filters( $output_filter, $html, $product, $price );
	}

	/**
	 * Print a paragraph of Afterpay info onto the individual product pages if enabled and the product is valid.
	 *
	 * Note:	Hooked onto the "woocommerce_single_product_summary" Action.
	 *
	 * @since	2.0.0
	 * @see		Afterpay_Plugin::__construct()							For hook attachment.
	 * @param	WC_Product|null		$product							The product for which to print instalment info.
	 * @uses	self::process_and_print_afterpay_paragraph()
	 */
	public function print_info_for_product_detail_page($product = null) {
		if (!isset($this->settings['show-info-on-product-pages']) || $this->settings['show-info-on-product-pages'] != 'yes' || empty($this->settings['product-pages-info-text'])) {
			# Don't display anything on product pages unless the "Payment info on individual product pages"
			# box is ticked and there is a message to display.
			return;
		}

		$this->process_and_print_afterpay_paragraph($this->settings['product-pages-info-text'], 'afterpay_html_on_individual_product_pages', $product);
	}

	/**
	 * Print a paragraph of Afterpay info onto each product item in the shop loop if enabled and the product is valid.
	 *
	 * Note:	Hooked onto the "woocommerce_after_shop_loop_item_title" Action.
	 *
	 * @since	2.0.0
	 * @see		Afterpay_Plugin::__construct()							For hook attachment.
	 * @param	WC_Product|null		$product							The product for which to print instalment info.
	 * @uses	self::process_and_print_afterpay_paragraph()
	 * @used-by	self::filter_woocommerce_get_price_html()
	 * @used-by	self::filter_woocommerce_variation_price_html()
	 */
	public function print_info_for_listed_products($product = null) {
		if (!isset($this->settings['show-info-on-category-pages']) || $this->settings['show-info-on-category-pages'] != 'yes' || empty($this->settings['category-pages-info-text'])) {
			# Don't display anything on product items within the shop loop unless
			# the "Payment info on product listing pages" box is ticked
			# and there is a message to display.
			return;
		}

		$this->process_and_print_afterpay_paragraph($this->settings['category-pages-info-text'], 'afterpay_html_on_product_thumbnails', $product);
	}

	/**
	 * Format float as currency.
	 *
	 * @since	2.0.0
	 * @param	float $price
	 * @return	string The formatted price HTML.
	 * @used-by	self::process_and_print_afterpay_paragraph()
	 * @used-by	self::render_schedule_on_cart_page()
	 */
	private function display_price_html($price) {
		if (function_exists('wc_price')) {
			return wc_price($price);
		} elseif (function_exists('woocommerce_price')) {
			return woocommerce_price($price);
		}
		return '$' . number_format($price, 2, '.', ',');
	}

	/**
	 * Instalment calculation.
	 *
	 * @since	2.0.0
	 * @see		PaymentScheduleManager::generateSchedule()	From java core infrastructure.
	 * @param	float	$order_amount						The order amount in dollars.
	 * @param	int		$number_of_payments					The number of payments. Defaults to 4.
	 * @return	array										The instalment amounts in dollars.
	 * @used-by	self::render_schedule_on_cart_page()
	 * @used-by	self::payment_fields()
	 */
	private function generate_payment_schedule($order_amount, $number_of_payments = 4) {
		$order_amount_in_cents = $order_amount * 100;
		$instalment_amount_in_cents = round($order_amount_in_cents / $number_of_payments, 0, PHP_ROUND_HALF_UP);
		$cents_left_over = $order_amount_in_cents - ($instalment_amount_in_cents * $number_of_payments);

		$schedule = array();

		for ($i = 0; $i < $number_of_payments; $i++) {
			$schedule[$i] = $instalment_amount_in_cents / 100;
		}

		$schedule[$i - 1] += $cents_left_over / 100;

		return $schedule;
	}

	/**
	 * Render Afterpay elements (logo and payment schedule) on Cart page.
	 *
	 * This is dependant on all of the following criteria being met:
	 *		- The Afterpay Payment Gateway is enabled.
	 *		- The cart total is valid and within the merchant payment limits.
	 *		- The "Payment Info on Cart Page" box is ticked and there is a message to display.
	 *		- All of the items in the cart are considered eligible to be purchased with Afterpay.
	 *
	 * Note:	Hooked onto the "woocommerce_cart_totals_after_order_total" Action.
	 *
	 * @since	2.0.0
	 * @see		Afterpay_Plugin::__construct()								For hook attachment.
	 * @uses	self::generate_payment_schedule()
	 * @uses	self::display_price_html()
	 * @uses	self::apply_modal_window()
	 * @uses	apply_filters()												Available in WordPress core since 0.17.
	 */
	public function render_schedule_on_cart_page() {
		if (!array_key_exists('enabled', $this->settings) || $this->settings['enabled'] != 'yes') {
			return;
		} else {
			$total = WC()->cart->total;
			if ($total <= 0 || $total < $this->settings['pay-over-time-limit-min'] || $total > $this->settings['pay-over-time-limit-max']) {
				return;
			}
		}

		if (!isset($this->settings['show-info-on-cart-page']) || $this->settings['show-info-on-cart-page'] != 'yes' || empty($this->settings['cart-page-info-text'])) {
			return;
		}

		foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
			$product = $cart_item['data'];
			if (!$this->is_product_supported($product)) {
				return;
			}
		}

		$schedule = $this->generate_payment_schedule(WC()->cart->total);
		$amount = $this->display_price_html($schedule[0]);

		$html = str_replace(array(
			'[AMOUNT]'
		), array(
			$amount
		), $this->settings['cart-page-info-text']);

		# Execute shortcodes on the string before applying filters and rendering it.
		$html = do_shortcode( $html );

		# Add the Modal Window to the page
		# Website Admin have no access to the Modal Window codes for data integrity reasons
		$html = $this->apply_modal_window($html);

		# Allow other plugins to maniplulate or replace the HTML echoed by this funtion.
		echo apply_filters( 'afterpay_html_on_cart_page', $html );
	}

	/**
	 * Display as a payment option on the checkout page.
	 *
	 * Note:	This overrides the method defined in the parent class.
	 *
	 * @since	2.0.0
	 * @see		WC_Payment_Gateway::payment_fields()						For the method that this overrides.
	 * @uses	WC()														Available in WooCommerce core since 2.1.0.
	 * @uses	Afterpay_Plugin_Merchant::get_payment_types_for_amount()	If configured to use API v0.
	 * @uses	get_woocommerce_currency()									Available in WooCommerce core since 2.6.0.
	 * @uses	self::generate_payment_schedule()
	 * @uses	self::apply_modal_window()
	 * @uses	apply_filters()												Available in WordPress core since 0.17.
	 */
	public function payment_fields() {
		$order_total = WC()->cart->total;

		if ($this->settings['api-version'] == 'v0') {
			$merchant = new Afterpay_Plugin_Merchant;
			$payment_types = $merchant->get_payment_types_for_amount($order_total);

			if (count($payment_types) == 0) {
				echo "Error 004 - Unfortunately, orders of this value cannot be processed through Afterpay.";
				return false;
			}
		} elseif ($this->settings['api-version'] == 'v1') {
			$limit_min = $this->settings['pay-over-time-limit-min'];
			$limit_max = $this->settings['pay-over-time-limit-max'];
			$store_currency = strtoupper(get_woocommerce_currency());

			if ($order_total < $limit_min) {
				# Order total is less than the minimum payment allowed for this merchant.
				self::log("Afterpay hidden from checkout because the order total is outside merchant payment limits. ('{$order_total}' < '{$limit_min}')");
				echo "Error 001 - Unfortunately, orders of this value cannot be processed through Afterpay.";
				return false;
			} elseif ($order_total > $limit_max) {
				# Order total is more than the maximum payment allowed for this merchant.
				self::log("Afterpay hidden from checkout because the order total is outside merchant payment limits. ('{$order_total}' > '{$limit_max}')");
				echo "Error 002 - Unfortunately, orders of this value cannot be processed through Afterpay.";
				return false;
			} elseif (!$this->is_currency_supported()) {
				# WooCommerce is not using AUD / NZD.
				self::log("Afterpay hidden from checkout because the store currency is not supported. ('{$store_currency}')");
				echo "Error 003 - Unfortunately, orders of this value cannot be processed through Afterpay.";
				return false;
			}
		}

		$instalments = $this->generate_payment_schedule($order_total);

		# Give other plugins a chance to manipulate or replace the HTML echoed by this funtion.
		ob_start();
		include "{$this->include_path}/instalments.html.php";
		
		$html = ob_get_clean();

		# Add the Modal Window to the page
		# Website Admin have no access to the Modal Window codes for data integrity reasons
		$html = $this->apply_modal_window($html);

		echo apply_filters( 'afterpay_html_at_checkout', $html, $order_total, $instalments );
	}

	/**
	 * Build a return URL based on the current site URL.
	 *
	 * Note:	The Afterpay API appends a string in the following format:
	 *			"?&status=<STATUS>&token=<TOKEN>"
	 *			This can corrupt existing querystring parameters.
	 *			This is fixed by injecting the following into $extra_args: 'q' => '', which
	 *			suffixes our Return URLs with "&q=". This means we'll end up with $_GET['q'] => '?'
	 *			instead of having a question mark injected into one of our important parameters.
	 *
	 * @since	2.0.0
	 * @param	int		$p			The Post ID of the Afterpay_Quote item.
	 * @param	string	$action		The name of the action that should be taken on the return page.
	 * @param	string	$nonce		The WordPress Nonce that was generated for this URL.
	 * @param	array	$extra_args	Any additional querystring parameters to be incorporated into the Return URL.
	 * @return	string
	 * @used-by	self::process_payment()
	 * @used-by	self::override_order_creation()
	 */
	public function build_afterpay_quote_url($p, $action, $nonce, $extra_args = array()) {
		$site_url = get_site_url();
		$site_url_components = parse_url($site_url);
		$return_url = '';

		# Scheme:

		if (isset($site_url_components['scheme'])) {
			$return_url .= $site_url_components['scheme'] . '://';
		}

		# Host:

		if (isset($site_url_components['host'])) {
			$return_url .= $site_url_components['host'];
		}

		# Port:

		if (isset($site_url_components['port'])) {
			$return_url .= ':' . $site_url_components['port'];
		}

		# Path:

		if (isset($site_url_components['path'])) {
			$return_url .= rtrim($site_url_components['path'], '/') . '/';
		} else {
			$return_url .= '/';
		}

		# Query:

		$existing_args = array();

		if (isset($site_url_components['query'])) {
			parse_str($site_url_components['query'], $existing_args);
		}

		$args = array(
			'post_type' => 'afterpay_quote',
			'p' => $p,
			'action' => $action,
			'nonce' => $nonce
		);

		$args = array_merge($existing_args, $args, $extra_args);

		$return_url .= '?' . http_build_query($args);

		# Fragment:

		if (isset($site_url_components['fragment'])) {
			$return_url .= '#' . $site_url_components['fragment'];
		}

		# Return the constructed URL.

		return $return_url;
	}

	/**
	 * Order Creation - Part 1 of 2: Afterpay Quote.
	 *
	 * Override WooCommerce's create_order function and make our own order-quote object. We will manually
	 * convert this into a proper WC_Order object later, if the checkout completes successfully. Part of the data
	 * collected here is submitted to the Afterpay API to generate a token, the rest is persisted to the
	 * database to build the WC_Order object. Based on WooCommerce 2.6.8.
	 *
	 * Note:	This is only applicable for API v1.
	 *
	 * Note:	This needs to follow the WC_Checkout::create_order() method very closely. In order to properly
	 * 			create the WC_Order object later, we need to make sure we're storing all of the data that will be
	 * 			needed later. If it fails, it needs to return an integer that evaluates to true in order to bypass the
	 * 			standard WC_Order creation process.
	 *
	 * Note:	Hooked onto the "woocommerce_create_order" Filter.
	 *
	 * @since	2.0.0
	 * @see		Afterpay_Plugin::__construct()				For hook attachment.
	 * @see		WC_Checkout::create_order()					For the method we're overriding.
	 * @see		self::create_wc_order_from_afterpay_quote()	For where the data persisted by this method is used to construct
	 *														a WC_Order object.
	 * @param	null		$null							I can't be bothered figuring out why WC passes this param.
	 * @param	WC_Checkout	$checkout						The current checkout instance.
	 * @uses	wp_insert_post()							Available in WordPress core.
	 * @uses	is_wp_error()								Available in WordPress core.
	 * @uses	WC()										Available in WooCommerce core since 2.1.0.
	 * @uses	get_woocommerce_currency()					Available in WooCommerce core since 2.6.0.
	 * @uses	WC_Checkout::get_posted_address_data()		Available in WooCommerce core since 2.1.0.
	 * @uses	WC_Cart::get_cart()
	 * @uses	version_compare()							Available in PHP since 4.1
	 * @uses	WC_Product::get_sku()
	 * @uses	WC_Cart::get_fees()
	 * @uses	WC_Cart::has_discount()
	 * @uses	WC_Cart::get_coupons()
	 * @uses	WC_Cart::get_coupon_discount_amount()
	 * @uses	WC_Cart::get_coupon_discount_tax_amount()
	 * @uses	wp_create_nonce()
	 * @uses	self::build_afterpay_quote_url()
	 * @uses	WC_Cart::get_tax_amount()
	 * @uses	WC_Cart::get_shipping_tax_amount()
	 * @uses	WC_Shipping::get_packages()
	 * @uses	WC_Shipping_Rate::get_meta_data()
	 * @uses	Afterpay_Plugin_Merchant::get_order_token_for_afterpay_quote()
	 * @uses	get_current_user_id()						Available in WordPress core since 2.0.3.
	 * @uses	wc_clean()									Available in WooCommerce core since 2.6.0.
	 * @uses	WC_Cart::get_cart_for_session()
	 * @uses	WC_Cart::get_cart_discount_total()
	 * @uses	WC_Cart::get_cart_discount_tax_total()
	 * @uses	WC_Cart::needs_shipping()
	 * @return	int|void
	 */
	public function override_order_creation($null, $checkout) {
		# Only override the order creation if the customer is paying with Afterpay.

		if ($checkout->posted['payment_method'] != 'afterpay') {
			return;
		}

		# Only override the order creation if the gateway is configured to use API v1.

		if ($this->settings['api-version'] != 'v1') {
			return;
		}
		
		# Create an Afterpay Quote object. We need to do this before sending the order data to the API
		# so that we can include the quote ID in the callback URLs.

		$post_id = wp_insert_post( array(
			'post_content' => 'Thank you for your order. Now redirecting you to Afterpay to complete your payment...',
			'post_title' => 'Afterpay Order',
			'post_status' => 'publish',
			'post_type' => 'afterpay_quote'
		), true );

		if (!is_wp_error( $post_id )) {
			# Log the ID and Permalink of the newly created post.

			self::log("New Afterpay Quote generated with ID:{$post_id} and permalink:\"" . get_permalink( $post_id ) . "\"");

			# Store references to the WooCommerce WC_Cart, WC_Shipping and WC_Session objects.

			$cart = WC()->cart;
			$shipping = WC()->shipping;
			$session = WC()->session;

			# Define the array for the data we will send to the Afterpay API.

			$data = array();

			# Total amount.

			$data['totalAmount'] = array(
				'amount' => number_format($cart->total, 2, '.', ''),
				'currency' => get_woocommerce_currency()
			);

			# Billing address.

			$billing_address = array();
			if ( $checkout->checkout_fields['billing'] ) {
				foreach ( array_keys( $checkout->checkout_fields['billing'] ) as $field ) {
					$field_name = str_replace( 'billing_', '', $field );
					$billing_address[ $field_name ] = $checkout->get_posted_address_data( $field_name );
				}
			}
			# Handle Stateless Countries
			if(empty($billing_address['state'])) {
				$billing_address['state'] = $billing_address['city'];
			}

			$data['billing'] = array(
				'name' => $billing_address['first_name'] . ' ' . $billing_address['last_name'],
				'line1' => $billing_address['address_1'],
				'line2' => $billing_address['address_2'],
				'suburb' => $billing_address['city'],
				'state' => $billing_address['state'],
				'postcode' => $billing_address['postcode'],
				'countryCode' => $billing_address['country'],
				'phone' => $billing_address['phone']
			);

			# Shipping address.

			$shipping_address = array();
			if ( $checkout->checkout_fields['shipping'] ) {
				foreach ( array_keys( $checkout->checkout_fields['shipping'] ) as $field ) {
					$field_name = str_replace( 'shipping_', '', $field );
					$shipping_address[ $field_name ] = $checkout->get_posted_address_data( $field_name, 'shipping' );
				}
			}

			# Handle Stateless Countries
			if(empty($shipping_address['state'])) {
				$shipping_address['state'] = $shipping_address['city'];
			}

			$data['shipping'] = array(
				'name' => $shipping_address['first_name'] . ' ' . $shipping_address['last_name'],
				'line1' => $shipping_address['address_1'],
				'line2' => $shipping_address['address_2'],
				'suburb' => $shipping_address['city'],
				'state' => $shipping_address['state'],
				'postcode' => $shipping_address['postcode'],
				'countryCode' => $shipping_address['country'],
				'phone' => $shipping_address['phone']
			);

			# Consumer.

			$data['consumer'] = array(
				'phoneNumber' => $billing_address['phone'],
				'givenNames' => $billing_address['first_name'],
				'surname' => $billing_address['last_name'],
				'email' => $billing_address['email']
			);

			# Cart items.

			$data['items'] = array(); # Store data for the Afterpay API.
			$cart_items = array(); # Store data to build a WC_Order object later.

			foreach ($cart->get_cart() as $cart_item_key => $values) {
				$product = $values['data'];

				if (version_compare( WC_VERSION, '3.0.0', '>=' )) {
					$cart_items[$cart_item_key] = array(
						'props' => array(
							'quantity'     => $values['quantity'],
							'variation'    => $values['variation'],
							'subtotal'     => $values['line_subtotal'],
							'total'        => $values['line_total'],
							'subtotal_tax' => $values['line_subtotal_tax'],
							'total_tax'    => $values['line_tax'],
							'taxes'        => $values['line_tax_data']
						)
					);
					if ($product) {
						$cart_items[$cart_item_key]['id'] = $product->get_id();
						$cart_items[$cart_item_key]['props'] = array_merge($cart_items[$cart_item_key]['props'], array(
							'name'         => $product->get_name(),
							'tax_class'    => $product->get_tax_class(),
							'product_id'   => $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id(),
							'variation_id' => $product->is_type( 'variation' ) ? $product->get_id() : 0
						));
					}
				} else {
					$cart_items[$cart_item_key] = array(
						'class' => get_class($product),
						'id' => $product->id,
						'quantity' => $values['quantity'],
						'variation' => $values['variation'],
						'totals' => array(
							'subtotal' => $values['line_subtotal'],
							'subtotal_tax' => $values['line_subtotal_tax'],
							'total' => $values['line_total'],
							'tax' => $values['line_tax'],
							'tax_data' => $values['line_tax_data'] # Since WooCommerce 2.2
						)
					);
				}

				$data['items'][] = array(
					'name' => $product->post->post_title,
					'sku' => $product->get_sku(),
					'quantity' => $values['quantity'],
					'price' => array(
						'amount' => number_format((($values['line_subtotal'] + $values['line_subtotal_tax']) / $values['quantity']), 2, '.', ''),
						'currency' => get_woocommerce_currency()
					)
				);
			}

			# Fees.

			$cart_fees = array();

			foreach ( $cart->get_fees() as $fee_key => $fee ) {
				$cart_fees[$fee_key] = $fee;
			}

			# Discounts.

			if ($cart->has_discount()) {
				# The total is stored in $cart->get_total_discount(), but we should also be able to get a list.
				$data['discounts'] = array();
				foreach ($cart->coupon_discount_amounts as $code => $amount) {
					$data['discounts'][] = array(
						'displayName' => $code,
						'amount' => array(
							'amount' => number_format($amount, 2, '.', ''),
							'currency' => get_woocommerce_currency()
						)
					);
				}
			}

			# Coupons.

			$cart_coupons = array();
			foreach ($cart->get_coupons() as $code => $coupon) {
				$cart_coupons[$code] = array(
					'discount_amount' => $cart->get_coupon_discount_amount($code),
					'discount_tax_amount' => $cart->get_coupon_discount_tax_amount($code),
					'coupon' => $coupon
				);
			}

			# Merchant callback URLs.

			$afterpay_fe_confirm_nonce = wp_create_nonce( "afterpay_fe_confirm_nonce-{$post_id}" );
			$afterpay_fe_cancel_nonce = wp_create_nonce( "afterpay_fe_cancel_nonce-{$post_id}" );

			$fe_confirm_url = $this->build_afterpay_quote_url($post_id, 'fe-confirm', $afterpay_fe_confirm_nonce, array('q' => ''));
			$fe_cancel_url = $this->build_afterpay_quote_url($post_id, 'fe-cancel', $afterpay_fe_cancel_nonce, array('q' => ''));

			$data['merchant'] = array(
				'redirectConfirmUrl' => $fe_confirm_url,
				'redirectCancelUrl' => $fe_cancel_url
			);

			# Taxes.

			$data['taxAmount'] = array(
				'amount' => number_format($cart->tax_total + $cart->shipping_tax_total, 2, '.', ''),
				'currency' => get_woocommerce_currency()
			);

			$cart_taxes = array();
			foreach (array_keys($cart->taxes + $cart->shipping_taxes) as $tax_rate_id) {
				if ($tax_rate_id && $tax_rate_id !== apply_filters( 'woocommerce_cart_remove_taxes_zero_rate_id', 'zero-rated' )) {
					$cart_taxes[$tax_rate_id] = array(
						'tax_amount' => $cart->get_tax_amount($tax_rate_id),
						'shipping_tax_amount' => $cart->get_shipping_tax_amount($tax_rate_id)
					);
				}
			}

			# Shipping costs.

			if (!is_null($cart->shipping_total) && $cart->shipping_total > 0) {
				$data['shippingAmount'] = array(
					'amount' => number_format($cart->shipping_total + $cart->shipping_tax_total, 2, '.', ''),
					'currency' => get_woocommerce_currency()
				);
			}

			# Shipping methods.

			if (version_compare( WC_VERSION, '3.0.0', '>=' )) {
				$chosen_shipping_methods = $session->get( 'chosen_shipping_methods' );

				/**
				 * Don't send an empty shipping address object to Afterpay if shipping is not needed.
				 *
				 * @see		WC_Order::needs_shipping_address()	https://docs.woocommerce.com/wc-apidocs/source-class-WC_Order.html#1243-1266
				 */
				$methods_without_shipping = apply_filters( 'woocommerce_order_hide_shipping_address', array('local_pickup') );
				$needs_address = false;

				if (!empty($chosen_shipping_methods)) {
					foreach ($chosen_shipping_methods as $shiping_method_id) {
						$shipping_method_name = current(explode(':', $shiping_method_id));
						if (!in_array($shipping_method_name, $methods_without_shipping)) {
							$needs_address = true;
							break;
						}
					}
				}

				if (!$needs_address) {
					unset($data['shipping']);
				}
			} else {
				/**
				 * Don't send an empty shipping address object to Afterpay if shipping is not needed.
				 * Note that prior to WooCommerce 3.0, this only prevents the empty object from being sent,
				 * it doesn't care if it was needed or not.
				 */
				$needs_address = false;

				if (array_key_exists('shipping', $data) && is_array($data['shipping']) && !empty($data['shipping'])) {
					foreach ($data['shipping'] as $field_name => $field_value) {
						if (!empty($field_value)) {
							$needs_address = true;
							break;
						}
					}
				}

				if (!$needs_address) {
					unset($data['shipping']);
				}
			}

			# Shipping packages.

			$shipping_packages = array();

			foreach ($shipping->get_packages() as $package_key => $package) {
				if (isset($package['rates'][$checkout->shipping_methods[$package_key]])) {
					$package = $package['rates'][$checkout->shipping_methods[$package_key]];
					$shipping_packages[$package_key] = array(
						'id' => $package->id,
						'label' => $package->label,
						'cost' => $package->cost,
						'taxes' => $package->taxes,
						'method_id' => $package->method_id,
						'meta_data' => $package->get_meta_data()
					);
				}
			}

			# Send the order data to Afterpay to get a token.

			$merchant = new Afterpay_Plugin_Merchant;
			$response_obj = $merchant->get_order_token_for_afterpay_quote($data);

			if ($response_obj !== false) {
				self::log("WP_Post #{$post_id} given Afterpay Order token: {$response_obj->token}");

				# Generate a nonce for the preauth URL.
				$afterpay_preauth_nonce = wp_create_nonce( "afterpay_preauth_nonce-{$post_id}" );

				# Add the meta data to the Afterpay_Quote post record.
				add_post_meta( $post_id, 'status', 'pending' );
				add_post_meta( $post_id, 'token', $response_obj->token );
				add_post_meta( $post_id, 'token_expiry', $response_obj->expires ); # E.g.: "2016-05-10T13:14:01Z"
				add_post_meta( $post_id, 'customer_id', apply_filters( 'woocommerce_checkout_customer_id', get_current_user_id() ) ); # WC_Checkout::$customer_id is private. See WC_Checkout::process_checkout() for how it populates this property.
				add_post_meta( $post_id, 'cart_hash', md5( json_encode( wc_clean( $cart->get_cart_for_session() ) ) . $cart->total ) );
				add_post_meta( $post_id, 'cart_shipping_total', $cart->shipping_total );
				add_post_meta( $post_id, 'cart_shipping_tax_total', $cart->shipping_tax_total );
				add_post_meta( $post_id, 'cart_discount_total', $cart->get_cart_discount_total() );
				add_post_meta( $post_id, 'cart_discount_tax_total', $cart->get_cart_discount_tax_total() );
				add_post_meta( $post_id, 'cart_tax_total', $cart->tax_total );
				add_post_meta( $post_id, 'cart_total', $cart->total );
				add_post_meta( $post_id, 'cart_items', json_encode($cart_items) );
				add_post_meta( $post_id, 'cart_fees', json_encode($cart_fees) );
				add_post_meta( $post_id, 'cart_coupons', json_encode($cart_coupons) );
				add_post_meta( $post_id, 'cart_taxes', json_encode($cart_taxes) );
				add_post_meta( $post_id, 'cart_needs_shipping', (bool)$cart->needs_shipping() );
				if (version_compare( WC_VERSION, '3.0.0', '>=' )) {
					add_post_meta( $post_id, 'chosen_shipping_methods', json_encode($chosen_shipping_methods) );
				}
				add_post_meta( $post_id, 'shipping_packages', json_encode($shipping_packages) );
				add_post_meta( $post_id, 'billing_address', json_encode($billing_address) );
				add_post_meta( $post_id, 'shipping_address', json_encode($shipping_address) );
				add_post_meta( $post_id, 'api_data', json_encode($data) );
				add_post_meta( $post_id, 'posted', json_encode($checkout->posted) );
				add_post_meta( $post_id, 'afterpay_preauth_nonce', $afterpay_preauth_nonce );
				add_post_meta( $post_id, 'afterpay_fe_confirm_nonce', $afterpay_fe_confirm_nonce );
				add_post_meta( $post_id, 'afterpay_fe_cancel_nonce', $afterpay_fe_cancel_nonce );

				# Return the ID of the Afterpay_Quote if you want
				# to let WooCommerce trigger the payment process.

				//return $post_id;

				# Or, execute this ourselves to skip
				# the action/filter hooks, including:
				# - "woocommerce_checkout_order_processed"
				# - "woocommerce_payment_successful_result"

				$this->process_payment($post_id);
			} else {
				# Afterpay didn't give us a token for the order.
				# Mark the quote as failed.
				add_post_meta( $post_id, 'status', 'failed' );

				# Log the error and return a truthy integer (otherwise WooCommerce will not bypass the standard order creation process).
				self::log("WC_Gateway_Afterpay::override_order_creation() returned -2 (Afterpay did not provide a token for this order.)");
				self::log("Error API Payload: " . json_encode($data));
				return -2;
			}
		} else {
			# The Afterpay_Quote post could not be created.
			# Log the error and return a truthy integer (otherwise WooCommerce will not bypass the standard order creation process).
			$errors_str = implode($post_id->get_error_messages(), ' ');
			self::log("WC_Gateway_Afterpay::override_order_creation() returned -1 (Could not create \"afterpay_quote\" post. WordPress threw error(s): {$errors_str})");
			return -1; 
		}
	}

	/**
	 * Order Creation - Part 2 of 2: WooCommerce Order.
	 *
	 * Creates an order based on WooCommerce 2.6.8. This method must only be called if the payment is approved
	 * and the capture is successful.
	 *
	 * Note:	This is only applicable for API v1.
	 *
	 * @since	2.0.0
	 * @see		self::override_order_creation()			For where the data used by this method was persisted to the database.
	 * @param	int					$post_id			The ID of the Afterpay_Quote, which will become the Merchant Order Number.
	 * @global	wpdb				$wpdb				The WordPress Database Access Abstraction Object.
	 * @uses	wc_transaction_query()					Available in WooCommerce core since 2.5.0.
	 * @uses	wp_delete_post()
	 * @uses	wc_create_order()						Available in WooCommerce core since 2.6.0.
	 * @uses	WC_Abstract_Order::add_product()		Available in WooCommerce core since 2.2.
	 * @uses	WC_Abstract_Order::add_fee()			Available in WooCommerce core.
	 * @uses	WC_Abstract_Order::add_shipping()		Available in WooCommerce core.
	 * @uses	WC_Abstract_Order::add_tax()			Available in WooCommerce core since 2.2.
	 * @uses	WC_Abstract_Order::add_coupon()			Available in WooCommerce core.
	 * @uses	WC_Abstract_Order::set_address()		Available in WooCommerce core.
	 * @uses	WC_Abstract_Order::set_prices_include_tax()		Available in WooCommerce core since 3.0.0.
	 * @uses	WC_Geolocation::get_ip_address()				Available in WooCommerce core since 2.4.0.
	 * @uses	WC_Abstract_Order::set_customer_ip_address()	Available in WooCommerce core since 3.0.0.
	 * @uses	wc_get_user_agent()								Available in WooCommerce core since 3.0.0.
	 * @uses	WC_Abstract_Order::set_customer_user_agent()	Available in WooCommerce core since 3.0.0.
	 * @uses	WC_Abstract_Order::set_payment_method	Available in WooCommerce core.
	 * @uses	WC_Abstract_Order::set_shipping_total()	Available in WooCommerce core since 3.0.0.
	 * @uses	WC_Abstract_Order::set_total			Available in WooCommerce core.
	 * @uses	WC_Abstract_Order::set_shipping_tax()	Available in WooCommerce core since 3.0.0.
	 * @uses	WC_Abstract_Order::add_order_note()		Available in WooCommerce core.
	 * @uses	WC_Abstract_Order::payment_complete()	Available in WooCommerce core.
	 * @return	WC_Order|WP_Error
	 * @used-by	self::confirm_afterpay_quote()
	 */
	public function create_wc_order_from_afterpay_quote($post_id) {
		global $wpdb;

		try {
			// Start transaction if available
			wc_transaction_query( 'start' );

			# Retrieve the order data from the Afterpay_Quote item.

			$token = get_post_meta( $post_id, 'token', true );
			$customer_id = get_post_meta( $post_id, 'customer_id', true );
			$cart_hash = get_post_meta( $post_id, 'cart_hash', true );
			$cart_shipping_total = (float)get_post_meta( $post_id, 'cart_shipping_total', true );
			$cart_shipping_tax_total = (float)get_post_meta( $post_id, 'cart_shipping_tax_total', true );
			$cart_discount_total = (float)get_post_meta( $post_id, 'cart_discount_total', true );
			$cart_discount_tax_total = (float)get_post_meta( $post_id, 'cart_discount_tax_total', true );
			$cart_tax_total = (float)get_post_meta( $post_id, 'cart_tax_total', true );
			$cart_total = (float)get_post_meta( $post_id, 'cart_total', true );
			$cart_items = json_decode(get_post_meta( $post_id, 'cart_items', true ), true);
			$cart_fees = json_decode(get_post_meta( $post_id, 'cart_fees', true ), false);
			$cart_coupons = json_decode(get_post_meta( $post_id, 'cart_coupons', true ), true);
			$cart_taxes = json_decode(get_post_meta( $post_id, 'cart_taxes', true ), true);
			$cart_needs_shipping = (bool)get_post_meta( $post_id, 'cart_needs_shipping', true );
			if (version_compare( WC_VERSION, '3.0.0', '>=' )) {
				$chosen_shipping_methods = json_decode(get_post_meta( $post_id, 'chosen_shipping_methods', true ), true);
			}
			$shipping_packages = json_decode(get_post_meta( $post_id, 'shipping_packages', true ), true);
			$billing_address = json_decode(get_post_meta( $post_id, 'billing_address', true ), true);
			$shipping_address = json_decode(get_post_meta( $post_id, 'shipping_address', true ), true);
			$api_data = json_decode(get_post_meta( $post_id, 'api_data', true ), true);
			$posted = json_decode(get_post_meta( $post_id, 'posted', true ), true);
			$afterpay_preauth_nonce = get_post_meta( $post_id, 'afterpay_preauth_nonce', true );
			$afterpay_fe_confirm_nonce = get_post_meta( $post_id, 'afterpay_fe_confirm_nonce', true );
			$afterpay_fe_cancel_nonce = get_post_meta( $post_id, 'afterpay_fe_cancel_nonce', true );
			$afterpay_order_id = get_post_meta( $post_id, 'afterpay_order_id', true );

			# Force-delete the Afterpay_Quote item. This will make its ID available to be used as the WC_Order ID.

			wp_delete_post( $post_id, true );

			# Create the WC_Order item.

			$order_data = array(
				'status'        => apply_filters( 'woocommerce_default_order_status', 'pending' ),
				'customer_id'   => $customer_id,
				'customer_note' => isset( $posted['order_comments'] ) ? $posted['order_comments'] : '',
				'cart_hash'     => $cart_hash,
				'created_via'   => 'checkout',
			);

			$GLOBALS['afterpay_quote_id'] = $post_id;
			$order = wc_create_order( $order_data );
			if (isset($GLOBALS['afterpay_quote_id'])) {
				unset($GLOBALS['afterpay_quote_id']);
			}

			if ( is_wp_error( $order ) ) {
				throw new Exception( sprintf( __( 'Error %d: Unable to create order. Please try again.', 'woocommerce' ), 520 ) );
			} elseif ( false === $order ) {
				throw new Exception( sprintf( __( 'Error %d: Unable to create order. Please try again.', 'woocommerce' ), 521 ) );
			} else {
				# avoid older WooCommerce error
				if (method_exists($order, "get_id")) {
					$order_id = $order->get_id();
				}
				else {
					$order_id = $order->ID;
				}
				do_action( 'woocommerce_new_order', $order_id );
			}

			// Store the line items to the new/resumed order
			foreach ( $cart_items as $cart_item_key => $cart_item ) {
				if (version_compare( WC_VERSION, '3.0.0', '>=' )) {
					$values = array(
						'data' => wc_get_product($cart_item['id']),
						'quantity' => $cart_item['props']['quantity'],
						'variation' => $cart_item['props']['variation'],
						'line_subtotal' => $cart_item['props']['subtotal'],
						'line_total' => $cart_item['props']['total'],
						'line_subtotal_tax' => $cart_item['props']['subtotal_tax'],
						'line_tax' => $cart_item['props']['total_tax'],
						'line_tax_data' => $cart_item['props']['taxes']
					);

					$item                       = new WC_Order_Item_Product();
					$item->legacy_values        = $values; // @deprecated For legacy actions.
					$item->legacy_cart_item_key = $cart_item_key; // @deprecated For legacy actions.
					$item->set_props( array(
						'quantity'     => $cart_item['props']['quantity'],
						'variation'    => $cart_item['props']['variation'],
						'subtotal'     => $cart_item['props']['subtotal'],
						'total'        => $cart_item['props']['total'],
						'subtotal_tax' => $cart_item['props']['subtotal_tax'],
						'total_tax'    => $cart_item['props']['total_tax'],
						'taxes'        => $cart_item['props']['taxes'],
						'name'         => $cart_item['props']['name'],
						'tax_class'    => $cart_item['props']['tax_class'],
						'product_id'   => $cart_item['props']['product_id'],
						'variation_id' => $cart_item['props']['variation_id']
					) );
					$item->set_backorder_meta();

					do_action( 'woocommerce_checkout_create_order_line_item', $item, $cart_item_key, $values, $order );

					// Add item to order and save.
					$order->add_item( $item );
				} else {
					$product = new $cart_item['class']( $cart_item['id'] );
					unset( $cart_item['class'] );
					unset( $cart_item['id'] );
					$cart_item['data'] = $product;

					$item_id = $order->add_product(
						$product,
						$cart_item['quantity'],
						array(
							'variation' => $cart_item['variation'],
							'totals'    => $cart_item['totals']
						)
					);

					if ( ! $item_id ) {
						throw new Exception( sprintf( __( 'Error %d: Unable to create order. Please try again.', 'woocommerce' ), 525 ) );
					}

					// Allow plugins to add order item meta
					do_action( 'woocommerce_add_order_item_meta', $item_id, $cart_item, $cart_item_key );
				}
			}

			// Store fees
			foreach ( $cart_fees as $fee_key => $fee ) {
				# $fee needs to be an object, so we parsed the JSON to an object,
				# but $fee->tax_data needs to be an associative array, with numeric keys.
				# Just convert it now.
				//$fee->tax_data = (array)$fee->tax_data; # This keeps the array keys as strings. We want integers.
				$tax_data = array();
				foreach ($fee->tax_data as $key_str => $amount) {
					$tax_data[(int)$key_str] = $amount;
				}
				$fee->tax_data = $tax_data;

				$item_id = $order->add_fee( $fee );

				if ( ! $item_id ) {
					throw new Exception( sprintf( __( 'Error %d: Unable to create order. Please try again.', 'woocommerce' ), 526 ) );
				}

				// Allow plugins to add order item meta to fees
				do_action( 'woocommerce_add_order_fee_meta', $order_id, $item_id, $fee, $fee_key );
			}

			// Store shipping for all packages
			foreach ( $shipping_packages as $package_key => $package_data ) {
				$package = new WC_Shipping_Rate( $package_data['id'], $package_data['label'], $package_data['cost'], $package_data['taxes'], $package_data['method_id'] );

				if (version_compare( WC_VERSION, '3.0.0', '>=' )) {
					if (in_array($package->id, $chosen_shipping_methods)) {
						$item = new WC_Order_Item_Shipping;
						$item->legacy_package_key = $package_key; // @deprecated For legacy actions.
						$item->set_props( array(
							'method_title' => $package->label,
							'method_id'    => $package->id,
							'total'        => wc_format_decimal( $package->cost ),
							'taxes'        => array(
								'total' => $package->taxes,
							),
						) );

						foreach ( $package_data['meta_data'] as $key => $value ) {
							$item->add_meta_data( $key, $value, true );
						}

						/**
						 * Action hook to adjust item before save.
						 * @since 3.0.0
						 */
						do_action( 'woocommerce_checkout_create_order_shipping_item', $item, $package_key, $package, $order );

						// Add item to order and save.
						$order->add_item( $item );
					}
				} else {
					foreach ($package_data['meta_data'] as $key => $value) {
						$package->add_meta_data($key, $value);
					}

					$item_id = $order->add_shipping( $package );

					if ( ! $item_id ) {
						throw new Exception( sprintf( __( 'Error %d: Unable to create order. Please try again.', 'woocommerce' ), 527 ) );
					}

					// Allows plugins to add order item meta to shipping
					do_action( 'woocommerce_add_shipping_order_item', $order_id, $item_id, $package_key );
				}
			}

			// Store tax rows
			foreach ( $cart_taxes as $tax_rate_id => $cart_tax ) {
				if ( ! $order->add_tax( $tax_rate_id, $cart_tax['tax_amount'], $cart_tax['shipping_tax_amount'] ) ) {
					throw new Exception( sprintf( __( 'Error %d: Unable to create order. Please try again.', 'woocommerce' ), 528 ) );
				}
			}

			// Store coupons
			foreach ( $cart_coupons as $code => $coupon_data ) {
				if ( ! $order->add_coupon( $code, $coupon_data['discount_amount'], $coupon_data['discount_tax_amount'] ) ) {
					throw new Exception( sprintf( __( 'Error %d: Unable to create order. Please try again.', 'woocommerce' ), 529 ) );
				}
			}

			$order->set_address( $billing_address, 'billing' );
			$order->set_address( $shipping_address, 'shipping' );
			if (version_compare( WC_VERSION, '3.0.0', '>=' )) {
				$order->set_prices_include_tax( 'yes' === get_option( 'woocommerce_prices_include_tax' ) );
				$order->set_customer_ip_address( WC_Geolocation::get_ip_address() );
				$order->set_customer_user_agent( wc_get_user_agent() );
			}
			$order->set_payment_method( $this );
			if (version_compare( WC_VERSION, '3.0.0', '>=' )) {
				$order->set_shipping_total( $cart_shipping_total );
			} else {
				$order->set_total( $cart_shipping_total, 'shipping' );
			}
			$order->set_total( $cart_discount_total, 'cart_discount' );
			$order->set_total( $cart_discount_tax_total, 'cart_discount_tax' );
			$order->set_total( $cart_tax_total, 'tax' );
			if (version_compare( WC_VERSION, '3.0.0', '>=' )) {
				$order->set_shipping_tax( $cart_shipping_tax_total );
			} else {
				$order->set_total( $cart_shipping_tax_total, 'shipping_tax' );
			}
			$order->set_total( $cart_total );
			$order->add_order_note( __( "Payment approved. Afterpay Order ID: {$afterpay_order_id}", 'woo_afterpay') );
			$order->payment_complete( $afterpay_order_id );

			// Update user meta
			if ( $customer_id ) {
				# Can't do the following unless we can get an instance of the WC_Checkout...
				/*if ( apply_filters( 'woocommerce_checkout_update_customer_data', true, $checkout ) ) {
					foreach ( $billing_address as $key => $value ) {
						update_user_meta( $customer_id, 'billing_' . $key, $value );
					}
					if ( WC()->cart->needs_shipping() ) {
						foreach ( $shipping_address as $key => $value ) {
							update_user_meta( $customer_id, 'shipping_' . $key, $value );
						}
					}
				}
				do_action( 'woocommerce_checkout_update_user_meta', $customer_id, $posted );*/
			}

			// Let plugins add meta
			do_action( 'woocommerce_checkout_update_order_meta', $order_id, $posted );

			// If we got here, the order was created without problems!
			wc_transaction_query( 'commit' );
		} catch ( Exception $e ) {
			// There was an error adding order data!
			wc_transaction_query( 'rollback' );
			return new WP_Error( 'checkout-error', $e->getMessage() );
		}

		return $order;
	}

	/**
	 * Is this a post of type "afterpay_quote"?
	 *
	 * @since	2.0.0
	 * @param	WP_Post|int	$post	The WP_Post object or ID.
	 * @return	bool				Whether or not the given post is of type "afterpay_quote".
	 */
	private function is_post_afterpay_quote($post) {
		if (is_numeric($post) && $post > 0) {
			$post = get_post( (int)$post );
		}

		if ($post instanceof WP_Post) {
			if ($post->post_type == 'afterpay_quote') {
				return true;
			}
		}

		return false;
	}

	/**
	 * Render the HTML that runs the front-end JS for launching the Afterpay lightbox.
	 *
	 * @since	2.0.0
	 * @param	string		$token					The order token to use when launching the lightbox.
	 * @param	string		$lightbox_launch_method	Optional. The method to use when launching the Afterpay lightbox.
	 *												"redirect" or "display". Defaults to "redirect".
	 * @param	array|null	$init_object			Optional. A jsonifiable object to be passed to the Afterpay.init()
	 *												JS method. Defaults to null.
	 * @uses	get_woocommerce_currency()
	 * @used-by	self::override_single_post_template_for_afterpay_quotes()
	 * @used-by	self::inject_preauth_html()
	 * @used-by	self::receipt_page()
	 */
	private function render_js($token, $lightbox_launch_method = 'redirect', $init_object = null) {

		# Get the Store Currency to determine the country code
		$currency = get_woocommerce_currency();
		$country = "AU";

		if ($currency == "USD") {
			$country = "US";
		}
		else if ($currency == "NZD") {
			$country = "NZ";	
		}

		if (empty($init_object)) {
			$init_object = 	array(
								"countryCode" => $country
							);	
		}
		else {
			$init_object["country_code"] = $country; 
		}
		
		include "{$this->include_path}/afterpay_js_init.html.php";
	}

	/**
	 * This is called by the WooCommerce checkout via AJAX, if Afterpay was the selected payment method.
	 *
	 * Note:	This overrides the method defined in the parent class.
	 *
	 * @since	2.0.0
	 * @see		WC_Payment_Gateway::process_payment()	For the method we are overriding.
	 * @param	int|null	$order_id					The ID of the order. This would normally be the ID of a WC_Order post,
	 *													but in our case it should be the ID of an "afterpay_quote" post,
	 *													because we have overridden the order creation method.
	 * @uses	self::build_afterpay_quote_url()
	 * @uses	wp_send_json()							Available as part of WordPress core since 3.5.0
	 * @return	array									May also render JSON and exit.
	 */
	public function process_payment($order_id = null) {
		if ($this->settings['api-version'] == 'v0') {
			$order_total = WC()->cart->total;

			if( function_exists("wc_get_order") ) {
				$order = wc_get_order( $order_id );	
			} else {
				$order = new WC_Order( $order_id );
			}

			$merchant = new Afterpay_Plugin_Merchant;
			$token = $merchant->get_order_token_for_wc_order($order);
			$payment_types = $merchant->get_payment_types_for_amount($order_total);

			if (count($payment_types) == 0) {
				$order->add_order_note( __( 'Order amount: $' . number_format($order_total, 2) . ' is not supported.', 'woo_afterpay' ) );
				wc_add_notice( __( 'Unfortunately, an order of $' . number_format($order_total, 2) . ' cannot be processed through Afterpay.', 'woo_afterpay' ), 'error' );

				return array(
					'result' => 'failure',
					'redirect' => $order->get_checkout_payment_url( true )
				);
			} elseif ($token == false) {
				$order->add_order_note( __( 'Unable to generate the order token. Payment couldn\'t proceed.', 'woo_afterpay' ) );
				wc_add_notice( __( 'Sorry, there was a problem preparing your payment.', 'woo_afterpay' ), 'error' );

				return array(
					'result' => 'failure',
					'redirect' => $order->get_checkout_payment_url( true )
				);
			} else {
				update_post_meta( $order_id, '_afterpay_token', $token );
			}

			return array(
				'result' => 'success',
				'redirect' => $order->get_checkout_payment_url( true )
			);
		} elseif ($this->settings['api-version'] == 'v1') {
			if ($order_id == -2) {
				# Afterpay didn't give us a token for the order.
				wp_send_json(array(
					'result'	=> 'success',
					'messages'	=> '<div class="woocommerce-error">There was a problem preparing your payment. Please try again.</div>'
				));
			} elseif ($order_id == -1) {
				# The Afterpay_Quote post could not be created.
			} elseif ($order_id > 0) {

				$afterpay_quote = get_post($order_id);

				if ($this->is_post_afterpay_quote($afterpay_quote)) {
					$afterpay_preauth_nonce = get_post_meta( $afterpay_quote->ID, 'afterpay_preauth_nonce', true );
					
					$result = array(
						'result'	=> 'success',
						'redirect'	=> $this->build_afterpay_quote_url($afterpay_quote->ID, 'preauth', $afterpay_preauth_nonce)
					);

					# Don't return $result because we're not sending
					# this back to WooCommerce. Instead, send the
					# response directly to the browser so that we
					# avoid triggering the action/filter hooks:
					# - "woocommerce_checkout_order_processed"
					# - "woocommerce_payment_successful_result"

					if ( is_ajax() ) {
						wp_send_json( $result );
					} else {
						wp_redirect( $result['redirect'] );
						exit;
					}
				}
			}
		}

		# If all else fails, send a generic failure message.
		wp_send_json(array(
			'result'	=> 'success',
			'messages'	=> '<div class="woocommerce-error">An unexpected error has occurred. Please try again.</div>'
		));
	}

	/**
	 * If calling wc_create_order() for an Afterpay Quote, tell wp_insert_post() to reuse the ID of the quote.
	 *
	 * Note:	Hooked onto the "woocommerce_new_order_data" Filter.
	 * Note:	The "woocommerce_new_order_data" Filter has been part of WooCommerce core since 2.6.0.
	 * @see		http://hookr.io/plugins/woocommerce/2.6.0/filters/woocommerce_new_order_data/
	 *
	 * @since	2.0.0
	 * @see		Afterpay_Plugin::__construct() for hook attachement.
	 * @param	array $order_data An array of parameters to pass to wp_insert_post().
	 * @return	array The filtered array to be passed as the first argument of wp_insert_post().
	 */
	public function filter_woocommerce_new_order_data( $order_data ) {
		if (array_key_exists('afterpay_quote_id', $GLOBALS) && is_numeric($GLOBALS['afterpay_quote_id']) && $GLOBALS['afterpay_quote_id'] > 0) {
			$order_data['import_id'] = (int)$GLOBALS['afterpay_quote_id'];
			unset($GLOBALS['afterpay_quote_id']);
		}
		return $order_data;
	}

	/**
	 * Cancel the Afterpay_Quote from the lightbox and return to the checkout.
	 *
	 * Note:	This is only applicable for API v1.
	 *
	 * @since	2.0.0
	 * @param	int	$afterpay_quote_id	The ID of the quote that was cancelled.
	 * @uses	wp_trash_post()			Available in WooCommerce core since 2.9.0.
	 * @uses	wc_add_notice()			Available in WooCommerce core since 2.1.
	 * @uses	wc_get_checkout_url()	Available in WooCommerce core since 2.5.0.
	 * @uses	wp_redirect()			Available in WordPress core since 1.5.1.
	 * @return	false					Only returns false if the redirect fails.
	 * @used-by	self::override_single_post_template_for_afterpay_quotes()
	 */
	private function cancel_afterpay_quote($afterpay_quote_id) {
		global $wpdb;

		# Mark the quote as cancelled.
		update_post_meta( $afterpay_quote_id, 'status', 'cancelled' );

		# Don't use `wp_trash_post` or `wp_delete_post`
		# because we don't want any hooks to fire.
		
		self::log($wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->postmeta}` WHERE `post_id` = %d", $afterpay_quote_id ) ) . " row(s) deleted from `{$wpdb->postmeta}` table.");
		self::log($wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->posts}` WHERE `ID` = %d LIMIT 1", $afterpay_quote_id ) ) . " row(s) deleted from `{$wpdb->posts}` table.");

		# Store a checkout notice in the session.
		wc_add_notice( __( 'Your order has been cancelled.', 'woo_afterpay' ), 'notice' );

		# Redirect back the the checkout.
		if (wp_redirect( wc_get_checkout_url() )) {
			exit;
		}

		return false;
	}

	/**
	 * Confirm the Afterpay_Quote from the lightbox.
	 * This is called if the quote is pre-approved. It will process the capture, and create the
	 * WC_Order if the payment is captured successfully.
	 *
	 * Note:	This is only applicable for API v1.
	 *
	 * @since	2.0.0
	 * @param	int		$afterpay_quote_id	The Post ID of the Aterpay_Quote.
	 * @param	string	$token				The token to be captured.
	 * @uses	wc_add_notice()				Available since WooCommerce 2.1.
	 * @see		https://docs.woocommerce.com/wc-apidocs/function-wc_add_notice.html
	 * @uses	wc_get_checkout_url()		Available since WooCommerce 2.5.0.
	 * @see		https://docs.woocommerce.com/wc-apidocs/source-function-wc_get_checkout_url.html#1131-1148
	 * @return	WP_Error|false				Only returns if it doesn't write redirect headers and die.
	 * @used-by	self::override_single_post_template_for_afterpay_quotes()
	 */
	private function confirm_afterpay_quote($afterpay_quote_id, $token) {
		# Process the capture.

		$merchant = new Afterpay_Plugin_Merchant;
		$capture_response = $merchant->direct_payment_capture($token, $afterpay_quote_id);

		if ($capture_response == 'APPROVED') {
			# Convert the Afterpay_Quote into a WC_Order.

			$order = $this->create_wc_order_from_afterpay_quote($afterpay_quote_id);

			# Redirect to the receipt page (if front-end)

			if (!is_wp_error($order)) {
				/**
				 * @todo Figure out a way to run this hook here.
				 *       We'd need to have a copy of $posted_data
				 *       from the original post of the checkout form.
				 */
				//do_action( 'woocommerce_checkout_order_processed', $afterpay_quote_id, $posted_data, $order );

				if (wp_redirect( $order->get_checkout_order_received_url() )) {
					exit;
				}
			}

			# Return the WP_Error is the WC_Order could not be created.

			return $order;
		} elseif ($capture_response == 'DECLINED') {
			# Log the event.

			self::log("Afterpay Quote #{$afterpay_quote_id} declined by Afterpay.");

			# Redirect back to the checkout page with an error (if front-end).

			wc_add_notice( __( 'Your payment was declined. For more information, please contact the Afterpay Customer Service Team on 1300 100 729.', 'woo_afterpay' ), 'error' );
			if (wp_redirect( wc_get_checkout_url() )) {
				exit;
			}
		} else {
			# We don't know what happened. Hopefully it was an API error which we logged. In any case,
			# display a generic error at the checkout.

			wc_add_notice( __( 'Your payment could not be processed. Please try again.', 'woo_afterpay' ), 'error' );

			if (wp_redirect( wc_get_checkout_url() )) {
				exit;
			}
		}

		# Can only reach this point if wp_redirect() failed.
		return false;
	}

	/**
	 * When viewing the public URL for an "afterpay_quote" post, intercept the rendering of the page and just write the javascript
	 * for redirecting to the Afterpay payment gateway. This is because the process_payment() method must return JSON with
	 * either a redirect URL or a message to display (the message can be HTML), and since we skipped the WC_Order creation
	 * there's no WooCommerce page to render.
	 *
	 * Note:	This is only applicable for API v1, as v0 does not create "afterpay_quote" posts.
	 *
	 * Note:	Hooked onto the "template_redirect" Action.
	 *
	 * @since	2.0.0
	 * @global	WP_Query	$wp_query
	 * @see		Afterpay_Plugin::__construct()	For hook attachement.
	 * @see		self::process_payment()			For how the user is redirected to the URL that implements this function.
	 * @uses	current_time()
	 * @uses	self::render_js()
	 * @uses	metadata_exists()				Available in WordPress core since 3.3.0.
	 * @uses	self::cancel_afterpay_quote()
	 * @uses	self::confirm_afterpay_quote()
	 */
	public function override_single_post_template_for_afterpay_quotes() {
		if (!is_admin()) {
			if (!empty($_GET)) {
				$afterpay_quote = null;
				if (array_key_exists('post_type', $_GET) && array_key_exists('p', $_GET)) {
					if ($_GET['post_type'] == 'afterpay_quote' && is_numeric($_GET['p'])) {
						$afterpay_quote = get_post( (int)$_GET['p'] );
					}
				}
				if (is_null($afterpay_quote)) {
					$afterpay_quote = get_post();
				}
				if ($this->is_post_afterpay_quote($afterpay_quote)) {

					# should it be 404
					$is_404 = true;

					if (array_key_exists('action', $_GET) && array_key_exists('nonce', $_GET)) {
						switch ($_GET['action']) {
							case 'preauth':
								$afterpay_preauth_nonce = $_GET['nonce'];
								if (wp_verify_nonce( $afterpay_preauth_nonce, "afterpay_preauth_nonce-{$afterpay_quote->ID}" ) && $afterpay_preauth_nonce == get_post_meta( $afterpay_quote->ID, 'afterpay_preauth_nonce', true )) {
									delete_post_meta($afterpay_quote->ID, 'afterpay_preauth_nonce'); # Force the nonce to actually be a proper single-use nonce.

									$token = get_post_meta( $afterpay_quote->ID, 'token', true );
									$token_expiry = get_post_meta( $afterpay_quote->ID, 'token_expiry', true );

									if (!empty($token) && is_string($token) && strlen($token) > 0) {
										if (current_time( 'timestamp', true ) < strtotime($token_expiry)) { # Note: This is comparing the current GMT time to the stored UTC time.
											if (false) {
												# Redirect mode.
												# Render the JS in redirect mode and exit.
												if (!headers_sent()) {
													header('Content-type: text/html');
												}
												$this->render_js($token);
												exit;
											} else {
												# Display mode.
												# Queue the token for rendering on the page.
												# Return, allowing the public post to render normally.
												$this->token = $token;
												return;
											}
										} else {
											# The token has expired. No point trying to launch the lightbox with a token
											# that we know has expired because it won't work. It may actually start the
											# spinner and fail to handle the 404, resulting in a never-ending progress
											# indicator. Avoid the potentiall terrible UX and just tell the customer their
											# token has expired.

											# Log the event.
											self::log("The token for Afterpay Quote #{$afterpay_quote->ID} has expired. Customer will be returned to checkout and notified.");

											# Update and trash the post.
											update_post_meta( $afterpay_quote->ID, 'status', 'failed' );
											if (function_exists('wp_trash_post')) {
												wp_trash_post( $afterpay_quote->ID  );
											}

											# Store an error notice and redirect the customer back to the checkout.
											wc_add_notice( __( 'Your payment token has expired. Please try again.', 'woo_afterpay' ), 'error' );
											if (wp_redirect( wc_get_checkout_url() )) {
												exit;
											}
										}
									} else {
										# The customer should not have reached this point if Afterpay did not create a token.
										# An error would have been returned by the AJAX request to place the order.
										# @see self::process_payment()
									}
								} elseif (function_exists('metadata_exists') && !metadata_exists( 'post', $afterpay_quote->ID, 'afterpay_preauth_nonce' )) {
									# Trying to re-use a nonce. This is probably a refresh when the JS was rendered in
									# display mode. Give the customer the same "token expired" message and take them back
									# to the checkout.

									# Log the event.
									self::log("Customer tried to re-use the preauth nonce, probably by refreshing the page. Customer will be returned to checkout and notified that the token expired.");

									# Update and trash the post.
									update_post_meta( $afterpay_quote->ID, 'status', 'failed' );
									if (function_exists('wp_trash_post')) {
										wp_trash_post( $afterpay_quote->ID  );
									}

									# Store an error notice and redirect the customer back to the checkout.
									wc_add_notice( __( 'Your payment token has expired. Please try again.', 'woo_afterpay' ), 'error' );
									if (wp_redirect( wc_get_checkout_url() )) {
										exit;
									}
								}
							break;

							case 'fe-cancel':
								$afterpay_fe_cancel_nonce = $_GET['nonce'];
								if (wp_verify_nonce( $afterpay_fe_cancel_nonce, "afterpay_fe_cancel_nonce-{$afterpay_quote->ID}" ) && $afterpay_fe_cancel_nonce == get_post_meta( $afterpay_quote->ID, 'afterpay_fe_cancel_nonce', true )) {
									delete_post_meta($afterpay_quote->ID, 'afterpay_fe_cancel_nonce'); # Force the nonce to actually be a proper single-use nonce.

									if ($_GET['status'] == 'CANCELLED') {
										# Log the event.
										self::log("Afterpay Quote #{$afterpay_quote->ID} cancelled by Consumer.");

										# Cancel the Afterpay Quote:
										# - Mark the quote as cancelled.
										# - Redirect back to the checkout with a notice.
										$this->cancel_afterpay_quote($afterpay_quote->ID);
									} else {
										# What?
									}
								}
							break;

							case 'fe-confirm':
								$afterpay_fe_confirm_nonce = $_GET['nonce'];
								if (wp_verify_nonce( $afterpay_fe_confirm_nonce, "afterpay_fe_confirm_nonce-{$afterpay_quote->ID}" ) && $afterpay_fe_confirm_nonce == get_post_meta( $afterpay_quote->ID, 'afterpay_fe_confirm_nonce', true )) {
									delete_post_meta($afterpay_quote->ID, 'afterpay_fe_confirm_nonce'); # Force the nonce to actually be a proper single-use nonce.

									if ($_GET['status'] == 'SUCCESS') {
										# Log the event.
										self::log("Afterpay Quote #{$afterpay_quote->ID} confirmed by Consumer.");

										# The order reached pre-approval status.
										# Confirm the Afterpay Quote:
										# - Submit the direct payment capture request to the API.
										# - Convert the Afterpay_Quote into a WC_Order.
										# - Redirect to the receipt page.
										$this->confirm_afterpay_quote($afterpay_quote->ID, $_GET['orderToken']);
									} elseif ($_GET['status'] == 'FAILURE') {
										# Log the event.
										self::log("Afterpay Quote #{$afterpay_quote->ID} declined by Afterpay.");

										# This should never happen in v1, because the capture hasn't been initiated yet.
										# This is the same as a decline.
										# @see self::confirm_afterpay_quote() where $capture_response == 'DECLINED'
										wc_add_notice( __( 'Your payment was declined. For more information, please contact the Afterpay Customer Service Team on 1300 100 729.', 'woo_afterpay' ), 'error' );
										if (wp_redirect( wc_get_checkout_url() )) {
											exit;
										}
									} else {
										# What?
									}
								}
							break;

							case 'fe-capture-retry':
								# Capture Retry Page
								$is_404 = false;

								/**
								 * Add a icon to the beginning of every post page.
								 *
								 * @param string 	$content 	The HTML from the page
								 * @uses is_single()
								 */
								function my_the_content_filter( $content ) {   
									include dirname(__FILE__) . '/WC_Gateway_Afterpay' . "/capture_retry.html.php";
								}
								add_filter( 'the_content', 'my_the_content_filter', 20);
							break;
						}
					}

					if ($is_404) {
						global $wp_query;
						$wp_query->set_404();
						status_header(404);
						nocache_headers();
						include get_query_template( '404' );
						exit;
					}
				}
			}
		}
	}

	/**
	 * Inject the preauth HTML onto the page, only if a token has been queued to render. $this->token will only hold
	 * a value if self::override_single_post_template_for_afterpay_quotes() validated the preauth URL on "wp_loaded".
	 *
	 * Note:	Hooked onto the "wp_head" Action.
	 * Note:	Hooked onto the "wp_footer" Action.
	 * Note:	Hooked onto the "shutdown" Action.
	 *
	 * @since	2.0.0
	 * @see		Afterpay_Plugin::__construct()	For hook attachment.
	 * @uses	self::render_js()
	 */
	public function inject_preauth_html() {
		if (!empty($this->token)) {
			$this->render_js($this->token, 'display');
			$this->token = null;
		}
	}

	/**
	 * Trigger Afterpay JavaScript on Receipt page.
	 *
	 * Note:	This is only applicable for API v0.
	 * Note:	Hooked onto the "woocommerce_receipt_afterpay" Action.
	 *
	 * @since	1.0.0
	 * @param	string 		$order_id
	 * @uses	wc_get_order()
	 * @uses	get_bloginfo()							Available in WordPress core since 0.71.
	 * @uses	WC_Payment_Gateway::get_return_url()
	 * @uses	WC_Payment_Gateway::has_status()
	 * @uses	WC_Payment_Gateway::update_status()
	 * @uses	self::render_js()
	 */
	public function receipt_page($order_id) {
		if ($this->settings['api-version'] != 'v0') {
			return;
		}

		if (function_exists('wc_get_order') ) {
			$order = wc_get_order( $order_id );	
		} else {
			$order = new WC_Order( $order_id );
		}
		
		# Get the order token.
		$token = get_post_meta( $order_id, '_afterpay_token', true );

		if (empty($token)) {
			self::log("Failed to render checkout receipt page - token cannot be empty.");
			/**
			 * @todo Cancel the order.
			 * @todo Store an error message.
			 * @todo Redirect back to the checkout.
			 */
			return;
		}

		# Return URL.
		$blogurl = str_replace(array('https:', 'http:'), '', get_bloginfo('url'));
		$returnurl = str_replace(array('https:', 'http:', $blogurl), '', $this->get_return_url($order));

		# Update order status if not already pending.
		$is_pending = false;
		if (property_exists($order, 'has_status')) {
			$is_pending = $order->has_status('pending'); 
		} else {
			$is_pending = ($order->status == 'pending');
		}
		if (!$is_pending) {
			$order->update_status('pending');
		}

		# Render the JS.
		$this->render_js($token, 'display', array(
			'relativeCallbackURL' => $returnurl
		));
	}

	/**
	 * Validate the order status on the Thank You page. Will never actually alter the Order ID.
	 *
	 * Note:	This is only applicable for API v0.
	 * Note:	Hooked onto the "woocommerce_thankyou_order_id" Filter.
	 *
	 * @since	1.0.0
	 * @param	int			$order_id
	 * @uses	Afterpay_Plugin_Merchant::get_order()
	 * @return	int
	 */
	public function payment_callback($order_id) {
		if ($this->settings['api-version'] != 'v0') {
			return $order_id;
		}

		if (array_key_exists('orderId', $_GET)) {
			$afterpay_order_id = $_GET['orderId'];

			self::log("Checking status of WooCommerce Order #{$order_id} (Afterpay Order #{$afterpay_order_id})");

			$merchant = new Afterpay_Plugin_Merchant;
			$response = $merchant->get_order(null, $afterpay_order_id);

			if ($response === false) {
				self::log("Afterpay_Plugin_Merchant::get_order() returned false.");
			} elseif (is_object($response)) {
				self::log("Afterpay_Plugin_Merchant::get_order() returned an order with a status of \"{$response->status}\".");

				if (function_exists('wc_get_order')) {
					$order = wc_get_order( $order_id );	
				} else {
					$order = new WC_Order( $order_id );
				}

				$is_completed = $is_processing = $is_pending = $is_on_hold = $is_failed = false;

				if (method_exists($order, 'has_status')) {
					$is_completed = $order->has_status( 'completed' ); 
					$is_processing = $order->has_status( 'processing' ); 
					$is_pending = $order->has_status( 'pending' ); 
					$is_on_hold = $order->has_status( 'on-hold' ); 
					$is_failed = $order->has_status( 'failed' ); 
				} else {
					if ($order->status == 'completed') {
						$is_completed = true;
					} elseif ($order->status == 'processing') {
						$is_processing = true;
					} elseif ($order->status == 'pending') {
						$is_pending = true;
					} elseif ($order->status == 'on-hold') {
						$is_on_hold = true;
					} elseif ($order->status == 'failed') {
						$is_failed = true;
					}
				}

				if ($response->status == 'APPROVED') {
					if (!$is_completed && !$is_processing) {
						self::log("Updating status of WooCommerce Order #{$order_id} to \"Processing\".");

						$order->add_order_note( sprintf(__( 'Payment approved. Afterpay Order ID: %s', 'woo_afterpay' ), $response->id) );
						$order->payment_complete($response->id);

						if (function_exists("wc_empty_cart")) {
							wc_empty_cart();
						}
						else {
							woocommerce_empty_cart();
						}
					}
				} elseif ($response->status == 'PENDING') {
					if (!$is_on_hold) {
						self::log("Updating status of WooCommerce Order #{$order_id} to \"On Hold\".");

						$order->add_order_note( sprintf(__( 'Afterpay payment is pending approval. Afterpay Order ID: %s', 'woo_afterpay' ), $response->id) );
						$order->update_status( 'on-hold' );
						update_post_meta($order_id,'_transaction_id',$response->id);
					}
				} elseif ($response->status == 'FAILURE' || $response->status == 'FAILED') {
					if (!$is_failed) {
						self::log("Updating status of WooCommerce Order #{$order_id} to \"Failed\".");

						$order->add_order_note( sprintf(__( 'Afterpay payment declined. Order ID from Afterpay: %s', 'woo_afterpay' ), $response->id) );
						$order->update_status( 'failed' );
					}
				} else {
					if (!$is_pending) {
						self::log("Updating status of WooCommerce Order #{$order_id} to \"Pending Payment\".");

						$order->add_order_note( sprintf(__( 'Payment %s. Afterpay Order ID: %s', 'woo_afterpay' ), strtolower($response->status), $response->id) );
						$order->update_status( 'pending' );
					}
				}
			}
		}

		return $order_id;
	}

	/**
	 * Can the order be refunded?
	 *
	 * @since	1.0.0
	 * @param	WC_Order	$order
	 * @return	bool
	 */
	public function can_refund_order($order) {
		if ($order instanceof WC_Order && method_exists($order, 'get_transaction_id')) {
			return $order && $order->get_transaction_id();
		}

		return false;
	}

	/**
	 * Process a refund if supported.
	 *
	 * Note:	This overrides the method defined in the parent class.
	 *
	 * @since	1.0.0
	 * @see		WC_Payment_Gateway::process_refund()		For the method that this overrides.
	 * @param	int			$order_id
	 * @param	float		$amount							Optional. The amount to refund. This cannot exceed the total.
	 * @param	string		$reason							Optional. The reason for the refund. Defaults to an empty string.
	 * @uses	Afterpay_Plugin_Merchant::create_refund()
	 * @return	bool
	 */
	public function process_refund($order_id, $amount = null, $reason = '') {
		$order_id = (int)$order_id;

		self::log("Refunding WooCommerce Order #{$order_id} for \${$amount}...");

		if (function_exists('wc_get_order')) {
			$order = wc_get_order( $order_id );	
		} else {
			$order = new WC_Order( $order_id );
		}

		if (!$this->can_refund_order($order)) {
			self::log('Refund Failed - No Transaction ID.');
			return false;
		}

		$merchant = new Afterpay_Plugin_Merchant;
		$success = $merchant->create_refund($order, $amount);

		if ($success) {
			$order->add_order_note( __( "Refund of \${$amount} sent to Afterpay. Reason: {$reason}", 'woo_afterpay' ) );
			return true;
		}

		$order->add_order_note( __( "Failed to send refund of \${$amount} to Afterpay.", 'woo_afterpay' ) );
		return false;
	}

	/**
	 * Check if the customer cancelled the payment from the lightbox.
	 *
	 * Note:	This is only applicable for API v0.
	 * Note:	Hooked onto the "template_redirect" Action.
	 *
	 * @since	1.0.0
	 * @see		Afterpay_Plugin::__construct()		For hook attachment.
	 * @uses	wc_get_order_id_by_order_key()
	 * @uses	wc_get_order()
	 * @uses	wp_redirect()
	 * @uses	WC_Order::get_cancel_order_url_raw()
	 * @uses	WC_Order::get_cart_url()
	 */
	public function afterpay_check_for_cancelled_payment() {
		if ($this->settings['api-version'] != 'v0') {
			return;
		}

		if (array_key_exists('key', $_GET) && array_key_exists('status', $_GET) && $_GET['status'] == 'CANCELLED' && array_key_exists('orderToken', $_GET)) {
			$order_id = wc_get_order_id_by_order_key($_GET['key']);
			
			if ($order_id > 0) {	
				if (function_exists('wc_get_order')) {
					$order = wc_get_order( $order_id );	
				} else {
					$order = new WC_Order( $order_id );
				}
			} else {
				$order = null;
			}

			if ($order instanceof WC_Order) {
				self::log("Order #{$order_id} payment cancelled by the customer from the Afterpay lightbox.");
				
				if (method_exists($order, 'get_cancel_order_url_raw')) {
					if (wp_redirect( $order->get_cancel_order_url_raw() )) {
						exit;
					}
				} else {
					$order->update_status( 'cancelled' );
					if (wp_redirect( WC()->cart->get_cart_url() )) {
						exit;
					}
				}
			}
		}
	}

	/**
	 * Check if Afterpay Capture has been done successfully by the Retry CRON
	 *
	 * Note:	This is only applicable for API V1.
	 * Note:	Hooked onto the "wp" action.
	 *
	 * @since	2.0.0
	 * @see		Afterpay_Plugin::__construct()		For hook attachment.
	 * @uses	wp_verify_nonce()
	 * @uses	wc_get_order()
	 * @uses	WC_Order::get_checkout_order_received_url()
	 */
	function afterpay_retry_capture_call() {
		if (!empty($_GET['afterpay_capture']) && !empty($_GET['quote_id'])) {
			$quote_id = $_POST['quote_id'];
			$nonce = $_POST['nonce'];
			$afterpay_fe_retry_nonce = $_POST['nonce'];
			
			if (wp_verify_nonce( $afterpay_fe_retry_nonce, "afterpay_fe_retry_nonce-{$quote_id}")) {

				Afterpay_Plugin_Idempotency_Cron::idempotency_processing();

				if (function_exists('wc_get_order')) {
					$order = wc_get_order( $quote_id );	
				} else {
					$order = new WC_Order( $quote_id );
				}

				if (!empty($order)) {
					# output redirection URL if the Order is successful
						echo json_encode(
							array(
								"success" 	=> 	true,
								"redirect"	=>	$order->get_checkout_order_received_url(),
								"message" 	=> 	"Afterpay Order Capture Successful"
							)
						);
					exit;
				}
				else {
					echo json_encode(
						array(
							"success" 	=> 	false,
							"message" 	=> 	"No Detected Afterpay Order Capture"
						)
					);
					exit;
				}
			}
			else {
				# failed nonce check
				echo json_encode(
					array(
						"success" 	=> 	false,
						"message" 	=> 	"Afterpay Security Check Failure"
					)
				);
			}
		}
		else {
			# failed params check - not Afterpay
		}
	}

}