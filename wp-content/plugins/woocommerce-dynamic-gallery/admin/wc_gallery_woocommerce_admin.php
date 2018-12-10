<?php
function wc_dynamic_gallery_show() {
	WC_Gallery_Display_Class::wc_dynamic_gallery_display();
}

function wc_dynamic_gallery_install(){
	update_option('a3rev_woo_dgallery_lite_version', WOO_DYNAMIC_GALLERY_VERSION );
	update_option('a3_dynamic_gallery_db_version', WOO_DYNAMIC_GALLERY_DB_VERSION);

	// Set Settings Default from Admin Init
	global $wc_dgallery_admin_init;
	$wc_dgallery_admin_init->set_default_settings();

	// Build sass
	global $wc_wc_dynamic_gallery_less;
	$wc_wc_dynamic_gallery_less->plugin_build_sass();

	delete_metadata( 'user', 0, $wc_dgallery_admin_init->plugin_name . '-' . 'plugin_framework_global_box' . '-' . 'opened', '', true );


	update_option('a3rev_woo_dgallery_just_installed', true);
}

/**
 * Load languages file
 */
function wc_dynamic_gallery_init() {
	if ( get_option('a3rev_woo_dgallery_just_installed') ) {
		delete_option('a3rev_woo_dgallery_just_installed');
	}

	wc_dynamic_gallery_plugin_textdomain();
}

// Add language
add_action('init', 'wc_dynamic_gallery_init');

// Add custom style to dashboard
add_action( 'admin_enqueue_scripts', array( 'WC_Dynamic_Gallery_Functions', 'a3_wp_admin' ) );

// Add text on right of Visit the plugin on Plugin manager page
add_filter( 'plugin_row_meta', array('WC_Dynamic_Gallery_Functions', 'plugin_extra_links'), 10, 2 );

// Need to call Admin Init to show Admin UI
global $wc_dgallery_admin_init;
$wc_dgallery_admin_init->init();

// Add upgrade notice to Dashboard pages
add_filter( $wc_dgallery_admin_init->plugin_name . '_plugin_extension_boxes', array( 'WC_Dynamic_Gallery_Functions', 'plugin_extension_box' ) );

// Add extra link on left of Deactivate link on Plugin manager page
add_action('plugin_action_links_' . WOO_DYNAMIC_GALLERY_NAME, array( 'WC_Dynamic_Gallery_Functions', 'settings_plugin_links' ) );

add_action( 'wp', array( 'WC_Gallery_Display_Class', 'frontend_register_scripts' ) );

$woocommerce_db_version = get_option( 'woocommerce_db_version', null );

// Change the image show in cart page
if ( version_compare( $woocommerce_db_version, '2.1', '<' ) ) {
	add_filter( 'woocommerce_in_cart_product_thumbnail', array('WC_Dynamic_Gallery_Variations', 'change_image_in_cart_page'), 50, 3 );
} else {
	add_filter( 'woocommerce_cart_item_thumbnail', array('WC_Dynamic_Gallery_Variations', 'change_image_in_cart_page'), 50, 3 );
}

add_action( 'wp', 'setup_dynamic_gallery', 20);
function setup_dynamic_gallery() {
	global $post;
	$current_db_version = get_option( 'woocommerce_db_version', null );
	$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
	if ( is_singular( array( 'product' ) ) || (! empty( $post->post_content ) && stristr($post->post_content, '[product_page') !== false ) ) {
		$global_wc_dgallery_activate = get_option( WOO_DYNAMIC_GALLERY_PREFIX.'activate' );
		$actived_d_gallery = get_post_meta($post->ID, '_actived_d_gallery',true);

		if ($actived_d_gallery == '' && $global_wc_dgallery_activate != 'no') {
			$actived_d_gallery = 1;
		}

		if($actived_d_gallery == 1){

			// Include google fonts into header
			add_action( 'wp_enqueue_scripts', array( 'WC_Dynamic_Gallery_Functions', 'add_google_fonts'), 9 );

			remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);
			remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
			add_action( 'woocommerce_before_single_product_summary', 'wc_dynamic_gallery_show', 30);


			wp_enqueue_style( 'a3-dgallery-style' );
			wp_enqueue_script( 'a3-dgallery-script' );


			$popup_gallery = get_option( WOO_DYNAMIC_GALLERY_PREFIX.'popup_gallery' );
			if ($popup_gallery == 'fb') {
				wp_enqueue_style( 'woocommerce_fancybox_styles' );
				wp_enqueue_script( 'fancybox' );
			} elseif ($popup_gallery == 'colorbox') {
				wp_enqueue_style( 'a3_colorbox_style' );
				wp_enqueue_script( 'colorbox_script' );
			}

			if ( in_array( 'woocommerce-professor-cloud/woocommerce-professor-cloud.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && get_option('woocommerce_cloud_enableCloud') == 'true' ) :
				remove_action( 'woocommerce_before_single_product_summary', 'wc_dynamic_gallery_show', 30);
			endif;
		}
	}
}

// Check upgrade functions
add_action('init', 'woo_dgallery_lite_upgrade_plugin');
function woo_dgallery_lite_upgrade_plugin () {

	// Upgrade to 1.5.0
	if( version_compare(get_option('a3rev_woo_dgallery_lite_version'), '1.5.0') === -1 ){
		update_option('a3rev_woo_dgallery_lite_version', '1.5.0');

		// Build sass
		global $wc_wc_dynamic_gallery_less;
		$wc_wc_dynamic_gallery_less->plugin_build_sass();
	}

	// Upgrade to 1.6.0
	if ( version_compare(get_option('a3rev_woo_dgallery_lite_version'), '1.6.0') === -1 ) {
		update_option('a3rev_woo_dgallery_lite_version', '1.6.0');
		include( WOO_DYNAMIC_GALLERY_FILE_PATH. '/includes/updates/update-1.6.0.php' );
	}

	// Upgrade to 1.8.0
	if ( version_compare(get_option('a3rev_woo_dgallery_lite_version'), '1.8.0') === -1 ) {
		update_option('a3rev_woo_dgallery_lite_version', '1.8.0');
		include( WOO_DYNAMIC_GALLERY_FILE_PATH. '/includes/updates/update-1.8.0.php' );
	}

	// Upgrade to 2.0.0
	if ( version_compare(get_option('a3rev_woo_dgallery_lite_version'), '2.0.0') === -1 ) {
		update_option('a3rev_woo_dgallery_lite_version', '2.0.0');
		include( WOO_DYNAMIC_GALLERY_FILE_PATH. '/includes/updates/update-2.0.0.php' );
	}

	// Upgrade to 2.1.0
	if ( version_compare(get_option('a3rev_woo_dgallery_lite_version'), '2.1.0') === -1 ) {
		update_option('a3rev_woo_dgallery_lite_version', '2.1.0');
		update_option('wc_dgallery_lite_clean_on_deletion', 'no');
		update_option('a3_dynamic_gallery_db_updated', 'no');
	}

	// Upgrade to 2.1.1
	if ( version_compare(get_option('a3rev_woo_dgallery_lite_version'), '2.2.0') === -1 ) {
		update_option('a3rev_woo_dgallery_lite_version', '2.2.0');
		update_option('woo_dynamic_gallery_style_version', time() );
	}

	// Upgrade to 2.3.0
	if( version_compare(get_option('a3rev_woo_dgallery_lite_version'), '2.3.0') === -1 ){
		update_option('a3rev_woo_dgallery_lite_version', '2.3.0');

		// Build sass
		global $wc_wc_dynamic_gallery_less;
		$wc_wc_dynamic_gallery_less->plugin_build_sass();
	}

	update_option('a3rev_woo_dgallery_lite_version', WOO_DYNAMIC_GALLERY_VERSION );
}

?>