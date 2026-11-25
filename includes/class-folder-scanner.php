<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Folder_Gallery_Scanner {

	/**
	 * Allowed image extensions.
	 *
	 * @var string[]
	 */
	private static $allowed_extensions = array( 'jpg', 'jpeg', 'png', 'webp', 'gif' );

	/**
	 * Scan a directory and return sorted folders and images.
	 *
	 * @param string $relative_path Relative path inside folder-gallery (empty for root).
	 * @return array|WP_Error
	 */
	public static function scan( $relative_path = '' ) {
		$base_dir = Folder_Gallery_Storage::get_base_dir();
		$base_url = Folder_Gallery_Storage::get_base_url();

		if ( is_wp_error( $base_dir ) ) {
			return $base_dir;
		}
		if ( is_wp_error( $base_url ) ) {
			return $base_url;
		}

		$relative_path = trim( str_replace( '\\', '/', $relative_path ), '/' );
		$target_dir    = '' === $relative_path ? $base_dir : trailingslashit( $base_dir ) . $relative_path;
		$target_url    = '' === $relative_path ? $base_url : trailingslashit( $base_url ) . $relative_path;

		// Ensure the resolved path stays inside the base gallery directory.
		$real_base   = realpath( $base_dir );
		$real_target = realpath( $target_dir );

		if ( false === $real_base || false === $real_target || 0 !== strpos( $real_target, $real_base ) ) {
			return new WP_Error(
				'folder_gallery_invalid_path',
				__( 'Invalid folder path.', 'folder-gallery' )
			);
		}

		$items = @scandir( $real_target );
		if ( false === $items ) {
			return new WP_Error(
				'folder_gallery_read_failed',
				__( 'Could not read folder contents.', 'folder-gallery' )
			);
		}

		$folders = array();
		$images  = array();

		foreach ( $items as $item ) {
			// Skip current/parent directory entries and hidden files/folders.
			if ( '.' === $item[0] ) {
				continue;
			}

			$full_path = $real_target . DIRECTORY_SEPARATOR . $item;

			if ( is_dir( $full_path ) ) {
				$mtime     = @filemtime( $full_path );
				$folders[] = array(
					'kind'        => 'folder',
					'name'        => $item,
					'path'        => '' === $relative_path ? $item : $relative_path . '/' . $item,
					'mtime'       => $mtime ? $mtime : 0,
					'type'        => __( 'Folder', 'folder-gallery' ),
					'image_count' => self::count_images_in_dir( $full_path ),
				);
			} elseif ( is_file( $full_path ) ) {
				$ext = strtolower( pathinfo( $item, PATHINFO_EXTENSION ) );
				if ( ! in_array( $ext, self::$allowed_extensions, true ) ) {
					continue;
				}

				$mtime    = @filemtime( $full_path );
				$filesize = @filesize( $full_path );

				$images[] = array(
					'kind'     => 'image',
					'name'     => $item,
					'path'     => '' === $relative_path ? $item : $relative_path . '/' . $item,
					'url'      => trailingslashit( $target_url ) . rawurlencode( $item ),
					'mtime'    => $mtime ? $mtime : 0,
					'type'     => strtoupper( $ext ),
					'filesize' => false !== $filesize ? $filesize : 0,
				);
			}
		}

		// Natural case-insensitive sort by name.
		usort(
			$folders,
			function ( $a, $b ) {
				return strnatcasecmp( $a['name'], $b['name'] );
			}
		);

		usort(
			$images,
			function ( $a, $b ) {
				return strnatcasecmp( $a['name'], $b['name'] );
			}
		);

		return array(
			'folders' => $folders,
			'images'  => $images,
		);
	}

	/**
	 * Count allowed image files directly inside the given directory.
	 *
	 * @param string $dir_path Absolute directory path.
	 * @return int
	 */
	private static function count_images_in_dir( $dir_path ) {
		$items = @scandir( $dir_path );
		if ( false === $items ) {
			return 0;
		}

		$count = 0;
		foreach ( $items as $item ) {
			if ( '.' === $item[0] ) {
				continue;
			}

			$full_path = $dir_path . DIRECTORY_SEPARATOR . $item;
			if ( is_file( $full_path ) ) {
				$ext = strtolower( pathinfo( $item, PATHINFO_EXTENSION ) );
				if ( in_array( $ext, self::$allowed_extensions, true ) ) {
					$count++;
				}
			}
		}

		return $count;
	}

	/**
	 * Delete a file or folder (recursively) inside the base gallery directory.
	 *
	 * @param string $relative_path Relative path inside folder-gallery.
	 * @return true|WP_Error
	 */
	public static function delete( $relative_path ) {
		$base_dir = Folder_Gallery_Storage::get_base_dir();

		if ( is_wp_error( $base_dir ) ) {
			return $base_dir;
		}

		$relative_path = trim( str_replace( '\\', '/', $relative_path ), '/' );
		if ( '' === $relative_path ) {
			return new WP_Error(
				'folder_gallery_cannot_delete_root',
				__( 'Cannot delete the root gallery folder.', 'folder-gallery' )
			);
		}

		$target_path = trailingslashit( $base_dir ) . $relative_path;

		$real_base   = realpath( $base_dir );
		$real_target = realpath( $target_path );

		// Ensure the target exists and is strictly inside (not equal to) the base directory.
		if (
			false === $real_base ||
			false === $real_target ||
			$real_target === $real_base ||
			0 !== strpos( $real_target, $real_base . DIRECTORY_SEPARATOR )
		) {
			return new WP_Error(
				'folder_gallery_invalid_delete_path',
				__( 'Invalid item path for deletion.', 'folder-gallery' )
			);
		}

		if ( is_dir( $real_target ) ) {
			if ( ! self::delete_directory_recursive( $real_target ) ) {
				return new WP_Error(
					'folder_gallery_delete_dir_failed',
					__( 'Could not delete the selected folder.', 'folder-gallery' )
				);
			}
			return true;
		}

		if ( is_file( $real_target ) ) {
			if ( ! @unlink( $real_target ) ) {
				return new WP_Error(
					'folder_gallery_delete_file_failed',
					__( 'Could not delete the selected file.', 'folder-gallery' )
				);
			}
			return true;
		}

		return new WP_Error(
			'folder_gallery_item_not_found',
			__( 'Selected item was not found.', 'folder-gallery' )
		);
	}

	/**
	 * Recursively remove a directory and all of its contents.
	 *
	 * @param string $dir_path Real absolute path to directory.
	 * @return bool
	 */
	private static function delete_directory_recursive( $dir_path ) {
		$items = @scandir( $dir_path );
		if ( false === $items ) {
			return false;
		}

		foreach ( $items as $item ) {
			if ( '.' === $item || '..' === $item ) {
				continue;
			}

			$full_path = $dir_path . DIRECTORY_SEPARATOR . $item;

			if ( is_dir( $full_path ) ) {
				if ( ! self::delete_directory_recursive( $full_path ) ) {
					return false;
				}
			} elseif ( ! @unlink( $full_path ) ) {
				return false;
			}
		}

		return @rmdir( $dir_path );
	}


	/**
	 * Upload one or more image files into the target relative directory.
	 *
	 * @param string $relative_path Relative path inside folder-gallery (empty for root).
	 * @param array  $files         Normalized array of uploaded file entries from $_FILES.
	 * @return array|WP_Error       Summary with uploaded count and any per-file errors.
	 */
	public static function upload_images( $relative_path, $files ) {
		$base_dir = Folder_Gallery_Storage::get_base_dir();

		if ( is_wp_error( $base_dir ) ) {
			return $base_dir;
		}

		$relative_path = trim( str_replace( '\\', '/', $relative_path ), '/' );
		$target_dir    = '' === $relative_path ? $base_dir : trailingslashit( $base_dir ) . $relative_path;

		$real_base   = realpath( $base_dir );
		$real_target = realpath( $target_dir );

		if ( false === $real_base || false === $real_target || 0 !== strpos( $real_target, $real_base ) || ! is_dir( $real_target ) ) {
			return new WP_Error(
				'folder_gallery_invalid_upload_path',
				__( 'Invalid target folder for upload.', 'folder-gallery' )
			);
		}

		$allowed_mimes = array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'png'          => 'image/png',
			'webp'         => 'image/webp',
			'gif'          => 'image/gif',
		);

		$uploaded = 0;
		$errors   = array();

		foreach ( $files as $file ) {
			$original_name = isset( $file['name'] ) ? $file['name'] : '';

			$upload_error = isset( $file['error'] ) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;
			if ( UPLOAD_ERR_OK !== $upload_error || empty( $file['tmp_name'] ) ) {
				$errors[] = self::format_upload_error( $upload_error, $original_name );
				continue;
			}

			if ( ! is_uploaded_file( $file['tmp_name'] ) ) {
				$errors[] = sprintf(
					/* translators: %s: file name */
					__( 'Invalid uploaded file: %s', 'folder-gallery' ),
					$original_name
				);
				continue;
			}

			$clean_name = sanitize_file_name( $original_name );
			if ( '' === $clean_name ) {
				$clean_name = 'image';
			}

			$checked = wp_check_filetype_and_ext( $file['tmp_name'], $clean_name, $allowed_mimes );
			if ( empty( $checked['ext'] ) || empty( $checked['type'] ) ) {
				$errors[] = sprintf(
					/* translators: %s: file name */
					__( 'Unsupported image format: %s', 'folder-gallery' ),
					$original_name
				);
				continue;
			}

			$unique_name = wp_unique_filename( $real_target, $clean_name );
			$destination = $real_target . DIRECTORY_SEPARATOR . $unique_name;

			if ( ! @move_uploaded_file( $file['tmp_name'], $destination ) ) {
				$errors[] = sprintf(
					/* translators: %s: file name */
					__( 'Could not save file: %s', 'folder-gallery' ),
					$original_name
				);
				continue;
			}

			$uploaded++;
		}

		if ( 0 === $uploaded && ! empty( $errors ) ) {
			return new WP_Error( 'folder_gallery_upload_failed', implode( ' ', $errors ) );
		}

		return array(
			'uploaded' => $uploaded,
			'errors'   => $errors,
		);
	}

	/**
	 * Create a new subfolder inside the target relative directory.
	 *
	 * @param string $relative_path Relative path inside folder-gallery (empty for root).
	 * @param string $folder_name   Requested folder name.
	 * @return array|WP_Error
	 */
	public static function create_folder( $relative_path, $folder_name ) {
		$base_dir = Folder_Gallery_Storage::get_base_dir();

		if ( is_wp_error( $base_dir ) ) {
			return $base_dir;
		}

		$relative_path = trim( str_replace( '\\', '/', $relative_path ), '/' );
		$target_dir    = '' === $relative_path ? $base_dir : trailingslashit( $base_dir ) . $relative_path;

		$real_base   = realpath( $base_dir );
		$real_target = realpath( $target_dir );

		if ( false === $real_base || false === $real_target || 0 !== strpos( $real_target, $real_base ) || ! is_dir( $real_target ) ) {
			return new WP_Error(
				'folder_gallery_invalid_parent_path',
				__( 'Invalid parent folder path.', 'folder-gallery' )
			);
		}

		// Sanitize folder name so no path traversal or unsafe characters get through.
		$clean_name = sanitize_file_name( trim( (string) $folder_name ) );
		if ( '' === $clean_name || '.' === $clean_name || '..' === $clean_name ) {
			return new WP_Error(
				'folder_gallery_invalid_folder_name',
				__( 'Please enter a valid folder name.', 'folder-gallery' )
			);
		}

		$new_folder_path = $real_target . DIRECTORY_SEPARATOR . $clean_name;

		if ( file_exists( $new_folder_path ) ) {
			return new WP_Error(
				'folder_gallery_folder_exists',
				__( 'A folder or file with that name already exists.', 'folder-gallery' )
			);
		}

		if ( ! wp_mkdir_p( $new_folder_path ) ) {
			return new WP_Error(
				'folder_gallery_create_folder_failed',
				__( 'Could not create the new folder.', 'folder-gallery' )
			);
		}

		// Add an empty index.html to prevent directory listing.
		$index_file = $new_folder_path . DIRECTORY_SEPARATOR . 'index.html';
		if ( ! file_exists( $index_file ) ) {
			@file_put_contents( $index_file, '' );
		}

		return array(
			'name' => $clean_name,
			'path' => '' === $relative_path ? $clean_name : $relative_path . '/' . $clean_name,
		);
	}

	/**
	 * Copy a file or folder (recursively) into the target folder inside folder-gallery.
	 *
	 * @param string $source_relative_path        Relative path of item to copy.
	 * @param string $target_folder_relative_path Relative path of destination folder (empty for root).
	 * @return array|WP_Error
	 */
	public static function paste_item( $source_relative_path, $target_folder_relative_path ) {
		$base_dir = Folder_Gallery_Storage::get_base_dir();

		if ( is_wp_error( $base_dir ) ) {
			return $base_dir;
		}

		$source_rel = trim( str_replace( '\\', '/', $source_relative_path ), '/' );
		$target_rel = trim( str_replace( '\\', '/', $target_folder_relative_path ), '/' );

		if ( '' === $source_rel ) {
			return new WP_Error(
				'folder_gallery_invalid_source',
				__( 'Cannot copy the root gallery folder.', 'folder-gallery' )
			);
		}

		$real_base   = realpath( $base_dir );
		$real_source = realpath( trailingslashit( $base_dir ) . $source_rel );
		$target_dir  = '' === $target_rel ? $base_dir : trailingslashit( $base_dir ) . $target_rel;
		$real_target = realpath( $target_dir );

		if (
			false === $real_base ||
			false === $real_source ||
			false === $real_target ||
			$real_source === $real_base ||
			0 !== strpos( $real_source, $real_base . DIRECTORY_SEPARATOR ) ||
			( $real_target !== $real_base && 0 !== strpos( $real_target, $real_base . DIRECTORY_SEPARATOR ) ) ||
			! is_dir( $real_target )
		) {
			return new WP_Error(
				'folder_gallery_invalid_paste_path',
				__( 'Invalid source or destination path.', 'folder-gallery' )
			);
		}

		$source_name = basename( $real_source );

		if ( is_dir( $real_source ) ) {
			// Prevent copying a folder into itself or into one of its own descendants.
			$source_prefix = $real_source . DIRECTORY_SEPARATOR;
			$target_check  = $real_target . DIRECTORY_SEPARATOR;
			if ( $real_target === $real_source || 0 === strpos( $target_check, $source_prefix ) ) {
				return new WP_Error(
					'folder_gallery_copy_into_self',
					__( 'Cannot copy a folder into itself or its subfolder.', 'folder-gallery' )
				);
			}

			$dest_name = self::generate_unique_copy_name( $real_target, $source_name, true );
			$dest_path = $real_target . DIRECTORY_SEPARATOR . $dest_name;

			if ( ! self::copy_directory_recursive( $real_source, $dest_path ) ) {
				return new WP_Error(
					'folder_gallery_copy_dir_failed',
					__( 'Could not paste the selected folder.', 'folder-gallery' )
				);
			}
		} elseif ( is_file( $real_source ) ) {
			$dest_name = self::generate_unique_copy_name( $real_target, $source_name, false );
			$dest_path = $real_target . DIRECTORY_SEPARATOR . $dest_name;

			if ( ! @copy( $real_source, $dest_path ) ) {
				return new WP_Error(
					'folder_gallery_copy_file_failed',
					__( 'Could not paste the selected file.', 'folder-gallery' )
				);
			}
		} else {
			return new WP_Error(
				'folder_gallery_source_missing',
				__( 'Source item was not found.', 'folder-gallery' )
			);
		}

		return array(
			'name' => $dest_name,
			'path' => '' === $target_rel ? $dest_name : $target_rel . '/' . $dest_name,
		);
	}

	/**
	 * Recursively copy a directory and its contents.
	 *
	 * @param string $source_dir Real path to source directory.
	 * @param string $dest_dir   Destination directory path.
	 * @return bool
	 */
	private static function copy_directory_recursive( $source_dir, $dest_dir ) {
		if ( ! wp_mkdir_p( $dest_dir ) ) {
			return false;
		}

		$items = @scandir( $source_dir );
		if ( false === $items ) {
			return false;
		}

		foreach ( $items as $item ) {
			if ( '.' === $item || '..' === $item ) {
				continue;
			}

			$src_item  = $source_dir . DIRECTORY_SEPARATOR . $item;
			$dest_item = $dest_dir . DIRECTORY_SEPARATOR . $item;

			if ( is_dir( $src_item ) ) {
				if ( ! self::copy_directory_recursive( $src_item, $dest_item ) ) {
					return false;
				}
			} elseif ( ! @copy( $src_item, $dest_item ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Build a non-colliding file or folder name in the target directory.
	 *
	 * @param string $target_dir Real path to destination folder.
	 * @param string $name       Original item name.
	 * @param bool   $is_dir     Whether the item is a directory.
	 * @return string
	 */
	private static function generate_unique_copy_name( $target_dir, $name, $is_dir ) {
		$candidate = $name;
		if ( ! file_exists( $target_dir . DIRECTORY_SEPARATOR . $candidate ) ) {
			return $candidate;
		}

		if ( $is_dir ) {
			$base = $name;
			$ext  = '';
		} else {
			$info = pathinfo( $name );
			$base = isset( $info['filename'] ) ? $info['filename'] : $name;
			$ext  = ! empty( $info['extension'] ) ? '.' . $info['extension'] : '';
		}

		$suffix = '-copy';
		$index  = 1;

		while ( true ) {
			$candidate = $base . $suffix . $ext;
			if ( ! file_exists( $target_dir . DIRECTORY_SEPARATOR . $candidate ) ) {
				return $candidate;
			}
			$index++;
			$suffix = '-copy-' . $index;
		}
	}


	/**
	 * Delete multiple files or folders inside the base gallery directory.
	 *
	 * @param string[] $relative_paths List of relative paths inside folder-gallery.
	 * @return array|WP_Error
	 */
	public static function delete_items( $relative_paths ) {
		if ( ! is_array( $relative_paths ) || empty( $relative_paths ) ) {
			return new WP_Error(
				'folder_gallery_no_items_to_delete',
				__( 'No items selected for deletion.', 'folder-gallery' )
			);
		}

		$deleted = 0;
		$errors  = array();

		foreach ( $relative_paths as $path ) {
			$result = self::delete( $path );
			if ( is_wp_error( $result ) ) {
				$errors[] = $result->get_error_message();
			} else {
				$deleted++;
			}
		}

		if ( 0 === $deleted && ! empty( $errors ) ) {
			return new WP_Error( 'folder_gallery_bulk_delete_failed', implode( ' ', $errors ) );
		}

		return array(
			'deleted' => $deleted,
			'errors'  => $errors,
		);
	}


	/**
	 * Extract and import allowed folders and images from a ZIP archive into the target directory.
	 *
	 * @param string $relative_path Relative path inside folder-gallery (empty for root).
	 * @param array  $file          Uploaded file entry from $_FILES.
	 * @return array|WP_Error
	 */
	public static function upload_zip( $relative_path, $file ) {
		if ( ! class_exists( 'ZipArchive' ) ) {
			return new WP_Error(
				'folder_gallery_zip_not_supported',
				__( 'ZIP archive support (ZipArchive) is not available on this server.', 'folder-gallery' )
			);
		}

		$base_dir = Folder_Gallery_Storage::get_base_dir();
		if ( is_wp_error( $base_dir ) ) {
			return $base_dir;
		}

		$relative_path = trim( str_replace( '\\', '/', $relative_path ), '/' );
		$target_dir    = '' === $relative_path ? $base_dir : trailingslashit( $base_dir ) . $relative_path;

		$real_base   = realpath( $base_dir );
		$real_target = realpath( $target_dir );

		if ( false === $real_base || false === $real_target || 0 !== strpos( $real_target, $real_base ) || ! is_dir( $real_target ) ) {
			return new WP_Error(
				'folder_gallery_invalid_zip_target',
				__( 'Invalid target folder for ZIP upload.', 'folder-gallery' )
			);
		}

		$original_name = isset( $file['name'] ) ? $file['name'] : 'archive.zip';
		$upload_error  = isset( $file['error'] ) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;
		$tmp_name      = isset( $file['tmp_name'] ) ? (string) $file['tmp_name'] : '';

		if ( UPLOAD_ERR_OK !== $upload_error ) {
			return new WP_Error(
				'folder_gallery_zip_upload_error',
				self::format_upload_error( $upload_error, $original_name )
			);
		}

		if ( '' === $tmp_name || ! file_exists( $tmp_name ) ) {
			return new WP_Error(
				'folder_gallery_zip_missing_tmp',
				sprintf(
					/* translators: %s: archive file name */
					__( 'Temporary uploaded file is missing for ZIP archive: %s', 'folder-gallery' ),
					$original_name
				)
			);
		}

		$ext = strtolower( pathinfo( $original_name, PATHINFO_EXTENSION ) );
		if ( 'zip' !== $ext ) {
			return new WP_Error(
				'folder_gallery_not_a_zip',
				__( 'Please select a valid .zip file.', 'folder-gallery' )
			);
		}

		// Create a temporary extraction directory outside the public gallery folder.
		$temp_root = trailingslashit( get_temp_dir() ) . 'fg-zip-' . wp_generate_password( 12, false );
		if ( ! wp_mkdir_p( $temp_root ) ) {
			return new WP_Error(
				'folder_gallery_temp_dir_failed',
				__( 'Could not prepare temporary directory for ZIP extraction.', 'folder-gallery' )
			);
		}

		$zip = new ZipArchive();
		if ( true !== $zip->open( $file['tmp_name'] ) ) {
			self::delete_directory_recursive( $temp_root );
			return new WP_Error(
				'folder_gallery_zip_open_failed',
				__( 'Could not open the ZIP archive.', 'folder-gallery' )
			);
		}

		// Guard against Zip Slip by validating every entry path before extraction.
		for ( $i = 0; $i < $zip->numFiles; $i++ ) {
			$entry_name = $zip->getNameIndex( $i );
			if ( false === $entry_name ) {
				continue;
			}

			$normalized_entry = str_replace( '\\', '/', $entry_name );
			if (
				0 === strpos( $normalized_entry, '/' ) ||
				preg_match( '/^[a-zA-Z]:/', $normalized_entry ) ||
				in_array( '..', explode( '/', $normalized_entry ), true )
			) {
				$zip->close();
				self::delete_directory_recursive( $temp_root );
				return new WP_Error(
					'folder_gallery_zip_unsafe_path',
					__( 'ZIP archive contains unsafe file paths.', 'folder-gallery' )
				);
			}
		}

		if ( ! $zip->extractTo( $temp_root ) ) {
			$zip->close();
			self::delete_directory_recursive( $temp_root );
			return new WP_Error(
				'folder_gallery_zip_extract_failed',
				__( 'Could not extract the ZIP archive.', 'folder-gallery' )
			);
		}

		$zip->close();

		$stats = array(
			'images_imported'  => 0,
			'folders_created'  => 0,
			'skipped_files'    => 0,
		);

		self::import_extracted_directory( $temp_root, $real_target, $stats );
		self::delete_directory_recursive( $temp_root );

		return $stats;
	}

	/**
	 * Recursively copy allowed image files and subfolders from an extracted temp folder.
	 *
	 * @param string $source_dir Temporary extracted directory path.
	 * @param string $dest_dir   Real target directory inside folder-gallery.
	 * @param array  $stats      Counters updated by reference.
	 * @return void
	 */
	private static function import_extracted_directory( $source_dir, $dest_dir, &$stats ) {
		$items = @scandir( $source_dir );
		if ( false === $items ) {
			return;
		}

		$allowed_mimes = array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'png'          => 'image/png',
			'webp'         => 'image/webp',
			'gif'          => 'image/gif',
		);

		foreach ( $items as $item ) {
			if ( '.' === $item || '..' === $item || '.' === $item[0] || '__MACOSX' === $item ) {
				continue;
			}

			$src_path = $source_dir . DIRECTORY_SEPARATOR . $item;

			if ( is_dir( $src_path ) ) {
				$clean_folder = sanitize_file_name( $item );
				if ( '' === $clean_folder || '.' === $clean_folder || '..' === $clean_folder ) {
					continue;
				}

				$new_dest_dir = $dest_dir . DIRECTORY_SEPARATOR . $clean_folder;
				if ( ! file_exists( $new_dest_dir ) ) {
					if ( wp_mkdir_p( $new_dest_dir ) ) {
						$stats['folders_created']++;
						$index_file = $new_dest_dir . DIRECTORY_SEPARATOR . 'index.html';
						if ( ! file_exists( $index_file ) ) {
							@file_put_contents( $index_file, '' );
						}
					} else {
						continue;
					}
				}

				$real_new_dest = realpath( $new_dest_dir );
				if ( false !== $real_new_dest ) {
					self::import_extracted_directory( $src_path, $real_new_dest, $stats );
				}
			} elseif ( is_file( $src_path ) ) {
				$clean_name = sanitize_file_name( $item );
				if ( '' === $clean_name ) {
					$stats['skipped_files']++;
					continue;
				}

				$checked = wp_check_filetype_and_ext( $src_path, $clean_name, $allowed_mimes );
				if ( empty( $checked['ext'] ) || empty( $checked['type'] ) ) {
					$stats['skipped_files']++;
					continue;
				}

				$unique_name = wp_unique_filename( $dest_dir, $clean_name );
				$dest_file   = $dest_dir . DIRECTORY_SEPARATOR . $unique_name;

				if ( @copy( $src_path, $dest_file ) ) {
					$stats['images_imported']++;
				} else {
					$stats['skipped_files']++;
				}
			}
		}
	}

	/**
	 * Convert a PHP file upload error code into a user-friendly message.
	 *
	 * @param int    $error_code PHP UPLOAD_ERR_* constant.
	 * @param string $file_name  Original file name.
	 * @return string
	 */
	private static function format_upload_error( $error_code, $file_name ) {
		if ( UPLOAD_ERR_INI_SIZE === $error_code || UPLOAD_ERR_FORM_SIZE === $error_code ) {
			return sprintf(
				/* translators: 1: file name, 2: maximum upload size */
				__( 'The file "%1$s" exceeds the maximum allowed upload size of %2$s.', 'folder-gallery' ),
				$file_name,
				size_format( wp_max_upload_size() )
			);
		}

		return sprintf(
			/* translators: %s: file name */
			__( 'Could not upload file: %s', 'folder-gallery' ),
			$file_name
		);
	}

	/**
	 * Rename a file or folder inside the base gallery directory.
	 *
	 * @param string $relative_path Relative path of the item inside folder-gallery.
	 * @param string $new_name      Requested new name.
	 * @return array|WP_Error
	 */
	public static function rename_item( $relative_path, $new_name ) {
		$base_dir = Folder_Gallery_Storage::get_base_dir();

		if ( is_wp_error( $base_dir ) ) {
			return $base_dir;
		}

		$relative_path = trim( str_replace( '\\', '/', $relative_path ), '/' );
		if ( '' === $relative_path ) {
			return new WP_Error(
				'folder_gallery_cannot_rename_root',
				__( 'Cannot rename the root gallery folder.', 'folder-gallery' )
			);
		}

		$target_path = trailingslashit( $base_dir ) . $relative_path;
		$real_base   = realpath( $base_dir );
		$real_target = realpath( $target_path );

		if (
			false === $real_base ||
			false === $real_target ||
			$real_target === $real_base ||
			0 !== strpos( $real_target, $real_base . DIRECTORY_SEPARATOR )
		) {
			return new WP_Error(
				'folder_gallery_invalid_rename_path',
				__( 'Invalid item path for renaming.', 'folder-gallery' )
			);
		}

		$parent_dir = dirname( $real_target );
		$raw_name   = trim( (string) $new_name );

		// Preserve file extension for images if the user types only the base name.
		if ( is_file( $real_target ) ) {
			$current_ext = strtolower( pathinfo( $real_target, PATHINFO_EXTENSION ) );
			$new_ext     = strtolower( pathinfo( $raw_name, PATHINFO_EXTENSION ) );

			if ( '' !== $current_ext && '' === $new_ext ) {
				$raw_name .= '.' . $current_ext;
			}
		}

		$clean_name = sanitize_file_name( $raw_name );
		if ( '' === $clean_name || '.' === $clean_name || '..' === $clean_name ) {
			return new WP_Error(
				'folder_gallery_invalid_new_name',
				__( 'Please enter a valid name.', 'folder-gallery' )
			);
		}

		$current_name = basename( $real_target );
		if ( $clean_name === $current_name ) {
			return array(
				'name' => $current_name,
				'path' => $relative_path,
			);
		}

		$new_full_path = $parent_dir . DIRECTORY_SEPARATOR . $clean_name;
		if ( file_exists( $new_full_path ) ) {
			return new WP_Error(
				'folder_gallery_rename_conflict',
				__( 'An item with that name already exists in this folder.', 'folder-gallery' )
			);
		}

		if ( ! @rename( $real_target, $new_full_path ) ) {
			return new WP_Error(
				'folder_gallery_rename_failed',
				__( 'Could not rename the selected item.', 'folder-gallery' )
			);
		}

		$parent_rel = dirname( $relative_path );
		$new_rel    = ( '.' === $parent_rel || '' === $parent_rel )
			? $clean_name
			: trim( str_replace( '\\', '/', $parent_rel ), '/' ) . '/' . $clean_name;

		return array(
			'name' => $clean_name,
			'path' => $new_rel,
		);
	}

}

