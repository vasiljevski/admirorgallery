<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Folder_Gallery_Manifest {

	const FILENAME = 'manifest.json';
	const VERSION  = 1;

	/**
	 * Check whether a filename is an allowed gallery image.
	 *
	 * @param string $filename File name.
	 * @return bool
	 */
	public static function is_supported_image_file( $filename ) {
		if ( ! is_string( $filename ) || '' === $filename || self::FILENAME === $filename ) {
			return false;
		}
		$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
		return in_array( $ext, array( 'jpg', 'jpeg', 'png', 'webp', 'gif' ), true );
	}

	/**
	 * Allowed HTML tags and attributes for item and folder descriptions.
	 *
	 * @return array
	 */
	public static function get_allowed_description_tags() {
		return array(
			'p'          => array(),
			'br'         => array(),
			'strong'     => array(),
			'b'          => array(),
			'em'         => array(),
			'i'          => array(),
			'u'          => array(),
			'ul'         => array(),
			'ol'         => array(),
			'li'         => array(),
			'blockquote' => array(),
			'span'       => array(
				'class' => true,
			),
			'a'          => array(
				'href'   => true,
				'title'  => true,
				'target' => true,
				'rel'    => true,
			),
		);
	}

	/**
	 * Sanitize HTML description string.
	 *
	 * @param string $html Raw HTML input.
	 * @return string
	 */
	public static function sanitize_description( $html ) {
		$clean = wp_kses( (string) $html, self::get_allowed_description_tags() );
		return trim( $clean );
	}

	/**
	 * Build default manifest array for a folder (empty title and description by default).
	 *
	 * @return array
	 */
	public static function default_manifest() {
		return array(
			'version'     => self::VERSION,
			'title'       => '',
			'description' => '',
			'images'      => array(),
		);
	}

	/**
	 * Read manifest data from disk without pruning image entries.
	 *
	 * @param string $dir_path Real directory path on disk.
	 * @return array
	 */
	public static function read_raw_manifest( $dir_path ) {
		$manifest  = self::default_manifest();
		$file_path = trailingslashit( $dir_path ) . self::FILENAME;

		if ( file_exists( $file_path ) && is_readable( $file_path ) ) {
			$raw = file_get_contents( $file_path );
			if ( false !== $raw && '' !== trim( $raw ) ) {
				$decoded = json_decode( $raw, true );
				if ( is_array( $decoded ) ) {
					if ( isset( $decoded['title'] ) && is_string( $decoded['title'] ) ) {
						$manifest['title'] = sanitize_text_field( $decoded['title'] );
					}
					if ( isset( $decoded['description'] ) && is_string( $decoded['description'] ) ) {
						$manifest['description'] = self::sanitize_description( $decoded['description'] );
					}
					if ( isset( $decoded['images'] ) && is_array( $decoded['images'] ) ) {
						foreach ( $decoded['images'] as $img_name => $meta ) {
							if ( ! is_string( $img_name ) || '' === $img_name ) {
								continue;
							}
							$clean_key = basename( $img_name );
							if ( ! self::is_supported_image_file( $clean_key ) ) {
								continue;
							}
							$meta_arr  = is_array( $meta ) ? $meta : array();
							$manifest['images'][ $clean_key ] = array(
								'title'       => isset( $meta_arr['title'] ) ? sanitize_text_field( (string) $meta_arr['title'] ) : '',
								'description' => isset( $meta_arr['description'] ) ? self::sanitize_description( (string) $meta_arr['description'] ) : '',
							);
						}
					}
				}
			}
		}

		return $manifest;
	}

	/**
	 * Read and sync manifest.json against actual image files in a directory.
	 *
	 * @param string $dir_path         Real directory path on disk.
	 * @param array  $disk_image_names List of image filenames currently on disk in this folder.
	 * @param bool   $write_if_changed Whether to persist sync changes to disk.
	 * @return array
	 */
	public static function read_and_sync( $dir_path, $disk_image_names = array(), $write_if_changed = false ) {
		$file_path = trailingslashit( $dir_path ) . self::FILENAME;
		$changed   = ! file_exists( $file_path );
		$manifest  = self::read_raw_manifest( $dir_path );

		// Filter disk image names to supported images only.
		$valid_disk_images = array_values(
			array_filter(
				$disk_image_names,
				array( __CLASS__, 'is_supported_image_file' )
			)
		);
		$disk_set = array_flip( $valid_disk_images );

		foreach ( array_keys( $manifest['images'] ) as $existing_key ) {
			if ( ! isset( $disk_set[ $existing_key ] ) ) {
				unset( $manifest['images'][ $existing_key ] );
				$changed = true;
			}
		}

		foreach ( $valid_disk_images as $disk_img ) {
			if ( ! isset( $manifest['images'][ $disk_img ] ) ) {
				$manifest['images'][ $disk_img ] = array(
					'title'       => '',
					'description' => '',
				);
				$changed = true;
			}
		}

		if ( $write_if_changed && $changed ) {
			self::write( $dir_path, $manifest );
		}

		return $manifest;
	}

	/**
	 * Write manifest.json safely with exclusive lock.
	 *
	 * @param string $dir_path Real directory path on disk.
	 * @param array  $manifest Manifest data.
	 * @return bool
	 */
	public static function write( $dir_path, $manifest ) {
		if ( ! is_dir( $dir_path ) || ! is_writable( $dir_path ) ) {
			return false;
		}

		$clean_images = array();
		if ( isset( $manifest['images'] ) && is_array( $manifest['images'] ) ) {
			foreach ( $manifest['images'] as $img_name => $meta ) {
				if ( ! is_string( $img_name ) || '' === $img_name ) {
					continue;
				}
				$key = basename( $img_name );
				if ( ! self::is_supported_image_file( $key ) ) {
					continue;
				}
				$meta_arr  = is_array( $meta ) ? $meta : array();
				$title_val = isset( $meta_arr['title'] ) ? sanitize_text_field( (string) $meta_arr['title'] ) : '';
				$desc_val  = isset( $meta_arr['description'] ) ? self::sanitize_description( (string) $meta_arr['description'] ) : '';

				$clean_images[ $key ] = array(
					'title'       => $title_val,
					'description' => $desc_val,
				);
			}
		}

		$payload = array(
			'version'     => self::VERSION,
			'title'       => isset( $manifest['title'] ) ? sanitize_text_field( (string) $manifest['title'] ) : '',
			'description' => isset( $manifest['description'] ) ? self::sanitize_description( (string) $manifest['description'] ) : '',
			'images'      => $clean_images,
		);

		$json = wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( false === $json ) {
			return false;
		}

		$file_path = trailingslashit( $dir_path ) . self::FILENAME;
		$handle    = @fopen( $file_path, 'c+' );
		if ( false === $handle ) {
			return false;
		}

		$written = false;
		if ( flock( $handle, LOCK_EX ) ) {
			ftruncate( $handle, 0 );
			rewind( $handle );
			$res     = fwrite( $handle, $json . PHP_EOL );
			$written = ( false !== $res );
			fflush( $handle );
			flock( $handle, LOCK_UN );
		}

		fclose( $handle );
		return $written;
	}

	/**
	 * Update title and description for a folder or an image inside a folder.
	 *
	 * @param string $dir_path    Real directory path containing the target (or being the folder itself).
	 * @param string $kind        'folder' or 'image'.
	 * @param string $item_name   Image filename (when kind === 'image') or unused when kind === 'folder'.
	 * @param string $title       New title (can be empty).
	 * @param string $description New HTML description.
	 * @return array|WP_Error
	 */
	public static function update_item_details( $dir_path, $kind, $item_name, $title, $description ) {
		$clean_title = sanitize_text_field( (string) $title );
		$clean_desc  = self::sanitize_description( (string) $description );

		if ( 'folder' === $kind ) {
			$manifest                = self::read_raw_manifest( $dir_path );
			$manifest['title']       = $clean_title;
			$manifest['description'] = $clean_desc;

			if ( ! self::write( $dir_path, $manifest ) ) {
				return new WP_Error(
					'folder_gallery_manifest_write_failed',
					__( 'Could not save folder details to manifest.json.', 'folder-gallery' )
				);
			}

			return array(
				'title'       => $manifest['title'],
				'description' => $manifest['description'],
			);
		}

		$disk_images = array();
		$entries     = scandir( $dir_path );
		if ( is_array( $entries ) ) {
			foreach ( $entries as $entry ) {
				if ( '.' === $entry || '..' === $entry ) {
					continue;
				}
				if ( is_file( $dir_path . DIRECTORY_SEPARATOR . $entry ) && self::is_supported_image_file( $entry ) ) {
					$disk_images[] = $entry;
				}
			}
		}

		$manifest = self::read_and_sync( $dir_path, $disk_images, false );
		$img_key  = basename( (string) $item_name );

		$manifest['images'][ $img_key ] = array(
			'title'       => $clean_title,
			'description' => $clean_desc,
		);

		if ( ! self::write( $dir_path, $manifest ) ) {
			return new WP_Error(
				'folder_gallery_manifest_write_failed',
				__( 'Could not save image details to manifest.json.', 'folder-gallery' )
			);
		}

		return $manifest['images'][ $img_key ];
	}

	/**
	 * Transfer image metadata entry when an image is renamed inside a folder.
	 *
	 * @param string $parent_dir Real path of parent directory.
	 * @param string $old_name   Old filename.
	 * @param string $new_name   New filename.
	 */
	public static function handle_image_rename( $parent_dir, $old_name, $new_name ) {
		$old_key  = basename( (string) $old_name );
		$new_key  = basename( (string) $new_name );
		$manifest = self::read_raw_manifest( $parent_dir );

		if ( isset( $manifest['images'][ $old_key ] ) ) {
			$meta = $manifest['images'][ $old_key ];
			unset( $manifest['images'][ $old_key ] );
			$manifest['images'][ $new_key ] = $meta;
		} else {
			$manifest['images'][ $new_key ] = array(
				'title'       => '',
				'description' => '',
			);
		}

		self::write( $parent_dir, $manifest );
	}

	/**
	 * Remove image metadata entry when an image is deleted.
	 *
	 * @param string $parent_dir Real path of parent directory.
	 * @param string $file_name  Deleted filename.
	 */
	public static function handle_image_delete( $parent_dir, $file_name ) {
		$key      = basename( (string) $file_name );
		$manifest = self::read_raw_manifest( $parent_dir );

		if ( isset( $manifest['images'][ $key ] ) ) {
			unset( $manifest['images'][ $key ] );
			self::write( $parent_dir, $manifest );
		}
	}

	/**
	 * Copy image metadata entry from source folder manifest to destination folder manifest.
	 *
	 * @param string $src_dir  Source folder real path.
	 * @param string $src_name Source image filename.
	 * @param string $dst_dir  Destination folder real path.
	 * @param string $dst_name Destination image filename.
	 */
	public static function copy_image_metadata( $src_dir, $src_name, $dst_dir, $dst_name ) {
		$src_key      = basename( (string) $src_name );
		$dst_key      = basename( (string) $dst_name );
		$src_manifest = self::read_raw_manifest( $src_dir );

		$meta = isset( $src_manifest['images'][ $src_key ] )
			? $src_manifest['images'][ $src_key ]
			: array(
				'title'       => '',
				'description' => '',
			);

		$dst_manifest                       = self::read_raw_manifest( $dst_dir );
		$dst_manifest['images'][ $dst_key ] = $meta;
		self::write( $dst_dir, $dst_manifest );
	}
}