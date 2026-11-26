<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FG_Lightbox_Theme_Manager {

	const OPTION_ACTIVE_THEME          = 'folder_gallery_active_lightbox_theme';
	const OPTION_THEME_SETTINGS_PREFIX = 'folder_gallery_lightbox_theme_settings_';

	/**
	 * Get the base filesystem path to the lightbox themes directory.
	 *
	 * @return string
	 */
	public static function get_themes_dir() {
		return trailingslashit( FOLDER_GALLERY_PATH ) . 'lightbox-themes';
	}

	/**
	 * Get the base URL to the lightbox themes directory.
	 *
	 * @return string
	 */
	public static function get_themes_url() {
		return trailingslashit( FOLDER_GALLERY_URL ) . 'lightbox-themes';
	}

	/**
	 * Discover all installed lightbox themes inside /lightbox-themes/.
	 *
	 * @return array<string, array>
	 */
	public static function get_available_themes() {
		return FG_Theme_Workspace_Helper::discover_themes(
			self::get_themes_dir(),
			self::get_themes_url()
		);
	}

	/**
	 * Get the active lightbox theme slug.
	 *
	 * @return string
	 */
	public static function get_active_theme_slug() {
		$themes     = self::get_available_themes();
		$saved_slug = sanitize_key( (string) get_option( self::OPTION_ACTIVE_THEME, '' ) );

		if ( '' !== $saved_slug && isset( $themes[ $saved_slug ] ) ) {
			return $saved_slug;
		}

		if ( isset( $themes['default'] ) ) {
			return 'default';
		}

		if ( ! empty( $themes ) ) {
			$first = reset( $themes );
			return isset( $first['slug'] ) ? (string) $first['slug'] : '';
		}

		return '';
	}

	/**
	 * Set the active lightbox theme slug.
	 *
	 * @param string $slug Theme slug.
	 * @return bool
	 */
	public static function set_active_theme( $slug ) {
		$slug   = sanitize_key( (string) $slug );
		$themes = self::get_available_themes();

		if ( '' === $slug || ! isset( $themes[ $slug ] ) ) {
			return false;
		}

		return (bool) update_option( self::OPTION_ACTIVE_THEME, $slug );
	}

	/**
	 * Resolve the active lightbox theme (or optional shortcode override).
	 *
	 * @param string $requested_slug Optional theme slug passed via shortcode attribute.
	 * @return array|null
	 */
	public static function resolve_theme( $requested_slug = '' ) {
		$themes = self::get_available_themes();
		if ( empty( $themes ) ) {
			return null;
		}

		$requested_slug = sanitize_key( (string) $requested_slug );
		if ( '' !== $requested_slug && isset( $themes[ $requested_slug ] ) ) {
			return $themes[ $requested_slug ];
		}

		$active_slug = self::get_active_theme_slug();
		if ( '' !== $active_slug && isset( $themes[ $active_slug ] ) ) {
			return $themes[ $active_slug ];
		}

		return reset( $themes );
	}

	/**
	 * Resolve lightbox theme for a gallery render.
	 * Returns null when explicitly disabled ('none') or when no theme is available.
	 *
	 * @param string $requested_slug Optional shortcode lightbox attribute.
	 * @return array|null
	 */
	public static function resolve_active_for_gallery( $requested_slug = '' ) {
		$raw_requested = strtolower( trim( (string) $requested_slug ) );
		if ( 'none' === $raw_requested || 'off' === $raw_requested || 'false' === $raw_requested ) {
			return null;
		}

		$themes = self::get_available_themes();
		if ( empty( $themes ) ) {
			return null;
		}

		$slug = sanitize_key( $raw_requested );
		if ( '' !== $slug && isset( $themes[ $slug ] ) ) {
			return $themes[ $slug ];
		}

		$active_slug = self::get_active_theme_slug();
		if ( '' !== $active_slug && isset( $themes[ $active_slug ] ) ) {
			return $themes[ $active_slug ];
		}

		return null;
	}

	/**
	 * Get settings schema for a lightbox theme.
	 *
	 * @param string $slug Theme slug.
	 * @return array<string, array>
	 */
	public static function get_theme_settings_schema( $slug ) {
		$slug   = sanitize_key( (string) $slug );
		$themes = self::get_available_themes();

		if ( isset( $themes[ $slug ]['settings_schema'] ) && is_array( $themes[ $slug ]['settings_schema'] ) ) {
			return $themes[ $slug ]['settings_schema'];
		}

		return array();
	}

	/**
	 * Get resolved settings values for a lightbox theme.
	 *
	 * @param string $slug Theme slug.
	 * @return array<string, mixed>
	 */
	public static function get_theme_settings( $slug ) {
		return FG_Theme_Workspace_Helper::get_theme_settings(
			$slug,
			self::get_available_themes(),
			self::OPTION_THEME_SETTINGS_PREFIX
		);
	}

	/**
	 * Save settings values for a lightbox theme.
	 *
	 * @param string $slug Raw theme slug.
	 * @param array  $raw  Raw submitted values.
	 * @return array<string, mixed>
	 */
	public static function update_theme_settings( $slug, array $raw ) {
		return FG_Theme_Workspace_Helper::update_theme_settings(
			$slug,
			$raw,
			self::get_available_themes(),
			self::OPTION_THEME_SETTINGS_PREFIX
		);
	}

	/**
	 * Install a lightbox theme from a ZIP archive.
	 *
	 * @param string $zip_path Uploaded ZIP path.
	 * @return array{ok: bool, slug?: string, error?: string}
	 */
	public static function install_theme_from_zip( $zip_path ) {
		return FG_Theme_Workspace_Helper::install_theme_from_zip(
			$zip_path,
			self::get_themes_dir()
		);
	}

	/**
	 * Delete a non-active lightbox theme.
	 *
	 * @param string $slug Theme slug.
	 * @return array{ok: bool, error?: string}
	 */
	public static function delete_theme( $slug ) {
		return FG_Theme_Workspace_Helper::delete_theme(
			$slug,
			self::get_active_theme_slug(),
			self::get_themes_dir(),
			self::OPTION_THEME_SETTINGS_PREFIX
		);
	}

	/**
	 * Build safe HTML data-* attributes for a gallery image link.
	 * Returns empty string when lightbox is disabled or no theme is active.
	 *
	 * @param array $image        Image item array from $gallery_data['images'].
	 * @param array $gallery_data Gallery payload array.
	 * @return string
	 */
	public static function get_link_attributes( array $image, array $gallery_data = array() ) {
		$active_slug = isset( $gallery_data['lightbox'] ) ? sanitize_key( (string) $gallery_data['lightbox'] ) : '';
		if ( '' === $active_slug ) {
			return '';
		}

		$full_url = isset( $image['url'] ) ? (string) $image['url'] : '';
		if ( '' === $full_url ) {
			return '';
		}

		$title       = isset( $image['title'] ) && '' !== (string) $image['title']
			? (string) $image['title']
			: ( isset( $image['name'] ) ? (string) $image['name'] : '' );
		$description = isset( $image['description'] ) ? (string) $image['description'] : '';
		$group_id    = isset( $gallery_data['group_id'] ) ? (string) $gallery_data['group_id'] : '';

		$attrs = array(
			'data-fg-lightbox-item'        => '1',
			'data-fg-lightbox-src'         => esc_url( $full_url ),
			'data-fg-lightbox-title'       => esc_attr( wp_strip_all_tags( $title ) ),
			'data-fg-lightbox-description' => esc_attr( $description ),
		);

		if ( '' !== $group_id ) {
			$attrs['data-fg-lightbox-group'] = esc_attr( $group_id );
		}

		$parts = array();
		foreach ( $attrs as $name => $val ) {
			$parts[] = $name . '="' . $val . '"';
		}

		return implode( ' ', $parts );
	}

	/**
	 * Build asset URL for a relative path inside a lightbox theme directory.
	 *
	 * @param array  $theme    Theme metadata array.
	 * @param string $rel_path Relative file path inside the theme directory.
	 * @return string
	 */
	private static function build_asset_url( array $theme, $rel_path ) {
		$encoded_parts = array_map( 'rawurlencode', explode( '/', $rel_path ) );
		return trailingslashit( $theme['url'] ) . implode( '/', $encoded_parts );
	}

	/**
	 * Enqueue lightbox theme assets and render its template.php.
	 *
	 * @param array  $gallery_data   Standardized gallery payload.
	 * @param string $requested_slug Optional shortcode lightbox override.
	 * @return string
	 */
	public static function render( array $gallery_data, $requested_slug = '' ) {
		$theme = self::resolve_active_for_gallery( $requested_slug );
		if ( null === $theme ) {
			return '';
		}

		$base_handle = 'folder-gallery-lightbox-' . $theme['slug'];

		if ( ! empty( $theme['styles'] ) ) {
			foreach ( $theme['styles'] as $index => $style_rel ) {
				$handle = 0 === $index ? $base_handle : $base_handle . '-style-' . ( $index + 1 );
				wp_enqueue_style(
					$handle,
					self::build_asset_url( $theme, $style_rel ),
					array(),
					$theme['version']
				);
			}
		}

		if ( ! empty( $theme['scripts'] ) ) {
			foreach ( $theme['scripts'] as $index => $script_rel ) {
				$handle = 0 === $index ? $base_handle : $base_handle . '-script-' . ( $index + 1 );
				wp_enqueue_script(
					$handle,
					self::build_asset_url( $theme, $script_rel ),
					array(),
					$theme['version'],
					true
				);
			}
		}

		$lightbox_data = array(
			'group_id' => isset( $gallery_data['group_id'] ) ? (string) $gallery_data['group_id'] : '',
			'images'   => isset( $gallery_data['images'] ) && is_array( $gallery_data['images'] ) ? $gallery_data['images'] : array(),
			'theme'    => array(
				'slug'    => $theme['slug'],
				'name'    => $theme['name'],
				'version' => $theme['version'],
			),
			'settings' => self::get_theme_settings( $theme['slug'] ),
		);

		ob_start();
		include $theme['template'];
		return (string) ob_get_clean();
	}
}