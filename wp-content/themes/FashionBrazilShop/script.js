jQuery(function ($) { 
    $(document).ready(function() {
        $('.menu-item-has-children').click(function(){
            $(this).children('.sub-menu').toggle("fast");
        });

        $('.carousel-inner').hover(function(){
            $('.item.active h4').fadeTo("fast", 1.0);
            $('.item.active p').fadeTo("fast", 1.0);
        }, function(){
            $('.item.active h4').fadeTo("slow", 0.7);
            $('.item.active p').fadeTo("slow", 0.7);
        });

        $('.collapse-button a').toggle(function(){
            $(this).children("span").html('<i class="fa fa-minus" aria-hidden="true"></i>');
            $('#collapseOne').show("fast");
        }, function(){
            $(this).children("span").html('<i class="fa fa-plus" aria-hidden="true"></i>');
            $('#collapseOne').hide("fast");
        });
        jQuery('a[target^="_new"]').click(function() {
            var width = window.innerWidth * 0.66 ;
            var height = width * window.innerHeight / window.innerWidth ;
            window.open(this.href , 'newwindow', 'width=' + width + ', height=' + height + ', top=' + ((window.innerHeight - height) / 2) + ', left=' + ((window.innerWidth - width) / 2));
        });

        $('.simple_add_to_favourites').on('click', function(e) {
            e.preventDefault();
            $(this).addClass('simple-remove-from-favourites');
            $(this).removeClass('simple_add_to_favourites');

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
        })

        $('.simple-remove-from-favourites').on('click', function(e) {
            e.preventDefault();
            $(this).addClass('simple_add_to_favourites');
            $(this).removeClass('simple-remove-from-favourites');

            var prod_id = $(this).data().product_id;
            if(isNaN(prod_id)){
                return;
            }
            prod_id = parseInt(prod_id);
            data = {
                prod_id:prod_id,
                action:'simple_ajax_remove_from_favourites',
                simple_favourites_nonce:simple_nonce.simple_favourites_nonce
            }
        });

        $('.add-ons .afterpay').on('click', function() {
            $(this).find('.afterpay-price').toggle();
        });

        $('.add-ons .laybuy').on('click', function() {
            $(this).find('.laybuy-price').toggle();
        });
    });
});
