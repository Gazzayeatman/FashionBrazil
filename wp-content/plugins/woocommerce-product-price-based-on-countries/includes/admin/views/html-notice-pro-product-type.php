<?php
/**
 * Admin View: Notice - Pro Product type supported
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p <?php echo ( empty( $type ) ? '' : 'style="display:none;" ' ); ?>class="wc-price-based-country-upgrade-notice <?php echo ( empty( $type ) ? 'show' : 'wc-pbc-show-if-' . $type ); ?>">
	<?php printf( __( '%sUpgrade to Price Based on Country Pro to enable compatibility with %s%s.', 'wc-price-based-country' ), '<a target="_blank" href="https://www.pricebasedcountry.com/pricing/?utm_source=' . $utm_source . '&utm_medium=banner&utm_campaign=Get_Pro">', $name, '</a>' ); ?>
</p>		