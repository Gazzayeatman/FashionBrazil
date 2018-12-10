<?php 
/**
 * Template Name: Single blog
 */
get_header(); ?>
<div class="container-fluid">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <ul>
                    <?php
                        $my_query = new WP_Query( 'cat=34' );
                        if ( $my_query->have_posts() ) { 
                            while ( $my_query->have_posts() ) {
                                $my_query->the_post();
                                echo '
                                    <li><a href="'.get_the_permalink().'">'.get_the_title().'</li></a>
                                    ';
                                }
                            }
                        wp_reset_postdata();
                    ?>
                </ul>
            </div>
            <div class="col-md-9">
                <?php
                    while ( have_posts() ) {
                        the_post();
                        echo '
                            <h1>'.get_the_title().'</h1>
                            <p>'.get_the_content_with_formatting().'</p>
                            <p class="pull-right">Posted: '.get_the_date().'<br />
                            By: '.get_the_author().'</p>
                            ';
                        }
                    ?>
            </div>
        </div>
    </div>
<?php get_footer(); ?>