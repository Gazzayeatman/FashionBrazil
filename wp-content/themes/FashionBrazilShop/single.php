<?php
/**
 * The template for displaying all single posts.
 *
 * @package storefront
 */

get_header(); ?>
<div class="container-fluid">
    <div class="container text-content">
        <div class="row">
			<div class="col-md-3">
				<?php
					if (in_category(34)) {
						$my_query = new WP_Query( 'cat=34' );
						echo "<h4>Other posts</h4>";
						echo '<ul>';
						if ( $my_query->have_posts() ) { 
							while ( $my_query->have_posts() ) {
								$my_query->the_post();
								echo '
									<li><a href="'.get_the_permalink().'">'.get_the_title().'</li></a>
									';
								}
							}
						wp_reset_postdata();
					}
					if (in_category (51)) {
						$my_query = new WP_Query( 'cat=51' );
						echo "<h4>Upcoming Events</h4>";
						echo '<ul>';
						if ( $my_query->have_posts() ) { 
							while ( $my_query->have_posts() ) {
								$my_query->the_post();
								echo '
									<li><a href="'.get_the_permalink().'">'.get_the_title().'</li></a>
									';
								}
							}
						wp_reset_postdata();
					}
				?>
				</ul>
            </div>
				<?php
					if (in_category(34)) {
						if ( have_posts() ) { 
							echo '<div class="col-md-9">';
							while ( have_posts() ) {
								the_post();
								echo '
									<h1>'.get_the_title().'</h1>
									<p>'.get_the_content_with_formatting().'</p>
									<p class="pull-right">Posted: '.get_the_date(). 
										'<div class="share">
											<h4>Share this item</h4>
											<ul class="share-social-icons">
												<li><a target="_new" href="https://www.facebook.com/sharer/sharer.php?u=http://fashionbrazil.co.nz'.$_SERVER[REQUEST_URI].'&t='.get_the_title().'>"><i class="fa fa-facebook-square" aria-hidden="true"></i></a></li>
												<li><a target="_new" href="https://plus.google.com/share?url=http://fashionbrazil.co.nz'.$_SERVER[REQUEST_URI].'"><i class="fa fa-google-plus-square" aria-hidden="true"></i></a></li>
												<li><a target="_new" href="https://twitter.com/intent/tweet?text='.get_the_title().'>&url=http://fashionbrazil.co.nz'.$_SERVER[REQUEST_URI].'"><i class="fa fa-twitter-square" aria-hidden="true"></i></a></li>
											</ul>
										</div>';
								}
							}
						wp_reset_postdata();
					}
					if (in_category(51)) {
						if ( have_posts() ) {
							echo "<div class='col-md-6'>";
							while ( have_posts() ) {
								the_post();
                                echo '
                                    <h1>'.get_the_title().'</h1>
									'. get_the_post_thumbnail() .'
                                    <p>'.get_the_content_with_formatting().'</p>
                                    <p class="pull-right">Posted: '.get_the_date()
								;
							}
							echo
							'<div class="share">
								<h4>Share this item</h4>
								<ul class="share-social-icons">
									<li><a target="_new" href="https://www.facebook.com/sharer/sharer.php?u=http://fashionbrazil.co.nz'.$_SERVER[REQUEST_URI].'&t='.get_the_title().'>"><i class="fa fa-facebook-square" aria-hidden="true"></i></a></li>
									<li><a target="_new" href="https://plus.google.com/share?url=http://fashionbrazil.co.nz'.$_SERVER[REQUEST_URI].'"><i class="fa fa-google-plus-square" aria-hidden="true"></i></a></li>
									<li><a target="_new" href="https://twitter.com/intent/tweet?text='.get_the_title().'>&url=http://fashionbrazil.co.nz'.$_SERVER[REQUEST_URI].'"><i class="fa fa-twitter-square" aria-hidden="true"></i></a></li>
								</ul>
							</div>';
							echo '</div>';
							wp_reset_postdata();
						}
						echo '<div class="col-md-3">';
						echo '<h4>Event Information</h4>';
							if (have_posts() ) {
								while (have_posts() ) {
									$summary = get_field("event_summary");  
									the_post();
										echo $summary;
									}
								}
							wp_reset_postdata();
						echo '</div>';
						echo "<div class='col-md-12 map'>";
							$map = get_field("event_location");
							echo $map;
						echo "</div>";
					}
				?>
            </div>
        </div>
    </div>
<?php get_footer(); ?>