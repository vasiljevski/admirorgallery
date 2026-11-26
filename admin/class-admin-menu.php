<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Folder_Gallery_Admin_Menu {

	public function init() {
		add_action( 'admin_menu', array( $this, 'register_menus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_fg_scan_folder', array( $this, 'ajax_scan_folder' ) );
		add_action( 'wp_ajax_fg_delete_item', array( $this, 'ajax_delete_item' ) );
		add_action( 'wp_ajax_fg_upload_images', array( $this, 'ajax_upload_images' ) );
		add_action( 'wp_ajax_fg_create_folder', array( $this, 'ajax_create_folder' ) );
		add_action( 'wp_ajax_fg_paste_item', array( $this, 'ajax_paste_item' ) );
		add_action( 'wp_ajax_fg_upload_zip', array( $this, 'ajax_upload_zip' ) );
		add_action( 'wp_ajax_fg_rename_item', array( $this, 'ajax_rename_item' ) );
		add_action( 'wp_ajax_fg_save_details', array( $this, 'ajax_save_details' ) );
		add_action( 'wp_ajax_fg_regenerate_sizes', array( $this, 'ajax_regenerate_sizes' ) );
		add_action( 'admin_post_fg_activate_theme', array( $this, 'handle_activate_theme' ) );
		add_action( 'admin_post_fg_save_settings', array( $this, 'handle_save_settings' ) );
	}

	public function ajax_regenerate_sizes() {
		check_ajax_referer( 'fg_scan_folder_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Permission denied.', 'folder-gallery' ) ),
				403
			);
		}

		$target_path = isset( $_POST['path'] ) ? sanitize_text_field( wp_unslash( $_POST['path'] ) ) : '';
		$result      = Folder_Gallery_Scanner::regenerate_folder_sizes( $target_path );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array( 'message' => $result->get_error_message() ),
				400
			);
		}

		wp_send_json_success( $result );
	}

	public function ajax_save_details() {
		check_ajax_referer( 'fg_scan_folder_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Permission denied.', 'folder-gallery' ) ),
				403
			);
		}

		$target_path = isset( $_POST['path'] ) ? sanitize_text_field( wp_unslash( $_POST['path'] ) ) : '';
		$kind        = isset( $_POST['kind'] ) ? sanitize_key( wp_unslash( $_POST['kind'] ) ) : 'image';
		$title       = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$description = isset( $_POST['description'] ) ? wp_unslash( $_POST['description'] ) : '';

		$saved = Folder_Gallery_Scanner::save_details( $target_path, $kind, $title, $description );

		if ( is_wp_error( $saved ) ) {
			wp_send_json_error(
				array( 'message' => $saved->get_error_message() ),
				400
			);
		}

		wp_send_json_success( $saved );
	}

	public function enqueue_assets( $hook_suffix ) {
		if ( 'folder-gallery_page_folder-gallery-themes' === $hook_suffix ) {
			wp_enqueue_style(
				'folder-gallery-admin',
				FOLDER_GALLERY_URL . 'admin/assets/css/admin.css',
				array(),
				FOLDER_GALLERY_VERSION
			);
			return;
		}

		// Load explorer assets only on the main Galleries screen.
		if ( 'toplevel_page_folder-gallery' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_script( 'quicktags' );

		wp_enqueue_style(
			'folder-gallery-admin',
			FOLDER_GALLERY_URL . 'admin/assets/css/admin.css',
			array(),
			FOLDER_GALLERY_VERSION
		);

		wp_enqueue_script(
			'folder-gallery-admin',
			FOLDER_GALLERY_URL . 'admin/assets/js/admin.js',
			array(),
			FOLDER_GALLERY_VERSION,
			true
		);

		$max_upload_bytes = wp_max_upload_size();

		wp_localize_script(
			'folder-gallery-admin',
			'fgAdminConfig',
			array(
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'nonce'          => wp_create_nonce( 'fg_scan_folder_nonce' ),
				'maxUploadBytes' => (int) $max_upload_bytes,
				'maxUploadLabel' => size_format( $max_upload_bytes ),
				'i18n'           => array(
					'emptyFolder'     => __( 'This folder is empty.', 'folder-gallery' ),
					'folder'          => __( 'Folder', 'folder-gallery' ),
					'singleImage'     => __( '%d image', 'folder-gallery' ),
					'pluralImages'    => __( '%d images', 'folder-gallery' ),
					'unknownError'    => __( 'Could not load folder contents.', 'folder-gallery' ),
					'deleteError'     => __( 'Could not delete the selected item(s).', 'folder-gallery' ),
					'uploadError'     => __( 'Could not upload selected image(s).', 'folder-gallery' ),
					'zipError'        => __( 'Could not extract or import the ZIP archive.', 'folder-gallery' ),
					'fileTooLarge'    => __( 'The file "%1$s" exceeds the maximum allowed upload size of %2$s.', 'folder-gallery' ),
					'createFolderErr' => __( 'Could not create the new folder.', 'folder-gallery' ),
					'pasteError'      => __( 'Could not paste the copied item(s).', 'folder-gallery' ),
					'newFolderPrompt' => __( 'Enter a name for the new folder:', 'folder-gallery' ),
					'confirmFolder'   => __( 'Are you sure you want to delete the folder "%s" and all its contents?', 'folder-gallery' ),
					'confirmImage'    => __( 'Are you sure you want to delete "%s"?', 'folder-gallery' ),
					'confirmMultiple' => __( 'Are you sure you want to delete %d selected items?', 'folder-gallery' ),
					'renamePrompt'    => __( 'Enter a new name:', 'folder-gallery' ),
					'renameError'     => __( 'Could not rename the selected item.', 'folder-gallery' ),
					'regenError'      => __( 'Could not regenerate image sizes for this folder.', 'folder-gallery' ),
					'smallVariant'    => __( 'Small', 'folder-gallery' ),
					'mediumVariant'   => __( 'Medium', 'folder-gallery' ),
					'detailsEmpty'    => __( 'Select a folder or image to view and edit its details.', 'folder-gallery' ),
					'detailsMulti'    => __( '%d items selected. Select a single folder or image to edit details.', 'folder-gallery' ),
					'updateSaved'     => __( 'Details saved.', 'folder-gallery' ),
					'updateError'     => __( 'Could not save item details.', 'folder-gallery' ),
					'copyShortcode'   => __( 'Copy', 'folder-gallery' ),
					'copiedShortcode' => __( 'Copied!', 'folder-gallery' ),
				),
			)
		);
	}

	public function ajax_scan_folder() {
		check_ajax_referer( 'fg_scan_folder_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Permission denied.', 'folder-gallery' ) ),
				403
			);
		}

		$relative_path = isset( $_POST['path'] ) ? sanitize_text_field( wp_unslash( $_POST['path'] ) ) : '';
		$result        = Folder_Gallery_Scanner::scan( $relative_path );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array( 'message' => $result->get_error_message() ),
				400
			);
		}

		$date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

		$folders = array();
		foreach ( $result['folders'] as $folder ) {
			$folders[] = array(
				'name'        => $folder['name'],
				'title'       => isset( $folder['title'] ) ? $folder['title'] : '',
				'description' => isset( $folder['description'] ) ? $folder['description'] : '',
				'path'        => $folder['path'],
				'date'        => $folder['mtime'] ? wp_date( $date_format, $folder['mtime'] ) : '—',
				'type'        => $folder['type'],
				'image_count' => (int) $folder['image_count'],
			);
		}

		$images = array();
		foreach ( $result['images'] as $image ) {
			$images[] = array(
				'name'        => $image['name'],
				'title'       => isset( $image['title'] ) ? $image['title'] : '',
				'description' => isset( $image['description'] ) ? $image['description'] : '',
				'path'        => $image['path'],
				'url'         => $image['url'],
				'date'        => $image['mtime'] ? wp_date( $date_format, $image['mtime'] ) : '—',
				'type'        => $image['type'],
				'size'        => size_format( $image['filesize'], 1 ),
				'variants'    => isset( $image['variants'] ) ? $image['variants'] : array(),
			);
		}

		wp_send_json_success(
			array(
				'path'    => trim( str_replace( '\\', '/', $relative_path ), '/' ),
				'folders' => $folders,
				'images'  => $images,
			)
		);
	}

	public function register_menus() {
		add_menu_page(
			__( 'Folder Gallery', 'folder-gallery' ),
			__( 'Folder Gallery', 'folder-gallery' ),
			'manage_options',
			'folder-gallery',
			array( $this, 'render_galleries_page' ),
			'dashicons-format-gallery',
			25
		);

		add_submenu_page(
			'folder-gallery',
			__( 'Galleries', 'folder-gallery' ),
			__( 'Galleries', 'folder-gallery' ),
			'manage_options',
			'folder-gallery',
			array( $this, 'render_galleries_page' )
		);

		add_submenu_page(
			'folder-gallery',
			__( 'Gallery Themes', 'folder-gallery' ),
			__( 'Gallery Themes', 'folder-gallery' ),
			'manage_options',
			'folder-gallery-themes',
			array( $this, 'render_gallery_themes_page' )
		);

		add_submenu_page(
			'folder-gallery',
			__( 'Settings', 'folder-gallery' ),
			__( 'Settings', 'folder-gallery' ),
			'manage_options',
			'folder-gallery-settings',
			array( $this, 'render_settings_page' )
		);
	}

	public function render_galleries_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$storage_status = Folder_Gallery_Storage::ensure_base_dir_exists();
		$scan_result    = is_wp_error( $storage_status )
			? $storage_status
			: Folder_Gallery_Scanner::scan( '' );

		require FOLDER_GALLERY_PATH . 'admin/views/page-galleries.php';
	}

	public function render_gallery_themes_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$themes       = Folder_Gallery_Theme_Manager::get_available_themes();
		$active_theme = Folder_Gallery_Theme_Manager::resolve_theme();
		$active_slug  = is_array( $active_theme ) && isset( $active_theme['slug'] ) ? $active_theme['slug'] : '';

		require FOLDER_GALLERY_PATH . 'admin/views/page-gallery-themes.php';
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		require FOLDER_GALLERY_PATH . 'admin/views/page-settings.php';
	}

	public function ajax_delete_item() {
		check_ajax_referer( 'fg_scan_folder_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Permission denied.', 'folder-gallery' ) ),
				403
			);
		}

		$paths = array();

		if ( isset( $_POST['paths'] ) && is_array( $_POST['paths'] ) ) {
			foreach ( $_POST['paths'] as $raw_path ) {
				$clean = sanitize_text_field( wp_unslash( $raw_path ) );
				if ( '' !== $clean ) {
					$paths[] = $clean;
				}
			}
		} elseif ( isset( $_POST['path'] ) ) {
			$clean = sanitize_text_field( wp_unslash( $_POST['path'] ) );
			if ( '' !== $clean ) {
				$paths[] = $clean;
			}
		}

		$result = Folder_Gallery_Scanner::delete_items( $paths );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array( 'message' => $result->get_error_message() ),
				400
			);
		}

		wp_send_json_success( $result );
	}

	public function ajax_upload_images() {
		check_ajax_referer( 'fg_scan_folder_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Permission denied.', 'folder-gallery' ) ),
				403
			);
		}

		if ( empty( $_FILES['images'] ) || ! is_array( $_FILES['images']['name'] ) ) {
			wp_send_json_error(
				array( 'message' => __( 'No image files received.', 'folder-gallery' ) ),
				400
			);
		}

		// Normalize multi-file $_FILES['images'] structure into a clean list of file arrays.
		$normalized_files = array();
		$file_count       = count( $_FILES['images']['name'] );

		for ( $i = 0; $i < $file_count; $i++ ) {
			$normalized_files[] = array(
				'name'     => isset( $_FILES['images']['name'][ $i ] ) ? sanitize_text_field( wp_unslash( $_FILES['images']['name'][ $i ] ) ) : '',
				'type'     => isset( $_FILES['images']['type'][ $i ] ) ? sanitize_text_field( wp_unslash( $_FILES['images']['type'][ $i ] ) ) : '',
				'tmp_name' => isset( $_FILES['images']['tmp_name'][ $i ] ) ? $_FILES['images']['tmp_name'][ $i ] : '',
				'error'    => isset( $_FILES['images']['error'][ $i ] ) ? (int) $_FILES['images']['error'][ $i ] : UPLOAD_ERR_NO_FILE,
				'size'     => isset( $_FILES['images']['size'][ $i ] ) ? (int) $_FILES['images']['size'][ $i ] : 0,
			);
		}

		$target_path = isset( $_POST['path'] ) ? sanitize_text_field( wp_unslash( $_POST['path'] ) ) : '';
		$result      = Folder_Gallery_Scanner::upload_images( $target_path, $normalized_files );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array( 'message' => $result->get_error_message() ),
				400
			);
		}

		wp_send_json_success( $result );
	}

	public function ajax_create_folder() {
		check_ajax_referer( 'fg_scan_folder_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Permission denied.', 'folder-gallery' ) ),
				403
			);
		}

		$parent_path = isset( $_POST['path'] ) ? sanitize_text_field( wp_unslash( $_POST['path'] ) ) : '';
		$folder_name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

		$created = Folder_Gallery_Scanner::create_folder( $parent_path, $folder_name );

		if ( is_wp_error( $created ) ) {
			wp_send_json_error(
				array( 'message' => $created->get_error_message() ),
				400
			);
		}

		wp_send_json_success( $created );
	}

	public function ajax_paste_item() {
		check_ajax_referer( 'fg_scan_folder_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Permission denied.', 'folder-gallery' ) ),
				403
			);
		}

		$target_path  = isset( $_POST['target_path'] ) ? sanitize_text_field( wp_unslash( $_POST['target_path'] ) ) : '';
		$source_paths = array();

		if ( isset( $_POST['source_paths'] ) && is_array( $_POST['source_paths'] ) ) {
			foreach ( $_POST['source_paths'] as $raw_src ) {
				$clean = sanitize_text_field( wp_unslash( $raw_src ) );
				if ( '' !== $clean ) {
					$source_paths[] = $clean;
				}
			}
		} elseif ( isset( $_POST['source_path'] ) ) {
			$clean = sanitize_text_field( wp_unslash( $_POST['source_path'] ) );
			if ( '' !== $clean ) {
				$source_paths[] = $clean;
			}
		}

		if ( empty( $source_paths ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Nothing to paste.', 'folder-gallery' ) ),
				400
			);
		}

		$pasted = 0;
		$errors = array();

		foreach ( $source_paths as $src ) {
			$res = Folder_Gallery_Scanner::paste_item( $src, $target_path );
			if ( is_wp_error( $res ) ) {
				$errors[] = $res->get_error_message();
			} else {
				$pasted++;
			}
		}

		if ( 0 === $pasted && ! empty( $errors ) ) {
			wp_send_json_error(
				array( 'message' => implode( ' ', $errors ) ),
				400
			);
		}

		wp_send_json_success(
			array(
				'pasted' => $pasted,
				'errors' => $errors,
			)
		);
	}

	public function ajax_upload_zip() {
		if ( ! check_ajax_referer( 'fg_scan_folder_nonce', 'nonce', false ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Security check failed (invalid or expired nonce). Refresh the page and try again.', 'folder-gallery' ) ),
				403
			);
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Permission denied.', 'folder-gallery' ) ),
				403
			);
		}

		if ( empty( $_FILES['zip_file'] ) || ! is_array( $_FILES['zip_file'] ) ) {
			wp_send_json_error(
				array( 'message' => __( 'No ZIP file received.', 'folder-gallery' ) ),
				400
			);
		}

		$file = array(
			'name'     => isset( $_FILES['zip_file']['name'] ) ? sanitize_text_field( wp_unslash( $_FILES['zip_file']['name'] ) ) : '',
			'type'     => isset( $_FILES['zip_file']['type'] ) ? sanitize_text_field( wp_unslash( $_FILES['zip_file']['type'] ) ) : '',
			'tmp_name' => isset( $_FILES['zip_file']['tmp_name'] ) ? $_FILES['zip_file']['tmp_name'] : '',
			'error'    => isset( $_FILES['zip_file']['error'] ) ? (int) $_FILES['zip_file']['error'] : UPLOAD_ERR_NO_FILE,
			'size'     => isset( $_FILES['zip_file']['size'] ) ? (int) $_FILES['zip_file']['size'] : 0,
		);

		$target_path = isset( $_POST['path'] ) ? sanitize_text_field( wp_unslash( $_POST['path'] ) ) : '';
		$result      = Folder_Gallery_Scanner::upload_zip( $target_path, $file );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array( 'message' => $result->get_error_message() ),
				400
			);
		}

		wp_send_json_success( $result );
	}

	public function ajax_rename_item() {
		check_ajax_referer( 'fg_scan_folder_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Permission denied.', 'folder-gallery' ) ),
				403
			);
		}

		$target_path = isset( $_POST['path'] ) ? sanitize_text_field( wp_unslash( $_POST['path'] ) ) : '';
		$new_name    = isset( $_POST['new_name'] ) ? sanitize_text_field( wp_unslash( $_POST['new_name'] ) ) : '';

		$renamed = Folder_Gallery_Scanner::rename_item( $target_path, $new_name );

		if ( is_wp_error( $renamed ) ) {
			wp_send_json_error(
				array( 'message' => $renamed->get_error_message() ),
				400
			);
		}

		wp_send_json_success( $renamed );
	}

	public function handle_activate_theme() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'folder-gallery' ), 403 );
		}

		check_admin_referer( 'fg_activate_theme_nonce' );

		$theme_slug = isset( $_POST['theme'] ) ? sanitize_key( wp_unslash( $_POST['theme'] ) ) : '';
		$themes     = Folder_Gallery_Theme_Manager::get_available_themes();

		if ( '' === $theme_slug || ! isset( $themes[ $theme_slug ] ) ) {
			wp_safe_redirect(
				add_query_arg(
					array(
						'page'       => 'folder-gallery-themes',
						'fg_message' => 'invalid_theme',
					),
					admin_url( 'admin.php' )
				)
			);
			exit;
		}

		update_option( Folder_Gallery_Theme_Manager::OPTION_ACTIVE_THEME, $theme_slug );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'       => 'folder-gallery-themes',
					'fg_message' => 'theme_activated',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	public function handle_save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'folder-gallery' ), 403 );
		}

		check_admin_referer( 'fg_save_settings_nonce' );

		$raw_small  = isset( $_POST['small_height'] ) ? (int) wp_unslash( $_POST['small_height'] ) : Folder_Gallery_Image_Sizes::DEFAULT_SMALL_HEIGHT;
		$raw_medium = isset( $_POST['medium_height'] ) ? (int) wp_unslash( $_POST['medium_height'] ) : Folder_Gallery_Image_Sizes::DEFAULT_MEDIUM_HEIGHT;

		$small_height  = ( $raw_small >= 50 && $raw_small <= 4000 ) ? $raw_small : Folder_Gallery_Image_Sizes::DEFAULT_SMALL_HEIGHT;
		$medium_height = ( $raw_medium >= 50 && $raw_medium <= 4000 ) ? $raw_medium : Folder_Gallery_Image_Sizes::DEFAULT_MEDIUM_HEIGHT;

		update_option( Folder_Gallery_Image_Sizes::OPTION_SMALL_HEIGHT, $small_height );
		update_option( Folder_Gallery_Image_Sizes::OPTION_MEDIUM_HEIGHT, $medium_height );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'       => 'folder-gallery-settings',
					'fg_message' => 'settings_saved',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

}