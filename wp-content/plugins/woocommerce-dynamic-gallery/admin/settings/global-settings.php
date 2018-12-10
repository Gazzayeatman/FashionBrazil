<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
/*-----------------------------------------------------------------------------------
WC Dynamic Gallery Settings

TABLE OF CONTENTS

- var parent_tab
- var subtab_data
- var option_name
- var form_key
- var position
- var form_fields
- var form_messages

- __construct()
- subtab_init()
- set_default_settings()
- get_settings()
- subtab_data()
- add_subtab()
- settings_form()
- init_form_fields()

-----------------------------------------------------------------------------------*/

class WC_Dynamic_Gallery_Global_Settings extends WC_Dynamic_Gallery_Admin_UI
{
	
	/**
	 * @var string
	 */
	private $parent_tab = 'global-settings';
	
	/**
	 * @var array
	 */
	private $subtab_data;
	
	/**
	 * @var string
	 * You must change to correct option name that you are working
	 */
	public $option_name = '';
	
	/**
	 * @var string
	 * You must change to correct form key that you are working
	 */
	public $form_key = 'wc_dgallery_global_settings';
	
	/**
	 * @var string
	 * You can change the order show of this sub tab in list sub tabs
	 */
	private $position = 1;
	
	/**
	 * @var array
	 */
	public $form_fields = array();
	
	/**
	 * @var array
	 */
	public $form_messages = array();
	
	/*-----------------------------------------------------------------------------------*/
	/* __construct() */
	/* Settings Constructor */
	/*-----------------------------------------------------------------------------------*/
	public function __construct() {
		$this->init_form_fields();
		$this->subtab_init();
		
		$this->form_messages = array(
				'success_message'	=> __( 'Dynamic Gallery Settings successfully saved.', 'woocommerce-dynamic-gallery' ),
				'error_message'		=> __( 'Error: Dynamic Gallery Settings can not save.', 'woocommerce-dynamic-gallery' ),
				'reset_message'		=> __( 'Dynamic Gallery Settings successfully reseted.', 'woocommerce-dynamic-gallery' ),
			);

		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_end', array( $this, 'include_script' ) );

		add_action( $this->plugin_name . '_set_default_settings' , array( $this, 'set_default_settings' ) );
		
		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_init' , array( $this, 'reset_default_settings' ) );
		
		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_init' , array( $this, 'after_save_settings' ) );
		//add_action( $this->plugin_name . '_get_all_settings' , array( $this, 'get_settings' ) );
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* subtab_init() */
	/* Sub Tab Init */
	/*-----------------------------------------------------------------------------------*/
	public function subtab_init() {
		
		add_filter( $this->plugin_name . '-' . $this->parent_tab . '_settings_subtabs_array', array( $this, 'add_subtab' ), $this->position );
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* set_default_settings()
	/* Set default settings with function called from Admin Interface */
	/*-----------------------------------------------------------------------------------*/
	public function set_default_settings() {
		global $wc_dgallery_admin_interface;
		
		$wc_dgallery_admin_interface->reset_settings( $this->form_fields, $this->option_name, false );
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* reset_default_settings()
	/* Reset default settings with function called from Admin Interface */
	/*-----------------------------------------------------------------------------------*/
	public function reset_default_settings() {
		global $wc_dgallery_admin_interface;
		
		$wc_dgallery_admin_interface->reset_settings( $this->form_fields, $this->option_name, true, true );
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* after_save_settings()
	/* Process when clean on deletion option is un selected */
	/*-----------------------------------------------------------------------------------*/
	public function after_save_settings() {
		if ( isset( $_POST['bt_save_settings'] ) && isset( $_POST[WOO_DYNAMIC_GALLERY_PREFIX.'reset_galleries_activate'] ) ) {
			delete_option( WOO_DYNAMIC_GALLERY_PREFIX.'reset_galleries_activate' );
			WC_Dynamic_Gallery_Functions::reset_products_galleries_activate();			
		}
		if ( isset( $_POST['bt_save_settings'] ) && isset( $_POST[WOO_DYNAMIC_GALLERY_PREFIX.'reset_feature_image_activate'] ) ) {
			delete_option( WOO_DYNAMIC_GALLERY_PREFIX.'reset_feature_image_activate' );
			WC_Dynamic_Gallery_Functions::reset_auto_feature_image_activate();			
		}
		if ( isset( $_POST['bt_save_settings'] ) && isset( $_POST[WOO_DYNAMIC_GALLERY_PREFIX.'reset_image_source'] ) ) {
			delete_option( WOO_DYNAMIC_GALLERY_PREFIX.'reset_image_source' );
			WC_Dynamic_Gallery_Functions::reset_image_source();			
		}
		if ( ( isset( $_POST['bt_save_settings'] ) || isset( $_POST['bt_reset_settings'] ) ) && get_option( $this->plugin_name . '_clean_on_deletion' ) == 'no'  )  {
			$uninstallable_plugins = (array) get_option('uninstall_plugins');
			unset($uninstallable_plugins[ $this->plugin_path ]);
			update_option('uninstall_plugins', $uninstallable_plugins);
		}
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* get_settings()
	/* Get settings with function called from Admin Interface */
	/*-----------------------------------------------------------------------------------*/
	public function get_settings() {
		global $wc_dgallery_admin_interface;
		
		$wc_dgallery_admin_interface->get_settings( $this->form_fields, $this->option_name );
	}
	
	/**
	 * subtab_data()
	 * Get SubTab Data
	 * =============================================
	 * array ( 
	 *		'name'				=> 'my_subtab_name'				: (required) Enter your subtab name that you want to set for this subtab
	 *		'label'				=> 'My SubTab Name'				: (required) Enter the subtab label
	 * 		'callback_function'	=> 'my_callback_function'		: (required) The callback function is called to show content of this subtab
	 * )
	 *
	 */
	public function subtab_data() {
		
		$subtab_data = array( 
			'name'				=> 'global-settings',
			'label'				=> __( 'Settings', 'woocommerce-dynamic-gallery' ),
			'callback_function'	=> 'wc_dgallery_global_settings_form',
		);
		
		if ( $this->subtab_data ) return $this->subtab_data;
		return $this->subtab_data = $subtab_data;
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* add_subtab() */
	/* Add Subtab to Admin Init
	/*-----------------------------------------------------------------------------------*/
	public function add_subtab( $subtabs_array ) {
	
		if ( ! is_array( $subtabs_array ) ) $subtabs_array = array();
		$subtabs_array[] = $this->subtab_data();
		
		return $subtabs_array;
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* settings_form() */
	/* Call the form from Admin Interface
	/*-----------------------------------------------------------------------------------*/
	public function settings_form() {
		global $wc_dgallery_admin_interface;
		
		$output = '';
		$output .= $wc_dgallery_admin_interface->admin_forms( $this->form_fields, $this->form_key, $this->option_name, $this->form_messages );
		
		return $output;
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* init_form_fields() */
	/* Init all fields of this form */
	/*-----------------------------------------------------------------------------------*/
	public function init_form_fields() {

		$wc_version = get_option( 'woocommerce_version', '1.0' );

		$wc_display_settings_url = admin_url( 'customize.php?autofocus[panel]=woocommerce&autofocus[section]=woocommerce_product_images' );
		if ( version_compare( $wc_version, '3.3.0', '<' ) ) {
			$wc_display_settings_url = admin_url( 'admin.php?page=wc-settings&tab=products&section=display' );
		}

  		// Define settings
     	$this->form_fields = apply_filters( $this->option_name . '_settings_fields', array(

			array(
            	'name' 		=> __( 'Plugin Framework Global Settings', 'woocommerce-dynamic-gallery' ),
            	'id'		=> 'plugin_framework_global_box',
                'type' 		=> 'heading',
                'first_open'=> true,
                'is_box'	=> true,
           	),

           	array(
           		'name'		=> __( 'Customize Admin Setting Box Display', 'woocommerce-dynamic-gallery' ),
           		'desc'		=> __( 'By default each admin panel will open with all Setting Boxes in the CLOSED position.', 'woocommerce-dynamic-gallery' ),
                'type' 		=> 'heading',
           	),
           	array(
				'type' 		=> 'onoff_toggle_box',
			),
			array(
           		'name'		=> __( 'Google Fonts', 'woocommerce-dynamic-gallery' ),
           		'desc'		=> __( 'By Default Google Fonts are pulled from a static JSON file in this plugin. This file is updated but does not have the latest font releases from Google.', 'woocommerce-dynamic-gallery' ),
                'type' 		=> 'heading',
           	),
           	array(
                'type' 		=> 'google_api_key',
           	),
           	array(
            	'name' 		=> __( 'House Keeping', 'woocommerce-dynamic-gallery' ),
                'type' 		=> 'heading',
            ),
			array(
				'name' 		=> __( 'Clean Up On Deletion', 'woocommerce-dynamic-gallery' ),
				'desc' 		=> __( 'On deletion (not deactivate) the plugin will completely remove all tables and data it created, leaving no trace it was ever here.', 'woocommerce-dynamic-gallery' ),
				'id' 		=> $this->plugin_name . '_clean_on_deletion',
				'type' 		=> 'onoff_checkbox',
				'default'	=> 'no',
				'separate_option'	=> true,
				'free_version'		=> true,
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'woocommerce-dynamic-gallery' ),
				'unchecked_label' 	=> __( 'OFF', 'woocommerce-dynamic-gallery' ),
			),

			array(
				'name' => __('Dynamic Gallery Activation', 'woocommerce-dynamic-gallery' ),
				'type' => 'heading',
				'desc' => __( 'When activated Dynamic Gallery function is applied to each products WooCommerce Default gallery images. Dynamic Gallery menu is added to each product pages WooCommerce Product Data menu. The WooCommerce Product Gallery is converted to Dynamic Product Gallery.', 'woocommerce-dynamic-gallery' ),
				'id'     => 'wc_dgallery_global_activation_box',
				'is_box' => true,
			),
			array(
				'name' 		=> __( 'Gallery Activation Default', 'woocommerce-dynamic-gallery' ),
				'desc' 		=> __( 'Changes to the default Gallery activation does NOT apply to existing products. It will be applied to all products created after changing the default.', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'activate',
				'default'	=> 'yes',
				'type' 		=> 'onoff_checkbox',
				'free_version'		=> true,
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'woocommerce-dynamic-gallery' ),
				'unchecked_label' 	=> __( 'OFF', 'woocommerce-dynamic-gallery' ),
			),
			array(  
				'name' 		=> __( 'Reset Activation To Default', 'woocommerce-dynamic-gallery' ),
				'desc' 		=> __( 'Switch ON and Save Changes will reset ALL existing and future products to the Gallery Activation Default that you have set above.', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'reset_galleries_activate',
				'default'	=> 'no',
				'type' 		=> 'onoff_checkbox',
				'free_version'		=> true,
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'woocommerce-dynamic-gallery' ),
				'unchecked_label' 	=> __( 'OFF', 'woocommerce-dynamic-gallery' ),
			),

			array(
				'name' => __('Dynamic Gallery Image Source Options', 'woocommerce-dynamic-gallery' ),
				'type' => 'heading',
				'desc' => __( 'Set where Dynamic Gallery should get its images from. The option set here applies to all products but can be changed on a product by product basis from the Dynamic Gallery menu on Product data meta box.', 'woocommerce-dynamic-gallery' ),
				'id'     => 'wc_dgallery_image_source_box',
				'is_box' => true,
			),
			array(
				'name' 		=> __( 'Attached Images', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX . 'image_source',
				'type' 		=> 'onoff_radio',
				'free_version'		=> true,
				'default' 	=> 'wc_gallery',
				'onoff_options' => array(
					array(
						'val' 				=> 'attached',
						'text' 				=> __( 'Switch ON will show all images uploaded to the post in the Dynamic Gallery', 'woocommerce-dynamic-gallery' ),
						'checked_label'		=> __( 'ON', 'woocommerce-dynamic-gallery' ) ,
						'unchecked_label' 	=> __( 'OFF', 'woocommerce-dynamic-gallery' ) ,
					),

				),
			),
			array(
				'name' 		=> __( 'WC Product Images', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX . 'image_source',
				'type' 		=> 'onoff_radio',
				'free_version'		=> true,
				'default' 	=> 'wc_gallery',
				'onoff_options' => array(
					array(
						'val' 				=> 'wc_gallery',
						'text' 				=> __( "Switch ON and Dynamic gallery will get only those images that have been uploaded to the WC Product images", 'woocommerce-dynamic-gallery' ),
						'checked_label'		=> __( 'ON', 'woocommerce-dynamic-gallery' ) ,
						'unchecked_label' 	=> __( 'OFF', 'woocommerce-dynamic-gallery' ) ,
					),

				),
			),
			array(  
				'name' 		=> __( 'Reset To Default', 'woocommerce-dynamic-gallery' ),
				'desc' 		=> __( 'Switch ON and Save Changes will reset ALL products to get images from the option that is set above.', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'reset_image_source',
				'default'	=> 'no',
				'type' 		=> 'onoff_checkbox',
				'free_version'		=> true,
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'woocommerce-dynamic-gallery' ),
				'unchecked_label' 	=> __( 'OFF', 'woocommerce-dynamic-gallery' ),
			),

			array(
				'name' => __( 'Product Feature Image', 'woocommerce-dynamic-gallery' ),
				'type' => 'heading',
				'desc' => '<ul>
<li>* '.__( 'ON this option and the Product Image (featured image) will show as the first image in the gallery without having to upload it to the Gallery.', 'woocommerce-dynamic-gallery' ).'</li>
<li>* '.__( 'OFF and the uploaded Product Image (feature image) will show on the product card but not in the Gallery on Product Page.', 'woocommerce-dynamic-gallery' ).'</li>
<li>* '.__( 'Can be turned ON or OFF for each product from the WooCommerce Product data Dynamic Gallery menu.', 'woocommerce-dynamic-gallery' ).'</li>
</ul>',
				'id'     => 'wc_dgallery_feature_image_box',
				'is_box' => true,
			),
			array(
				'name' 		=> __( 'Include in Gallery', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'auto_feature_image',
				'default'	=> 'yes',
				'type' 		=> 'onoff_checkbox',
				'free_version'		=> true,
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'woocommerce-dynamic-gallery' ),
				'unchecked_label' 	=> __( 'OFF', 'woocommerce-dynamic-gallery' ),
			),
			array(  
				'name' 		=> __( 'Reset Activation To Default', 'woocommerce-dynamic-gallery' ),
				'desc' 		=> __( "Switch ON and Save Changes will reset ALL existing and future products to the 'Include in Gallery' Default that you have set above.", 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'reset_feature_image_activate',
				'default'	=> 'no',
				'type' 		=> 'onoff_checkbox',
				'free_version'		=> true,
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'woocommerce-dynamic-gallery' ),
				'unchecked_label' 	=> __( 'OFF', 'woocommerce-dynamic-gallery' ),
			),

			array(
            	'name' 		=> __( "VARIATION GALLERIES SUPER POWERS", 'woocommerce-dynamic-gallery' ),
                'type' 		=> 'heading',
                'desc'		=> '<img class="rwd_image_maps" src="'.WOO_DYNAMIC_GALLERY_IMAGES_URL.'/variation_galleries_activation_premium.png" usemap="#productCardsMap" style="width: auto; max-width: 100%;" border="0" />
<map name="productCardsMap" id="productCardsMap">
	<area shape="rect" coords="260,395,620,330" href="'.$this->pro_plugin_page_url.'" target="_blank" />
</map>',
				'alway_open'=> true,
                'id'		=> 'dgallery_icon_styles_premium_box',
                'is_box'	=> true,
           	),
			array(
				'name' => __( 'Variations Galleries Activation', 'woocommerce-dynamic-gallery' ),
				'type' => 'heading',
				'class'=> 'pro_feature_fields pro_feature_hidden',
				'desc' => __( 'Variations Galleries are auto applied to all Variable products upon first install. A Variations Gallery is added to each WooCommerce Product Variation. Variation Gallery can be activated / deactivated from the Dynamic Gallery menu on each product edit page.', 'woocommerce-dynamic-gallery' ),
				'id'     => 'wc_dgallery_variations_activation_box',
				'is_box' => true,
			),
			array(  
				'name' 		=> __( 'Variations Activation Default', 'woocommerce-dynamic-gallery' ),
				'desc' 		=> __( 'Changes to the default Variation Galleries activation does NOT apply to existing variable products. It will be applied to all variable products created after changing the default.', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'show_variation',
				'default'	=> 'no',
				'type' 		=> 'onoff_checkbox',
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'woocommerce-dynamic-gallery' ),
				'unchecked_label' 	=> __( 'OFF', 'woocommerce-dynamic-gallery' ),
			),
			array(  
				'name' 		=> __( 'Reset Activation To Default', 'woocommerce-dynamic-gallery' ),
				'desc' 		=> __( 'Switch ON and Save Changes will reset ALL existing and future variable products to the Variations Gallery Activation Default that you have set above.', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'reset_variation_activate',
				'default'	=> 'no',
				'type' 		=> 'onoff_checkbox',
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'woocommerce-dynamic-gallery' ),
				'unchecked_label' 	=> __( 'OFF', 'woocommerce-dynamic-gallery' ),
			),

			array(
				'name' => __( 'Image Zoom Function', 'woocommerce-dynamic-gallery' ),
				'type' => 'heading',
				'id'     => 'wc_dgallery_image_zoom_box',
				'is_box' => true,
			),
			array(
				'name' => __( 'Gallery Popup', 'woocommerce-dynamic-gallery' ),
				'desc' 		=> '',
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'popup_gallery',
				'default'	=> 'fb',
				'type' 		=> 'onoff_radio',
				'free_version'		=> true,
				'onoff_options' => array(
					array(
						'val' => 'fb',
						'text' => __( 'Fancybox', 'woocommerce-dynamic-gallery' ),
						'checked_label'	=> 'ON',
						'unchecked_label' => 'OFF',
					),
					array(
						'val' => 'colorbox',
						'text' => __( 'ColorBox', 'woocommerce-dynamic-gallery' ),
						'checked_label'	=> 'ON',
						'unchecked_label' => 'OFF',
					),
					array(
						'val' => 'deactivate',
						'text' => __( 'Deactivate', 'woocommerce-dynamic-gallery' ),
						'checked_label'	=> 'ON',
						'unchecked_label' => 'OFF',
					),
				),
			),

			array(
				'name' => __('Gallery Image Dimensions', 'woocommerce-dynamic-gallery' ),
				'type' => 'heading',
				'id'     => 'wc_dgallery_image_dimensions_box',
				'is_box' => true,
			),
			array(
                'type' 		=> 'heading',
				'desc'		=> '<table class="form-table"><tbody>
				<tr valign="top">
				<th class="titledesc" scope="row"><label>' . __( 'Gallery Images', 'woocommerce-dynamic-gallery' ) . '</label></th>
				<td class="forminp">' . sprintf( __( 'Set via the <a href="%s" target="_blank">Product Main Image Dimensions and Hard Crop</a> option from WooCommerce Settings', 'woocommerce-dynamic-gallery' ), $wc_display_settings_url ) . '</td>
				</tr>
				<tr valign="top">
				<th class="titledesc" scope="row"><label>' . __( 'Gallery Thumbnails', 'woocommerce-dynamic-gallery' ) . '</label></th>
				<td class="forminp">' . sprintf( __( 'Set via the <a href="%s" target="_blank">Product Thumbnails Dimensions and Hard Crop</a> option from WooCommerce Settings', 'woocommerce-dynamic-gallery' ), $wc_display_settings_url ) . '</td>
				</tr></tbody></table>',
           	),
        ));
	}

	public function include_script() {
		wp_enqueue_script( 'jquery-rwd-image-maps' );
	}
}

global $wc_dgallery_global_settings;
$wc_dgallery_global_settings = new WC_Dynamic_Gallery_Global_Settings();

/** 
 * wc_dgallery_global_settings_form()
 * Define the callback function to show subtab content
 */
function wc_dgallery_global_settings_form() {
	global $wc_dgallery_global_settings;
	$wc_dgallery_global_settings->settings_form();
}

?>
