/* global wc_price_based_country_admin_params */
jQuery( function( $ ) {

	$('body').on( 'keyup', '.wcpbc_sale_price[type=text]', function(){
		
		var sale_price_field = $(this);			
		var regular_price_field = $('#' + sale_price_field.attr('id').replace('_sale','_regular') ) ;		

		var sale_price    = parseFloat( accounting.unformat( sale_price_field.val(), woocommerce_admin.mon_decimal_point ) );
		var regular_price = parseFloat( accounting.unformat( regular_price_field.val(), woocommerce_admin.mon_decimal_point ) );		

		if( sale_price >= regular_price ) {
			if ( $(this).parent().find('.wc_error_tip').size() === 0 ) {
				var offset = $(this).position();
				$(this).after( '<div class="wc_error_tip">' + woocommerce_admin.i18_sale_less_than_regular_error + '</div>' );
				$('.wc_error_tip')
					.css('left', offset.left + $(this).width() - ( $(this).width() / 2 ) - ( $('.wc_error_tip').width() / 2 ) )
					.css('top', offset.top + $(this).height() )
					.fadeIn('100');
			}
		} else {
			$('.wc_error_tip').fadeOut('100', function(){ $(this).remove(); } );
		}
		return this;
	});

	$('body').on( 'change', '.wcpbc_sale_price[type=text]', function(){			

		var sale_price_field = $(this);				
		var regular_price_field = $('#' + sale_price_field.attr('id').replace('_sale','_regular') ) ;		

		var sale_price    = parseFloat( accounting.unformat( sale_price_field.val(), woocommerce_admin.mon_decimal_point ) );
		var regular_price = parseFloat( accounting.unformat( regular_price_field.val(), woocommerce_admin.mon_decimal_point ) );

		var sale_price    = parseFloat( accounting.unformat( sale_price_field.val(), woocommerce_admin.mon_decimal_point ) );
		var regular_price = parseFloat( accounting.unformat( regular_price_field.val(), woocommerce_admin.mon_decimal_point ) );

		if( sale_price >= regular_price ) {
			sale_price_field.val( regular_price_field.val() );
		} else {
			$('.wc_error_tip').fadeOut('100', function(){ $(this).remove(); } );
		}
		return this;			

	});		
	
	$('body').on( 'click', '.wcpbc_sale_price_dates[type="radio"], .wcpbc_price_method[type="radio"]', function(){
		var parent_class = '.' + $(this).attr('name') + '_field';					

		parent_class = parent_class.replace('[', '_');
		parent_class = parent_class.replace(']', '');
		
		$(this).closest(parent_class).next('.wcpbc_show_if_manual').toggle( $(this).val() == 'manual' );		
	});

	$('.wcpbc-region-settings').on( 'click', '.select_eur', function(){
		var countries = $(this).data('countries');		
		
		if ( countries instanceof Array ) {
			$( this ).closest( 'td' ).find( 'select option' ).each( function( index, that ) {
				if ( countries.indexOf( $(that).attr('value') ) > -1 ) {
					$(that).attr('selected', 'selected');
				}
			});	
			$( this ).closest( 'td' ).find( 'select' ).trigger('change');
		}

		return false;		
	});
	
	$('#wc_price_based_country_test_mode').on('change', function() {
   		if ($(this).is(':checked')) {
   			$('#wc_price_based_country_test_country').closest('tr').show();
   		} else {
   			$('#wc_price_based_country_test_country').closest('tr').hide();
   		}
   	}).change();
	
	$('#general_coupon_data #discount_type').on('change', function(){
		$('#general_coupon_data #zone_pricing_type').closest('p').toggle( $(this).val()=='fixed_cart' ||  $(this).val()=='fixed_product' );
	});
	
	$(document).ready( function (){
		$('.wcpbc_sale_price_dates[type="radio"][value="manual"], .wcpbc_price_method[type="radio"][value="manual"]').each( function(){			
			var parent_class = '.' + $(this).attr('name') + '_field';
			parent_class = parent_class.replace('[', '_');
			parent_class = parent_class.replace(']', '');
			
			$(this).closest(parent_class).next('.wcpbc_show_if_manual').toggle( $(this).prop( "checked" ) );				
		});		
		
		$('#general_coupon_data #zone_pricing_type').closest('p').toggle( $('#general_coupon_data #discount_type').val()=='fixed_cart' ||  $('#general_coupon_data #discount_type').val()=='fixed_product' );

		// Variation pricing bulk edit	
		$( 'select.variation_actions' ).on('wcpbc_variable_bulk_edit_popup', function(e){
			tb_show( 'Upgrade to Price Based on Country Pro now!', '#TB_inline?width=600&height=350&inlineId=variable-bulk-edit-popup', false );
			$('#TB_window').css({
				width: '680px',
				height: '380px'
			});
			$('#TB_window .button.button-primary').focus();
		});
				
	});

	$('body').on( 'click', 'a.wcpbc-delete-zone', function(e){
		if ( ! confirm( wc_price_based_country_admin_params.i18n_delete_zone_alert ) ) {
			e.preventDefault();
		}
	});

	$('body').on( 'click', 'input#wc_price_based_country_caching_support', function(){
		if ( $(this).is(':checked') ) {
			alert(wc_price_based_country_admin_params.i18n_caching_support_alert);
		}
	});

	// Pro product type supported notice	
	function wcpbc_show_and_hide_pro_notice(){		
		var select_val = $( 'select#product-type' ).val();		
		if ( typeof woocommerce_admin_meta_boxes !== 'undefined' ) {
			$('.wc-price-based-country-upgrade-notice:not(.show)').hide();
			$('.wc-price-based-country-upgrade-notice.wc-pbc-show-if-'+select_val).show();	
			$('#general_product_data .wc-price-based-country-upgrade-notice.wc-pbc-show-if-variable-subscription').hide();	
			$('#general_product_data .wc-price-based-country-upgrade-notice.wc-pbc-show-if-booking').hide();				
			$('#general_product_data .wc-price-based-country-upgrade-notice.wc-pbc-show-if-not-supported.product-type-'+select_val).toggle(
				0 > $.inArray( select_val, wc_price_based_country_admin_params.product_type_supported )
			);

		}
	}
	
	$(document).ready(wcpbc_show_and_hide_pro_notice)				
	$(document.body).on( 'woocommerce-product-type-change', wcpbc_show_and_hide_pro_notice );
	$(document.body).on( 'woocommerce_variations_added', wcpbc_show_and_hide_pro_notice );
	$('#woocommerce-product-data' ).on( 'woocommerce_variations_loaded', wcpbc_show_and_hide_pro_notice );		

	// Hide notice 
	$( '.notice-pbc a.pbc-hide-notice').on('click', function(e) {
		e.preventDefault();					
		var el = $(this).closest('.notice-pbc');			
		$(el).find('.pbc-wait').remove();
		$(el).append('<div class="pbc-wait"></div>');
		if ( $('.notice-pbc.updating').length > 0 ) {
			var button = $(this);
			setTimeout(function(){
				button.triggerHandler( 'click' );
			}, 100);
			return false;
		}
		$(el).addClass('updating');		
		$.post( wc_price_based_country_admin_params.ajax_url, {
				action: 	'wcpbc_hide_notice',
				security: 	$(this).data('nonce'),
				notice: 	$(this).data('notice'),
				remind: 	$(this).hasClass( 'remind-later' ) ? 'yes' : 'no'
		}, function(){
			$(el).removeClass('updating');
			$(el).fadeOut(100);	
		});
	});

	// Move submit button in setting page
	$('.wc-price-based-country-setting-wrapper-ads .wc-price-based-country-setting-content').append($('p.submit'));
});
