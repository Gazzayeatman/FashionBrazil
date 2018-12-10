<?php 
/**
 * Template Name: Subpage
 */
get_header(); ?>
<div class="container-fluid">
    <?php while ( have_posts() ) : the_post();?>
    <div class="container text-content">
        <div class="row">
            <div class="col-md-12">
                <h1><?php echo the_title(); ?></h1>
                <?php echo the_content(); ?>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
<?php get_footer(); ?>