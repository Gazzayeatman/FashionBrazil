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

        $('.add-ons .afterpay').on('click', function() {
            var popup = $('.popuptext');
            popup.toggle("show");
        })
    });
});
