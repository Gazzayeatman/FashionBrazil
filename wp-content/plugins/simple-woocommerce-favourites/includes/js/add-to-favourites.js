jQuery(document).ready(function($){

	$(document).on('click', '.simple_add_to_favourites', function(e){
		e.preventDefault();
		var prod_id = $(this).data().productid;
		if( isNaN(prod_id) ){
			return;
		}
		prod_id = parseInt(prod_id);
		data = {
			prod_id:prod_id,
			action:'simple_ajax_add_to_favourites',
			simple_favourites_nonce:simple_nonce.simple_favourites_nonce
		}
		var $this_button = $(this);
		$.post(myAjax.ajaxurl, data, function(msg){
			var $this_messsage = $this_button.closest('.simple_container').find('.simple_message');
			$this_messsage.html(msg);
			$this_messsage.fadeIn();
			setTimeout(function(){ $this_messsage.fadeOut(); }, 4000);
		});
	});

	$(document).on('click', '.simple-remove-from-favourites', function(){
		var prod_id    = $(this).data().product_id;
		if( isNaN(prod_id) ){
			return;
		}
		prod_id = parseInt(prod_id);
		data = {
			prod_id:prod_id,
			action:'simple_ajax_remove_from_favourites',
			simple_favourites_nonce:simple_nonce.simple_favourites_nonce
		}
		$.post(myAjax.ajaxurl, data, function(msg){
			location.reload();
		});
	});

	if( $('#simple_favourites_display').length != 0 ){
		var max_height = 0;
		$('ul.products li.product').each(function(){
			max_height = $(this).height() > max_height ? $(this).height() : max_height;
		});
		$('ul.products li.product').height(max_height);
	}

});