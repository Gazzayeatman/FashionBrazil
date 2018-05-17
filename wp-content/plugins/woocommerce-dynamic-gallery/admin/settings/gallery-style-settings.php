<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
/*-----------------------------------------------------------------------------------
WC Dynamic Gallery Style Settings

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

class WC_Dynamic_Gallery_Style_Settings extends WC_Dynamic_Gallery_Admin_UI
{
	
	/**
	 * @var string
	 */
	private $parent_tab = 'gallery-style';
	
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
	public $form_key = 'wc_dgallery_style_settings';
	
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
				'success_message'	=> __( 'Dynamic Gallery Style successfully saved.', 'woocommerce-dynamic-gallery' ),
				'error_message'		=> __( 'Error: Dynamic Gallery Style can not save.', 'woocommerce-dynamic-gallery' ),
				'reset_message'		=> __( 'Dynamic Gallery Style successfully reseted.', 'woocommerce-dynamic-gallery' ),
			);
		
		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_end', array( $this, 'include_script' ) );
			
		add_action( $this->plugin_name . '_set_default_settings' , array( $this, 'set_default_settings' ) );
		
		add_action( $this->plugin_name . '-' . $this->form_key . '_after_settings_save' , array( $this, 'reset_default_settings' ) );

		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_init' , array( $this, 'after_save_settings' ) );
		//add_action( $this->plugin_name . '_get_all_settings' , array( $this, 'get_settings' ) );
		
		add_action('wp_ajax_woo_dynamic_gallery', array('WC_Gallery_Preview_Display','wc_dynamic_gallery_preview'));
		add_action('wp_ajax_nopriv_woo_dynamic_gallery', array('WC_Gallery_Preview_Display','wc_dynamic_gallery_preview'));
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
	/* after_save_settings()
	/* Process when clean on deletion option is un selected */
	/*-----------------------------------------------------------------------------------*/
	public function after_save_settings() {
		if ( isset( $_POST['bt_save_settings'] ) && isset( $_POST[WOO_DYNAMIC_GALLERY_PREFIX.'reset_thumbnails_activate'] ) ) {
			delete_option( WOO_DYNAMIC_GALLERY_PREFIX.'reset_thumbnails_activate' );
			WC_Dynamic_Gallery_Functions::reset_thumbnails_activate();			
		}
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
			'name'				=> 'gallery-style',
			'label'				=> __( 'Gallery Style', 'woocommerce-dynamic-gallery' ),
			'callback_function'	=> 'wc_dgallery_style_settings_form',
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
	
	// fix conflict with mandrill plugin
	public function remove_mandrill_notice() {
		remove_action( 'admin_notices', array( 'wpMandrill', 'adminNotices' ) );
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* init_form_fields() */
	/* Init all fields of this form */
	/*-----------------------------------------------------------------------------------*/
	public function init_form_fields() {
		add_action( 'admin_enqueue_scripts', array( 'WC_Gallery_Display_Class', 'backend_register_scripts' ) );

  		// Define settings			
     	$this->form_fields = array(
		
			array(
            	'name' 		=> '',
				'desc'		=> '<a href="'.  admin_url( 'admin-ajax.php', 'relative') .'?act=preview-dgallery" class="preview_gallery">' . __( 'Click here to preview gallery', 'woocommerce-dynamic-gallery' ) . '</a>',
                'type' 		=> 'heading',
           	),
			
			array(
				'name' => __('Gallery Dimensions', 'woocommerce-dynamic-gallery' ),
				'type' => 'heading',
				'id'     => 'wc_dgallery_dimensions_box',
				'is_box' => true,
			),
			
			array(  
				'name' 		=> __( 'Gallery Type', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX . 'width_type',
				'class'		=> 'gallery_width_type',
				'type' 		=> 'switcher_checkbox',
				'default'	=> '%',
				'free_version'		=> true,
				'checked_value'		=> '%',
				'unchecked_value' 	=> 'px',
				'checked_label'		=> __( 'Responsive', 'woocommerce-dynamic-gallery' ),
				'unchecked_label' 	=> __( 'Fixed Wide', 'woocommerce-dynamic-gallery' ),
			),
			
			array(
            	'class' 	=> 'gallery_width_type_percent',
                'type' 		=> 'heading',
           	),
			array(  
				'name' 		=> __( 'Gallery Width', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX . 'product_gallery_width_responsive',
				'desc'		=> '%' . '</span></div><div style="clear:both;"></div><div><span>' . __( 'of the width of your themes Product Page Product Gallery container', 'woocommerce-dynamic-gallery' ),
				'type' 		=> 'slider',
				'default'	=> 100,
				'min'		=> 20,
				'max'		=> 100,
				'increment'	=> 1,
				'free_version'		=> true,
			),

			array(
            	'class' 	=> 'gallery_width_type_fixed',
                'type' 		=> 'heading',
           	),
			array(  
				'name' 		=> __( 'Gallery Width', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX . 'product_gallery_width_fixed',
				'desc'		=> 'px. ' . __( 'Fixed maximum width in large screens. Width will scale to screen size in mobile browsers', 'woocommerce-dynamic-gallery' ),
				'type' 		=> 'text',
				'default'	=> 320,
				'free_version'		=> true,
				'css' 		=> 'width:40px;',
			),

			array(
                'type' 		=> 'heading',
           	),
			array(
				'name' 		=> __( 'Gallery Container Height', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX . 'gallery_height_type',
				'desc'		=> __( 'Dynamic and Gallery Container height will auto adjust to the scaled height of each image.', 'woocommerce-dynamic-gallery' ),
				'class'		=> 'gallery_height_type',
				'type' 		=> 'switcher_checkbox',
				'default'	=> 'fixed',
				'checked_value'		=> 'fixed',
				'unchecked_value' 	=> 'dynamic',
				'checked_label'		=> __( 'FIXED', 'woocommerce-dynamic-gallery' ),
				'unchecked_label' 	=> __( 'DYNAMIC', 'woocommerce-dynamic-gallery' ),
				'free_version'		=> true,
			),

			array(
            	'class' 	=> 'gallery_height_type_fixed',
                'type' 		=> 'heading',
           	),
			array(  
				'name' 		=> __( 'Gallery Height', 'woocommerce-dynamic-gallery' ),
				'desc'		=> 'px',
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'product_gallery_height',
				'type' 		=> 'text',
				'default'	=> 215,
				'free_version'		=> true,
				'css' 		=> 'width:40px;',
			),
			
			array(	
				'name' => __('Gallery Image Transition Effects', 'woocommerce-dynamic-gallery' ),
				'desc' => __( 'Note! These settings DO NOT apply to mobile and tablet when the + Mobile and Tablet Touch Swipe feature is switched on.', 'woocommerce-dynamic-gallery' ),
				'type' => 'heading',
				'id'     => 'wc_dgallery_effects_box',
				'is_box' => true,
			),
			array(  
				'name' => __( 'Auto Start', 'woocommerce-dynamic-gallery' ),
				'desc' 		=> '',
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'product_gallery_auto_start',
				'default'	=> 'true',
				'type' 		=> 'onoff_checkbox',
				'checked_value'		=> 'true',
				'unchecked_value'	=> 'false',
				'checked_label'		=> __( 'ON', 'woocommerce-dynamic-gallery' ),
				'unchecked_label' 	=> __( 'OFF', 'woocommerce-dynamic-gallery' ),
				'free_version'		=> true,
			),
			array(  
				'name' => __( 'Slide Transition Effect', 'woocommerce-dynamic-gallery' ),
				'desc' 		=> '',
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'product_gallery_effect',
				'css' 		=> 'width:120px;',
				'default'	=> 'slide-vert',
				'type' 		=> 'select',
				'options' => array( 
					'none'  			=> __( 'None', 'woocommerce-dynamic-gallery' ),
					'fade'				=> __( 'Fade', 'woocommerce-dynamic-gallery' ),
					'slide-hori'		=> __( 'Slide Hori', 'woocommerce-dynamic-gallery' ),
					'slide-vert'		=> __( 'Slide Vert', 'woocommerce-dynamic-gallery' ),
					'resize'			=> __( 'Resize', 'woocommerce-dynamic-gallery' ),
				),
				'free_version'		=> true,
			),
			array(  
				'name' => __( 'Time Between Transitions', 'woocommerce-dynamic-gallery' ),
				'desc' 		=> 'seconds',
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'product_gallery_speed',
				'type' 		=> 'slider',
				'default'	=> 4,
				'min'		=> 1,
				'max'		=> 10,
				'increment'	=> 1,
				'free_version'		=> true,
			),
			array(  
				'name' => __( 'Transition Effect Speed', 'woocommerce-dynamic-gallery' ),
				'desc' 		=> 'seconds',
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'product_gallery_animation_speed',
				'type' 		=> 'slider',
				'default'	=> 2,
				'min'		=> 1,
				'max'		=> 10,
				'increment'	=> 1,
				'free_version'		=> true,
			),
			
			array(  
				'name' 		=> __( 'Single Image Transition', 'woocommerce-dynamic-gallery' ),
				'desc' 		=> __( 'ON to auto deactivate image transition effect when only 1 image is loaded to gallery.', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'stop_scroll_1image',
				'default'	=> 'no',
				'type' 		=> 'onoff_checkbox',
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'woocommerce-dynamic-gallery' ),
				'unchecked_label' 	=> __( 'OFF', 'woocommerce-dynamic-gallery' ),
				'free_version'		=> true,
			),

			array(
				'name'   => __('Gallery Container', 'woocommerce-dynamic-gallery' ),
				'type'   => 'heading',
				'id'     => 'wc_dgallery_container_box',
				'is_box' => true,
			),
			array(
				'name' => __( 'Background Colour', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'main_bg_color',
				'type' 		=> 'bg_color',
				'free_version'		=> true,
				'default'	=> array( 'enable' => 1, 'color' => '#FFFFFF' )
			),
			array(
				'name' => __( 'Border', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'main_border',
				'type' 		=> 'border',
				'free_version'		=> true,
				'default'	=> array( 'width' => '1px', 'style' => 'solid', 'color' => '#666', 'corner' => 'square' , 'top_left_corner' => 3 , 'top_right_corner' => 3 , 'bottom_left_corner' => 3 , 'bottom_right_corner' => 3 ),
			),
			array(
				'name' => __( 'Border Shadow', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'main_shadow',
				'type' 		=> 'box_shadow',
				'free_version'		=> true,
				'default'	=> array( 'enable' => 0, 'h_shadow' => '0px' , 'v_shadow' => '0px', 'blur' => '0px' , 'spread' => '0px', 'color' => '#DBDBDB', 'inset' => '' )
			),
			array(
				'name' 		=> __( 'Border Margin', 'woocommerce-dynamic-gallery' ),
				'desc' 		=> __( 'Margin around the Container border.', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'main_margin',
				'type' 		=> 'array_textfields',
				'free_version'		=> true,
				'ids'		=> array(
	 								array(  'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'main_margin_top',
	 										'name' 		=> __( 'Top', 'woocommerce-dynamic-gallery' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
											'free_version'		=> true,
	 										'default'	=> '0' ),

	 								array(  'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'main_margin_bottom',
	 										'name' 		=> __( 'Bottom', 'woocommerce-dynamic-gallery' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
											'free_version'		=> true,
	 										'default'	=> '0' ),

									array(  'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'main_margin_left',
	 										'name' 		=> __( 'Left', 'woocommerce-dynamic-gallery' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
											'free_version'		=> true,
	 										'default'	=> '0' ),

									array(  'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'main_margin_right',
	 										'name' 		=> __( 'Right', 'woocommerce-dynamic-gallery' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
											'free_version'		=> true,
	 										'default'	=> '0' ),
	 							)
			),
			array(
				'name' 		=> __( 'Border Padding', 'woocommerce-dynamic-gallery' ),
				'desc' 		=> __( 'Padding between the main image and Container border.', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'main_padding',
				'type' 		=> 'array_textfields',
				'free_version'		=> true,
				'ids'		=> array(
	 								array(  'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'main_padding_top',
	 										'name' 		=> __( 'Top', 'woocommerce-dynamic-gallery' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
											'free_version'		=> true,
	 										'default'	=> '0' ),

	 								array(  'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'main_padding_bottom',
	 										'name' 		=> __( 'Bottom', 'woocommerce-dynamic-gallery' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
											'free_version'		=> true,
	 										'default'	=> '0' ),

									array(  'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'main_padding_left',
	 										'name' 		=> __( 'Left', 'woocommerce-dynamic-gallery' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
											'free_version'		=> true,
	 										'default'	=> '0' ),

									array(  'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'main_padding_right',
	 										'name' 		=> __( 'Right', 'woocommerce-dynamic-gallery' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
											'free_version'		=> true,
	 										'default'	=> '0' ),
	 							)
			),
			array(  
				'name' => __( 'Gallery Icon Display Type', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'icons_display_type',
				'default'	=> 'hover',
				'type' 		=> 'switcher_checkbox',
				'free_version'		=> true,
				'checked_value'		=> 'show',
				'unchecked_value'	=> 'hover',
				'checked_label'		=> __( 'SHOW', 'woocommerce-dynamic-gallery' ),
				'unchecked_label' 	=> __( 'ON HOVER', 'woocommerce-dynamic-gallery' ),
			),

			array(
				'name'   => __('Nav Bar Control Container', 'woocommerce-dynamic-gallery' ),
				'type'   => 'heading',
				'class'  => 'pro_feature_fields pro_feature_hidden',
				'id'     => 'wc_dgallery_navbar_control_box',
				'is_box' => true,
			),
			array(
				'name' 		=> __( 'Control Nav Bar', 'woocommerce-dynamic-gallery' ),
				'desc' 		=> __( "ON to show 'Zoom', Stop Slideshow, Start Slideshow", 'woocommerce-dynamic-gallery' ),
				'class'		=> 'gallery_nav_control',
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'product_gallery_nav',
				'default'	=> 'yes',
				'type' 		=> 'onoff_checkbox',
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'woocommerce-dynamic-gallery' ),
				'unchecked_label' 	=> __( 'OFF', 'woocommerce-dynamic-gallery' ),
			),

			array(
				'type' 		=> 'heading',
				'class'		=> 'nav_bar_container',
			),
			array(
				'name' 		=> __( 'Font', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'navbar_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '12px', 'line_height' => '1.4em', 'face' => 'Arial, sans-serif', 'style' => 'normal', 'color' => '#000000' )
			),
			array(
				'name' => __( 'Background Colour', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'navbar_bg_color',
				'type' 		=> 'bg_color',
				'default'	=> array( 'enable' => 1, 'color' => '#FFFFFF' )
			),
			array(
				'name' => __( 'Border', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'navbar_border',
				'type' 		=> 'border',
				'default'	=> array( 'width' => '1px', 'style' => 'solid', 'color' => '#666', 'corner' => 'square' , 'top_left_corner' => 3 , 'top_right_corner' => 3 , 'bottom_left_corner' => 3 , 'bottom_right_corner' => 3 ),
			),
			array(
				'name' => __( 'Border Shadow', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'navbar_shadow',
				'type' 		=> 'box_shadow',
				'default'	=> array( 'enable' => 0, 'h_shadow' => '0px' , 'v_shadow' => '0px', 'blur' => '0px' , 'spread' => '0px', 'color' => '#DBDBDB', 'inset' => '' )
			),
			array(
				'name' 		=> __( 'Border Margin', 'woocommerce-dynamic-gallery' ),
				'desc' 		=> __( 'Margin around the Nav Bar border.', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'navbar_margin',
				'type' 		=> 'array_textfields',
				'ids'		=> array(
	 								array(  'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'navbar_margin_top',
	 										'name' 		=> __( 'Top', 'woocommerce-dynamic-gallery' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '0' ),

	 								array(  'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'navbar_margin_bottom',
	 										'name' 		=> __( 'Bottom', 'woocommerce-dynamic-gallery' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '0' ),

									array(  'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'navbar_margin_left',
	 										'name' 		=> __( 'Left', 'woocommerce-dynamic-gallery' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '0' ),

									array(  'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'navbar_margin_right',
	 										'name' 		=> __( 'Right', 'woocommerce-dynamic-gallery' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '0' ),
	 							)
			),
			array(
				'name' 		=> __( 'Border Padding', 'woocommerce-dynamic-gallery' ),
				'desc' 		=> __( 'Padding between the the Text and Nav Bar border.', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'navbar_padding',
				'type' 		=> 'array_textfields',
				'ids'		=> array(
	 								array(  'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'navbar_padding_top',
	 										'name' 		=> __( 'Top', 'woocommerce-dynamic-gallery' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '5' ),

	 								array(  'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'navbar_padding_bottom',
	 										'name' 		=> __( 'Bottom', 'woocommerce-dynamic-gallery' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '5' ),

									array(  'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'navbar_padding_left',
	 										'name' 		=> __( 'Left', 'woocommerce-dynamic-gallery' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '5' ),

									array(  'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'navbar_padding_right',
	 										'name' 		=> __( 'Right', 'woocommerce-dynamic-gallery' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '5' ),
	 							)
			),
			array(
				'name' => __( 'Vertical Separator', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'navbar_separator',
				'type' 		=> 'border_styles',
				'default'	=> array( 'width' => '1px', 'style' => 'solid', 'color' => '#666' ),
			),

			array(
				'name'   => __('Caption Text Container', 'woocommerce-dynamic-gallery' ),
				'type'   => 'heading',
				'class'  => 'pro_feature_fields pro_feature_hidden',
				'id'     => 'wc_dgallery_caption_text_box',
				'is_box' => true,
			),
			array(
				'name' 		=> __( 'Font', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'caption_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '12px', 'line_height' => '1.4em', 'face' => 'Arial, sans-serif', 'style' => 'normal', 'color' => '#FFFFFF' )
			),
			array(
				'name' => __( 'Background Colour', 'woocommerce-dynamic-gallery' ),
				'desc' 		=> __( 'Caption text background colour.', 'woocommerce-dynamic-gallery' ),
				'class'		=> 'wc_dgallery_caption_bg_color',
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'caption_bg_color',
				'type' 		=> 'bg_color',
				'default'	=> array( 'enable' => 1, 'color' => '#000000' )
			),

			array(
				'type'   => 'heading',
				'class'     => 'wc_dgallery_caption_bg_color_container',
			),
			array(
				'name'      => __( 'Background Transparency', 'woocommerce-dynamic-gallery' ),
				'desc'      => '%. ' . __( 'Scale - 0 = 100% transparent - 100 = 100% Solid Colour.', 'woocommerce-dynamic-gallery' ),
				'id'        => WOO_DYNAMIC_GALLERY_PREFIX.'caption_bg_transparent',
				'type'      => 'slider',
				'default'   => 50,
				'min'       => 0,
				'max'       => 100,
				'increment' => 10,
			),

			array(
				'name'   => __('Lazy Load Scroll Bar Container', 'woocommerce-dynamic-gallery' ),
				'type'   => 'heading',
				'class'  => 'pro_feature_fields pro_feature_hidden',
				'id'     => 'wc_dgallery_lazyload_scroll_bar_box',
				'is_box' => true,
			),
			array(
				'name' 		=> __( 'Scroll Bar', 'woocommerce-dynamic-gallery' ),
				'class'		=> 'lazy_load_control',
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'lazy_load_scroll',
				'default'	=> 'yes',
				'type' 		=> 'onoff_checkbox',
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'woocommerce-dynamic-gallery' ),
				'unchecked_label' 	=> __( 'OFF', 'woocommerce-dynamic-gallery' ),
			),

			array(
				'type' 		=> 'heading',
				'class'		=> 'lazy_load_container',
			),
			array(
				'name' => __( 'Scroll Bar Colour', 'woocommerce-dynamic-gallery' ),
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'transition_scroll_bar',
				'type' 		=> 'color',
				'default'	=> '#000000'
			),

			array(
				'name'   => __('Product Variations Galleries', 'woocommerce-dynamic-gallery' ),
				'type'   => 'heading',
				'class'  => 'pro_feature_fields pro_feature_hidden',
				'id'     => 'wc_dgallery_variations_box',
				'is_box' => true,
			),
			array(  
				'name' => __( 'Gallery Load Effect', 'woocommerce-dynamic-gallery' ),
				'desc' 		=> '',
				'class'		=> 'variation_gallery_effect',
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'variation_gallery_effect',
				'default'	=> 'fade',
				'type' 		=> 'switcher_checkbox',
				'checked_value'		=> 'fade',
				'unchecked_value'	=> 'none',
				'checked_label'		=> __( 'FADE', 'woocommerce-dynamic-gallery' ),
				'unchecked_label' 	=> __( 'DEFAULT', 'woocommerce-dynamic-gallery' ),
			),
			array(	
				'type' 		=> 'heading',
				'class'		=> 'variation_load_effect_timing',
			),
			array(  
				'name' 		=> __( 'Load Effect Timing', 'woocommerce-dynamic-gallery' ),
				'desc' 		=> 'seconds',
				'id' 		=> WOO_DYNAMIC_GALLERY_PREFIX.'variation_gallery_effect_speed',
				'type' 		=> 'slider',
				'default'	=> 2,
				'min'		=> 1,
				'max'		=> 10,
				'increment'	=> 1,
			),
		
        );

		include_once( $this->admin_plugin_dir() . '/settings/thumbnails-settings.php' );
		global $wc_dgallery_thumbnails_settings;
		$this->form_fields = array_merge( $this->form_fields, $wc_dgallery_thumbnails_settings->form_fields );

		$this->form_fields = array_merge( $this->form_fields, array(
			array(
            	'name' 		=> __( "GALLERY STYLES SUPER POWERS", 'woocommerce-dynamic-gallery' ),
                'type' 		=> 'heading',
                'desc'		=> '<img class="rwd_image_maps" src="'.WOO_DYNAMIC_GALLERY_IMAGES_URL.'/gallery_styles_tab.png" usemap="#productCardsMap" style="width: auto; max-width: 100%;" border="0" />
<map name="productCardsMap" id="productCardsMap">
	<area shape="rect" coords="325,270,925,205" href="'.$this->pro_plugin_page_url.'" target="_blank" />
</map>',
				'alway_open'=> true,
                'id'		=> 'dgallery_styles_premium_box',
                'is_box'	=> true,
           	),
		) );

		$this->form_fields = apply_filters( $this->form_key . '_settings_fields', $this->form_fields );

	}
	
	public function include_script() {
		wp_enqueue_script( 'jquery-rwd-image-maps' );
		add_action( 'admin_footer', array($this, 'wc_dynamic_gallery_add_script'), 10 );
	?>
<script>
(function($) {
$(document).ready(function() {
	if ( $("input.gallery_width_type:checked").val() == '%') {
		$(".gallery_width_type_percent").show();
		$(".gallery_width_type_fixed").hide();
	} else {
		$(".gallery_width_type_percent").hide();
		$(".gallery_width_type_fixed").show();
	}
	if ( $("input.gallery_nav_control:checked").val() != 'yes') {
		$('.nav_bar_container').css( {'visibility': 'hidden', 'height' : '0px', 'overflow' : 'hidden', 'margin-bottom' : '0px' } );
	}
	if ( $("input.lazy_load_control:checked").val() != 'yes') {
		$(".lazy_load_container").hide();
	}
	if ( $("input.variation_gallery_effect:checked").val() != 'fade') {
		$(".variation_load_effect_timing").hide();
	}

	if ( $("input.wc_dgallery_thumb_show_type:checked").val() != 'slider') {
		$('.wc_dgallery_thumbnail_slider_container').css( {'visibility': 'hidden', 'height' : '0px', 'overflow' : 'hidden', 'margin-bottom' : '0px' } );
	}

	
	$(document).on( "a3rev-ui-onoff_checkbox-switch", '.gallery_width_type', function( event, value, status ) {
		if ( status == 'true' ) {
			$(".gallery_width_type_percent").slideDown();
			$(".gallery_width_type_fixed").slideUp();
			$(".gallery_height_type_fixed").slideUp();
		} else {
			$(".gallery_width_type_percent").slideUp();
			$(".gallery_width_type_fixed").slideDown();
			if ( $("input.gallery_height_type:checked").val() == 'fixed') {
				$(".gallery_height_type_fixed").slideDown();
			}
		}
	});
	
	$(document).on( "a3rev-ui-onoff_checkbox-switch", '.gallery_nav_control', function( event, value, status ) {
		$('.nav_bar_container').attr('style','display:none;');
		if ( status == 'true' ) {
			$(".nav_bar_container").slideDown();
		} else {
			$(".nav_bar_container").slideUp();
		}
	});
	
	$(document).on( "a3rev-ui-onoff_checkbox-switch", '.lazy_load_control', function( event, value, status ) {
		if ( status == 'true' ) {
			$(".lazy_load_container").slideDown();
		} else {
			$(".lazy_load_container").slideUp();
		}
	});
	
	$(document).on( "a3rev-ui-onoff_checkbox-switch", '.variation_gallery_effect', function( event, value, status ) {
		if ( status == 'true' ) {
			$(".variation_load_effect_timing").slideDown();
		} else {
			$(".variation_load_effect_timing").slideUp();
		}
	});

	if ( $("input.enable_gallery_thumb:checked").val() != 'yes') {
		$(".gallery_thumb_container").css( {'visibility': 'hidden', 'height' : '0px', 'overflow' : 'hidden', 'margin-bottom' : '0px' } );
	}

	$(document).on( "a3rev-ui-onoff_checkbox-switch", '.enable_gallery_thumb', function( event, value, status ) {
		$('.gallery_thumb_container').attr('style','display:none;');
		if ( status == 'true' ) {
			$(".gallery_thumb_container").slideDown();
		} else {
			$(".gallery_thumb_container").slideUp();
		}
	});

	if ( $("input.gallery_height_type:checked").val() == 'fixed') {
		if ( $("input.gallery_width_type:checked").val() != '%') {
			$(".gallery_height_type_fixed").show();
		} else {
			$(".gallery_height_type_fixed").hide();
		}
	} else {
		$(".gallery_height_type_fixed").hide();
	}

	$(document).on( "a3rev-ui-onoff_checkbox-switch", '.gallery_height_type', function( event, value, status ) {
		if ( status == 'true' ) {
			if ( $("input.gallery_width_type:checked").val() != '%') {
				$(".gallery_height_type_fixed").slideDown();
			} else {
				$(".gallery_height_type_fixed").slideUp();
			}
		} else {
			$(".gallery_height_type_fixed").slideUp();
		}
	});


	$(document).on( "a3rev-ui-onoff_checkbox-switch", '.wc_dgallery_thumb_show_type', function( event, value, status ) {
		$('.wc_dgallery_thumbnail_slider_container').attr('style','display:none;');
		if ( status == 'true' ) {
			$(".wc_dgallery_thumbnail_slider_container").slideDown();
		} else {
			$(".wc_dgallery_thumbnail_slider_container").slideUp();
		}
	});

});
})(jQuery);
</script>
    <?php	
	}

	public function wc_dynamic_gallery_add_script(){
		wp_enqueue_style( 'a3-dgallery-style' );

		$popup_gallery = get_option( WOO_DYNAMIC_GALLERY_PREFIX.'popup_gallery' );
		if ( 'fb' == $popup_gallery ) {
			wp_enqueue_style( 'woocommerce_fancybox_styles' );
			wp_enqueue_script( 'fancybox' );
		} elseif ( 'colorbox' == $popup_gallery  ) {
			wp_enqueue_style( 'a3_colorbox_style' );
			wp_enqueue_script( 'colorbox_script' );
		}

		wp_enqueue_script( 'preview-gallery-script' );

		wp_enqueue_script( 'a3-dgallery-script' );
	}
}

global $wc_dgallery_style_settings;
$wc_dgallery_style_settings = new WC_Dynamic_Gallery_Style_Settings();

/** 
 * wc_dgallery_style_settings_form()
 * Define the callback function to show subtab content
 */
function wc_dgallery_style_settings_form() {
	global $wc_dgallery_style_settings;
	$wc_dgallery_style_settings->settings_form();
}

?>
