<?php get_header(); ?>
<div class="container-fluid">
        <div id="myCarousel" class="carousel slide" data-ride="carousel">
            <!-- Indicators -->
            <ol class="carousel-indicators">
                <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
                <li data-target="#myCarousel" data-slide-to="1"></li>
                <li data-target="#myCarousel" data-slide-to="2"></li>
                <li data-target="#myCarousel" data-slide-to="3"></li>
            </ol>

            <!-- Wrapper for slides -->
            <div class="carousel-inner" role="listbox">
                <div class="item active">
                <img src="img_chania.jpg" alt="Chania">
                </div>

                <div class="item">
                <img src="img_chania2.jpg" alt="Chania">
                </div>

                <div class="item">
                <img src="img_flower.jpg" alt="Flower">
                </div>

                <div class="item">
                <img src="img_flower2.jpg" alt="Flower">
                </div>
            </div>

            <!-- Left and right controls -->
            <a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
                <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
                <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
        <div class="container promotional">
            <div class="row">
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="promo-block">
                        <div class="promo-img">
                            <div class="img-overlay">
                                <h3>Swimwear</h3>
                                <p>
                                    Lorem ipsum dolor sit consecutor
                                </p>
                            </div>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/assets/placeholder2.jpg"></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="promo-block">
                        <div class="promo-img">
                            <div class="img-overlay">
                                <h3>Activewear</h3>
                                <p>
                                    Lorem ipsum dolor sit consecutor
                                </p>
                            </div>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/assets/placeholder3.jpg"></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="promo-block">
                        <div class="promo-img">
                            <div class="img-overlay">
                                <h3>Tops</h3>
                                <p>
                                    Lorem ipsum dolor sit consecutor
                                </p>
                            </div>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/assets/placeholder4.jpg"></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="promo-block">
                        <div class="promo-img">
                            <div class="img-overlay">
                                <h3>Clearance</h3>
                                <p>
                                    Praesent quis tellus id quam efficitur porta at vel sem.
                                </p>
                            </div>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/assets/placeholder3.jpg"></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="promo-block">
                        <div class="promo-img">
                            <div class="img-overlay">
                                <h3>New Items</h3>
                                <p>
                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec porttitor ullamcorper lorem, eu viverra dolor suscipit sed. Sed auctor dolor felis, vitae euismod erat ullamcorper
                                </p>
                            </div>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/assets/placeholder4.jpg"></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="promo-block">
                        <div class="promo-img">
                            <div class="img-overlay">
                                <h3>Yas</h3>
                                <p>
                                    Nam vel efficitur sapien, id pulvinar turpis. Etiam sit amet laoreet velit. Nulla finibus faucibus tellus id efficitur. Donec fringilla lobortis gravida. Donec vel quam dapibus, mollis purus vel, accumsan ante. Cras congue lectus massa, ut cursus est mollis eget. Sed efficitur fermentum sollicitudin.
                                </p>
                            </div>
                            <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/assets/placeholder2.jpg"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="instagram">
            </div>
        </div>
<?php get_footer(); ?>
