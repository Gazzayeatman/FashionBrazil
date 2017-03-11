<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Fashion Brazil</title>

    <!-- Bootstrap -->
    <link href="<?php echo get_template_directory_uri(); ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo get_template_directory_uri(); ?>/style.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/font-awesome/css/font-awesome.min.css">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    <div class="container-fluid header-container">
        <div class="container">
            <div class="row">
                <div class="col-md-2 col-xs-4 logo">
                    <a href="/"><img src="<?php echo get_template_directory_uri(); ?>/assets/logo_small.jpg" /></a>
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
