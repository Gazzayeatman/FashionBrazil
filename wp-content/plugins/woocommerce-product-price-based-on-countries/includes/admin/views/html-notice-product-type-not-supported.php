<?php
/**
 * Admin View: Notice - Product type NOT supported
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p style="display:none;border-left-color:#ffb900;position:relative;" class="notice-pbc wc-price-based-country-upgrade-notice wc-pbc-show-if-not-supported product-type-<?php echo $value;?>">
	<a style="padding: 12px;text-decoration:none;" class="notice-dismiss pbc-hide-notice" data-nonce="<?php echo wp_create_nonce( 'pbc-hide-notice' )?>" data-notice="<? echo $notice;?>" href="#"></a>
	<?php printf( __( 'Hi, this product type is not supported by %sPrice Based on Country%s. Use it with caution.', 'wc-price-based-country' ), '<strong>', '</strong>' ); ?>
</p>