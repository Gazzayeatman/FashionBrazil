jQuery(function($) {
	$('select#woocommerce_afterpay_testmode')
		.on('change', function(event) {
			if ($(this).val() != 'production') {
				$('input#woocommerce_afterpay_prod-id').closest('tr').hide();
				$('input#woocommerce_afterpay_prod-secret-key').closest('tr').hide();
				$('input#woocommerce_afterpay_test-id').closest('tr').show();
				$('input#woocommerce_afterpay_test-secret-key').closest('tr').show();
			} else {
				$('input#woocommerce_afterpay_prod-id').closest('tr').show();
				$('input#woocommerce_afterpay_prod-secret-key').closest('tr').show();
				$('input#woocommerce_afterpay_test-id').closest('tr').hide();
				$('input#woocommerce_afterpay_test-secret-key').closest('tr').hide();
			}
		})
		.trigger('change');

	$('input#woocommerce_afterpay_show-info-on-category-pages')
		.on('change', function(event) {
			if ($(this).is(':checked')) {
				$('input#woocommerce_afterpay_category-pages-info-text').closest('tr').show();
			} else {
				$('input#woocommerce_afterpay_category-pages-info-text').closest('tr').hide();
			}
		})
		.trigger('change');

	$('input#woocommerce_afterpay_show-info-on-product-pages')
		.on('change', function(event) {
			if ($(this).is(':checked')) {
				$('input#woocommerce_afterpay_product-pages-info-text').closest('tr').show();
			} else {
				$('input#woocommerce_afterpay_product-pages-info-text').closest('tr').hide();
			}
		})
		.trigger('change');
});
