<?php
/**
* Afterpay Plugin Idempotency Retry Handler Class
*/

class Afterpay_Plugin_Idempotency_Cron
{
	/**
	 * Create a new WP-Cron job scheduling interval so jobs can run "Every 4 seconds".
	 *
	 * Note:	Hooked onto the "cron_schedules" Filter.
	 *
	 * @since	2.0.0
	 * @param	array	$schedules	The current array of cron schedules.
	 * @return	array				Array of cron schedules with 5 seconds added.
	 **/
	public static function edit_cron_schedules($schedules) {
		$schedules['5sec'] = array(
			'interval' => 5, 
			'display' => __( 'Every 5 seconds', 'woo_afterpay' )
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
		$timestamp = wp_next_scheduled( 'afterpay_do_idempotency_cron_jobs' );
		if ($timestamp == false) {
			wp_schedule_event( time(), '5sec', 'afterpay_do_idempotency_cron_jobs' );
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
		wp_clear_scheduled_hook( 'afterpay_do_idempotency_cron_jobs' );
	}

	/**
	 * Fire the Afterpay WP-Cron job.
	 *
	 * Note:	Hooked onto the "afterpay_do_idempotency_cron_jobs" Action, which exists
	 *			because we scheduled a cron under that key when the plugin was activated.
	 *
	 * Note:	This CRON job firing would not be logged to preserve logging space 
	 *			The job would trigger every 5 seconds
	 *
	 *
	 * @since	2.0.0
	 * @see		Afterpay_Plugin::__construct()	For hook attachment.
	 * @see		self::create_jobs()				For initial scheduling (on plugin activation).
	 * @uses	self::idempotency_processing()
	 */
	public static function fire_jobs() {
		self::idempotency_processing();
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
	 * @uses	self::get_settings_key()
	 * @used-by	self::idempotency_processing()
	 */
	private static function get_settings_key() {
		$gateway = new WC_Gateway_Afterpay;
		$settings_key = $gateway->get_option_key();
		return $settings_key;
	}

	/**
	 * Process the Idempotency Queue
	 *
	 * Note:	This is only applicable for API V1.
	 *
	 * @since	2.0.0
	 * @uses	get_option()
	 * @uses	get_posts()
	 * @uses	get_post_stati()
	 * @uses	WC_Gateway_Afterpay::log()
	 * @uses	Afterpay_Plugin_Merchant::direct_payment_capture()
	 * @uses	self::process_idempotency_response()
	 * @used-by	self::fire_jobs()
	 */
	public static function idempotency_processing() {
		$settings_key = self::get_settings_key();
		$settings = get_option( $settings_key );

		if (!array_key_exists('api-version', $settings) || $settings['api-version'] != 'v1') {
			return;
		}

		$paged = 1;
		do {
			$quotes = get_posts( array(
				'post_type' 	=> 'afterpay_quote',
				'post_status' 	=> get_post_stati(),
				'meta_key' 		=> '_quote_retry',
				'meta_value' 	=> true,
				'paged' 		=> $paged,
				'posts_per_page'=> 5
			) );

			foreach ($quotes as $key => $quote) {
				$afterpay_token = get_post_meta($quote->ID, 'token', true);

				WC_Gateway_Afterpay::log("Retrying Capture for Quote #{$quote->ID}.");

				$merchant = new Afterpay_Plugin_Merchant;
				$capture_response = $merchant->direct_payment_capture($afterpay_token, $quote->ID);
				self::process_idempotency_response($capture_response, $quote->ID);
			}

			$paged++;
		} while (count($quotes) > 0);
	}


	/**
	 * Handle the Idempotency requests' responses
	 *
	 * Note:	This is only applicable for API V1.
	 *
	 * @since	2.0.0
	 * @param	string 		$capture_response		Required. The Capture Response
	 * @param	int 		$afterpay_quote_id		Required. The Afterpay Quote ID
	 * 
	 * @uses	WC_Gateway_Afterpay::create_wc_order_from_afterpay_quote()
	 * @uses	WC_Gateway_Afterpay::log()
	 * @uses	wp_delete_post()
	 * @used-by	self::idempotency_processing()
	 */
	private static function process_idempotency_response($capture_response, $afterpay_quote_id) {
		if ($capture_response == 'APPROVED') {
			# Convert the Afterpay_Quote into a WC_Order.

			$gateway = new WC_Gateway_Afterpay;
			$order = $gateway->create_wc_order_from_afterpay_quote($afterpay_quote_id);

			# Return the WP_Error is the WC_Order could not be created.
			return $order;

		} elseif ($capture_response == 'DECLINED') {
			# Log the event.
			WC_Gateway_Afterpay::log("Afterpay Quote #{$afterpay_quote_id} declined by Afterpay.");
			wp_delete_post($afterpay_quote_id, true);

		} else {
			# We don't know what happened, in this case let the order be for a second retry
		}

		# Can only reach this point if wp_redirect() failed.
		return false;
	}
}