<?php 
/**
 * Template Name: Blog Page
 */
get_header(); ?>
<div class="container-fluid">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <h4>Other Posts</h4>
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
                    $args = [
                        'cat' => '34',
                        'posts_per_page' => '1',
                    ];
                    $my_query = new WP_Query($args);
                    if ( $my_query->have_posts() ) { 
                        while ( $my_query->have_posts() ) {
                            $my_query->the_post();
                            echo '
                                    <h1>'.get_the_title().'</h1>
                                    <p>'.get_the_content_with_formatting().'</p>
                                    <p class="pull-right">Posted: '.get_the_date()
                                ;
                            }
                        }
                    wp_reset_postdata();
                ?>
                <div class="share">
                    <h4>Share this item</h4>
                    <ul class="share-social-icons">
                        <li><a target="_new" href="https://www.facebook.com/sharer/sharer.php?u=http://fashionbrazil.local<?php echo $_SERVER['REQUEST_URI']; ?>&t=<?php echo get_the_title(); ?>"><i class="fa fa-facebook-square" aria-hidden="true"></i></a></li>
                        <li><a target="_new" href="https://plus.google.com/share?url=http://fashionbrazil.local<?php echo $_SERVER['REQUEST_URI']; ?>"><i class="fa fa-google-plus-square" aria-hidden="true"></i></a></li>
                        <li><a target="_new" href="https://twitter.com/intent/tweet?text=<?php echo get_the_title(); ?>&url=http://fashionbrazil.local<?php echo $_SERVER['REQUEST_URI']; ?>"><i class="fa fa-twitter-square" aria-hidden="true"></i></a></li>
                        <!--<li><a href="http://fashionbrazil.local/shop/golden-sports-bra/#"><i class="fa fa-pinterest-square" aria-hidden="true"></i></a></li>-->
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php get_footer(); ?>