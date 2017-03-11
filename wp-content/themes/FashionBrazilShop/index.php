<?php get_header(); ?>
<div class="container-fluid">
        <?php echo do_shortcode('[image-carousel]'); ?>
        <div class="container promotional">
            <?php query_posts( 'category_name=home_page_featured&posts_per_page=9' ); ?>
            <?php
                $count = 1;
                while ( have_posts() ) : the_post();
                $link = get_field("link_to");
            ?>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="promo-block">
                    <div class="promo-img">
                        <a href="<?php echo $link; ?>"><div class="img-overlay">
                            <h3><?php echo the_title(); ?></h3>
                        </div>
                        <?php echo the_post_thumbnail(); ?>
                    </div></a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <div class="container">
            <div class="instagram">
            </div>
        </div>
    </div>
<?php get_footer(); ?>
