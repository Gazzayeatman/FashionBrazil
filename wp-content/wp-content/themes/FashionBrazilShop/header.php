<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package storefront
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
        <link rel="profile" href="http://gmpg.org/xfn/11">
        <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
        <link rel="stylesheet" href="https://fashionbrazil.co.nz/wp-content/themes/FashionBrazilShop/font-awesome/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://fashionbrazil.co.nz/wp-content/themes/FashionBrazilShop/style.css">
        <link href="https://fashionbrazil.co.nz/wp-content/themes/FashionBrazilShop/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <?php wp_head(); ?>
    </head>
<body <?php body_class(); ?>>
    <div class="container-fluid top-header">
        <div class="container">
            <div class="row">
                <div class="col-sm-6 hidden-xs col-md-4">
                    <div class="header-social-icons">
                        <ul>
                            <li><a target="_blank" href="http://www.facebook.com/fashionbrazilausnz"><i class="fa fa-facebook-official" aria-hidden="true"></i></a></li>
                            <li><a target="_blank" href="http://www.twitter.com/fashion_brazil"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
                            <li><a target="_blank" href="https://instagram.com/fashionbrazilausnz"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
                            <li><a target="_blank" href="https://nz.pinterest.com/fbausnz/"><i class="fa fa-pinterest" aria-hidden="true"></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-sm-5 free-shipping hidden-xs hidden-sm col-md-4">
                    <h4><a href="/information/shipping/">FREE SHIPPING IN NZ AND AUSTRALIA</a></h4>
                </div>
                <div class="col-sm-6 col-xs-12 col-md-4">
                    <div class="header-social-icons pull-right">
                        <?php wp_nav_menu( array( 'theme_location' => 'top_right' ) ); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid header-container">
        <div class="container">
            <div class="row center-header">
                <div class="col-md-12 col-xs-12 logo title">
                    <a href="/"><img src="http://fashionbrazil.co.nz/wp-content/themes/FashionBrazilShop/assets/logo_small.jpg" /></a>
                    <h1>Fashion Brazil</h1>
                </div>
            </div>
        </div>
            <nav class="navbar navbar-default">
                <div class="container">
                    <ul class="nav navbar-nav">
                        <?php wp_nav_menu( array( 'theme_location' => 'Main' ) ); ?>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
    