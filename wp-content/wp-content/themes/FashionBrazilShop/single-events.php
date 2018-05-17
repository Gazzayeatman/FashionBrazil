<?php 
/**
 * Template Name: Single Event
 */
get_header(); ?>
<div class="container-fluid">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <ul>
                    <h4>Up-coming events</h4>
                    <?php
                        $my_query = new WP_Query( 'cat=26' ); 
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
            <div class="col-md-6">
                <?php
                        $args = [
                            'category' => '26',
                            'posts_per_page' => '1',
                        ];
                        $my_query = new WP_Query($args);
                        if ( $my_query->have_posts() ) { 
                            while ( $my_query->have_posts() ) {
                                $map = $get_field('event_location');
                                $my_query->the_post();
                                echo '
                                    <h1>'.get_the_title().'</h1>
                                    <p>'.get_the_content_with_formatting().'</p>
                                    <p class="pull-right">Posted: '.get_the_date().'<br />
                                    By: '.get_the_author().'</p>
                                    <p>'.map.'</p>
                                    ';
                                }
                            }
                        wp_reset_postdata();
                    ?>
            </div>
            <div class="col-md-3">
                <h4>Event Information</h4>
                <?php
                    $args = [
                        'category' => '26'
                    ];
                    $my_query = new WP_Query($args);
                    if ( $my_query->have_posts() ) {
                        while ( $my_query->have_posts() ) {
                            $summary = get_field("event_summary");  
                            $my_query->the_post();
                                echo $summary;
                            }
                        }
                    wp_reset_postdata();
                ?>
            </div>
        </div>
    </div>
<?php get_footer(); ?>