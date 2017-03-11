<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package storefront
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
        <link rel="profile" href="http://gmpg.org/xfn/11">
        <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
        <link rel="stylesheet" href="http://garry.local/wp-content/themes/FashionBrazilShop/font-awesome/css/font-awesome.min.css">
        <link href="http://garry.local/wp-content/themes/FashionBrazilShop/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <?php wp_head(); ?>
    </head>
<body <?php body_class(); ?>>
    <div id="page" class="hfeed site">
        <?php
            do_action( 'storefront_before_header' ); 
            /**
            * Functions hooked into storefront_header action
            *
            * @hooked storefront_skip_links                       - 0
            * @hooked storefront_social_icons                     - 10
            * @hooked storefront_site_branding                    - 20
            * @hooked storefront_secondary_navigation             - 30
            * @hooked storefront_product_search                   - 40
            * @hooked storefront_primary_navigation_wrapper       - 42
            * @hooked storefront_primary_navigation               - 50
            * @hooked storefront_header_cart                      - 60
            * @hooked storefront_primary_navigation_wrapper_close - 68
            */
            //do_action( 'storefront_header' ); 
        ?>
    </div>

	<?php
	/**
	 * Functions hooked in to storefront_before_content
	 *
	 * @hooked storefront_header_widget_region - 10
	 */
	do_action( 'storefront_before_content' ); ?>
    <div class="container-fluid header-container">
        <div class="container">
            <div class="row">
                <div class="col-md-2 col-xs-4 logo">
                    <a href="/"><img src="http://garry.local/wp-content/themes/FashionBrazilShop/assets/logo_small.jpg" /></a>
                </div>
                <div class="col-md-10 col-xs-8 title">
                    <h1>Fashion Brazil</h1>
                    <div class="social-icons">
                        <ul>
                            <li><a href="#"><i class="fa fa-facebook-official" aria-hidden="true"></i></a></li>
                            <li><a href="#"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
                            <li><a href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
                            <li><a href="#"><i class="fa fa-pinterest" aria-hidden="true"></i></a></li>
                            <li><a href="#"><i class="fa fa-google-plus-official" aria-hidden="true"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
            <nav class="navbar navbar-default">
                <div class="container">
                    <ul class="nav navbar-nav">
                        <?php wp_nav_menu( array( 'theme_location' => 'header-menu' ) ); ?>
                    </ul>
                    <div class="cart">
                        <a href="#"><i class="fa fa-shopping-cart" aria-hidden="true"></i> Cart</a>
                    </div>
                    <div class="search">
                        <i class="fa fa-search" aria-hidden="true"></i> Search
                    </div>
                </div>
            </nav>
        </div>
    </div>
    