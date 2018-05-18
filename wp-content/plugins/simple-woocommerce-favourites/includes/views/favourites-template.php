<div id='simple_favourites_display'>
	<h2>My Favorites</h2>
	<?php if(!empty($favourites)) : ?>
		<?php echo do_shortcode('[products ids="' . implode(',', $favourites) . '" columns="3"]'); ?>
	<?php else : ?>
		<h2>There are no items in your favorites!</h2>
		<p>To add some now, visit the shop</p>
		<a href='<?php echo get_home_url(); ?>/shop'>Shop Now</a>
	<?php endif; ?>
</div>
