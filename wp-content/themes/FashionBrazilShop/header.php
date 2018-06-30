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
    <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-W6XKJ9P');</script>
        <!-- End Google Tag Manager -->
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
    <link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/style.css">
    <link href="<?php echo get_stylesheet_directory_uri(); ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <?php wp_head(); ?>
   
</head>
<body <?php body_class(); ?>>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-W6XKJ9P"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <div class="container-fluid top-header">
        <div class="row">
            <div class="col-sm-6 d-none d-sm-none col-md-4 d-md-block align-self-center">
                <div class="header-social-icons">
                    <ul>
                        <li>
                            <a target="_blank" href="http://www.facebook.com/fashionbrazilausnz">
                                <i class="fa fa-facebook-official" aria-hidden="true"></i>
                            </a>
                        </li>
                        <li>
                            <a target="_blank" href="http://www.twitter.com/fashion_brazil">
                                <i class="fa fa-twitter" aria-hidden="true"></i>
                            </a>
                        </li>
                        <li>
                            <a target="_blank" href="https://instagram.com/fashionbrazilausnz">
                                <i class="fa fa-instagram" aria-hidden="true"></i>
                            </a>
                        </li>
                        <li>
                            <a target="_blank" href="https://nz.pinterest.com/fbausnz/">
                                <i class="fa fa-pinterest" aria-hidden="true"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-sm-6 free-shipping d-sm-none d-md-block d-none col-md-4 align-self-center">
                <h4><a href="/information/shipping/">*FREE SHIPPING WORLDWIDE</a></h4>
            </div>
            <div class="col-sm-12 col-xs-12 col-md-4 align-self-center">
                <div class="header-social-icons pull-right">
                    <?php 
                        wp_nav_menu([
                            'theme_location' => 'top_right'
                        ]); 
                    ?>
                    <ul class="menu-top-right pull-right">
                        <li class="currency">
                            <?php
                                $ip_data = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip));
                                if ($ip_data && $ip_data->geoplugin_countryName != null) {
                                    $result['country'] = $ip_data->geoplugin_countryCode;
                                }
                                switch ($result['country']) {
                                    case 'NZ':
                                        echo '$NZD';
                                        break;
                                    case 'AU':
                                        echo '$AUS';
                                        break;
                                    default:
                                        echo '$NZD';
                                        break;
                                }
                            ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="container">
            <div class="row center-header">
                <div class="col-md-12 col-xs-12 logo title align-self-center">
                    <a href="/"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/logo_small.jpg" /></a>
                    <h1>
                        Fashion Brazil
                    </h1>
                </div>
            </div>
        </div>
        <nav class="navbar navbar-default">
            <div class="container nav-container">
                <ul class="nav navbar-nav">
                    <?php 
                        wp_nav_menu([
                            'theme_location' => 'Main']
                        );
                    ?>
                </ul>
            </div>
        </nav>
    </div>
