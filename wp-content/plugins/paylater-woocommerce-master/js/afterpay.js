jQuery(document).ready(function($) {
	
	checkout_payment_type();
	//register_fancybox();

	function checkout_payment_type() {
		$('input[type="radio"][name="afterpay_payment_type"]').on('change',function() {
			if ($('input[type="radio"][name="afterpay_payment_type"]:checked').val() == "PAD") {
				$('.afterpay_pad_description').slideDown(300);
				$('.afterpay_pbi_description').slideUp(300);
			} else {
				$('.afterpay_pad_description').slideUp(300);
				$('.afterpay_pbi_description').slideDown(300);
			}
		});

		$('input[name="afterpay_payment_type"]').trigger('change');
	}

	/*
	$(document).on("click", "#checkout-what-is-afterpay-link", function(event){
		event.preventDefault();
        register_fancybox();
        $("a[href='#afterpay-what-is-modal']").trigger("click");
	});

	function register_fancybox() {
		$("a[href='#afterpay-what-is-modal']").fancybox({
			afterShow: function() {
	        	$('#afterpay-modal-popup').find(".close-afterpay-button").on("click", function(event) {
	            	event.preventDefault();
	                $.fancybox.close();
				})
			}
	    });
	}
	*/
});
