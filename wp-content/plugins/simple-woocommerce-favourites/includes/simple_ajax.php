<?php

//ADD TO FAVOURITES
add_action("wp_ajax_simple_ajax_add_to_favourites", "simple_ajax_add_to_favourites");
add_action("wp_ajax_nopriv_simple_ajax_add_to_favourites", "simple_ajax_add_to_favourites");

function simple_ajax_add_to_favourites(){

	check_ajax_referer('simple_favourites_nonce', 'simple_favourites_nonce');
	$favourites = simple_favourites::get_favourites();

	$prod_id = sanitize_text_field( $_POST['prod_id'] );
	if( !in_array($prod_id, $favourites) ){
		$prod_id = (int)$prod_id;
		array_push($favourites, $prod_id);
		simple_favourites::update_favourites($favourites);
		echo 'This item has been added to your favourites.';
		die();
	}
	echo 'This item is already in your favourites.';
	die();
}

//REMOVE FROM FAVOURITES
add_action("wp_ajax_simple_ajax_remove_from_favourites", "simple_ajax_remove_from_favourites");
add_action("wp_ajax_nopriv_simple_ajax_remove_from_favourites", "simple_ajax_remove_from_favourites");

function simple_ajax_remove_from_favourites(){
	check_ajax_referer('simple_favourites_nonce', 'simple_favourites_nonce');
	$prod_id = (int)sanitize_text_field($_POST['prod_id']);
	$user_id = get_current_user_id();
	simple_favourites::remove($user_id, $prod_id);
	echo true;
	die();
}