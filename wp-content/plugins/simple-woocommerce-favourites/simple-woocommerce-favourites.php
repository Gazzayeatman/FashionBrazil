<?php
/**
 * Plugin Name: Simple Woocommerce Favourites
 * Plugin URI: https://simplistics.ca
 * Description: Manages a simple list of favourites for each user of their prefered products and displays it with a shortcode
 * Version: 1.0
 * Author: Jonathan Boss
 * Author URI: https://simplistics.ca
 */

function simple_add_scripts(){
	wp_register_script('simple_fav_script', plugin_dir_url(__FILE__) . 'includes/js/add-to-favourites.js', array('jquery'), '1.0.0');
	wp_enqueue_script('simple_fav_script');
	wp_localize_script("simple_fav_script", 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
	$simple_nonce = array(
        'simple_favourites_nonce' => wp_create_nonce('simple_favourites_nonce')
    );
	wp_localize_script('simple_fav_script', 'simple_nonce', $simple_nonce);
}
add_action('wp_enqueue_scripts', 'simple_add_scripts');

function simple_add_favourites_button(){
	if( is_user_logged_in() ){
		$favourites = simple_favourites::get_favourites($user_id);
		global $product;
		if( in_array($product->id, $favourites) ){ return; }
		require_once('includes/views/add-to-favourites-button.php');
	}
}
add_action('woocommerce_before_single_product', 'simple_add_favourites_button');

function simple_print_favourites( $atts ){
	$GLOBALS['simple_favourites_running'] = true;
	extract( shortcode_atts( array(
        'user_id' => false
    ), $atts ) );
	$favourites = simple_favourites::get_favourites($user_id);
	ob_start();
		require_once('includes/views/favourites-template.php');
	$view = ob_get_clean();
	unset($GLOBALS['simple_favourites_running']);
	return $view;
}
add_shortcode('simple_print_favourites', 'simple_print_favourites');

function simple_add_remove_button(){
	global $product;
	if($GLOBALS['simple_favourites_running']){
		echo '<button class="simple-remove-from-favourites" data-product_id="'. $product->id .'">Remove</button>';
	}
}
add_action('woocommerce_before_shop_loop_item', 'simple_add_remove_button', 10);

function simple_favourites_button(){
	global $product;
	if(!$product){
		return;
	}
	if( is_user_logged_in() && !$GLOBALS['simple_favourites_running'] ){
		include('includes/views/add-to-favourites-button.php');
	}
}
add_shortcode('simple_favourites_button', 'simple_favourites_button');

//REQUIRES
require_once('includes/simple_ajax.php');
require_once('includes/simple_favourites_class.php');