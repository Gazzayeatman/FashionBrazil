<?php
/**
 * Admin View: Notice - Tracking
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="notice notice-info notice-pbc">
	<p><?php printf( __( 'In order to improve all our features and functionality, %sWooCommerce Price Based on Country%s needs to collect non-sensitive diagnostic data and usage information. %sFind out more%s.', 'woocommerce' ), '<strong>', '</strong>', '<a target="_blank" rel="noopener noreferrer" href="https://www.pricebasedcountry.com/usage-tracking/">', '</a>' ); ?></p>
	<p class="submit">
		<a class="button-primary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wcpbc_tracker_optin', 'yes' ), 'pbc-hide-notice' ) ); ?>"><?php _e( 'Allow', 'wc-price-based-country' ); ?></a>
		<a class="skip button-secondary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wcpbc_tracker_optin', 'no' ), 'pbc-hide-notice' ) ); ?>"><?php _e( 'No, do not bother me again', 'wc-price-based-country' ); ?></a>
	</p>
</div>