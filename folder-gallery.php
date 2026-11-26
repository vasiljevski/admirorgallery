<?php
/**
 * Plugin Name: Folder Gallery
 * Description: This plugin created gallery from folders with images.
 * Version: 0.1.0
 * Author: Igor Kekeljevic, Nikola Vasiljevski
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FOLDER_GALLERY_VERSION', '0.1.0' );
define( 'FOLDER_GALLERY_PATH', plugin_dir_path( __FILE__ ) );
define( 'FOLDER_GALLERY_URL', plugin_dir_url( __FILE__ ) );

require_once FOLDER_GALLERY_PATH . 'includes/class-storage.php';
require_once FOLDER_GALLERY_PATH . 'includes/class-plugin.php';

register_activation_hook( __FILE__, array( 'FG_Storage', 'ensure_base_dir_exists' ) );

function folder_gallery_run() {
	$plugin = new FG_Plugin();
	$plugin->run();
}
folder_gallery_run();