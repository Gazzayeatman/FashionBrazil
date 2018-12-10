<?php 
define('WP_USE_THEMES', false);
$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );

$document_id = sanitize_text_field($_GET['doc_id']);
$user_id = get_current_user_id();

global $wpdb;
$prepared = $wpdb->prepare('SELECT file_uploaded FROM document_meta JOIN iapp ON document_meta.iapp_id = iapp.id WHERE document_id = %d AND user_id = %d', $document_id, $user_id);
$file_path = $wpdb->get_var($prepared);

if(is_user_logged_in()) {
	if(!is_file($file_path)){
	    header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found");
	    header("Status: 404 Not Found");
	    echo 'File not found!';
	    die;
	}
	if(!is_readable($file_path)){
	    header("{$_SERVER['SERVER_PROTOCOL']} 403 Forbidden");
	    header("Status: 403 Forbidden");
	    echo 'File not accessible!';
	    die;
	}
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.basename($file_path));
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header('Content-Length: ' . filesize($file_path));
	ob_clean();
	flush();
	readfile($file_path);
	exit;
}
