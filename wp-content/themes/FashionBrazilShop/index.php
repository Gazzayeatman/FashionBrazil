<?php 
/**
 * Template Name: Home Page Index
 */
get_header(); ?> 
<div class="container-fluid new-carousel home-page">
    <?php echo do_shortcode('[metaslider id="3118"]'); ?>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="line-header">
                        &nbsp;
                    </h2>
                </div>
            </div>
        </div>
        <div class="container padding-40">
            <div class="row">
                <?php
                    $my_query = new WP_Query('cat=11');
                    if ($my_query->have_posts()) { 
                        while ($my_query->have_posts()) {
                            $my_query->the_post();
                            $linkTo = get_field("link_to");
                            echo '
                                <div class="col-md-4 col-sm-12 col-xs-12 padding-20">
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
        <div class="container-fluid">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h2 class="line-header">
                            <span>
                                NEW ARRIVALS
                            </span>
                        </h2>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="container featured-row">
                    <div class="row">
                        <div class="col-md-12 col-sm-12 padding-20">
                            <div class="row">
                                <?php
                                    $meta_query   = WC()->query->get_meta_query();
                                    $meta_query = [
                                        'key'   => '_featured',
                                        'value' => 'yes'
                                    ];
                                    $args = [
                                        'post_type'   =>  'product',
                                        'stock'       =>  1,
                                        'showposts'   =>  6,
                                        'orderby'     =>  'date',
                                        'order'       =>  'DESC',
                                        'meta_query'  =>  $meta_query
                                    ];

                                    $loop = new WP_Query($args);
                                    if ($loop->have_posts()) {
                                        while ($loop->have_posts()) : $loop->the_post();
                                            wc_get_template_part('content', 'product');
                                        endwhile;
                                    } else {
                                        echo __('No products found');
                                    }
                                    wp_reset_postdata();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container padding-40">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="line-header">
                        <span>
                            <i class="fa fa-instagram" aria-hidden="true"></i> Follow our instagram
                        </span>
                    </h2>
                    <?php echo do_shortcode('[instagram-feed]'); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="//s3.amazonaws.com/downloads.mailchimp.com/js/signup-forms/popup/embed.js" data-dojo-config="usePlainJson: true, isDebug: false"></script><script type="text/javascript">require(["mojo/signup-forms/Loader"], function(L) { L.start({"baseUrl":"mc.us13.list-manage.com","uuid":"9e7f103fef2e73a2ab6db5563","lid":"31796dad68"}) })</script>
<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window,document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
 fbq('init', '1587859154867154'); 
fbq('track', 'PageView');
</script>
<noscript>
 <img height="1" width="1" 
src="https://www.facebook.com/tr?id=1587859154867154&ev=PageView
&noscript=1"/>
</noscript>
<!-- End Facebook Pixel Code -->
<?php get_footer(); ?>
