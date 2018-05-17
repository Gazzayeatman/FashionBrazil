<?php
/**
* Afterpay Plugin Merchant Handler Class
*/

class Afterpay_Plugin_Merchant {
	/**
	 * Protected variables.
	 *
	 * @var		WC_Gateway_Afterpay	$gateway					A reference to the WooCommerce Payment Gateway for Afterpay.
	 * 															This is used for retreiving user settings, such as the API URL.
	 * @var		string				$api_version				The Afterpay API version to use ("v0" or "v1").
	 * @var		string				$payment_types_url			The full URL to the "payment-types" API endpoint (v0).
	 * @var		string				$configuration_url			The full URL to the "configuration" API endpoint (v1).
	 * @var		string				$orders_url					The full URL to the "orders" API endpoint (v0 and v1).
	 * @var		string				$direct_payment_capture_url	The full URL to the "orders" API endpoint (v1).
	 */
	protected $gateway, $api_version, $payment_types_url, $configuration_url, $orders_url, $direct_payment_capture_url;

	/**
	 * Class constructor. Called when an object of this class is instantiated.
	 *
	 * @since	2.0.0
	 * @uses	WC_Gateway_Afterpay::get_api_url()
	 * @uses	WC_Gateway_Afterpay::get_api_version()
	 */
	public function __construct() {
		$endpoints = parse_ini_file('endpoints.ini', true);
		$this->gateway = WC_Gateway_Afterpay::getInstance();
		$api_url = $this->gateway->get_api_url();
		$this->api_version = $this->gateway->get_api_version();
		if ($this->api_version == 'v0') {
			$this->payment_types_url = $api_url . $endpoints['v0']['payment-types'];
			$this->orders_url = $api_url . $endpoints['v0']['orders'];
		} elseif ($this->api_version == 'v1') {
			$this->configuration_url = $api_url . $endpoints['v1']['configuration'];
			$this->orders_url = $api_url . $endpoints['v1']['orders'];
			$this->payments_url = $api_url . $endpoints['v1']['payments'];
			$this->direct_payment_capture_url = $api_url . $endpoints['v1']['capture-payment'];
		}
	}

	/**
	 * Filters the string used for Merchant IDs & Secret Keys.
	 *
	 * @since	2.0.0
	 * @param	string	$str
	 * @return	string
	 * @used-by	self::build_authorization_header()
	 */
	private function cleanup_string($str) {
		return preg_replace('/[^a-z0-9]+/i', '', $str);
	}

	/**
	 * Build the Afterpay Authorization header for use with the APIs.
	 *
	 * @since	2.0.0
	 * @uses	self::cleanup_string()
	 * @uses	WC_Gateway_Afterpay::get_merchant_id()
	 * @uses	WC_Gateway_Afterpay::get_secret_key()
	 * @return	string
	 * @used-by	self::get_from_api()
	 * @used-by	self::post_to_api()
	 */
	private function build_authorization_header() {
		$cleaned_merchant_id = $this->cleanup_string($this->gateway->get_merchant_id());
		$cleaned_secret_key = $this->cleanup_string($this->gateway->get_secret_key());

		return 'Basic ' . base64_encode($cleaned_merchant_id . ':' . $cleaned_secret_key);
	}

	/**
	 * Build the Afterpay User-Agent header for use with the APIs.
	 *
	 * @since	2.0.0
	 * @global	string	$wp_version
	 * @uses	WC()
	 * @return	string
	 * @used-by	self::get_from_api()
	 * @used-by	self::post_to_api()
	 */
	private function build_user_agent_header() {
		global $wp_version;

		$plugin_version = Afterpay_Plugin::$version;
		$php_version = PHP_VERSION;
		$woocommerce_version = WC()->version;
		$merchant_id = $this->gateway->get_merchant_id();

		$extra_detail_1 = '';
		$extra_detail_2 = '';

		$matches = array();
		if (array_key_exists('SERVER_SOFTWARE', $_SERVER) && preg_match('/^[a-zA-Z0-9]+\/\d+(\.\d+)*/', $_SERVER['SERVER_SOFTWARE'], $matches)) {
			$s = $matches[0];
			$extra_detail_1 .= "; {$s}";
		}

		if (array_key_exists('REQUEST_SCHEME', $_SERVER) && array_key_exists('HTTP_HOST', $_SERVER)) {
			$s = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
			$extra_detail_2 .= " {$s}";
		}

		return "Afterpay Gateway for WooCommerce/{$plugin_version} (PHP/{$php_version}; WordPress/{$wp_version}; WooCommerce/{$woocommerce_version}; Merchant/{$merchant_id}{$extra_detail_1}){$extra_detail_2}";
	}

	/**
	 * Build an Afterpay-compatible Money object for use in the API posts.
	 *
	 * @since	2.0.0
	 * @param	float	$amount
	 * @param	string	$currency
	 * @uses	get_woocommerce_currency()
	 * @return	array
	 * @used-by	self::get_payment_types_for_amount()
	 * @used-by	self::get_order_token_for_wc_order()
	 * @used-by	self::create_refund()
	 */
	private function build_money_object($amount, $currency = '') {
		if (empty($currency)) {
			$currency = get_woocommerce_currency();
		}

		return array(
			'amount' => number_format((float)$amount, 2, '.', ''),
			'currency' => $currency
		);
	}

	/**
	 * Store an API error. If the last request to an API resulted in an error, store it in the options table
	 * so we can show it throughout the admin.
	 *
	 * @since	2.0.0
	 * @param	mixed	$error
	 * @return	bool
	 */
	private function store_api_error($error) {
		return update_option( 'woocommerce_afterpay_api_error', $error );
	}

	/**
	 * Clear the last API error. This should be called each time an API call executes successfully.
	 *
	 * @since	2.0.0
	 * @return	bool
	 */
	private function clear_api_error() {
		return delete_option( 'woocommerce_afterpay_api_error' );
	}

	/**
	 * GET from an API endpoint.
	 *
	 * @since	2.0.0
	 * @param	string	$url
	 * @uses	wp_remote_get()
	 * @uses	self::build_authorization_header()
	 * @uses	self::build_user_agent_header()
	 * @uses	wp_remote_retrieve_body()
	 * @uses	WC_Gateway_Afterpay::log()
	 * @return	StdClass|WP_Error|false
	 * @used-by	self::get_payment_types()
	 * @used-by	self::get_configuration()
	 * @used-by	self::get_order()
	 */
	private function get_from_api($url) {
		WC_Gateway_Afterpay::log("GET {$url}");

		$response = wp_remote_get( $url, array(
			'timeout' => 80,
			'headers' => array(
				'Authorization' => $this->build_authorization_header(),
				'User-Agent' => $this->build_user_agent_header(),
				'Accepts' => 'application/json'
			)
		));

		if (!is_wp_error( $response )) {
			$body = json_decode(wp_remote_retrieve_body( $response ));

			if (!is_null($body)) {
				$return_obj = new \StdClass;
				$return_obj->body = $body;

				return $return_obj;
			}
		} else {
			# Unable to establish a secure connection with the Afterpay API endpoint.
			# Likely a TLS or network error.
			# Log the error details.
			foreach ($response->errors as $code => $messages_arr) {
				$messages_str = implode("\n", $messages_arr);
				WC_Gateway_Afterpay::log("API NETWORK ERROR! Code: \"{$code}\"; Message(s):\n" . $messages_str);
			}
			
			# Get CloudFlare Header for the error
			$cf_ray = wp_remote_retrieve_header($response, "cf-ray");

			if (!empty($cf_ray)) {
				WC_Gateway_Afterpay::log("Error CF-Ray: " .  $cf_ray);	
			}
			else {
				WC_Gateway_Afterpay::log("No CF-Ray Detected");
			}

			# Return the WP_Error object.
			return $response;
		}

		return false;
	}

	/**
	 * POST JSON to an API endpoint and load the response.
	 *
	 * @since	2.0.0
	 * @param	string	$url	The full URL to the API Endpoint.
	 * @param	mixed	$data	The jsonifiable data to be posted to the API.
	 * @uses	wp_remote_post()
	 * @uses	self::build_authorization_header()
	 * @uses	self::build_user_agent_header()
	 * @uses	self::check_idempotency_trigger()
	 * @uses	wp_remote_retrieve_body()
	 * @uses	WC_Gateway_Afterpay::log()
	 * @return	StdClass|false
	 * @used-by	self::get_payment_types_for_amount()
	 * @used-by	self::get_order_token_for_afterpay_quote()
	 * @used-by	self::direct_payment_capture()
	 * @used-by	self::create_refund()
	 */
	private function post_to_api($url, $data) {
		WC_Gateway_Afterpay::log("POST {$url}");
		
		# Differentiate Capture Timeout from the other POST
		if (!empty($url) && (strpos($url, 'capture') !== false || strpos($url, 'refund') !== false)) {
			$timeout = 5;
		}
		else {
			$timeout = 80;
		}

		$response = wp_remote_post( $url, array(
			'timeout' => $timeout,
			'headers' => array(
				'Authorization' => $this->build_authorization_header(),
				'User-Agent' => $this->build_user_agent_header(),
				'Content-Type' => 'application/json',
				'Accepts' => 'application/json'
			),
			'body' => json_encode($data)
		) );


		//Forced Idempotency Trigger
		$response = $this->check_idempotency_trigger($url, $data, $response);

		if (!is_wp_error( $response )) {
			$body = json_decode(wp_remote_retrieve_body( $response ));

			if (!is_null($body)) {
				$return_obj = new \StdClass;
				$return_obj->body = $body;

				return $return_obj;
			}
		} else {
			# Unable to establish a secure connection with the Afterpay API endpoint.
			# Likely a TLS or network error.
			# Log the error details.
			foreach ($response->errors as $code => $messages_arr) {
				$messages_str = implode("\n", $messages_arr);
				WC_Gateway_Afterpay::log("API NETWORK ERROR! Code: \"{$code}\"; Message(s):\n" . $messages_str);
			}

			# Get CloudFlare Header for the error
			$cf_ray = wp_remote_retrieve_header($response, "cf-ray");

			if (!empty($cf_ray)) {
				WC_Gateway_Afterpay::log("Error CF-Ray: " .  $cf_ray);	
			}
			else {
				WC_Gateway_Afterpay::log("No CF-Ray Detected");
			}

			# Return the WP_Error object.
			return $response;
		}

		return false;
	}

	/**
	 * Get the valid payment types for this merchant.
	 *
	 * Note:	This is only for API v0.
	 *
	 * @since	2.0.0
	 * @uses	self::get_from_api()
	 * @uses	self::store_api_error()
	 * @uses	WC_Admin_Settings::add_error()
	 * @uses	self::clear_api_error()
	 * @uses	WC_Gateway_Afterpay::log()
	 * @return	array|false					The list of available types, or false on error.
	 */
	public function get_payment_types() {
		$response = $this->get_from_api($this->payment_types_url);

		if (is_wp_error( $response )) {
			# Unable to establish a secure connection with the Afterpay API endpoint.
			# Likely a TLS or network error.
			# Show the error throughout the admin until corrected.
			$error_codes_arr = $response->get_error_codes();
			$error = new \StdClass;
			$error->timestamp = date('Y-m-d H:i:s');
			$error->code = 500;
			$error->message = 'The Afterpay Gateway for WooCommerce plugin cannot communicate with the Afterpay API.';
			if (count($error_codes_arr) > 0) {
				$error->message .= ' Error code(s) returned: "' . implode('", "', $error_codes_arr) . '".';
			}
			$this->store_api_error($error);
			if (is_admin()) {
				# Admin has just saved the settings. The "admin_notices" action has already triggered, so we need
				# to implement WooCommerce's secondary notice reporting function instead.
				WC_Admin_Settings::add_error(__( $error->message, 'woo_afterpay' ));
			}
		} elseif (is_object($response)) {
			$body = $response->body;

			if (is_array($body)) {
				$this->clear_api_error();
				return $body;
			} elseif (is_object($body) && property_exists($body, 'errorCode')) {
				# Log the error details.
				WC_Gateway_Afterpay::log("API ERROR #{$body->httpStatusCode} \"{$body->errorCode}\": {$body->message} (Error ID: {$body->errorId})");

				# Also display a simplified version of the error to the admin.
				$error = new \StdClass;
				$error->timestamp = date('Y-m-d H:i:s');
				if (property_exists($body, 'httpStatusCode') && $body->httpStatusCode == 401) {
					$error->code = 401;
					$error->message = 'Your Afterpay API credentials are incorrect.';
				} else {
					$error->code = 500;
					$error->message = 'The Afterpay Gateway for WooCommerce plugin cannot communicate with the Afterpay API.';
				}
				if (property_exists($body, 'errorId') && $body->errorId) {
					$error->id = $body->errorId;
				}
				$this->store_api_error($error);
				if (is_admin()) {
					# Admin has just saved the settings. The "admin_notices" action has already triggered, so we need
					# to implement WooCommerce's secondary notice reporting function instead.
					$text = __( "Afterpay API Error #{$error->code}: {$error->message}", 'woo_afterpay' );
					if (property_exists($error, 'id') && $error->id) {
						$text .= __( " (Error ID: {$error->id})", 'woo_afterpay' );
					}
					WC_Admin_Settings::add_error($text);
				}
			}
		} elseif ($response === false) {
			# Response is false
		}

		return false;
	}

	/**
	 * Get the configuration (valid payment types and limits) for this merchant.
	 *
	 * Note:	This is only for API v1.
	 *
	 * @since	2.0.0
	 * @uses	self::get_from_api()
	 * @uses	self::store_api_error()
	 * @uses	WC_Admin_Settings::add_error()
	 * @uses	self::clear_api_error()
	 * @uses	WC_Gateway_Afterpay::log()
	 * @return	array|false					The list of available payment types, or false on error.
	 */
	public function get_configuration() {
		$response = $this->get_from_api($this->configuration_url);

		if (is_wp_error( $response )) {
			# Unable to establish a secure connection with the Afterpay API endpoint.
			# Likely a TLS or network error.
			# Show the error throughout the admin until corrected.
			$error_codes_arr = $response->get_error_codes();
			$error = new \StdClass;
			$error->timestamp = date('Y-m-d H:i:s');
			$error->code = 500;
			$error->message = 'The Afterpay Gateway for WooCommerce plugin cannot communicate with the Afterpay API.';
			if (count($error_codes_arr) > 0) {
				$error->message .= ' Error code(s) returned: "' . implode('", "', $error_codes_arr) . '".';
			}
			$this->store_api_error($error);
			if (is_admin()) {
				# Admin has just saved the settings. The "admin_notices" action has already triggered, so we need
				# to implement WooCommerce's secondary notice reporting function instead.
				WC_Admin_Settings::add_error(__( $error->message, 'woo_afterpay' ));
			}
		} elseif (is_object($response)) {
			$body = $response->body;

			if (is_array($body)) {
				$this->clear_api_error();
				return $body;
			} elseif (is_object($body) && property_exists($body, 'errorCode')) {
				# Log the error details.
				WC_Gateway_Afterpay::log("API ERROR #{$body->httpStatusCode} \"{$body->errorCode}\": {$body->message} (Error ID: {$body->errorId})");

				# Also display a simplified version of the error to the admin.
				$error = new \StdClass;
				$error->timestamp = date('Y-m-d H:i:s');
				if (property_exists($body, 'httpStatusCode') && $body->httpStatusCode == 401) {
					$error->code = 401;
					$error->message = 'Your Afterpay API credentials are incorrect.';
				} else {
					$error->code = 500;
					$error->message = 'The Afterpay Gateway for WooCommerce plugin cannot communicate with the Afterpay API.';
				}
				if (property_exists($body, 'errorId') && $body->errorId) {
					$error->id = $body->errorId;
				}
				$this->store_api_error($error);
				if (is_admin()) {
					# Admin has just saved the settings. The "admin_notices" action has already triggered, so we need
					# to implement WooCommerce's secondary notice reporting function instead.
					$text = __( "Afterpay API Error #{$error->code}: {$error->message}", 'woo_afterpay' );
					if (property_exists($error, 'id') && $error->id) {
						$text .= __( " (Error ID: {$error->id})", 'woo_afterpay' );
					}
					WC_Admin_Settings::add_error($text);
				}
			}
		} elseif ($response === false) {
			# Response is false
		}

		return false;
	}

	/**
	 * Get the valid payment types available from Afterpay for this amount.
	 *
	 * Note:	This is only for API v0.
	 *
	 * @since	2.0.0
	 * @param	float	$order_total	Order Total Amount
	 * @uses	self::build_money_object()
	 * @uses	self::post_to_api()
	 * @return	array|false					The list of available types, or false on error.
	 */
	public function get_payment_types_for_amount($order_total) {
		$response = $this->post_to_api($this->payment_types_url, array(
			'orderAmount' => $this->build_money_object($order_total)
		));

		if (is_wp_error( $response )) {
			# WP Error Detected
		} elseif (is_object($response)) {
			$body = $response->body;

			if (is_array($body)) {
				return $body;
			} elseif (is_object($body) && property_exists($body, 'errorCode')) {
				# Log the error details.
				WC_Gateway_Afterpay::log("API ERROR #{$body->httpStatusCode} \"{$body->errorCode}\": {$body->message} (Error ID: {$body->errorId})");
			}
		} elseif ($response === false) {
			# Response is false
		}

		return false;
	}

	/**
	 * Request an order token from Afterpay.
	 *
	 * Note:	This is only for API v0.
	 *
	 * @since	2.0.0
	 * @param	WC_Order|null	$order	An instance of WC_Order or null.
	 * @param	string			$type	Payment type. Defaults to "PBI".
	 * @uses	self::build_money_object()
	 * @uses	wc_get_product()		Available in WooCommerce core since 2.2.0.
	 *									Also see:	WC()->product_factory->get_product()
	 *									Also see:	WC_Product_Factory::get_product()
	 * @return	string|bool				Returns the token string or false if no order token could be generated.
	 * @used-by	WC_Gateway_Afterpay::process_payment()
	 */
	public function get_order_token_for_wc_order($order = null, $type = 'PBI') {
		if (!($order instanceof WC_Order)) {
			return false;
		}

		$data = array(
			'consumer' => array(
				'mobile' => $order->billing_phone,
				'givenNames' => $order->billing_first_name,
				'surname' => $order->billing_last_name,
				'email' => $order->billing_email
			),
			'paymentType' => $type,
			'orderDetail' => array(
				'merchantOrderDate' => time(),
				'merchantOrderId' => $order->id,
				'items' => array(), # Populated below.
				'includedTaxes' => $this->build_money_object($order->get_cart_tax()),
				'shippingAddress' => array(
					'name' => $order->shipping_first_name . ' ' . $order->shipping_last_name,
					'address1' => $order->shipping_address_1,
					'address2' => $order->shipping_address_2,
					'suburb' => $order->shipping_city,
					'postcode' => $order->shipping_postcode
				),
				'billingAddress' => array(
					'name' => $order->billing_first_name . ' ' . $order->billing_last_name,
					'address1' => $order->billing_address_1,
					'address2' => $order->billing_address_2,
					'suburb' => $order->billing_city,
					'postcode' => $order->billing_postcode
				),
				'orderAmount' => $this->build_money_object($order->get_total())
			)
		);

		$order_items = $order->get_items();

		if (count($order_items)) {
			foreach ($order_items as $item) {
				if ($item['variation_id']) {
					if (function_exists('wc_get_product')) {
						$product = wc_get_product( $item['variation_id'] );
					} else {
						$product = new WC_Product( $item['variation_id'] );
					}
				} else {
					if (function_exists('wc_get_product')) {
						$product = wc_get_product( $item['product_id'] );
					} else {
						$product = new WC_Product( $item['product_id'] );
					}
				}

				$data['orderDetail']['items'][] = array(
					'name' => $item['name'],
					'sku' => $product->get_sku(),
					'quantity' => $item['qty'],
					'price' => $this->build_money_object($item['line_subtotal'] / $item['qty'])
				);
			}
		}

		if ($order->get_shipping_method()) {
			$data['orderDetail']['shippingCourier'] = substr($order->get_shipping_method(), 0, 127);
			$data['orderDetail']['shippingCost'] = $this->build_money_object($order->get_total_shipping());
		}

		if ($order->get_total_discount()) {
			$data['orderDetail']['discountType'] = 'Discount';
			$data['orderDetail']['discount'] = $this->build_money_object(0 - $order->get_total_discount());
		}

		$response = $this->post_to_api($this->orders_url, $data);

		if (is_wp_error( $response )) {
			# A WP Error is encountered
		} elseif (is_object($response)) {
			$body = $response->body;

			if (is_object($body)) {
				if (property_exists($body, 'orderToken') && is_string($body->orderToken) && strlen($body->orderToken) > 0) {
					return $body->orderToken;
				} elseif (property_exists($body, 'errorCode')) {
					# Log the error details.
					WC_Gateway_Afterpay::log("API ERROR #{$body->httpStatusCode} \"{$body->errorCode}\": {$body->message} (Error ID: {$body->errorId})");
				}
			} else {
				# Body is not an Object
			}
		} elseif ($response === false) {
			# Response is false
		}

		return false;
	}

	/**
	 * Request an order token from Afterpay.
	 *
	 * Note:	This is only for API v1.
	 *
	 * @since	2.0.0
	 * @param	array|null		$data	The jsonifiable order data to be posted to the API.
	 * @return	string|false
	 * @used-by	WC_Gateway_Afterpay::override_order_creation()
	 */
	public function get_order_token_for_afterpay_quote($data = null) {
		$response = $this->post_to_api($this->orders_url, $data);

		if (is_wp_error( $response )) {
			# A WP Error is encountered
		} elseif (is_object($response)) {
			$body = $response->body;

			if (is_object($body)) {
				if (property_exists($body, 'token') && is_string($body->token) && strlen($body->token) > 0) {
					return $body;
				} elseif (property_exists($body, 'errorCode')) {
					# Log the error details.
					WC_Gateway_Afterpay::log("API ERROR #{$body->httpStatusCode} \"{$body->errorCode}\": {$body->message} (Error ID: {$body->errorId})");
				}
			} else {
				# Body is not an Object
			}
		} elseif ($response === false) {
			# Response is false
		}

		return false;
	}

	/**
	 * Post a direct payment capture request for a given token.
	 *
	 * Note:	This is only for API v1.
	 *
	 * @since	2.0.0
	 * @param	string			$token				The token that Afterpay gave us for the order.
	 * @param	string			$afterpay_quote_id	The Merchant Order Number.
	 * @return	string|false						Either "APPROVED", "DECLINED" or false.
	 * @used-by	WC_Gateway_Afterpay::confirm_afterpay_quote()
	 */
	public function direct_payment_capture($token, $afterpay_quote_id = '') {
		
		$quote = get_post($afterpay_quote_id);
		$gateway = new WC_Gateway_Afterpay;
		
		$data = array(
			'token' => $token
		);

		if (!empty($afterpay_quote_id)) {
			$data['merchantReference'] = $afterpay_quote_id;
		}

		$response = $this->post_to_api($this->direct_payment_capture_url, $data);

		if (is_wp_error( $response )) {

			# Log the WP Error object.
			$error_code = json_encode($response->get_error_codes());
			$error_messages = json_encode($response->get_error_messages());

			$log_str = "WP ERROR: {$error_code}; WP ERROR MESSAGES: {$error_messages}";
			WC_Gateway_Afterpay::log($log_str);
			
			# Add the Order to Idempotent Queue
			$this->add_quote_to_retry($afterpay_quote_id, $token);


		} elseif (is_object($response)) {
			$body = $response->body;

			if (is_object($body)) {
				if (property_exists($body, 'status') && is_string($body->status) && strlen($body->status) > 0) {
					# Note: $body->status will be either "APPROVED" or "DECLINED".

					# If successful, attach the Afterpay Order ID to the quote.
					if (property_exists($body, 'id') && $body->id && $afterpay_quote_id > 0) {
						add_post_meta( $afterpay_quote_id, 'afterpay_order_id', (int)$body->id );
					}

					# Log the response.
					$log_str = "PAYMENT {$body->status}";
					if (property_exists($body, 'id') && $body->id) {
						$log_str .= " (Afterpay Order ID: {$body->id})";
					}
					if (property_exists($body, 'errorId') && $body->errorId) {
						$log_str .= " (Error ID: {$body->errorId})";
					}
					WC_Gateway_Afterpay::log($log_str);

					# Return the status response.
					return $body->status;
				} elseif (property_exists($body, 'httpStatusCode') && $body->httpStatusCode == 402) {
					# Note: If the payment is declined, the API will probably throw a 402 error instead of $body->status == "DECLINED".

					# Log the decline.
					WC_Gateway_Afterpay::log('PAYMENT DECLINED' . ((property_exists($body, 'errorId') && $body->errorId) ? " (Error ID: {$body->errorId})" : ''));

					# Return the standardised status response.
					return 'DECLINED';
				} elseif (property_exists($body, 'errorCode')) {
					# Log the error details.
					WC_Gateway_Afterpay::log("API ERROR #{$body->httpStatusCode} \"{$body->errorCode}\": {$body->message} (Error ID: {$body->errorId})");

					if (property_exists($body, 'httpStatusCode') && ($body->httpStatusCode == 409 || $body->httpStatusCode == 503))  {
						# Add to Idempotent Queue
						$this->add_quote_to_retry($afterpay_quote_id, $token);
					}
				} else {
					# Empty Body 
					# Add to Idempotent Queue
					$this->add_quote_to_retry($afterpay_quote_id, $token);
				}
			} else {
				# Body is not an object
				# Add to Idempotent Queue
				$this->add_quote_to_retry($afterpay_quote_id, $token);
			}
		} elseif ($response === false) {
			# Response is not an object
			# Add to Idempotent Queue
			$this->add_quote_to_retry($afterpay_quote_id, $token);
		} else {
			# Response is not false, not an object and no WP Error
			# Add to Idempotent Queue
			$this->add_quote_to_retry($afterpay_quote_id, $token);
		}

		return false;
	}

	/**
	 * Post a refund request for a given payment.
	 *
	 * @since	2.0.0
	 * @param	WC_Order	$order	The WooCommerce order that this refund relates to.
	 * @param	float		$amount	The amount to be refunded.
	 * @param	int			$count	The count indicator for Idempotency
	 * @param	string		$refund_request_id	The Refund Request ID unique to this Refund 
	 * @uses	self::build_money_object()
	 * @uses	self::post_to_api()
	 * @uses	property_exists()
	 * @uses	is_object()
	 * @uses	is_wp_error()
	 * @used-by	WC_Gateway_Afterpay::process_refund()
	 */
	public function create_refund($order, $amount, $count = 0, $refund_request_id = NULL) {
		$afterpay_order_id = $order->get_transaction_id();

		if ($this->api_version == 'v0') {
			$response = $this->post_to_api("{$this->orders_url}/{$afterpay_order_id}/refunds", array(
				'amount' => $this->build_money_object(0 - $amount, $order->get_order_currency()),
				'merchantRefundId' => ''
			));
			$refund_id_property = 'id';
		} elseif ($this->api_version == 'v1') {

			# construct Request ID for Refund
			$merchant_id = $this->cleanup_string($this->gateway->get_merchant_id());
			$time = microtime(true);

			# Use microtime to prevent Request ID duplicate if refunding during Partial Refunds
			if (empty($refund_request_id)) {
				$refund_request_id = wp_create_nonce("afterpay_be_refund_nonce-{$merchant_id}-{$afterpay_order_id}-{$time}");
			}

			$response = $this->post_to_api("{$this->payments_url}/{$afterpay_order_id}/refund", array(
				'amount' => $this->build_money_object($amount, $order->get_order_currency()),
				'merchantReference' => '',
				'requestId' => $refund_request_id
			));
			$refund_id_property = 'refundId';
		} else {
			# unknown API - do nothing
			$response = false;
		}

		# force an error on refund
		if ($count < 3 && strtolower($order->get_billing_first_name()) == "idempotency" && strtolower($order->get_billing_last_name()) == "test") {
			$response = false;
		}


		if (is_wp_error($response) || $response === false) {

			if ($this->gateway->get_api_version() == 'v1') {
				# In the event of Error or False Response, run the refund up until 3 retries and then stop the refund process 
				return $this->add_refund_to_retry($order, $amount, $count, $refund_request_id);	
			}
			else {
				return false;
			}

		} elseif (is_object($response)) {
			$body = $response->body;

			if (is_object($body)) {
				if (property_exists($body, $refund_id_property) && $body->{$refund_id_property}) {
					# Log the ID.
					WC_Gateway_Afterpay::log("Refund succesful. Refund ID: {$body->{$refund_id_property}}. Request ID: {$refund_request_id}");

					# Return true.
					return true;
				} elseif (property_exists($body, 'errorCode')) {
					# Log the error details.
					WC_Gateway_Afterpay::log("API ERROR #{$body->httpStatusCode} \"{$body->errorCode}\": {$body->message} (Error ID: {$body->errorId})");
					
					# Add to Refund Idempotency Queue
					if (property_exists($body, 'httpStatusCode') && ($body->httpStatusCode == 409 || $body->httpStatusCode == 503))  {
						return $this->add_refund_to_retry($order, $amount, $count, $refund_request_id);
					}
				} else {
					# Missing required Body Response
					# Add to Refund Idempotency Queue
					return $this->add_refund_to_retry($order, $amount, $count, $refund_request_id);
				}
			} else {
				# Response body is not an object
				return $this->add_refund_to_retry($order, $amount, $count, $refund_request_id);
			}
		}

		return false;
	}

	/**
	 * Do a recursive Create Refund for Idempotency.
	 *
	 * @since	2.0.0
	 * @param	WC_Order	$order	The WooCommerce order that this refund relates to.
	 * @param	float		$amount	The amount to be refunded.
	 * @param	int			$count	The retry count.
	 * @param	string		$refund_request_id	The Refund ID Nonce.
	 * @uses	self::create_refund()
	 * @uses	WC_Gateway_Afterpay::log()
	 * @used-by	self::create_refund()
	 */
	private function add_refund_to_retry($order, $amount, $count, $refund_request_id) {
		$count++;
		if ($count <= 5) {
			WC_Gateway_Afterpay::log("WP Error - Refund Retry Run: #{$count}. Request ID: {$refund_request_id}");
			return $this->create_refund($order, $amount, $count, $refund_request_id);
		}
		else {
			return false;
		}
	}

	/**
	 * Get the Afterpay Order Details
	 * Note:	This is only for API v0.
	 *
	 * @since	2.0.0
	 * @param	WC_Order|null	$order					The WooCommerce order that we want to find out about.
	 * @param	string			$afterpay_order_id		Optional. The ID of the Afterpay order that we want to
	 *													find out about. Defaults to "".
	 * @uses	self::get_from_api()
	 * @return	StdClass|false
	 * @used-by	WC_Gateway_Afterpay::payment_callback()
	 * @used-by	Afterpay_Plugin_Cron::check_pending_abandoned_orders()
	 */
	public function get_order($order, $afterpay_order_id = '') {
		if ($this->api_version != 'v0') {
			return false;
		}

		if (!empty($afterpay_order_id)) {
			$endpoint_url = "{$this->orders_url}/{$afterpay_order_id}";
		} elseif ($order instanceof WC_Order) {
			$custom_keys = get_post_custom_keys($order->id);
			if (in_array('_transaction_id', $custom_keys)) {
				# Use the Afterpay Order ID if available.
				$afterpay_order_id = get_post_meta( $order->id, '_transaction_id', true );
				$endpoint_url = "{$this->orders_url}/{$afterpay_order_id}";
			} elseif (in_array('_afterpay_token', $custom_keys)) {
				# Otherwise use the Afterpay Order token.
				$afterpay_order_token = get_post_meta( $order->id, '_afterpay_token', true );
				$endpoint_url = "{$this->orders_url}?token={$afterpay_order_token}";
			} else {
				# Missing vital arguments, failing the operations
				return false;
			}
		} else {
			# Invalid arguments.
			return false;
		}

		$response = $this->get_from_api($endpoint_url);

		if (is_wp_error( $response )) {
			# WP Error Detected
		} elseif (is_object($response)) {
			$body = $response->body;

			if (is_object($body)) {
				if (property_exists($body, 'id') && property_exists($body, 'status')) {
					return $body;
				} elseif (property_exists($body, 'errorCode')) {
					# Log the error details.
					WC_Gateway_Afterpay::log("API ERROR #{$body->httpStatusCode} \"{$body->errorCode}\": {$body->message} (Error ID: {$body->errorId})");
				}
			} else {
				# Body is not an Object
			}
		} elseif ($response === false) {
			# Response is not an Object
		}

		return false;
	}
	
	/**
	 * Add the Afterpay Quote Object into Idempotency Queue
	 * Note:	This is only for API V1.
	 *
	 * @since	2.0.0
	 * @param	int				$afterpay_quote_id		Required. The Afterpay Quote ID
	 * @param	string			$token					Required. The Afterpay Token for this Quote
	 * 
	 * @uses	add_post_meta()
	 * @uses	update_post_meta()
	 * @used-by	WC_Gateway_Afterpay::direct_payment_capture()
	 */
	public function add_quote_to_retry($afterpay_quote_id, $token) {
		$quote = get_post($afterpay_quote_id);

		# Flag the Quote as needing a Capture Re-check
		if (!add_post_meta($afterpay_quote_id, '_quote_retry', true, true)) { 
		   update_post_meta($afterpay_quote_id, '_quote_retry', true);
		}

		$args = array(
					'token' 	=> 	$token,
					'quote_id' 	=> 	$afterpay_quote_id
				);

		$gateway = new WC_Gateway_Afterpay;
		$afterpay_fe_retry_nonce = wp_create_nonce("afterpay_fe_retry_nonce-{$afterpay_quote_id}");
		$retry_url = $gateway->build_afterpay_quote_url($afterpay_quote_id, 'fe-capture-retry', $afterpay_fe_retry_nonce, $args);

		wp_redirect($retry_url);
		die();
	}
	
	/**
	 * Add a forced Idempotency Test for the plugin
	 * Note:	This is only for API V1.
	 *
	 * @since	2.0.0
	 * @param	string			$url		Required. The Target URL for the API Call
	 * @param	string			$data		Required. The Afterpay Data sent with the API Call
	 * @param	string			$response	Required. The original response for the Afterpay API Call
	 * 
	 * @uses	wp_remote_retrieve_body()
	 * @uses	WP_Error::class
	 * @used-by	self::post_to_api()
	 */
	public function check_idempotency_trigger($url, $data, $response) {
	
		if (!empty($url) && (strpos($url, 'capture') !== false || strpos($url, 'refund') !== false)) {
			
			$body = json_decode(wp_remote_retrieve_body( $response ));

			if (property_exists($body, 'orderDetails') && $body->orderDetails) {
				$details = $body->orderDetails;

				if (property_exists($details, 'consumer') && $details->consumer) {
					$consumer = $details->consumer;
					if (property_exists($consumer, 'givenNames') && $consumer->givenNames && strtolower($consumer->givenNames) == "idempotency" &&
						property_exists($consumer, 'surname') && $consumer->surname && strtolower($consumer->surname) == "test") {
						$ref = $data['merchantReference'];
						$quote_retry = get_post_meta($ref, '_quote_retry', true);
						
						if (!$quote_retry) {
							return new WP_Error( 'idempotency_test', __( "This is forced Idempotency test", "afterpay" ) );
						}
					}
				}
			}
		}

		return $response;
	}
}
