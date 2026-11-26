<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Folder_Gallery_Theme_Manager {

	const OPTION_ACTIVE_THEME = 'folder_gallery_active_theme';

	/**
	 * Get the base filesystem path to the themes directory.
	 *
	 * @return string
	 */
	public static function get_themes_dir() {
		return trailingslashit( FOLDER_GALLERY_PATH ) . 'themes';
	}

	/**
	 * Get the base URL to the themes directory.
	 *
	 * @return string
	 */
	public static function get_themes_url() {
		return trailingslashit( FOLDER_GALLERY_URL ) . 'themes';
	}

	/**
	 * Discover all installed gallery themes inside /themes/.
	 *
	 * @return array<string, array>
	 */
	public static function get_available_themes() {
		$themes_dir = self::get_themes_dir();
		if ( ! is_dir( $themes_dir ) ) {
			return array();
		}

		$entries = @scandir( $themes_dir );
		if ( false === $entries ) {
			return array();
		}

		$themes = array();

		foreach ( $entries as $entry ) {
			if ( '.' === $entry[0] ) {
				continue;
			}

			$slug      = sanitize_key( $entry );
			$theme_dir = trailingslashit( $themes_dir ) . $entry;
			$template  = trailingslashit( $theme_dir ) . 'template.php';

			if ( ! is_dir( $theme_dir ) || ! is_file( $template ) ) {
				continue;
			}

			$meta = array(
				'slug'        => $slug,
				'name'        => ucwords( str_replace( array( '-', '_' ), ' ', $slug ) ),
				'description' => '',
				'version'     => '1.0.0',
				'author'      => '',
			);

			$json_path = trailingslashit( $theme_dir ) . 'theme.json';
			if ( is_file( $json_path ) ) {
				$raw     = @file_get_contents( $json_path );
				$decoded = is_string( $raw ) ? json_decode( $raw, true ) : null;
				if ( is_array( $decoded ) ) {
					if ( ! empty( $decoded['name'] ) ) {
						$meta['name'] = sanitize_text_field( $decoded['name'] );
					}
					if ( isset( $decoded['description'] ) ) {
						$meta['description'] = sanitize_text_field( $decoded['description'] );
					}
					if ( ! empty( $decoded['version'] ) ) {
						$meta['version'] = sanitize_text_field( $decoded['version'] );
					}
					if ( ! empty( $decoded['author'] ) ) {
						$meta['author'] = sanitize_text_field( $decoded['author'] );
					}
				}
			}

			$meta['dir']          = $theme_dir;
			$meta['url']          = trailingslashit( self::get_themes_url() ) . rawurlencode( $entry );
			$meta['template']     = $template;
			$meta['has_style']    = is_file( trailingslashit( $theme_dir ) . 'style.css' );
			$meta['has_script']   = is_file( trailingslashit( $theme_dir ) . 'script.js' );
			$themes[ $slug ]      = $meta;
		}

		return $themes;
	}

	/**
	 * Resolve the active theme (or optional shortcode override), falling back to the single/first available theme.
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

		$saved_slug = sanitize_key( (string) get_option( self::OPTION_ACTIVE_THEME, '' ) );
		if ( '' !== $saved_slug && isset( $themes[ $saved_slug ] ) ) {
			return $themes[ $saved_slug ];
		}

		if ( isset( $themes['simple'] ) ) {
			return $themes['simple'];
		}

		return reset( $themes );
	}

	/**
	 * Enqueue theme CSS/JS if present and render its template.php with standardized $gallery_data.
	 *
	 * @param array  $gallery_data   Standardized gallery payload.
	 * @param string $requested_slug Optional shortcode theme override.
	 * @return string
	 */
	public static function render( array $gallery_data, $requested_slug = '' ) {
		$theme = self::resolve_theme( $requested_slug );
		if ( null === $theme ) {
			return '<p>' . esc_html__( 'No gallery theme is available.', 'folder-gallery' ) . '</p>';
		}

		$handle = 'folder-gallery-theme-' . $theme['slug'];

		if ( ! empty( $theme['has_style'] ) ) {
			wp_enqueue_style(
				$handle,
				trailingslashit( $theme['url'] ) . 'style.css',
				array(),
				$theme['version']
			);
		}

		if ( ! empty( $theme['has_script'] ) ) {
			wp_enqueue_script(
				$handle,
				trailingslashit( $theme['url'] ) . 'script.js',
				array(),
				$theme['version'],
				true
			);
		}

		$gallery_data['theme'] = array(
			'slug'    => $theme['slug'],
			'name'    => $theme['name'],
			'version' => $theme['version'],
		);

		ob_start();
		include $theme['template'];
		return (string) ob_get_clean();
	}
}
