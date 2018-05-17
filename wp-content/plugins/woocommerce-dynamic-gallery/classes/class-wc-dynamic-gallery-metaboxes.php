<?php
/**
 * WooCommerce Dynamic Gallery Meta_Boxes Class
 *
 * Class Function into woocommerce plugin
 *
 * Table Of Contents
 *
 * woocommerce_meta_boxes_image()
 * woocommerce_product_image_box()
 * save_actived_d_gallery()
 */
class WC_Dynamic_Gallery_Meta_Boxes
{

	public function __construct() {
		$current_db_version = get_option( 'woocommerce_db_version', null );

		if ( version_compare( $current_db_version, '2.3.0', '>=' ) ) {
			add_filter( 'woocommerce_product_data_tabs', array( $this, 'dynamic_gallery_tab' ), 100 );
			add_action( 'woocommerce_product_data_panels', array( $this, 'dynamic_gallery_panel' ), 100 );
		} else {
			add_action( 'add_meta_boxes', array( $this, 'woocommerce_meta_boxes_image' ), 9 );
		}

		add_action( 'save_post', array( $this, 'save_actived_d_gallery' ), 11 );
	}

	public function dynamic_gallery_tab( $product_data_tabs ) {
		$product_data_tabs['dgallery'] = array(
			'label'  => __( 'Dynamic Gallery', 'woocommerce-dynamic-gallery' ),
			'target' => 'wc-dgallery-product-images',
			'class'  => array( 'product_dgallery_tab' ),
		);

		return $product_data_tabs;
	}

	public function dynamic_gallery_panel() {
		global $post;

		$global_wc_dgallery_activate  = get_option( WOO_DYNAMIC_GALLERY_PREFIX.'activate' );
		$actived_d_gallery            = get_post_meta( $post->ID, '_actived_d_gallery',true );

		if ($actived_d_gallery == '' && $global_wc_dgallery_activate != 'no') {
			$actived_d_gallery = 1;
		}


		$default_image_source = get_option( WOO_DYNAMIC_GALLERY_PREFIX.'image_source' );
		$image_source         = get_post_meta($post->ID, '_wc_dgallery_image_source',true);
		if ( empty( $image_source ) ) {
			$image_source = $default_image_source;
		}
		if ( empty( $image_source ) ) {
			$image_source = 'wc_gallery' ;
		}

		$default_enable_gallery_thumb = get_option( WOO_DYNAMIC_GALLERY_PREFIX.'enable_gallery_thumb' );
		$enable_gallery_thumb         = get_post_meta($post->ID, '_wc_dgallery_enable_gallery_thumb',true);
		if ( $enable_gallery_thumb == '' ) {
			$enable_gallery_thumb = $default_enable_gallery_thumb;
		}
		if ( $enable_gallery_thumb == 1 || $enable_gallery_thumb == 'yes' ) {
			$enable_gallery_thumb = 1 ;
		}

		$default_auto_feature_image = get_option( WOO_DYNAMIC_GALLERY_PREFIX.'auto_feature_image' );
		$auto_feature_image         = get_post_meta($post->ID, '_wc_dgallery_auto_feature_image',true);
		if ( $auto_feature_image == '' ) {
			$auto_feature_image = $default_auto_feature_image;
		}
		if ( $auto_feature_image == 1 || $auto_feature_image == 'yes' ) {
			$auto_feature_image = 1 ;
		}

		wp_enqueue_style( 'a3-dynamic-metabox-admin-style' );
		if ( is_rtl() ) {
			wp_enqueue_style( 'a3-dynamic-metabox-admin-style-rtl' );
		}
		wp_enqueue_script( 'a3-dynamic-metabox-admin-script' );
		wp_localize_script( 'a3-dynamic-metabox-admin-script', 'a3_dgallery_metabox', array( 'ajax_url' => admin_url( 'admin-ajax.php', 'relative' ) ) );
		wp_enqueue_media();

	?>
		<div id="wc-dgallery-product-images" class="panel woocommerce_options_panel">

	        <script type="text/javascript">
			jQuery(document).ready(function() {
				var dynamic_gallery_title_text    = '<?php echo __( 'Dynamic Product Gallery', 'woocommerce-dynamic-gallery' ); ?>';
				var dynamic_gallery_link_add_text = '<?php echo __( 'Add dynamic gallery images', 'woocommerce-dynamic-gallery' ); ?>';
				var woo_gallery_title             = jQuery('#woocommerce-product-images').find('.hndle span');
				var woo_gallery_link_add          = jQuery('#woocommerce-product-images').find('.add_product_images a');
				var woo_gallery_title_text        = woo_gallery_title.html();
				var woo_gallery_link_add_text     = woo_gallery_link_add.html();

				if( jQuery('input.actived_d_gallery').is(":checked") ) {
					woo_gallery_title.html(dynamic_gallery_title_text);
					woo_gallery_link_add.html(dynamic_gallery_link_add_text);
				}

				jQuery('input.actived_d_gallery').change(function() {
					if( jQuery(this).is(":checked") ) {
						woo_gallery_title.html(dynamic_gallery_title_text);
						woo_gallery_link_add.html(dynamic_gallery_link_add_text);
					} else {
						woo_gallery_title.html(woo_gallery_title_text);
						woo_gallery_link_add.html(woo_gallery_link_add_text);
					}
				});
			});
			</script>

			<div class="options_group">

				<p class="form-field">
					<label for="actived_d_gallery"><?php _e( 'a3 Dynamic Gallery', 'woocommerce-dynamic-gallery' ); ?></label>
					<input type="checkbox" <?php checked( 1, $actived_d_gallery, true ); ?> value="1" id="actived_d_gallery" name="actived_d_gallery" class="checkbox actived_d_gallery" />
					<span class="description"><?php _e( 'Activate a3 Dynamic Image Gallery', 'woocommerce-dynamic-gallery' ); ?></span>
					<br />
					<?php echo __( 'Use the Product Gallery Meta box in the right sidebar of this product edit page to Add, Move or Delete images.', 'woocommerce-dynamic-gallery' ); ?>
					<br />
					<?php echo __( '<strong>Important!</strong> If you do not see the Product Gallery meta box in the sidebar go to the Screen Options Tab at the top right corner of this page and check the [ ] Product Gallery box so it will show.', 'woocommerce-dynamic-gallery' ); ?>
					<br />
					<?php echo __( '<strong>Tip!</strong> When a3 Dynamic Gallery is activated for this product the meta box name auto changes from Product Gallery to Dynamic Product Gallery.', 'woocommerce-dynamic-gallery' ); ?>
				</p>
			</div>

			<div id="main_dgallery_panel" class="options_group a3_dgallery_is_variable_product" style="<?php if ( 1 != $actived_d_gallery ) { echo 'display: none;'; } ?>">

				<p class="form-field">
					<label for="wc_dgallery_image_source"><?php _e( 'Gallery Image Source', 'woocommerce-dynamic-gallery' ); ?></label>
					<input type="radio" <?php checked( 'attached', $image_source, true ); ?> value="attached" name="wc_dgallery_image_source" class="radio" />
					<span class="description"><?php _e( 'All images attached to this product', 'woocommerce-dynamic-gallery' ); ?></span><br>
					<input type="radio" <?php checked( 'wc_gallery', $image_source, true ); ?> value="wc_gallery" name="wc_dgallery_image_source" class="radio" />
					<span class="description"><?php _e( 'Only images uploaded to the WC Product Image gallery', 'woocommerce-dynamic-gallery' ); ?></span>
				</p>

				<p class="form-field">
					<label for="wc_dgallery_auto_feature_image"><?php _e( 'Include Feature Image', 'woocommerce-dynamic-gallery' ); ?></label>
					<input type="checkbox" <?php checked( 1, $auto_feature_image, true ); ?> value="1" id="wc_dgallery_auto_feature_image" name="wc_dgallery_auto_feature_image" class="checkbox wc_dgallery_auto_feature_image" />
					<span class="description"><?php _e( 'Check and Product Image (Feature Image) will show as the first image in the Gallery.', 'woocommerce-dynamic-gallery' ); ?></span>
					<br />
					<?php echo __( 'Unchecked and Product Image is not used in the gallery unless it is uploaded to the Dynamic Product Gallery', 'woocommerce-dynamic-gallery' ); ?>
				</p>

				<p class="form-field">
					<label for="wc_dgallery_enable_gallery_thumb"><?php _e( 'Gallery Thumbnails', 'woocommerce-dynamic-gallery' ); ?></label>
					<input type="checkbox" <?php checked( 1, $enable_gallery_thumb, true ); ?> value="1" id="wc_dgallery_enable_gallery_thumb" name="wc_dgallery_enable_gallery_thumb" class="checkbox wc_dgallery_enable_gallery_thumb" />
					<span class="description"><?php _e( 'Check to show Thumbnails with this Gallery', 'woocommerce-dynamic-gallery' ); ?></span>
				</p>

			</div>

			<?php
			// Add an nonce field so we can check for it later.
			wp_nonce_field( 'a3_dynamic_metabox_action', 'a3_dynamic_metabox_nonce_field' );
			?>
			<div style="clear: both;"></div>

		</div>
	<?php
	}

	public function woocommerce_meta_boxes_image() {
		add_meta_box( 'wc-dgallery-product-images', __( 'A3 Dynamic Image Gallery', 'woocommerce-dynamic-gallery' ), array( $this, 'woocommerce_product_image_box' ), 'product', 'normal', 'high' );
	}

	public function woocommerce_product_image_box() {
		global $post;

		$global_wc_dgallery_activate  = get_option( WOO_DYNAMIC_GALLERY_PREFIX.'activate' );
		$actived_d_gallery            = get_post_meta( $post->ID, '_actived_d_gallery',true );

		if ($actived_d_gallery == '' && $global_wc_dgallery_activate != 'no') {
			$actived_d_gallery = 1;
		}

		$default_enable_gallery_thumb = get_option( WOO_DYNAMIC_GALLERY_PREFIX.'enable_gallery_thumb' );
		$enable_gallery_thumb         = get_post_meta($post->ID, '_wc_dgallery_enable_gallery_thumb',true);
		if ( $enable_gallery_thumb == '' ) {
			$enable_gallery_thumb = $default_enable_gallery_thumb;
		}
		if ( $enable_gallery_thumb == 1 || $enable_gallery_thumb == 'yes' ) {
			$enable_gallery_thumb = 1 ;
		}

		$default_auto_feature_image = get_option( WOO_DYNAMIC_GALLERY_PREFIX.'auto_feature_image' );
		$auto_feature_image         = get_post_meta($post->ID, '_wc_dgallery_auto_feature_image',true);
		if ( $auto_feature_image == '' ) {
			$auto_feature_image = $default_auto_feature_image;
		}
		if ( $auto_feature_image == 1 || $auto_feature_image == 'yes' ) {
			$auto_feature_image = 1 ;
		}

		wp_enqueue_style( 'a3-dynamic-metabox-admin-style' );
		if ( is_rtl() ) {
			wp_enqueue_style( 'a3-dynamic-metabox-admin-style-rtl' );
		}
		wp_enqueue_script( 'a3-dynamic-metabox-admin-script' );
		wp_localize_script( 'a3-dynamic-metabox-admin-script', 'a3_dgallery_metabox', array( 'ajax_url' => admin_url( 'admin-ajax.php', 'relative' ) ) );
		wp_enqueue_media();

		ob_start();

		?>
        <div class="a3rev_panel_container a3-metabox-panel-wrap a3-dynamic-metabox-panel-wrap" style="padding-left: 0px;">

			<div style="margin-bottom:10px;">
        		<label class="a3_actived_d_gallery" style="margin-right: 50px;">
        			<input type="checkbox" <?php checked( 1, $actived_d_gallery, true ); ?> value="1" name="actived_d_gallery" class="actived_d_gallery" /> 
        			<?php echo __( 'A3 Dynamic Image Gallery activated', 'woocommerce-dynamic-gallery' ); ?>
        		</label>
        		<br />
				<?php echo __( 'Use the Product Gallery Meta box in the right sidebar of this product edit page to Add, Move or Delete images.', 'woocommerce-dynamic-gallery' ); ?>
				<br />
				<?php echo __( '<strong>Important!</strong> If you do not see the Product Gallery meta box in the sidebar go to the Screen Options Tab at the top right corner of this page and check the [ ] Product Gallery box so it will show.', 'woocommerce-dynamic-gallery' ); ?>
				<br />
				<?php echo __( '<strong>Tip!</strong> When a3 Dynamic Gallery is activated for this product the meta box name auto changes from Product Gallery to Dynamic Product Gallery.', 'woocommerce-dynamic-gallery' ); ?>
        	</div>

			<div id="main_dgallery_panel" class="dgallery_images_container a3-metabox-panel a3-metabox-options-panel" style="<?php if ( 1 != $actived_d_gallery ) { echo 'display: none;'; } ?>">

				<p class="a3_dgallery_is_variable_product">
					<label class="a3_wc_dgallery_enable_gallery_thumb">
						<input type="checkbox" <?php checked( 1, $enable_gallery_thumb, true ); ?> value="1" name="wc_dgallery_enable_gallery_thumb" class="wc_dgallery_enable_gallery_thumb" />
						<?php echo __( 'Check to show Thumbnails with this Gallery', 'woocommerce-dynamic-gallery' ); ?>
					</label>
				</p>

				<p class="a3_dgallery_is_variable_product">
					<label class="a3_wc_dgallery_auto_feature_image">
						<input type="checkbox" <?php checked( 1, $auto_feature_image, true ); ?> value="1" name="wc_dgallery_auto_feature_image" class="wc_dgallery_auto_feature_image" />
						<?php echo __( 'Check to auto include Feature Image to Gallery on frontend', 'woocommerce-dynamic-gallery' ); ?>
					</label>
				</p>

			</div>

			<?php
			// Add an nonce field so we can check for it later.
			wp_nonce_field( 'a3_dynamic_metabox_action', 'a3_dynamic_metabox_nonce_field' );
			?>
			<div style="clear: both;"></div>

		</div>
		<div style="clear: both;"></div>

        <script type="text/javascript">
		jQuery(document).ready(function() {
			var dynamic_gallery_title_text    = '<?php echo __( 'Dynamic Product Gallery', 'woocommerce-dynamic-gallery' ); ?>';
			var dynamic_gallery_link_add_text = '<?php echo __( 'Add dynamic gallery images', 'woocommerce-dynamic-gallery' ); ?>';
			var woo_gallery_title             = jQuery('#woocommerce-product-images').find('.hndle span');
			var woo_gallery_link_add          = jQuery('#woocommerce-product-images').find('.add_product_images a');
			var woo_gallery_title_text        = woo_gallery_title.html();
			var woo_gallery_link_add_text     = woo_gallery_link_add.html();

			if( jQuery('input.actived_d_gallery').is(":checked") ) {
				woo_gallery_title.html(dynamic_gallery_title_text);
				woo_gallery_link_add.html(dynamic_gallery_link_add_text);
			}

			jQuery('input.actived_d_gallery').change(function() {
				if( jQuery(this).is(":checked") ) {
					woo_gallery_title.html(dynamic_gallery_title_text);
					woo_gallery_link_add.html(dynamic_gallery_link_add_text);
				} else {
					woo_gallery_title.html(woo_gallery_title_text);
					woo_gallery_link_add.html(woo_gallery_link_add_text);
				}
			});
		});
		</script>
        <?php
		$output = ob_get_clean();
		echo $output;
	}

	public function save_actived_d_gallery( $post_id = 0 ) {

		if ( $post_id < 1 ) {
			global $post;
			$post_id = $post->ID;
		}

		// Check if our nonce is set.
		if ( ! isset( $_POST['a3_dynamic_metabox_nonce_field'] ) || ! check_admin_referer( 'a3_dynamic_metabox_action', 'a3_dynamic_metabox_nonce_field' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted,
		// so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

		if ( ! current_user_can( 'edit_post', $post_id ) )
			return $post_id;

		if ( 'product' != get_post_type( $post_id ) ) return $post_id;

		if ( isset( $_REQUEST['actived_d_gallery'] ) ) {
			update_post_meta( $post_id, '_actived_d_gallery', 1 );
		} else {
			update_post_meta( $post_id, '_actived_d_gallery', 0 );
		}

		if ( isset( $_REQUEST['wc_dgallery_image_source'] ) ) {
			update_post_meta( $post_id, '_wc_dgallery_image_source', $_REQUEST['wc_dgallery_image_source'] );
		} else {
			delete_post_meta( $post_id, '_wc_dgallery_image_source' );
		}

		if ( isset( $_REQUEST['wc_dgallery_enable_gallery_thumb'] ) ) {
			update_post_meta( $post_id, '_wc_dgallery_enable_gallery_thumb', 1 );
		} else {
			update_post_meta( $post_id, '_wc_dgallery_enable_gallery_thumb', 0 );
		}

		if ( isset( $_REQUEST['wc_dgallery_auto_feature_image'] ) ) {
			update_post_meta( $post_id, '_wc_dgallery_auto_feature_image', 1 );
		} else {
			update_post_meta( $post_id, '_wc_dgallery_auto_feature_image', 0 );
		}
	}

}

global $wc_dynamic_gallery_meta_boxes;
$wc_dynamic_gallery_meta_boxes = new WC_Dynamic_Gallery_Meta_Boxes();

?>
