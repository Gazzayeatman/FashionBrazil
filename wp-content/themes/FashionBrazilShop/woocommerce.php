<?php 
/**
 * Template Name: Subpage
 */
get_header(); ?>
<div class="woocommerce">
    <div class="container-fluid">
        <div class="container text-content">
            <?php woocommerce_breadcrumb() ?>
            <div class="row">
                <div class="col-md-12">
                    <?php
                        woocommerce_content();
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php get_footer(); ?>