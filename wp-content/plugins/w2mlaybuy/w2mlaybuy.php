<?php

/**
 * Plugin Name: WooCommerce Payment Extension for Laybuy
 * Description: A payment gateway extension for laybuy.com
 * Author: Larry Watene
 * Author URI: https://web2mobile.co.nz/website-ninja/
 * Plugin URI: https://www.laybuy.com/
 * Version: 2.5
 * Text Domain: w2mlaybuy
 */

if( !defined( 'ABSPATH' ) ) exit;

/**
 * Exit when woocommerce is not active.
 */
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

require_once 'constants.php';

add_action( 'plugins_loaded', 'w2mlaybuy_gateway_init' );

function w2mlaybuy_gateway_init() {
    require_once 'includes/functions.php';
    require_once 'includes/w2mlaybuy-form-handler.php';
    require_once 'w2mlaybuy-gateway.php';

    // Add the Gateway to WooCommerce
    add_filter( 'woocommerce_payment_gateways', 'w2mlaybuy_add_gateway' );

    function w2mlaybuy_add_gateway($gateways) {
        $gateways[] = 'W2MLAYBUY_Gateway';
        return $gateways;
    }
}

add_action( 'admin_notices', 'w2mlaybuy_recommend_ssl_notice' );
function w2mlaybuy_recommend_ssl_notice() {

    if( !is_ssl() ) {
        ?>
        <div class="error notice">
            <p><?php _e( 'Enabling SSL is highly recommended when using the Laybuy payment option.', 'w2mlaybuy' ); ?></p>
        </div>
        <?php
    }

}

/**
 * Add automated updates class
 */
require_once('wp-updates-plugin.php');
new WPUpdatesPluginUpdater_1709( 'http://wp-updates.com/api/2/plugin', plugin_basename(__FILE__));
