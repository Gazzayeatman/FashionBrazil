<?php
/**
* Afterpay Plugin CRON Handler Class
*/
class Afterpay_Plugin_Cron
{
	/**
	 * Create a new WP-Cron job scheduling interval so jobs can run "Every 15 minutes".
	 *
	 * Note:	Hooked onto the "cron_schedules" Filter.
	 *
	 * @since	2.0.0
	 * @param	array	$schedules	The current array of cron schedules.
	 * @return	array				Array of cron schedules with 15 minutes added.
	 **/
	public static function edit_cron_schedules($schedules) {
		$schedules['15min'] = array(
			'interval' => 15 * 60, 
			'display' => __( 'Every 15 minutes', 'woo_afterpay' )
		);
		return $schedules;
	}

	/**
	 * Schedule the WP-Cron job for Afterpay.
	 *
	 * @since	2.0.0
	 * @see		Afterpay_Plugin::activate_plugin()
	 * @uses	wp_next_scheduled()
	 * @uses	wp_schedule_event()
	 **/
	public static function create_jobs() {
		$timestamp = wp_next_scheduled( 'afterpay_do_cron_jobs' );
		if ($timestamp == false) {
			wp_schedule_event( time(), '15min', 'afterpay_do_cron_jobs' );
		}
	}

	/**
	 * Delete the Afterpay WP-Cron job.
	 *
	 * @since	2.0.0
	 * @see		Afterpay_Plugin::deactivate_plugin()
	 * @uses	wp_clear_scheduled_hook()
	 **/
	public static function delete_jobs() {
		wp_clear_scheduled_hook( 'afterpay_do_cron_jobs' );
	}

	/**
	 * Fire the Afterpay WP-Cron job.
	 *
	 * Note:	Hooked onto the "afterpay_do_cron_jobs" Action, which exists
	 *			because we scheduled a cron under that key when the plugin was activated.
	 *
	 * Note:	Hooked onto the "woocommerce_update_options_payment_gateways_afterpay"
	 *			Action as well.
	 *
	 * @since	2.0.0
	 * @see		Afterpay_Plugin::__construct()	For hook attachment.
	 * @see		self::create_jobs()				For initial scheduling (on plugin activation).
	 * @uses	is_admin()
	 * @uses	WC_Gateway_Afterpay::log()
	 * @uses	self::check_pending_abandoned_orders()
	 * @uses	self::update_payment_limits()
	 * @uses	self::cleanup_unused_quotes()
	 */
	public static function fire_jobs() {
		if (defined('DOING_CRON') && DOING_CRON === true) {
			$fired_by = 'schedule';
		} elseif (is_admin()) {
			$fired_by = 'admin';
		} else {
			$fired_by = 'unknown';
		}
		WC_Gateway_Afterpay::log("Firing cron by {$fired_by}...");

		self::update_payment_limits();
		self::check_pending_abandoned_orders();
		self::cleanup_unused_quotes();
	}

	/**
	 * Load Afterpay Settings
	 *
	 * Note:	Get the plugin settings to be processed within teh CRON
	 *			
	 *
	 * @since	2.0.0-rc3
	 * @return 	string
	 *
	 * @uses	WC_Gateway_Afterpay::get_option_key()	Getting the Plugin Settings Key in DB
	 * @used-by	self::update_payment_limits()
	 * @used-by	self::cleanup_unused_quotes()
	 */
	private static function get_settings_key() {
		$gateway = new WC_Gateway_Afterpay;
		$settings_key = $gateway->get_option_key();
		return $settings_key;
	}

	/**
	 * Load this merchant's payment limits from the API.
	 *
	 * Note:	If this fails, an error will be stored in the database, which should display throughout
	 *			the admin area until resolved.
	 *
	 * @since	2.0.0
	 * @uses	Afterpay_Plugin_Merchant::get_payment_types()	If configured to use v0.
	 * @uses	WC_Gateway_Afterpay::log()
	 * @uses	Afterpay_Plugin_Merchant::get_configuration()	If configured to use v1.
	 * @uses	self::get_settings_key()
	 * @used-by	self::fire_jobs()
	 */
	private static function update_payment_limits() {
		
		$settings_key = self::get_settings_key();
		$settings = get_option( $settings_key );

		if ($settings['enabled'] == 'yes') {
			if ($settings['testmode'] == 'production') {
				if (empty($settings['prod-id']) && empty($settings['prod-secret-key'])) {
					# Don't hit the Production API without Production creds.
					return false;
				}
			} elseif ($settings['testmode'] == 'sandbox') {
				if (empty($settings['test-id']) && empty($settings['test-secret-key'])) {
					# Don't hit the Sandbox API without Sandbox creds.
					return false;
				}
			}
		} else {
			# Don't hit any API when the gateway is not Enabled.
			return false;
		}

		$merchant = new Afterpay_Plugin_Merchant;
		$settings_changed = false;

		if ($settings['api-version'] == 'v0') {
			$payment_types = $merchant->get_payment_types();

			if (is_array($payment_types)) {
				foreach ($payment_types as $payment_type) {
					if ($payment_type->type == 'PBI') {
						$old_min = $settings['pay-over-time-limit-min'];
						$old_max = $settings['pay-over-time-limit-max'];
						$new_min = (property_exists($payment_type, 'minimumAmount') && is_object($payment_type->minimumAmount)) ? $payment_type->minimumAmount->amount : '0.00';
						$new_max = (property_exists($payment_type, 'maximumAmount') && is_object($payment_type->maximumAmount)) ? $payment_type->maximumAmount->amount : '0.00';
						if ($new_min != $old_min) {
							$settings_changed = true;
							WC_Gateway_Afterpay::log("Cron changing payment limit MIN from '{$old_min}' to '{$new_min}'.");
							$settings['pay-over-time-limit-min'] = $new_min;
						}
						if ($new_max != $old_max) {
							$settings_changed = true;
							WC_Gateway_Afterpay::log("Cron changing payment limit MAX from '{$old_max}' to '{$new_max}'.");
							$settings['pay-over-time-limit-max'] = $new_max;
						}
					}
				}
			}
			else {
				$settings_changed = true;
				$settings['pay-over-time-limit-min'] = 0;
				$settings['pay-over-time-limit-max'] = 0;
			}
		} elseif ($settings['api-version'] == 'v1') {
			$payment_configurations = $merchant->get_configuration();

			if (is_array($payment_configurations)) {
				foreach ($payment_configurations as $payment_configuration) {
					if ($payment_configuration->type == 'PAY_BY_INSTALLMENT') {
						$old_min = $settings['pay-over-time-limit-min'];
						$old_max = $settings['pay-over-time-limit-max'];
						$new_min = (property_exists($payment_configuration, 'minimumAmount') && is_object($payment_configuration->minimumAmount)) ? $payment_configuration->minimumAmount->amount : '0.00';
						$new_max = (property_exists($payment_configuration, 'maximumAmount') && is_object($payment_configuration->maximumAmount)) ? $payment_configuration->maximumAmount->amount : '0.00';
						if ($new_min != $old_min) {
							$settings_changed = true;
							WC_Gateway_Afterpay::log("Cron changing payment limit MIN from '{$old_min}' to '{$new_min}'.");
							$settings['pay-over-time-limit-min'] = $new_min;
						}
						if ($new_max != $old_max) {
							$settings_changed = true;
							WC_Gateway_Afterpay::log("Cron changing payment limit MAX from '{$old_max}' to '{$new_max}'.");
							$settings['pay-over-time-limit-max'] = $new_max;
						}
					}
				}
			}
			else {
				$settings_changed = true;
				$settings['pay-over-time-limit-min'] = 0;
				$settings['pay-over-time-limit-max'] = 0;
			}
		}

		if ($settings_changed) {
			update_option( $settings_key, $settings );
		}
	}

	/**
	 * Check the order status of all orders that didn't return to the thank you page or marked as Pending by Afterpay.
	 *
	 * Note:	This is only applicable for API v0.
	 * Note:	WordPress would have used its timezone config when inserting the post,
	 *			not PHP/system time or MySQL time. Therefore, we use current_time() in
	 *			our WP_Date_Query clauses.
	 *
	 * @since	2.0.0
	 * @uses	get_posts()
	 * @uses	Afterpay_Plugin_Merchant::get_order()
	 * @used-by	self::fire_jobs()
	 */
	private static function check_pending_abandoned_orders() {
		# Get "Pending Payment" orders that may have been abandoned, or browser window closed after approved.

		$paged = 1;
		do {
			$pending_orders = get_posts( array(
				'post_type' => 'shop_order',
				'post_status' => 'wc-pending',
				'meta_query' => array(
					array(
						'relation' => 'AND',
						array(
							'key' => '_payment_method',
							'value' => 'afterpay',
							'compare' => '=',
							'type' => 'CHAR'
						),
						array(
							'key' => '_afterpay_token',
							'compare' => 'EXISTS'
						)
					)
				),
				'paged' => $paged,
				'posts_per_page' => 5,
				'orderby' => 'ID',
				'order' => 'ASC'
			) );

			foreach ($pending_orders as $post) {
				WC_Gateway_Afterpay::log("Checking WC_Order #{$post->ID}...");

				if (function_exists('wc_get_order')) {
					$order = wc_get_order( $post->ID );	
				} else {
					$order = new WC_Order( $post->ID );
				}

				$merchant = new Afterpay_Plugin_Merchant;
				$response = $merchant->get_order($order);

				if ($response === false) {
					WC_Gateway_Afterpay::log("Afterpay_Plugin_Merchant::get_order() returned false.");

					if (strtotime(current_time( 'mysql' )) - strtotime($order->get_date_completed()) > (60 * 75)) {
						# The order has been On Hold for more than 75 min, and we can't find
						# a corresponding order in the Afterpay Merchant Portal.

						WC_Gateway_Afterpay::log("Updating status of WooCommerce Order #{$order->id} to \"Failed\".");

						$order->add_order_note( __( 'Pending Order Expired', 'woo_afterpay' ) );
						$order->update_status( 'failed' );
					}
				} elseif (is_object($response)) {
					WC_Gateway_Afterpay::log("Afterpay_Plugin_Merchant::get_order() returned an order with a status of \"{$response->status}\".");

					if ($response->status == 'APPROVED') {
						WC_Gateway_Afterpay::log("Updating status of WooCommerce Order #{$order->id} to \"Processing\".");

						$order->add_order_note( sprintf(__( 'Checked payment status with Afterpay. Payment approved. Afterpay Order ID: %s', 'woo_afterpay' ), $response->id) );
						$order->payment_complete($response->id);
					} else {
						WC_Gateway_Afterpay::log("Updating status of WooCommerce Order #{$order->id} to \"Failed\".");

						$order->add_order_note( sprintf(__( 'Checked payment status with Afterpay. Payment %s. Afterpay Order ID: %s', 'woo_afterpay' ), strtolower($response->status), $response->id) );
						$order->update_status( 'failed' );
					}
				}
			}

			$paged++;
		} while (count($pending_orders) > 0);
	}

	/**
	 * Clean up (skip trash and force-delete) any quotes that have reached a certain age. The age limit
	 * depends on the debug setting.
	 *
	 * Note:	This is only applicable for API v0.
	 * Note:	A token has 30 minute lifespan, which starts at creation and resets at consumer login.
	 *			This means that a consumer cannot take more than 1 hour to complete a transaction.
	 * Note:	WordPress would have used its timezone config when inserting the post,
	 *			not PHP/system time or MySQL time. Therefore, we use current_time() in
	 *			our WP_Date_Query clauses.
	 *
	 * @since	2.0.0
	 * @uses	current_time()
	 * @uses	get_posts()
	 * @uses	get_post_stati()
	 * @uses	wp_delete_post()
	 * @used-by	self::fire_jobs()
	 */
	private static function cleanup_unused_quotes() {
		$settings_key = self::get_settings_key();
		$settings = get_option( $settings_key );

		if (!array_key_exists('api-version', $settings) || $settings['api-version'] != 'v1') {
			return;
		}

		$wp_current_time_str = current_time( 'mysql' );
		if (array_key_exists('debug', $settings) && $settings['debug'] == 'yes') {
			$time_int = strtotime("{$wp_current_time_str} -14 days");
		} else {
			$time_int = strtotime("{$wp_current_time_str} -75 minutes");
		}
		$time_str = date('Y-m-d H:i:s', $time_int);

		$paged = 1;
		do {
			$old_quotes = get_posts( array(
				'post_type' => 'afterpay_quote',
				'post_status' => get_post_stati(),
				'date_query' => array(
					array(
						'column' => 'post_date',
						array(
							'before' => $time_str
						)
					)
				),
				'paged' => $paged,
				'posts_per_page' => 5
			) );

			foreach ($old_quotes as $old_quote) {
				WC_Gateway_Afterpay::log("Deleting expired Afterpay Quote #{$old_quote->ID}.");
				wp_delete_post( $old_quote->ID, true );
			}

			$paged++;
		} while (count($old_quotes) > 0);
	}
}
