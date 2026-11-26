<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FG_Plugin {

	public function run() {
		$this->load_dependencies();

		if ( is_admin() ) {
			$admin_menu = new FG_Admin_Menu();
			$admin_menu->init();
		}

		$frontend_shortcode = new FG_Frontend_Shortcode();
		$frontend_shortcode->init();
	}

	private function load_dependencies() {
		require_once FOLDER_GALLERY_PATH . 'includes/class-storage.php';
		require_once FOLDER_GALLERY_PATH . 'includes/class-folder-scanner.php';
		require_once FOLDER_GALLERY_PATH . 'includes/class-folder-manifest.php';
		require_once FOLDER_GALLERY_PATH . 'includes/class-image-sizes.php';
		require_once FOLDER_GALLERY_PATH . 'includes/class-theme-workspace-helper.php';
		require_once FOLDER_GALLERY_PATH . 'includes/class-gallery-theme-manager.php';
		require_once FOLDER_GALLERY_PATH . 'includes/class-lightbox-theme-manager.php';
		require_once FOLDER_GALLERY_PATH . 'includes/class-frontend-shortcode.php';
		require_once FOLDER_GALLERY_PATH . 'admin/class-admin-menu.php';
	}
}
