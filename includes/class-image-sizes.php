<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FG_Image_Sizes {

	const OPTION_SMALL_HEIGHT  = 'folder_gallery_small_height';
	const OPTION_MEDIUM_HEIGHT = 'folder_gallery_medium_height';

	const DEFAULT_SMALL_HEIGHT  = 320;
	const DEFAULT_MEDIUM_HEIGHT = 900;

	const SUFFIX_SMALL  = '-fg-small';
	const SUFFIX_MEDIUM = '-fg-medium';

	/**
	 * Get configured max height for small variant.
	 *
	 * @return int
	 */
	public static function get_small_height() {
		$val = (int) get_option( self::OPTION_SMALL_HEIGHT, self::DEFAULT_SMALL_HEIGHT );
		return $val >= 50 && $val <= 4000 ? $val : self::DEFAULT_SMALL_HEIGHT;
	}

	/**
	 * Get configured max height for medium variant.
	 *
	 * @return int
	 */
	public static function get_medium_height() {
		$val = (int) get_option( self::OPTION_MEDIUM_HEIGHT, self::DEFAULT_MEDIUM_HEIGHT );
		return $val >= 50 && $val <= 4000 ? $val : self::DEFAULT_MEDIUM_HEIGHT;
	}

	/**
	 * Check whether a file name is a generated small or medium variant.
	 *
	 * @param string $filename File name inside a gallery folder.
	 * @return bool
	 */
	public static function is_generated_variant( $filename ) {
		$base = pathinfo( (string) $filename, PATHINFO_FILENAME );
		return (
			substr( $base, -strlen( self::SUFFIX_SMALL ) ) === self::SUFFIX_SMALL ||
			substr( $base, -strlen( self::SUFFIX_MEDIUM ) ) === self::SUFFIX_MEDIUM
		);
	}

	/**
	 * Build variant file name for a given original image file name.
	 *
	 * @param string $filename Original image file name.
	 * @param string $size     'small' or 'medium'.
	 * @return string
	 */
	public static function get_variant_filename( $filename, $size ) {
		$info   = pathinfo( (string) $filename );
		$base   = isset( $info['filename'] ) ? $info['filename'] : $filename;
		$ext    = ! empty( $info['extension'] ) ? '.' . strtolower( $info['extension'] ) : '';
		$suffix = 'medium' === $size ? self::SUFFIX_MEDIUM : self::SUFFIX_SMALL;

		return $base . $suffix . $ext;
	}

	/**
	 * Generate or refresh small and medium variants for a single image file on disk.
	 *
	 * @param string $file_path    Full filesystem path to original image.
	 * @param bool   $force_small  Overwrite existing small variant even if present.
	 * @param bool   $force_medium Overwrite existing medium variant even if present.
	 * @return array{small: bool, medium: bool}
	 */
	public static function generate_for_image( $file_path, $force_small = false, $force_medium = null ) {
		if ( null === $force_medium ) {
			$force_medium = $force_small;
		}

		$result = array(
			'small'  => false,
			'medium' => false,
		);

		if ( ! is_file( $file_path ) ) {
			return $result;
		}

		$dir      = dirname( $file_path );
		$filename = basename( $file_path );

		if ( self::is_generated_variant( $filename ) ) {
			return $result;
		}

		$targets = array(
			'small'  => array(
				'height' => self::get_small_height(),
				'force'  => (bool) $force_small,
			),
			'medium' => array(
				'height' => self::get_medium_height(),
				'force'  => (bool) $force_medium,
			),
		);

		$to_process = array();
		foreach ( $targets as $key => $cfg ) {
			$dest = $dir . DIRECTORY_SEPARATOR . self::get_variant_filename( $filename, $key );
			if ( $cfg['force'] || ! is_file( $dest ) ) {
				$to_process[ $key ] = $dest;
			} else {
				$result[ $key ] = true;
			}
		}

		if ( empty( $to_process ) ) {
			return $result;
		}

		$editor = wp_get_image_editor( $file_path );
		if ( is_wp_error( $editor ) ) {
			return $result;
		}

		$size = $editor->get_size();
		if ( empty( $size['width'] ) || empty( $size['height'] ) ) {
			return $result;
		}

		$orig_w = (int) $size['width'];
		$orig_h = (int) $size['height'];

		foreach ( $to_process as $key => $dest_path ) {
			$target_h = $targets[ $key ]['height'];

			if ( $targets[ $key ]['force'] && file_exists( $dest_path ) ) {
				@unlink( $dest_path );
			}

			// Do not upscale images shorter than or equal to target height; copy original instead.
			if ( $orig_h <= $target_h ) {
				if ( @copy( $file_path, $dest_path ) ) {
					$result[ $key ] = true;
				}
				continue;
			}

			$new_w      = max( 1, (int) round( $orig_w * ( $target_h / $orig_h ) ) );
			$sub_editor = wp_get_image_editor( $file_path );
			if ( is_wp_error( $sub_editor ) ) {
				continue;
			}

			$resized = $sub_editor->resize( $new_w, $target_h, false );
			if ( is_wp_error( $resized ) ) {
				continue;
			}

			$saved = $sub_editor->save( $dest_path );
			if ( ! is_wp_error( $saved ) && is_file( $dest_path ) ) {
				$result[ $key ] = true;
			}
		}

		return $result;
	}

	/**
	 * Ensure small and medium variants exist and match current Settings heights for a folder.
	 *
	 * @param string     $dir_path Full filesystem path to folder.
	 * @param bool       $force    Force regeneration of both variants regardless of manifest meta.
	 * @param array|null $manifest Optional pre-loaded manifest array for this folder.
	 * @return void
	 */
	public static function process_directory( $dir_path, $force = false, $manifest = null ) {
		if ( ! is_dir( $dir_path ) ) {
			return;
		}

		if ( null === $manifest ) {
			$manifest = FG_Manifest::read_raw_manifest( $dir_path );
		}

		$cur_small  = self::get_small_height();
		$cur_medium = self::get_medium_height();

		$saved_small  = isset( $manifest['sizes_meta']['small_height'] ) ? (int) $manifest['sizes_meta']['small_height'] : 0;
		$saved_medium = isset( $manifest['sizes_meta']['medium_height'] ) ? (int) $manifest['sizes_meta']['medium_height'] : 0;

		$force_small  = $force || ( $saved_small !== $cur_small );
		$force_medium = $force || ( $saved_medium !== $cur_medium );

		$items = @scandir( $dir_path );
		if ( false === $items ) {
			return;
		}

		$allowed_ext = array( 'jpg', 'jpeg', 'png', 'webp', 'gif' );

		foreach ( $items as $item ) {
			if ( '.' === $item[0] || FG_Manifest::FILENAME === $item ) {
				continue;
			}

			if ( self::is_generated_variant( $item ) ) {
				continue;
			}

			$full_path = $dir_path . DIRECTORY_SEPARATOR . $item;
			if ( ! is_file( $full_path ) ) {
				continue;
			}

			$ext = strtolower( pathinfo( $item, PATHINFO_EXTENSION ) );
			if ( ! in_array( $ext, $allowed_ext, true ) ) {
				continue;
			}

			self::generate_for_image( $full_path, $force_small, $force_medium );
		}

		if ( $force_small || $force_medium ) {
			FG_Manifest::update_sizes_meta( $dir_path, $cur_small, $cur_medium );
		}
	}

	/**
	 * Delete small and medium variant files for an image in a folder.
	 *
	 * @param string $dir_path Full filesystem path to folder.
	 * @param string $filename Original image file name.
	 * @return void
	 */
	public static function delete_for_image( $dir_path, $filename ) {
		if ( self::is_generated_variant( $filename ) ) {
			return;
		}

		foreach ( array( 'small', 'medium' ) as $size ) {
			$variant_path = trailingslashit( $dir_path ) . self::get_variant_filename( $filename, $size );
			if ( file_exists( $variant_path ) ) {
				@unlink( $variant_path );
			}
		}
	}

	/**
	 * Clean up old variants when an image is renamed and generate variants for the new name.
	 *
	 * @param string $dir_path Full filesystem path to folder.
	 * @param string $old_name Previous image file name.
	 * @param string $new_name New image file name.
	 * @return void
	 */
	public static function rename_for_image( $dir_path, $old_name, $new_name ) {
		self::delete_for_image( $dir_path, $old_name );
		$new_file = trailingslashit( $dir_path ) . $new_name;
		if ( is_file( $new_file ) ) {
			self::generate_for_image( $new_file, true, true );
		}
	}

	/**
	 * Build variant status array (url with cache-busting mtime, dimensions, exists) for admin table.
	 *
	 * @param string $dir_path Full filesystem path to folder.
	 * @param string $dir_url  Base URL for folder.
	 * @param string $filename Original image file name.
	 * @return array{small: array, medium: array}
	 */
	public static function get_variants_info( $dir_path, $dir_url, $filename ) {
		$info = array();

		foreach ( array( 'small', 'medium' ) as $size ) {
			$var_name = self::get_variant_filename( $filename, $size );
			$var_path = trailingslashit( $dir_path ) . $var_name;

			if ( is_file( $var_path ) ) {
				$mtime = @filemtime( $var_path );
				$dims  = @getimagesize( $var_path );
				$url   = trailingslashit( $dir_url ) . rawurlencode( $var_name );
				if ( $mtime ) {
					$url = add_query_arg( 'v', (int) $mtime, $url );
				}

				$info[ $size ] = array(
					'exists' => true,
					'url'    => $url,
					'width'  => is_array( $dims ) && ! empty( $dims[0] ) ? (int) $dims[0] : 0,
					'height' => is_array( $dims ) && ! empty( $dims[1] ) ? (int) $dims[1] : 0,
				);
			} else {
				$info[ $size ] = array(
					'exists' => false,
					'url'    => '',
					'width'  => 0,
					'height' => 0,
				);
			}
		}

		return $info;
	}
}