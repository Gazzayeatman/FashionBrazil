<?php 
/**
 * Template Name: Home Page Index
 */
get_header(); ?> 
<div class="container-fluid">
        <?php echo do_shortcode('[image-carousel]'); ?>
        <div class="container padding-40">
            <div class="row">
                <?php
                    $my_query = new WP_Query( 'cat=33' );
                    if ( $my_query->have_posts() ) { 
                        while ( $my_query->have_posts() ) {
                            $my_query->the_post();
                            $linkTo = get_field("link_to");
                            echo '
                                <div class="col-md-3 col-sm-6 padding-20">
                                    <a href="'.$linkTo.'">
                                        <div class="featured-box-gallery" style="background:url('.get_the_post_thumbnail_url().') no-repeat">
                                            <div class="outline">
                                                &nbsp;
                                                <div class="featured-title">
                                                    <h2>'.get_the_title().'</h2>
                                                    <i class="underline">&nbsp;</i>
                                                    <h3>Shop Now</h3>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            ';
                        }
                    }
                    wp_reset_postdata();
                ?>
            </div>
        </div>
        <div class="hidden-sm hidden-xs">
            <div class="container padding-40">
                <div class="row">
                    <?php
                        $my_query = new WP_Query( 'cat=32' );
                        if ( $my_query->have_posts() ) { 
                            while ( $my_query->have_posts() ) {
                                $my_query->the_post();
                                $linkTo = get_field("link_to");
                                echo '
                                    <div class="col-md-12">
                                        <a href="'.$linkTo.'">
                                            <div class="featured-box-full-width" style="background:url('.get_the_post_thumbnail_url().') no-repeat">
                                                <div class="outline">
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                ';
                            }
                        }
                        wp_reset_postdata();
                    ?>
                </div>
            </div>
        </div>
        <div class="hidden-lg hidden-md">
            <div class="container padding-40">
                <div class="row">
                    <?php
                        $my_query = new WP_Query( 'cat=32' );
                        if ( $my_query->have_posts() ) { 
                            while ( $my_query->have_posts() ) {
                                $my_query->the_post();
                                $linkTo = get_field("link_to");
                                echo '
                                    <div class="col-xs-12">
                                        <a href="'.$linkTo.'">
                                            <div class="featured-box-full-width mobile-blue">
                                                <div class="outline">
                                                    &nbsp;
                                                    <div class="featured-title">
                                                        <h2>'.get_the_title().'</h2>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                ';
                            }
                        }
                        wp_reset_postdata();
                    ?>
                </div>
            </div>
        </div>
        <div class="container padding-40">
            <div class="row">
                <?php
                    $my_query = new WP_Query( 'cat=11' );
                    if ( $my_query->have_posts() ) { 
                        while ( $my_query->have_posts() ) {
                            $my_query->the_post();
                            $linkTo = get_field("link_to");
                            echo '
                                <div class="col-md-4 col-sm-4 padding-20">
                                    <a href="'.$linkTo.'">
                                        <div class="featured-box" style="background:url('.get_the_post_thumbnail_url().') no-repeat">
                                            <div class="outline">
                                                &nbsp;
                                                <div class="featured-title">
                                                    <h2>'.get_the_title().'</h2>
                                                    <i class="underline">&nbsp;</i>
                                                    <h3>Shop Now</h3>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            ';
                        }
                    }
                    wp_reset_postdata();
                ?>
            </div>
        </div>
        <div class="container padding-40">
            <div class="row">
                <div class="col-md-12">
                    <h2>Follow our instagram</h2>
                    <?php echo do_shortcode('[instagram-feed]'); ?>
                </div>
            </div>
        </div>
    </div>
<?php get_footer(); ?>
