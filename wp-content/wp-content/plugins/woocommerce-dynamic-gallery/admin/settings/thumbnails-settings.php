<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
/*-----------------------------------------------------------------------------------
WC Dynamic Gallery Style Settings

-----------------------------------------------------------------------------------*/

class WC_Dynamic_Gallery_Thumbnails_Settings
{
	/**
	 * @var array
	 */
	public $form_fields = array();

	/*-----------------------------------------------------------------------------------*/
	/* __construct() */
	/* Settings Constructor */
	/*-----------------------------------------------------------------------------------*/
	public function __construct() {
		$this->init_form_fields();
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* init_form_fields() */
	/* Init all fields of this form */
	/*-----------------------------------------------------------------------------------*/
	public function init_form_fields() {
				
  		// Define settings			
     	$this->form_fields = array(
		
			array(
            	'name' 		=> __('Image Thumbnails', 'woo_dgallery'),
                'type' 		=> 'heading',
                'id'     => 'wc_dgallery_thumbnails_box',
				'is_box' => true,
           	),
			array(  
				'name' 		=> __( 'Gallery Thumbnails', 'woo_dgallery' ),
				'desc'		=> __( 'Note! Gallery thumbnails can be turned ON or OFF for each product from the WooCommerce Product data Dynamic Gallery menu', 'woo_dgallery' ),
				'class'		=> 'enable_gallery_thumb',
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'enable_gallery_thumb',
				'default'			=> 'yes',
				'type' 				=> 'onoff_checkbox',
				'free_version'		=> true,
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'woo_dgallery' ),
				'unchecked_label' 	=> __( 'OFF', 'woo_dgallery' ),
			),
			array(  
				'name' 		=> __( 'Reset Activation To Default', 'woo_dgallery' ),
				'desc' 		=> __( "Switch ON and Save Changes will reset ALL existing and future products to the 'Gallery Thumbnail' Default that you have set above.", 'woo_dgallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'reset_thumbnails_activate',
				'default'	=> 'no',
				'type' 		=> 'onoff_checkbox',
				'free_version'		=> true,
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'woo_dgallery' ),
				'unchecked_label' 	=> __( 'OFF', 'woo_dgallery' ),
			),
			
			array(
                'type' 		=> 'heading',
				'class'		=> 'gallery_thumb_container',
           	),
			array(  
				'name' 		=> __( 'Single Image Thumbnail', 'woo_dgallery' ),
				'desc' 		=> __( "ON to hide thumbnail when only 1 image is loaded to gallery.", 'woo_dgallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'hide_thumb_1image',
				'default'			=> 'no',
				'type' 				=> 'onoff_checkbox',
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'woo_dgallery' ),
				'unchecked_label' 	=> __( 'OFF', 'woo_dgallery' ),
				'free_version'		=> true,
			),
			array(
				'name' 		=> __( 'Thumbnail Display', 'woo_dgallery' ),
				'desc'		=> __( 'Static displays all Gallery thumbnails in columns', 'woo_dgallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'thumb_show_type',
				'class'		=> 'wc_dgallery_thumb_show_type',
				'default'			=> 'slider',
				'type' 				=> 'switcher_checkbox',
				'checked_value'		=> 'slider',
				'unchecked_value'	=> 'static',
				'checked_label'		=> __( 'Slider', 'woo_dgallery' ),
				'unchecked_label' 	=> __( 'Static', 'woo_dgallery' ),
				'free_version'		=> true,
			),
			array(
				'class'		=> 'gallery_thumb_container',
                'type' 		=> 'heading',
				'desc'		=> '<table class="form-table"><tbody>
				<tr valign="top">
				<th class="titledesc" scope="row"><label>' . __( 'Thumbnail Dimensions', 'woo_dgallery' ) . '</label></th>
				<td class="forminp">' . sprintf( __( 'The plugin is using <a href="%s" target="_blank">Product Thumbnails Dimension</a> from WooCommerce Settings', 'woo_dgallery' ), admin_url( 'admin.php?page=wc-settings&tab=products&section=display' ) ) . '</td>
				</tr></tbody></table>',
           	),
			array(
				'name' 		=> __( 'Thumbnail Spacing', 'woo_dgallery' ),
				'desc' 		=> 'px',
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'thumb_spacing',
				'type' 		=> 'text',
				'css' 		=> 'width:40px;',
				'default'	=> '10',
				'free_version'		=> true,
			),
			array(
				'name' => __( 'Thumbnail Columns', 'woo_dgallery' ),
				'desc' 		=> __( 'columns', 'woo_dgallery' ) . '</span></div></div>
				<div style="clear: both;"></div>
				<div><div>' . __( 'Applies to Thumbnail Slider (number visible in Slider) and Static Thumbnail Display. Default of WooCommerce is 3 column', 'woo_dgallery' ) . '<span>',
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'thumb_columns',
				'type' 		=> 'slider',
				'default'	=> 3,
				'min'		=> 2,
				'max'		=> 8,
				'increment'	=> 1,
				'free_version'		=> true,
			),
			array(  
				'name' => __( 'Thumbnail Border Colour', 'woo_dgallery' ),
				'desc' 		=> __( 'Type in the word <code>transparent</code> for no colour', 'woo_dgallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'thumb_border_color',
				'type' 		=> 'color',
				'default'	=> 'transparent',
				'free_version'		=> true,
			),
			array(  
				'name' => __( 'Current Thumbail Border Colour', 'woo_dgallery' ),
				'desc' 		=> __( 'Type in the word <code>transparent</code> for no colour', 'woo_dgallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'thumb_current_border_color',
				'type' 		=> 'color',
				'default'	=> '#96588a',
				'free_version'		=> true,
			),

			array(
            	'name' 		=> __('Thumbnail Slider Container', 'woo_dgallery'),
                'type' 		=> 'heading',
                'id'     => 'wc_dgallery_thumbnail_slider_box',
                'class'  => 'wc_dgallery_thumbnail_slider_container',
				'is_box' => true,
           	),
           	array(
				'name' 		=> __( 'Background Colour', 'woo_dgallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'thumb_slider_background',
				'type' 		=> 'bg_color',
				'free_version'		=> true,
				'default'	=> array( 'enable' => 0, 'color' => '#FFF' )
			),
			array(
				'name' 		=> __( 'Border', 'woo_dgallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'thumb_slider_border',
				'type' 		=> 'border',
				'free_version'		=> true,
				'default'	=> array( 'width' => '0px', 'style' => 'solid', 'color' => '#ddd', 'corner' => 'square' , 'top_left_corner' => 3 , 'top_right_corner' => 3 , 'bottom_left_corner' => 3 , 'bottom_right_corner' => 3 ),
			),
			array(
				'name' => __( 'Border Shadow Effect', 'woo_dgallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'thumb_slider_shadow',
				'type' 		=> 'box_shadow',
				'free_version'		=> true,
				'default'	=> array( 'enable' => 0, 'h_shadow' => '0px' , 'v_shadow' => '1px', 'blur' => '0px' , 'spread' => '0px', 'color' => '#555555', 'inset' => 'inset' )
			),
        );
	}
}

global $wc_dgallery_thumbnails_settings;
$wc_dgallery_thumbnails_settings = new WC_Dynamic_Gallery_Thumbnails_Settings();

?>
