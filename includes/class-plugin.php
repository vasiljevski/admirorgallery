<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Folder_Gallery_Plugin {

	public function run() {
		$this->load_dependencies();

		if ( is_admin() ) {
			$admin_menu = new Folder_Gallery_Admin_Menu();
			$admin_menu->init();
		}

		$frontend_shortcode = new Folder_Gallery_Frontend_Shortcode();
		$frontend_shortcode->init();
	}

	private function load_dependencies() {
		require_once FOLDER_GALLERY_PATH . 'includes/class-storage.php';
		require_once FOLDER_GALLERY_PATH . 'includes/class-folder-scanner.php';
		require_once FOLDER_GALLERY_PATH . 'includes/class-folder-manifest.php';
		require_once FOLDER_GALLERY_PATH . 'includes/class-frontend-shortcode.php';
		require_once FOLDER_GALLERY_PATH . 'admin/class-admin-menu.php';
	}
}