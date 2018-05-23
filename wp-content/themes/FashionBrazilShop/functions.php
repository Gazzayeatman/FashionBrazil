<?php

/**
 * Storefront automatically loads the core CSS even if using a child theme as it is more efficient
 * than @importing it in the child theme style.css file.
 *
 * Uncomment the line below if you'd like to disable the Storefront Core CSS.
 *
 * If you don't plan to dequeue the Storefront Core CSS you can remove the subsequent line and as well
 * as the sf_child_theme_dequeue_style() function declaration.
 */
//add_action( 'wp_enqueue_scripts', 'sf_child_theme_dequeue_style', 999 );

/**
 * Dequeue the Storefront Parent theme core CSS
 */
function sf_child_theme_dequeue_style() {
    wp_dequeue_style( 'storefront-style' );
    wp_dequeue_style( 'storefront-woocommerce-style' );
}
/**
 * Note: DO NOT! alter or remove the code above this text and only add your custom PHP functions below this text.
 */
add_filter( 'get_product_search_form' , 'woo_custom_product_searchform' );

/**
 * woo_custom_product_searchform
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/
function woo_custom_product_searchform( $form ) {
	
	$form = '
	<form role="search" method="get" id="searchform" action="' . esc_url( home_url( '/'  ) ) . '">
		<div>
			<label class="screen-reader-text" for="s">' . __( 'Search for:', 'woocommerce' ) . '</label>
            <select>
                <option>Yas</option>
                <option>Babes</option>
            </select>
			<input type="text" value="' . get_search_query() . '" name="s" id="s" placeholder="' . __( 'My Search form', 'woocommerce' ) . '" />
			<input type="submit" id="searchsubmit" value="'. esc_attr__( 'Search', 'woocommerce' ) .'" />
			<input type="hidden" name="post_type" value="product" />
            <h1>babes</h1>
		</div>
	</form>';
	
	return $form;
}
function register_my_account_menu() {
  	register_nav_menu('My Account',__( 'My Account' ));
}
add_action( 'init', 'register_my_account_menu' );
function register_shop_menu() {
  	register_nav_menu('Shop',__( 'Shop' ));
}
add_action( 'init', 'register_shop_menu' );
function register_information_menu() {
  	register_nav_menu('Information',__( 'Information' ));
}
add_action( 'init', 'register_information_menu' );
function register_support_menu() {
  	register_nav_menu('Support',__( 'Support' ));
}
add_action( 'init', 'register_support_menu' );

function register_main_menu() {
  	register_nav_menu('Main',__( 'Main' ));
}
add_action( 'init', 'register_main_menu' );

function register_brands_menu() {
  	register_nav_menu('Brands',__( 'Brands' ));
}
add_action( 'init', 'register_brands_menu' );

function register_top_right_menu() {
  	register_nav_menu('top_right',__( 'Top-Right' ));
}
add_action( 'init', 'register_top_right_menu' );

function wpb_adding_scripts() {
	wp_register_script('script', 'https://fashionbrazil.local/wp-content/themes/FashionBrazilShop/script.js', array('jquery'),'1.9', true);
	wp_enqueue_script('script');
}

add_action( 'wp_enqueue_scripts', 'wpb_adding_scripts' ); 

/** 
 * Override 'woocommerce_content' function
 */

if ( ! function_exists( 'woocommerce_content' ) ) {
	/**
	* Output WooCommerce content.
	*
	* This function is only used in the optional 'woocommerce.php' template.
	* which people can add to their themes to add basic woocommerce support.
	* without hooks or modifying core templates.
	*
	*/

	function woocommerce_content() {
		if (is_singular( 'product')) {
			while (have_posts()) : the_post();
				if (has_term('my-cat-slug', 'product_cat')) {
					woocommerce_get_template_part( 'content', 'single-product-dogs' );
				} else {
					woocommerce_get_template_part( 'content', 'single-product' );
				}
			endwhile;
		} else { ?>
			<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
				<h1 class="page-title"><?php woocommerce_page_title(); ?></h1>
			<?php endif; ?>
			<?php do_action( 'woocommerce_archive_description' ); ?>
			<?php if ( have_posts() ) : ?>
				<?php do_action('woocommerce_before_shop_loop'); ?>
				<?php woocommerce_product_loop_start(); ?>
				<?php woocommerce_product_subcategories(); ?>
				<?php while ( have_posts() ) : the_post(); ?>
				<?php wc_get_template_part( 'content', 'product' ); ?>
				<?php endwhile; ?>
				<?php woocommerce_product_loop_end(); ?>
				<?php do_action('woocommerce_after_shop_loop'); ?>
			<?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>
				<?php wc_get_template( 'loop/no-products-found.php' ); ?>
			<?php endif;
		}
	}
}
function get_the_content_with_formatting ($more_link_text = '(more...)', $stripteaser = 0, $more_file = '') {
	$content = get_the_content($more_link_text, $stripteaser, $more_file);
	$content = apply_filters('the_content', $content);
	$content = str_replace(']]>', ']]&gt;', $content);
	return $content;
}
function wpse_category_single_template( $single_template ) {
    global $post;
    $all_cats = get_the_category();

    if ( $all_cats[0]->cat_ID == '34' ) {
        if ( file_exists(get_template_directory() . "/single-blog.php") ) return get_template_directory() . "/single-blog.php";
    } elseif ( $all_cats[0]->cat_ID == '51' ) {
        if ( file_exists(get_template_directory() . "/single-events.php") ) return get_template_directory() . "/single-events.php";
    }
    return $single_template;
}
add_filter( 'single_template', 'wpse_category_single_template' );
function add_custom_class() {
	$shopID =  get_the_title();
	if (is_shop()) {
		echo "<div class='custom-shop'>";
	}
}
add_action( 'woocommerce_before_shop_loop', 'add_custom_class', 10 );
function end_custom_class() {
	$shopID =  get_the_title('');
	if (is_shop()) {
		echo "</div>";
	}
}
add_action( 'woocommerce_after_shop_loop', 'end_custom_class', 10 );

remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 15 );