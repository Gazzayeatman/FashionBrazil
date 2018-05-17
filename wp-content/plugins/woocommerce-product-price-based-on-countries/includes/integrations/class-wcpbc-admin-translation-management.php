<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WCPBC_Admin_Translation_Management
 *
 * Built in Integration with WPML
 *
 * @class 		WCPBC_Admin_Translation_Management
 * @version		1.7.0
 * @author 		oscargare
 * @category	Class
 */
class WCPBC_Admin_Translation_Management {

	/**
	 * Get Custom plugin fields
	 */
	public static function add_custom_fields( $wpml_config ){		

		$region_keys = array_keys( WCPBC()->get_regions() );
	
		$meta_keys = wcpbc_get_overwrite_meta_keys();
		array_push( $meta_keys, '_price_method', '_sale_price_dates' );
		
		foreach ( $region_keys as $key ) {
			
			foreach ( $meta_keys as $field ) {
				$wpml_config[ 'wpml-config' ][ 'custom-fields' ]['custom-field'][] = array(
					'value' => '_' . $key . $field,
					'attr'  => array(
						'action' => 'copy'
					)
				);				
			}
		}

		return $wpml_config;
	}	

	/**
	 * Check if original product
	 *
	 * @param int $product_id
	 * @return bool
	 */
	private static function is_original_product( $product_id ) {
		global $wpdb;

		$cache_key =  $product_id;
	       $cache_group = 'is_original_product';
	       $is_original = wp_cache_get($cache_key, $cache_group);

	       if ( false === $is_original ) {

	       	$is_original = $wpdb->get_var( $wpdb->prepare(
		                "SELECT source_language_code IS NULL
		                                FROM {$wpdb->prefix}icl_translations
		                                WHERE element_id=%d AND element_type=%s",		                
		    $product_id, 'post_product'  ) );

	        wp_cache_set( $cache_key, $is_original, $cache_group );	
	       }        

	       return $is_original;
	}

	/**
	 * Enqueue scripts
	 */
	public static function wpml_scripts() {

		global $woocommerce_wpml, $pagenow;

		if ( isset($woocommerce_wpml) &&  is_object( $woocommerce_wpml ) && get_class($woocommerce_wpml) == 'woocommerce_wpml' ) {

			if( ($pagenow == 'post.php' && isset($_GET['post']) && get_post_type( absint( $_GET['post'] ) ) == 'product' && ! self::is_original_product( absint( $_GET['post'] ) ) ) ||
	           	($pagenow == 'post-new.php' && isset($_GET['source_lang']) && isset($_GET['post_type']) && $_GET['post_type'] == 'product') && 
	           	empty( $woocommerce_wpml->settings['trnsl_interface'] ) ) {

				$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	            wp_register_script('wcpbc-lock-fields', WCPBC()->plugin_url() . 'assets/js/wcpbc-admin-lock-fields' . $suffix . '.js', array('jquery'), WCPBC()->version, true );

	        	wp_localize_script( 'wcpbc-lock-fields', 'wcpbc_regions_keys', array_keys( WCPBC()->get_regions() ) );

	       		wp_enqueue_script( 'wcpbc-lock-fields' );

	       	}

		}
	}		
}
add_filter( 'wpml_config_array', 'WCPBC_Admin_Translation_Management::add_custom_fields' );
add_action( 'admin_enqueue_scripts', 'WCPBC_Admin_Translation_Management::wpml_scripts' );