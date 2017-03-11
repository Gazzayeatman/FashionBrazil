<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */

class WC_Dynamic_Gallery_Notices
{
	public function __construct() {
		add_action( 'admin_init', array( $this, 'manual_update_database' ) );
		add_action( 'admin_init', array( $this, 'check_show_update_notice' ), 11 );
	}

	public function check_show_update_notice() {
		$db_is_updated = get_option( 'a3_dynamic_gallery_db_updated', 'yes' );
		$a3_dynamic_gallery_db_version = get_option( 'a3_dynamic_gallery_db_version', '1.0.0' );
		if ( 'no' == $db_is_updated && version_compare( $a3_dynamic_gallery_db_version, WOO_DYNAMIC_GALLERY_DB_VERSION, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'update_notice' ) );
		}
	}

	public function update_notice() {
	?>
		<div id="message" class="error below-h2" style="margin-left:2px;">
			<p><?php _e( 'Thank you for updating to WooCommerce Dynamic Gallery Major Version 2.1.0 - we hope you enjoy it.', 'woo_dgallery' ); ?></p>
			<p><?php _e( '<strong>IMPORTANT!</strong> This update must be run to complete the upgrade and get all the benefits of this new version.', 'woo_dgallery' ); ?></p>
			<p><?php _e( "<strong>WARNING!</strong> This is a major upgrade - We strongly recommend that you do a database backup BEFORE you run the update. If you don't and something does go wrong, you may lose all of your product images. You have been warned.", 'woo_dgallery' ); ?></p>
			<p class="submit"><a href="<?php echo esc_url( add_query_arg( 'do_update_db_a3_dynamic_gallery', 'true', admin_url( 'admin.php?page=woo-dynamic-gallery' ) ) ); ?>" class="a3-dg-update-now button-primary"><?php _e( 'RUN UPDATE', 'woo_dgallery' ); ?></a></p>
		</div>
	<?php
	}

	public function updated_notice() {
		?>
		<div id="message" class="updated below-h2" style="margin-left:2px;">
			<p><?php _e( 'WooCommerce Dynamic Gallery Data update complete. Thank you for updating to the latest version!', 'woo_dgallery' ); ?></p>
		</div>
		<?php
	}

	public function manual_update_database() {
		if ( isset( $_GET['do_update_db_a3_dynamic_gallery'] ) ) {
			$this->update();
			add_action( 'admin_notices', array( $this, 'updated_notice' ) );
		}
	}

	public function update() {
		$db_is_updated                 = get_option( 'a3_dynamic_gallery_db_updated', 'yes' );
		$a3_dynamic_gallery_db_version = get_option( 'a3_dynamic_gallery_db_version', '1.0.0' );
		if ( 'no' == $db_is_updated && version_compare( $a3_dynamic_gallery_db_version, WOO_DYNAMIC_GALLERY_DB_VERSION, '<' ) ) {
			include( WOO_DYNAMIC_GALLERY_FILE_PATH. '/includes/updates/update-db-manual.php' );
		}
	}
}

new WC_Dynamic_Gallery_Notices();
?>
