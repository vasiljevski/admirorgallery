<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FG_Gallery_Theme_Manager {

	const OPTION_ACTIVE_THEME          = 'folder_gallery_active_gallery_theme';
	const OPTION_THEME_SETTINGS_PREFIX = 'folder_gallery_gallery_theme_settings_';

	/**
	 * Get the base filesystem path to the gallery themes directory.
	 *
	 * @return string
	 */
	public static function get_themes_dir() {
		return trailingslashit( FOLDER_GALLERY_PATH ) . 'gallery-themes';
	}

	/**
	 * Get the base URL to the gallery themes directory.
	 *
	 * @return string
	 */
	public static function get_themes_url() {
		return trailingslashit( FOLDER_GALLERY_URL ) . 'gallery-themes';
	}

	/**
	 * Discover all installed gallery themes inside /gallery-themes/.
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
	 * Get the active gallery theme slug.
	 *
	 * @return string
	 */
	public static function get_active_theme_slug() {
		$themes     = self::get_available_themes();
		$saved_slug = sanitize_key( (string) get_option( self::OPTION_ACTIVE_THEME, '' ) );

		if ( '' !== $saved_slug && isset( $themes[ $saved_slug ] ) ) {
			return $saved_slug;
		}

		if ( isset( $themes['simple'] ) ) {
			return 'simple';
		}

		if ( ! empty( $themes ) ) {
			$first = reset( $themes );
			return isset( $first['slug'] ) ? (string) $first['slug'] : '';
		}

		return '';
	}

	/**
	 * Set the active gallery theme slug.
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
	 * Resolve the active theme (or optional shortcode override).
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
	 * Get settings schema for a gallery theme.
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
	 * Get resolved settings values for a gallery theme.
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
	 * Save settings values for a gallery theme.
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
	 * Install a gallery theme from a ZIP archive.
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
	 * Delete a non-active gallery theme.
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
	 * Build asset URL for a relative path inside a theme directory.
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
	 * Enqueue theme CSS/JS if present and render its template.php with standardized $gallery_data.
	 *
	 * @param array  $gallery_data   Standardized gallery payload.
	 * @param string $requested_slug Optional shortcode gallery theme override.
	 * @return string
	 */
	public static function render( array $gallery_data, $requested_slug = '' ) {
		$theme = self::resolve_theme( $requested_slug );
		if ( null === $theme ) {
			return '<p>' . esc_html__( 'No gallery theme is available.', 'folder-gallery' ) . '</p>';
		}

		$base_handle = 'folder-gallery-theme-' . $theme['slug'];

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

		$gallery_data['theme'] = array(
			'slug'    => $theme['slug'],
			'name'    => $theme['name'],
			'version' => $theme['version'],
		);

		$gallery_data['theme_settings'] = self::get_theme_settings( $theme['slug'] );

		$requested_lightbox = isset( $gallery_data['requested_lightbox'] ) ? (string) $gallery_data['requested_lightbox'] : '';
		$lightbox_theme     = class_exists( 'FG_Lightbox_Theme_Manager' )
			? FG_Lightbox_Theme_Manager::resolve_active_for_gallery( $requested_lightbox )
			: null;

		$gallery_data['lightbox']          = null !== $lightbox_theme ? $lightbox_theme['slug'] : '';
		$gallery_data['lightbox_settings'] = ( null !== $lightbox_theme && class_exists( 'FG_Lightbox_Theme_Manager' ) )
			? FG_Lightbox_Theme_Manager::get_theme_settings( $lightbox_theme['slug'] )
			: array();

		ob_start();
		include $theme['template'];
		$gallery_html = (string) ob_get_clean();

		if ( null !== $lightbox_theme && class_exists( 'FG_Lightbox_Theme_Manager' ) ) {
			$gallery_html .= FG_Lightbox_Theme_Manager::render( $gallery_data, $requested_lightbox );
		}

		return $gallery_html;
	}
}