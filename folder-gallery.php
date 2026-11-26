<?php
/**
 * Plugin Name: Folder Gallery
 * Description: This plugin created gallery from folders with images.
 * Version:     0.1.0
 * Author:      Igor Kekeljevic, Nikola Vasiljevski
 * Text Domain: folder-gallery
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

add_action( 'admin_head-plugins.php', 'folder_gallery_plugins_list_icon_css' );

function folder_gallery_plugins_list_icon_css() {
	$plugin_slug = plugin_basename( __FILE__ );
	$logo_url    = esc_url( FOLDER_GALLERY_URL . 'admin/assets/images/logo.svg' );
	?>
	<style>
		tr[data-plugin="<?php echo esc_attr( $plugin_slug ); ?>"] .plugin-title strong {
			display: inline-flex;
			align-items: center;
			gap: 8px;
		}
		tr[data-plugin="<?php echo esc_attr( $plugin_slug ); ?>"] .plugin-title strong::before {
			content: "";
			display: inline-block;
			width: 24px;
			height: 24px;
			flex: 0 0 24px;
			background: url("<?php echo $logo_url; ?>") no-repeat center / contain;
		}
	</style>
	<?php
}

add_filter( 'plugin_row_meta', 'folder_gallery_plugin_row_meta', 10, 2 );

function folder_gallery_plugin_row_meta( $links, $file ) {
	if ( plugin_basename( FOLDER_GALLERY_PATH . 'folder-gallery.php' ) !== $file ) {
		return $links;
	}

	$links[] = sprintf(
		'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
		esc_url( 'https://wordpress-folder-gallery.com' ),
		esc_html__( 'View details', 'folder-gallery' )
	);

	return $links;
}