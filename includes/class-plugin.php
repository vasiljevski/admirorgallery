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
	}

	private function load_dependencies() {
		require_once FOLDER_GALLERY_PATH . 'includes/class-storage.php';
		require_once FOLDER_GALLERY_PATH . 'includes/class-folder-scanner.php';
		require_once FOLDER_GALLERY_PATH . 'admin/class-admin-menu.php';
	}
}