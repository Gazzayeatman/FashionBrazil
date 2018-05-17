        <div class="container-fluid footer">
            <div class="container">
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <h3>Shop</h3>
                        <?php wp_nav_menu( array( 'theme_location' => 'Shop' ) ); ?>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <h3>Customer Care</h3>
                        <?php wp_nav_menu( array( 'theme_location' => 'My Account' ) ); ?>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <h3>About Fashion Brazil</h3>
                        <?php wp_nav_menu( array( 'theme_location' => 'Information' ) ); ?>                        
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <h3>Brands</h3>
                        <?php wp_nav_menu( array( 'theme_location' => 'Brands' ) ); ?>                                                
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 col-sm-4 col-xs-12 fashion-brazil">
                        <h4>&copy; Fashion Brazil <?php echo date("Y"); ?></h4>
                    </div>
                    <div class="col-md-5 col-sm-8 col-xs-12 mail-chimp-signup text-center">
                        <?php echo do_shortcode('[mc4wp_form id="436"]'); ?>
                    </div>
                    <div class="col-md-3 col-sm-12 col-xs-12">
                        <div class="social-icons">
                            <ul>
                                <li><a target="_blank" href="http://www.facebook.com/fashionbrazilausnz"><i class="fa fa-facebook-official" aria-hidden="true"></i></a></li>
                                <li><a target="_blank" href="http://www.twitter.com/fashion_brazil"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
                                <li><a target="_blank" href="https://instagram.com/fashionbrazilausnz"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
                                <li><a target="_blank" href="https://nz.pinterest.com/fbausnz/"><i class="fa fa-pinterest" aria-hidden="true"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <span>Website created by <a target="_blank" href="http://yeatman.co.nz">Garry Yeatman</a></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://fashionbrazil.co.nz/wp-content/themes/FashionBrazilShop/bootstrap/js/bootstrap.min.js"></script>
    <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-W6XKJ9P"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
        <!-- Global site tag (gtag.js) - Google Analytics -->
            <script async src="https://www.googletagmanager.com/gtag/js?id=UA-110094632-1"></script>
            <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', 'UA-110094632-1');
            </script>
    <?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package storefront
 */

?>

<?php do_action( 'storefront_before_footer' ); ?>

<?php wp_footer(); ?>

  </body>
</html>
