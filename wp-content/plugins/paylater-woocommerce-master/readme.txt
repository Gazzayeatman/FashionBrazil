=== Afterpay Gateway for WooCommerce ===
Contributors: afterpayit
Tags: woocommerce, afterpay
Requires at least: 3.5.0
Tested up to: 4.8
Stable tag: 2.0.0
License: GNU Public License
License URI: https://www.gnu.org/licenses/

Provide Afterpay as a payment option for WooCommerce orders.

== Description ==

Give your customers the option to buy now and pay later with Afterpay. The "Afterpay Gateway for WooCommerce" plugin provides the functionality to display the Afterpay logo and instalment calculations below product prices on category pages, individual product pages, and on the cart page. It also provides the option to choose Afterpay as the payment method at the checkout. If the payment is approved by Afterpay, the order will be created inside the WooCommerce system like any other order. Also supports automatic refunds.

== Installation ==

This section outlines the steps to install the Afterpay plugin.

> Please note: If you are upgrading to a newer version of the Afterpay plugin, it is considered best practice to perform a backup of your website - including the WordPress database - before commencing the installation steps. Afterpay recommends all system and plugin updates to be tested in a staging environment prior to deployment to production.

1. Create and upload the plugin files to a `/wp-content/plugins/afterpay-gateway-for-woocommerce` directory, or install the plugin through the WordPress plugins screen directly.
1. Be sure to "Activate" the plugin.
1. Navigate to the "WooCommerce > Settings" page; select the "Checkout" tab, then the "Afterpay" sub-tab.
1. Enter the Merchant ID and Secret Key that were provided by Afterpay for Production use.
1. Save changes.

== Frequently Asked Questions ==

= What do I do if I need help? =

Please visit the official [Afterpay Help Centre](https://help.afterpay.com/hc) online. Most common questions are answered in the FAQ. There is also the option to create a support ticket if necessary.

== Changelog ==

= 2.0.0 =
*Release Date: 27 September 2017*

* Add support for the calculation of instalment amounts at the product level for variably priced products.
* Add support for orders that do not require shipping addresses.
* Add support for optionally including Afterpay elements on the cart page.
* Add a shortcode for rendering the standard Afterpay logo, with support for high pixel density screens and a choice of 3 colour variants.
* Improve ease of installation and configuration.
* Improve responsiveness of checkout elements.
* Improve customisability for developers.
* Change order button to read "Proceed to Afterpay" when configured to use the "v1 (recommended)" API Version.
* Change the payment declined messages to include the phone number for the Afterpay Customer Service Team.
* Change the default HTML for category pages and individual product pages to take advantage of the latest features.
* Change the plugin name from "WooCommerce Afterpay Gateway" to "Afterpay Gateway for WooCommerce".
* Remove deprecated CSS.

= 1.3.1 =
*Release Date: 10 April 2017*

* Improve compatibility with WooCommerce 3. Resolution of "invalid product" item at checkout.
