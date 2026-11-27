<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FG_Image_Sizes {

	const OPTION_SMALL_HEIGHT  = 'folder_gallery_small_height';
	const OPTION_MEDIUM_HEIGHT = 'folder_gallery_medium_height';
	const OPTION_MAX_IMAGE_MP  = 'folder_gallery_max_image_mp';

	const DEFAULT_SMALL_HEIGHT  = 320;
	const DEFAULT_MEDIUM_HEIGHT = 900;
	const DEFAULT_MAX_IMAGE_MP  = 12;

	const SUFFIX_SMALL  = '-fg-small';
	const SUFFIX_MEDIUM = '-fg-medium';

	/**
	 * Allowed preset megapixel limits for Settings dropdown.
	 *
	 * @return int[]
	 */
	public static function get_allowed_max_mp_options() {
		return array( 8, 12, 16, 24, 32, 48 );
	}

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
	 * Get configured max image size in megapixels (MP) before skipping resize.
	 *
	 * @return int
	 */
	public static function get_max_image_mp() {
		$val = (int) get_option( self::OPTION_MAX_IMAGE_MP, self::DEFAULT_MAX_IMAGE_MP );
		return in_array( $val, self::get_allowed_max_mp_options(), true ) ? $val : self::DEFAULT_MAX_IMAGE_MP;
	}

	/**
	 * Get configured max image size in total pixels.
	 *
	 * @return int
	 */
	public static function get_max_image_pixels() {
		return self::get_max_image_mp() * 1000000;
	}

	/**
	 * Lightweight metadata & megapixel check without decoding the image bitmap into RAM.
	 *
	 * @param string $file_path Full filesystem path to image.
	 * @return array{width: int, height: int, megapixels: float, too_large: bool, readable: bool}
	 */
	public static function inspect_image_dimensions( $file_path ) {
		$info = array(
			'width'      => 0,
			'height'     => 0,
			'megapixels' => 0.0,
			'too_large'  => false,
			'readable'   => false,
		);

		if ( ! is_file( $file_path ) ) {
			return $info;
		}

		$size = function_exists( 'wp_getimagesize' ) ? @wp_getimagesize( $file_path ) : @getimagesize( $file_path );
		if ( ! is_array( $size ) || empty( $size[0] ) || empty( $size[1] ) ) {
			return $info;
		}

		$w      = (int) $size[0];
		$h      = (int) $size[1];
		$pixels = $w * $h;
		$mp     = round( $pixels / 1000000, 1 );

		$info['width']      = $w;
		$info['height']     = $h;
		$info['megapixels'] = $mp;
		$info['readable']   = true;
		$info['too_large']  = ( $pixels > self::get_max_image_pixels() );

		return $info;
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
	 * Create a single resized variant and immediately free editor memory.
	 *
	 * @param string $src_path Source image path.
	 * @param string $dst_path Destination variant path.
	 * @param int    $src_w    Source width.
	 * @param int    $src_h    Source height.
	 * @param int    $target_h Target max height.
	 * @param bool   $force    Overwrite existing destination if present.
	 * @return bool
	 */
	private static function create_resized_variant( $src_path, $dst_path, $src_w, $src_h, $target_h, $force ) {
		if ( $force && file_exists( $dst_path ) ) {
			@unlink( $dst_path );
		}

		// Do not upscale images shorter than or equal to target height; copy source instead.
		if ( $src_h <= $target_h ) {
			return (bool) @copy( $src_path, $dst_path );
		}

		$new_w  = max( 1, (int) round( $src_w * ( $target_h / $src_h ) ) );
		$editor = wp_get_image_editor( $src_path );
		if ( is_wp_error( $editor ) ) {
			return false;
		}

		$ok      = false;
		$resized = $editor->resize( $new_w, $target_h, false );
		if ( ! is_wp_error( $resized ) ) {
			$saved = $editor->save( $dst_path );
			if ( ! is_wp_error( $saved ) && is_file( $dst_path ) ) {
				$ok = true;
			}
		}

		unset( $editor );
		if ( function_exists( 'gc_collect_cycles' ) ) {
			gc_collect_cycles();
		}

		return $ok;
	}

	/**
	 * Generate or refresh small and medium variants for a single image file on disk.
	 *
	 * @param string $file_path    Full filesystem path to original image.
	 * @param bool   $force_small  Overwrite existing small variant even if present.
	 * @param bool   $force_medium Overwrite existing medium variant even if present.
	 * @return array{small: bool, medium: bool, skipped: bool}
	 */
	public static function generate_for_image( $file_path, $force_small = false, $force_medium = null ) {
		if ( null === $force_medium ) {
			$force_medium = $force_small;
		}

		$result = array(
			'small'   => false,
			'medium'  => false,
			'skipped' => false,
		);

		if ( ! is_file( $file_path ) ) {
			return $result;
		}

		$dir      = dirname( $file_path );
		$filename = basename( $file_path );

		if ( self::is_generated_variant( $filename ) ) {
			return $result;
		}

		$small_h   = self::get_small_height();
		$medium_h  = self::get_medium_height();
		$small_dst = $dir . DIRECTORY_SEPARATOR . self::get_variant_filename( $filename, 'small' );
		$med_dst   = $dir . DIRECTORY_SEPARATOR . self::get_variant_filename( $filename, 'medium' );

		$need_small  = (bool) $force_small || ! is_file( $small_dst );
		$need_medium = (bool) $force_medium || ! is_file( $med_dst );

		if ( ! $need_small ) {
			$result['small'] = true;
		}
		if ( ! $need_medium ) {
			$result['medium'] = true;
		}

		if ( ! $need_small && ! $need_medium ) {
			return $result;
		}

		// Lightweight header check BEFORE opening wp_get_image_editor() so huge bitmaps never exhaust PHP RAM.
		$meta = self::inspect_image_dimensions( $file_path );
		if ( ! $meta['readable'] || $meta['too_large'] ) {
			$result['skipped'] = true;
			return $result;
		}

		$orig_w = $meta['width'];
		$orig_h = $meta['height'];

		if ( function_exists( 'wp_raise_memory_limit' ) ) {
			wp_raise_memory_limit( 'image' );
		}

		// 1. Generate medium first from original if needed.
		if ( $need_medium ) {
			$result['medium'] = self::create_resized_variant(
				$file_path,
				$med_dst,
				$orig_w,
				$orig_h,
				$medium_h,
				(bool) $force_medium
			);
		}

		// 2. Generate small from medium when available (uses a fraction of RAM), otherwise from original.
		if ( $need_small ) {
			$small_src   = $file_path;
			$small_src_w = $orig_w;
			$small_src_h = $orig_h;

			if ( $result['medium'] && is_file( $med_dst ) && $medium_h >= $small_h ) {
				$med_dims = @wp_getimagesize( $med_dst );
				if ( is_array( $med_dims ) && ! empty( $med_dims[0] ) && ! empty( $med_dims[1] ) ) {
					$small_src   = $med_dst;
					$small_src_w = (int) $med_dims[0];
					$small_src_h = (int) $med_dims[1];
				}
			}

			$result['small'] = self::create_resized_variant(
				$small_src,
				$small_dst,
				$small_src_w,
				$small_src_h,
				$small_h,
				(bool) $force_small
			);
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
