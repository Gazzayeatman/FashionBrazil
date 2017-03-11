jQuery( function( $ ){

	function a3_dgallery_tiptip( dgallery_tips ) {
		// Tool Tip
		dgallery_tips.tipTip({
			'attribute' : 'data-tip',
			'maxWidth' : '500px',
			'fadeIn' : 50,
			'fadeOut' : 50
		});
	}

	function a3_dgallery_sortable( dgallery_images ) {
		dgallery_images.sortable({
			items: 'li.image',
			cursor: 'move',
			scrollSensitivity:40,
			forcePlaceholderSize: true,
			forceHelperSize: false,
			helper: 'clone',
			opacity: 0.65,
			placeholder: 'metabox-sortable-placeholder',
			start:function(event,ui){
				ui.item.css('background-color','#f6f6f6');
			},
			stop:function(event,ui){
				ui.item.removeAttr('style');
			},
			update: function(event, ui) {
				var attachment_ids = '';
				dgallery_images_container = $(this).parents('.dgallery_images_container');
				dgallery_ids = dgallery_images_container.find('.dgallery_ids');

				$(this).find('li.image').css('cursor','default').each(function() {
					var attachment_id = $(this).attr( 'data-attachment_id' );
					attachment_ids = attachment_ids + attachment_id + ',';
				});

				dgallery_ids.val( attachment_ids );
				a3_dgallery_variation_save_gallery_ids( dgallery_images_container );
			}
		});
	}

	function a3_dgallery_show_hide_variation_gallery() {
		if ( $('input.actived_d_gallery').is(":checked") && $('input.wc_dgallery_show_variation').is(":checked") ) {
			$('.variations_dgallery_activated_panel_container').slideDown();
			$('.woocommerce_variation .upload_image').slideUp();
		} else {
			$('.variations_dgallery_activated_panel_container').slideUp();
			$('.woocommerce_variation .upload_image').slideDown();
		}
	}

	function a3_dgallery_variation_save_gallery_ids( dgallery_images_container ) {
		if ( ! dgallery_images_container.hasClass('dgallery_variation_images_container') ) return;

		dgallery_ids = dgallery_images_container.find('.dgallery_ids');
		dgallery_images_container.find('.a3_dg_variation_ajax_loader').show();

		var data = {
			action: 		"wc_dgallery_variation_save_gallery_ids",
			variation_id: 	dgallery_ids.data('variation-id'),
			dgallery_ids: 	dgallery_ids.val()
		};

		$.post( a3_dgallery_metabox.ajax_url, data, function(response) {
			dgallery_images_container.find('.a3_dg_variation_ajax_loader').hide();
		});
	}

	a3_dgallery_tiptip( $(".dg_tips") );

	$( document.body ).on( 'woocommerce-product-type-change', function( e, select_val ) {
		if ( 'variable' == select_val ) {
			$('.a3_dgallery_is_variable_product').slideDown();
		} else {
			$('.a3_dgallery_is_variable_product').slideUp();
		}
	} );

	$( '#woocommerce-product-data' ).on( 'woocommerce_variations_loaded', function() {
		a3_dgallery_tiptip( $('.woocommerce_variation .dg_tips') );
		a3_dgallery_sortable( $('.woocommerce_variation ul.dgallery_images') );
		a3_dgallery_show_hide_variation_gallery();
	} );

	$( document.body ).on( 'woocommerce_variations_added', function() {
		a3_dgallery_tiptip( $('.woocommerce_variation .dg_tips') );
		a3_dgallery_sortable( $('.woocommerce_variation ul.dgallery_images') );
		a3_dgallery_show_hide_variation_gallery();
	} );

	$('input.actived_d_gallery').change(function() {
		if( $(this).is(":checked") ) {
			$('#main_dgallery_panel').slideDown();
			$('.variations_dgallery_deactivated_panel_container').slideUp();
		} else {
			$('#main_dgallery_panel').slideUp();
			$('.variations_dgallery_deactivated_panel_container').slideDown();
		}
		a3_dgallery_show_hide_variation_gallery();
	} );

	$('input.wc_dgallery_show_variation').change(function() {
		a3_dgallery_show_hide_variation_gallery();
	} );

	// Product gallery file uploads
	$(document).on( 'click', '.add_dgallery_images a', function( event ) {
		var $el = $(this);
		var dgallery_frame;
		var dgallery_images_container = $(this).parents('.dgallery_images_container');
		var dgallery_ids = dgallery_images_container.find('.dgallery_ids');
		var dgallery_images = dgallery_images_container.find('ul.dgallery_images');

		event.preventDefault();

		// If the media frame already exists, reopen it.
		if ( dgallery_frame ) {
			dgallery_frame.open();
			return;
		}

		// Create the media frame.
		dgallery_frame = wp.media.frames.a3_dgallery = wp.media({
			// Set the title of the modal.
			title: $el.data('choose'),
			button: {
				text: $el.data('update'),
			},
			states : [
				new wp.media.controller.Library({
					title: $el.data('choose'),
					filterable :	'uploaded',
					allowLocalEdits: true,
					displayUserSettings: true,
					multiple : true,
					type : 'image'
				})
			]
		});

		// When an image is selected, run a callback.
		dgallery_frame.on( 'select', function() {

			var selection = dgallery_frame.state().get('selection');
			var attachment_ids = dgallery_ids.val();

			selection.map( function( attachment ) {

				attachment = attachment.toJSON();

				if ( attachment.id ) {
					attachment_ids   = attachment_ids ? attachment_ids + ',' + attachment.id : attachment.id;
					var attachment_image = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

					dgallery_images.append('\
					<li class="image" data-attachment_id="' + attachment.id + '">\
						<img src="' + attachment_image + '" />\
						<ul class="actions">\
							<li><a href="#" class="delete dg_tips" data-tip="' + $el.data('delete') + '" title="' + $el.data('delete') + '">' + $el.data('text') + '</a></li>\
						</ul>\
					</li>');
				}

			});

			dgallery_ids.val( attachment_ids );
			a3_dgallery_variation_save_gallery_ids( dgallery_images_container );
		});

		// Finally, open the modal.
		dgallery_frame.open();
	});

	// Image ordering
	$('.dgallery_images_container').find('ul.dgallery_images').each( function(){
		a3_dgallery_sortable( $(this) );
	});

	// Remove images
	$(document).on( 'click', '.dgallery_images_container a.delete', function() {
		var dgallery_images_container = $(this).parents('.dgallery_images_container');
		var dgallery_ids = dgallery_images_container.find('.dgallery_ids');
		var dgallery_images = dgallery_images_container.find('ul.dgallery_images');

		$('#tiptip_holder').hide();

		$(this).closest('li.image').remove();

		var attachment_ids = '';

		dgallery_images.find('li.image').css('cursor','default').each(function() {
			var attachment_id = $(this).attr( 'data-attachment_id' );
			attachment_ids = attachment_ids + attachment_id + ',';
		});

		dgallery_ids.val( attachment_ids );
		a3_dgallery_variation_save_gallery_ids( dgallery_images_container );

		//runTipTip();

		return false;
	});
});
