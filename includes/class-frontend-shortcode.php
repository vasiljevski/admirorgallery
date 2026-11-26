<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Folder_Gallery_Frontend_Shortcode {

	/**
	 * Allowed image extensions.
	 *
	 * @var string[]
	 */
	private static $allowed_extensions = array( 'jpg', 'jpeg', 'png', 'webp', 'gif' );

	/**
	 * Counter to isolate query parameters when multiple shortcodes appear on one page.
	 *
	 * @var int
	 */
	private static $instance_counter = 0;

	/**
	 * Register shortcode hook.
	 */
	public function init() {
		add_shortcode( 'folder_gallery', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Render [folder_gallery path="..." theme="..."] using the active theme.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string
	 */
	public function render_shortcode( $atts ) {
		self::$instance_counter++;
		$instance_id = self::$instance_counter;
		$query_key   = 1 === $instance_id ? 'fg_path' : 'fg_path_' . $instance_id;

		$atts = shortcode_atts(
			array(
				'path'  => '',
				'theme' => '',
			),
			$atts,
			'folder_gallery'
		);

		$base_dir = Folder_Gallery_Storage::get_base_dir();
		$base_url = Folder_Gallery_Storage::get_base_url();

		if ( is_wp_error( $base_dir ) || is_wp_error( $base_url ) ) {
			return '<p>' . esc_html__( 'Gallery storage is not available.', 'folder-gallery' ) . '</p>';
		}

		$real_base = realpath( $base_dir );
		if ( false === $real_base ) {
			return '<p>' . esc_html__( 'Gallery base directory was not found.', 'folder-gallery' ) . '</p>';
		}

		// Anchor root boundary strictly to the path defined in the shortcode.
		$root_rel      = self::normalize_relative_path( $atts['path'] );
		$root_dir      = '' === $root_rel ? $base_dir : trailingslashit( $base_dir ) . $root_rel;
		$real_root_dir = realpath( $root_dir );

		if (
			false === $real_root_dir ||
			( $real_root_dir !== $real_base && 0 !== strpos( $real_root_dir, $real_base . DIRECTORY_SEPARATOR ) ) ||
			! is_dir( $real_root_dir )
		) {
			return '<p>' . esc_html__( 'Invalid gallery folder path.', 'folder-gallery' ) . '</p>';
		}

		// Resolve requested subfolder from URL query parameter, clamped inside $real_root_dir.
		$requested_rel = '';
		if ( isset( $_GET[ $query_key ] ) ) {
			$requested_rel = self::normalize_relative_path( sanitize_text_field( wp_unslash( $_GET[ $query_key ] ) ) );
		}

		$current_rel      = $root_rel;
		$real_current_dir = $real_root_dir;

		if ( '' !== $requested_rel && self::is_within_root( $requested_rel, $root_rel ) ) {
			$candidate_dir      = trailingslashit( $base_dir ) . $requested_rel;
			$real_candidate_dir = realpath( $candidate_dir );

			if (
				false !== $real_candidate_dir &&
				( $real_candidate_dir === $real_root_dir || 0 === strpos( $real_candidate_dir, $real_root_dir . DIRECTORY_SEPARATOR ) ) &&
				is_dir( $real_candidate_dir )
			) {
				$current_rel      = $requested_rel;
				$real_current_dir = $real_candidate_dir;
			}
		}

		$current_url = '' === $current_rel ? $base_url : trailingslashit( $base_url ) . $current_rel;

		$items = @scandir( $real_current_dir );
		if ( false === $items ) {
			return '<p>' . esc_html__( 'Could not read gallery folder.', 'folder-gallery' ) . '</p>';
		}

		$manifest     = class_exists( 'Folder_Gallery_Manifest' )
			? Folder_Gallery_Manifest::read_raw_manifest( $real_current_dir )
			: array( 'images' => array() );
		$allowed_tags = class_exists( 'Folder_Gallery_Manifest' )
			? Folder_Gallery_Manifest::get_allowed_description_tags()
			: wp_kses_allowed_html( 'post' );

		$has_sizes_class = class_exists( 'Folder_Gallery_Image_Sizes' );

		$folders = array();
		$images  = array();

		foreach ( $items as $item ) {
			if ( '.' === $item[0] || 'manifest.json' === $item ) {
				continue;
			}

			if ( $has_sizes_class && Folder_Gallery_Image_Sizes::is_generated_variant( $item ) ) {
				continue;
			}

			$full_path = $real_current_dir . DIRECTORY_SEPARATOR . $item;

			if ( is_dir( $full_path ) ) {
				$sub_manifest = class_exists( 'Folder_Gallery_Manifest' )
					? Folder_Gallery_Manifest::read_raw_manifest( $full_path )
					: array();
				$folder_rel   = '' === $current_rel ? $item : $current_rel . '/' . $item;

				$folders[] = array(
					'name'        => $item,
					'title'       => isset( $sub_manifest['title'] ) ? $sub_manifest['title'] : '',
					'description' => isset( $sub_manifest['description'] ) ? wp_kses( $sub_manifest['description'], $allowed_tags ) : '',
					'path'        => $folder_rel,
					'url'         => self::build_folder_url( $query_key, $folder_rel, $root_rel ),
				);
			} elseif ( is_file( $full_path ) ) {
				$ext = strtolower( pathinfo( $item, PATHINFO_EXTENSION ) );
				if ( ! in_array( $ext, self::$allowed_extensions, true ) ) {
					continue;
				}

				$img_meta = isset( $manifest['images'][ $item ] ) && is_array( $manifest['images'][ $item ] )
					? $manifest['images'][ $item ]
					: array();

				$variants = $has_sizes_class
					? Folder_Gallery_Image_Sizes::get_variants_info( $real_current_dir, $current_url, $item )
					: array();

				$images[] = array(
					'name'        => $item,
					'title'       => isset( $img_meta['title'] ) ? $img_meta['title'] : '',
					'description' => isset( $img_meta['description'] ) ? wp_kses( $img_meta['description'], $allowed_tags ) : '',
					'path'        => '' === $current_rel ? $item : $current_rel . '/' . $item,
					'url'         => trailingslashit( $current_url ) . rawurlencode( $item ),
					'ext'         => strtoupper( $ext ),
					'variants'    => $variants,
				);
			}
		}

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

		$breadcrumbs = self::build_breadcrumbs( $base_dir, $root_rel, $current_rel, $query_key );

		$gallery_data = array(
			'instance_id'    => $instance_id,
			'query_key'      => $query_key,
			'root_path'      => $root_rel,
			'current_path'   => $current_rel,
			'current_folder' => array(
				'name'        => '' === $current_rel ? __( 'Gallery', 'folder-gallery' ) : basename( $current_rel ),
				'title'       => isset( $manifest['title'] ) ? $manifest['title'] : '',
				'description' => isset( $manifest['description'] ) ? wp_kses( $manifest['description'], $allowed_tags ) : '',
				'path'        => $current_rel,
			),
			'breadcrumbs'    => $breadcrumbs,
			'folders'        => $folders,
			'images'         => $images,
		);

		return Folder_Gallery_Theme_Manager::render( $gallery_data, $atts['theme'] );
	}

	/**
	 * Normalize a relative gallery path.
	 *
	 * @param string $path Raw path.
	 * @return string
	 */
	private static function normalize_relative_path( $path ) {
		$path = trim( str_replace( '\\', '/', (string) $path ), '/' );
		$path = preg_replace( '#/+#', '/', $path );
		return is_string( $path ) ? $path : '';
	}

	/**
	 * Check whether $candidate_rel is equal to or nested inside $root_rel.
	 *
	 * @param string $candidate_rel Candidate relative path.
	 * @param string $root_rel      Root boundary relative path.
	 * @return bool
	 */
	private static function is_within_root( $candidate_rel, $root_rel ) {
		if ( '' === $root_rel ) {
			return true;
		}
		return $candidate_rel === $root_rel || 0 === strpos( $candidate_rel, $root_rel . '/' );
	}

	/**
	 * Build a frontend navigation URL for a subfolder within this gallery instance.
	 *
	 * @param string $query_key  Query parameter key.
	 * @param string $target_rel Target relative path.
	 * @param string $root_rel   Root relative path.
	 * @return string
	 */
	private static function build_folder_url( $query_key, $target_rel, $root_rel ) {
		if ( $target_rel === $root_rel ) {
			return remove_query_arg( $query_key );
		}
		return add_query_arg( $query_key, $target_rel );
	}

	/**
	 * Build breadcrumb trail anchored at $root_rel as the top level.
	 *
	 * @param string $base_dir    Base gallery directory on disk.
	 * @param string $root_rel    Root relative path from shortcode.
	 * @param string $current_rel Current relative path being viewed.
	 * @param string $query_key   Query parameter key.
	 * @return array[]
	 */
	private static function build_breadcrumbs( $base_dir, $root_rel, $current_rel, $query_key ) {
		$crumbs = array();

		$root_disk     = '' === $root_rel ? $base_dir : trailingslashit( $base_dir ) . $root_rel;
		$root_manifest = class_exists( 'Folder_Gallery_Manifest' )
			? Folder_Gallery_Manifest::read_raw_manifest( $root_disk )
			: array();
		$root_fallback = '' === $root_rel ? __( 'Gallery', 'folder-gallery' ) : basename( $root_rel );
		$root_label    = ! empty( $root_manifest['title'] ) ? $root_manifest['title'] : $root_fallback;

		$crumbs[] = array(
			'label'   => $root_label,
			'path'    => $root_rel,
			'url'     => self::build_folder_url( $query_key, $root_rel, $root_rel ),
			'current' => ( $current_rel === $root_rel ),
		);

		if ( $current_rel === $root_rel ) {
			return $crumbs;
		}

		$sub_part = '' === $root_rel
			? $current_rel
			: ltrim( substr( $current_rel, strlen( $root_rel ) ), '/' );

		if ( '' === $sub_part ) {
			return $crumbs;
		}

		$segments    = explode( '/', $sub_part );
		$running_rel = $root_rel;
		$total       = count( $segments );

		foreach ( $segments as $index => $segment ) {
			$running_rel  = '' === $running_rel ? $segment : $running_rel . '/' . $segment;
			$seg_disk     = trailingslashit( $base_dir ) . $running_rel;
			$seg_manifest = class_exists( 'Folder_Gallery_Manifest' )
				? Folder_Gallery_Manifest::read_raw_manifest( $seg_disk )
				: array();
			$seg_label    = ! empty( $seg_manifest['title'] ) ? $seg_manifest['title'] : $segment;

			$crumbs[] = array(
				'label'   => $seg_label,
				'path'    => $running_rel,
				'url'     => self::build_folder_url( $query_key, $running_rel, $root_rel ),
				'current' => ( $index === $total - 1 ),
			);
		}

		return $crumbs;
	}
}