<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FG_Theme_Workspace_Helper {

	/**
	 * Normalize and validate a relative asset path inside a theme directory.
	 *
	 * @param mixed  $raw_file  Raw filename from theme.json.
	 * @param string $theme_dir Absolute theme directory path.
	 * @return string
	 */
	public static function resolve_theme_asset_file( $raw_file, $theme_dir ) {
		if ( ! is_string( $raw_file ) ) {
			return '';
		}

		$clean = ltrim( str_replace( '\\', '/', trim( $raw_file ) ), '/' );
		if ( '' === $clean || false !== strpos( $clean, "\0" ) ) {
			return '';
		}

		$segments      = explode( '/', $clean );
		$safe_segments = array();
		foreach ( $segments as $segment ) {
			if ( '' === $segment || '.' === $segment || '..' === $segment ) {
				return '';
			}
			if ( 1 !== preg_match( '/^[A-Za-z0-9._-]+$/', $segment ) ) {
				return '';
			}
			$safe_segments[] = $segment;
		}

		$rel_path   = implode( '/', $safe_segments );
		$full_path  = trailingslashit( $theme_dir ) . str_replace( '/', DIRECTORY_SEPARATOR, $rel_path );
		$real_theme = realpath( $theme_dir );
		$real_file  = realpath( $full_path );

		if (
			false === $real_theme ||
			false === $real_file ||
			0 !== strpos( $real_file, $real_theme . DIRECTORY_SEPARATOR ) ||
			! is_file( $real_file )
		) {
			return '';
		}

		return $rel_path;
	}

	/**
	 * Parse an asset list from theme.json with fallback to a default file.
	 *
	 * @param array  $decoded      Decoded theme.json data.
	 * @param string $key          Key in theme.json ('styles' or 'scripts').
	 * @param string $default_file Default fallback filename.
	 * @param string $theme_dir    Absolute theme directory path.
	 * @return string[]
	 */
	public static function parse_theme_assets( array $decoded, $key, $default_file, $theme_dir ) {
		$files = array();

		if ( isset( $decoded[ $key ] ) ) {
			$raw_list = is_array( $decoded[ $key ] ) ? $decoded[ $key ] : array( $decoded[ $key ] );
			foreach ( $raw_list as $raw_item ) {
				$resolved = self::resolve_theme_asset_file( $raw_item, $theme_dir );
				if ( '' !== $resolved && ! in_array( $resolved, $files, true ) ) {
					$files[] = $resolved;
				}
			}
			return $files;
		}

		$default_path = trailingslashit( $theme_dir ) . $default_file;
		if ( is_file( $default_path ) ) {
			$files[] = $default_file;
		}

		return $files;
	}

	/**
	 * Parse theme settings schema from decoded theme.json.
	 *
	 * @param array $decoded Decoded theme.json data.
	 * @return array<string, array>
	 */
	public static function parse_settings_schema( array $decoded ) {
		if ( empty( $decoded['settings'] ) || ! is_array( $decoded['settings'] ) ) {
			return array();
		}

		$schema = array();

		foreach ( $decoded['settings'] as $key => $field ) {
			if ( ! is_string( $key ) || ! is_array( $field ) ) {
				continue;
			}

			$clean_key = sanitize_key( $key );
			if ( '' === $clean_key ) {
				continue;
			}

			$type = isset( $field['type'] ) ? sanitize_key( (string) $field['type'] ) : 'text';
			if ( ! in_array( $type, array( 'number', 'integer', 'boolean', 'select', 'text' ), true ) ) {
				$type = 'text';
			}

			$entry = array(
				'key'         => $clean_key,
				'type'        => $type,
				'label'       => isset( $field['label'] ) ? sanitize_text_field( (string) $field['label'] ) : ucwords( str_replace( array( '-', '_' ), ' ', $clean_key ) ),
				'description' => isset( $field['description'] ) ? sanitize_text_field( (string) $field['description'] ) : '',
				'default'     => isset( $field['default'] ) ? $field['default'] : '',
			);

			if ( in_array( $type, array( 'number', 'integer' ), true ) ) {
				if ( isset( $field['min'] ) && is_numeric( $field['min'] ) ) {
					$entry['min'] = 0 + $field['min'];
				}
				if ( isset( $field['max'] ) && is_numeric( $field['max'] ) ) {
					$entry['max'] = 0 + $field['max'];
				}
				if ( isset( $field['step'] ) && is_numeric( $field['step'] ) && ( 0 + $field['step'] ) > 0 ) {
					$entry['step'] = 0 + $field['step'];
				}
			}

			if ( 'select' === $type ) {
				$options = array();
				if ( isset( $field['options'] ) && is_array( $field['options'] ) ) {
					foreach ( $field['options'] as $opt_key => $opt_val ) {
						if ( is_array( $opt_val ) ) {
							$val   = isset( $opt_val['value'] ) ? sanitize_text_field( (string) $opt_val['value'] ) : '';
							$label = isset( $opt_val['label'] ) ? sanitize_text_field( (string) $opt_val['label'] ) : $val;
							if ( '' !== $val ) {
								$options[ $val ] = $label;
							}
						} elseif ( ! is_int( $opt_key ) ) {
							$val = sanitize_key( (string) $opt_key );
							if ( '' !== $val ) {
								$options[ $val ] = sanitize_text_field( (string) $opt_val );
							}
						} else {
							$val = sanitize_text_field( (string) $opt_val );
							if ( '' !== $val ) {
								$options[ $val ] = $val;
							}
						}
					}
				}
				$entry['options'] = $options;
			}

			$entry['default']    = self::sanitize_setting_value( $entry['default'], $entry );
			$schema[ $clean_key ] = $entry;
		}

		return $schema;
	}

	/**
	 * Sanitize a single setting value against its schema definition.
	 *
	 * @param mixed $value Raw value.
	 * @param array $field Field schema definition.
	 * @return mixed
	 */
	public static function sanitize_setting_value( $value, array $field ) {
		$type = isset( $field['type'] ) ? $field['type'] : 'text';

		if ( 'boolean' === $type ) {
			if ( is_bool( $value ) ) {
				return $value;
			}
			if ( is_string( $value ) ) {
				$lower = strtolower( trim( $value ) );
				if ( in_array( $lower, array( '1', 'true', 'yes', 'on' ), true ) ) {
					return true;
				}
				if ( in_array( $lower, array( '0', 'false', 'no', 'off', '' ), true ) ) {
					return false;
				}
			}
			return (bool) $value;
		}

		if ( in_array( $type, array( 'number', 'integer' ), true ) ) {
			if ( ! is_numeric( $value ) ) {
				return isset( $field['default'] ) ? $field['default'] : 0;
			}
			$num = 'integer' === $type ? (int) round( (float) $value ) : (float) $value;
			if ( isset( $field['min'] ) && $num < $field['min'] ) {
				$num = $field['min'];
			}
			if ( isset( $field['max'] ) && $num > $field['max'] ) {
				$num = $field['max'];
			}
			return 'integer' === $type ? (int) $num : $num;
		}

		if ( 'select' === $type ) {
			$str     = sanitize_text_field( (string) $value );
			$options = isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array();
			if ( isset( $options[ $str ] ) ) {
				return $str;
			}
			return isset( $field['default'] ) ? $field['default'] : '';
		}

		return sanitize_text_field( (string) $value );
	}

	/**
	 * Discover themes inside a given themes directory.
	 *
	 * @param string $themes_dir Base filesystem directory.
	 * @param string $themes_url Base URL.
	 * @return array<string, array>
	 */
	public static function discover_themes( $themes_dir, $themes_url ) {
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

			if ( '' === $slug || ! is_dir( $theme_dir ) || ! is_file( $template ) ) {
				continue;
			}

			$meta = array(
				'slug'        => $slug,
				'name'        => ucwords( str_replace( array( '-', '_' ), ' ', $slug ) ),
				'description' => '',
				'version'     => '1.0.0',
				'author'      => '',
			);

			$decoded   = array();
			$json_path = trailingslashit( $theme_dir ) . 'theme.json';
			if ( is_file( $json_path ) ) {
				$raw        = @file_get_contents( $json_path );
				$json_array = is_string( $raw ) ? json_decode( $raw, true ) : null;
				if ( is_array( $json_array ) ) {
					$decoded = $json_array;
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

			$styles  = self::parse_theme_assets( $decoded, 'styles', 'style.css', $theme_dir );
			$scripts = self::parse_theme_assets( $decoded, 'scripts', 'script.js', $theme_dir );

			$meta['dir']             = $theme_dir;
			$meta['url']             = trailingslashit( $themes_url ) . rawurlencode( $entry );
			$meta['template']        = $template;
			$meta['styles']          = $styles;
			$meta['scripts']         = $scripts;
			$meta['has_style']       = ! empty( $styles );
			$meta['has_script']      = ! empty( $scripts );
			$meta['settings_schema'] = self::parse_settings_schema( $decoded );
			$meta['has_settings']    = ! empty( $meta['settings_schema'] );
			$themes[ $slug ]         = $meta;
		}

		return $themes;
	}

	/**
	 * Get resolved settings for a theme.
	 *
	 * @param string $slug            Theme slug.
	 * @param array  $themes          Available themes map.
	 * @param string $settings_prefix Option prefix.
	 * @return array<string, mixed>
	 */
	public static function get_theme_settings( $slug, array $themes, $settings_prefix ) {
		$slug = sanitize_key( (string) $slug );
		if ( ! isset( $themes[ $slug ]['settings_schema'] ) || ! is_array( $themes[ $slug ]['settings_schema'] ) ) {
			return array();
		}

		$schema = $themes[ $slug ]['settings_schema'];
		if ( empty( $schema ) ) {
			return array();
		}

		$values = array();
		foreach ( $schema as $key => $field ) {
			$values[ $key ] = $field['default'];
		}

		$saved = get_option( $settings_prefix . $slug, array() );
		if ( is_array( $saved ) ) {
			foreach ( $schema as $key => $field ) {
				if ( array_key_exists( $key, $saved ) ) {
					$values[ $key ] = self::sanitize_setting_value( $saved[ $key ], $field );
				}
			}
		}

		return $values;
	}

	/**
	 * Sanitize and save theme settings.
	 *
	 * @param string $slug            Theme slug.
	 * @param array  $raw             Submitted values.
	 * @param array  $themes          Available themes map.
	 * @param string $settings_prefix Option prefix.
	 * @return array<string, mixed>
	 */
	public static function update_theme_settings( $slug, array $raw, array $themes, $settings_prefix ) {
		$slug = sanitize_key( (string) $slug );
		if ( ! isset( $themes[ $slug ]['settings_schema'] ) || ! is_array( $themes[ $slug ]['settings_schema'] ) ) {
			return array();
		}

		$schema = $themes[ $slug ]['settings_schema'];
		if ( empty( $schema ) ) {
			return array();
		}

		$clean = array();
		foreach ( $schema as $key => $field ) {
			$submitted     = array_key_exists( $key, $raw ) ? $raw[ $key ] : $field['default'];
			$clean[ $key ] = self::sanitize_setting_value( $submitted, $field );
		}

		update_option( $settings_prefix . $slug, $clean );
		return $clean;
	}

	/**
	 * Install a theme from a ZIP archive containing a single root folder (e.g. masonry/theme.json).
	 *
	 * @param string $zip_path   Uploaded ZIP file path.
	 * @param string $themes_dir Target themes directory.
	 * @return array{ok: bool, slug?: string, error?: string}
	 */
	public static function install_theme_from_zip( $zip_path, $themes_dir ) {
		if ( ! class_exists( 'ZipArchive' ) ) {
			return array(
				'ok'    => false,
				'error' => __( 'ZipArchive extension is not available on the server.', 'folder-gallery' ),
			);
		}

		if ( ! is_file( $zip_path ) || ! is_readable( $zip_path ) ) {
			return array(
				'ok'    => false,
				'error' => __( 'Uploaded ZIP file could not be read.', 'folder-gallery' ),
			);
		}

		if ( ! wp_mkdir_p( $themes_dir ) ) {
			return array(
				'ok'    => false,
				'error' => __( 'Target themes directory could not be created.', 'folder-gallery' ),
			);
		}

		$zip    = new ZipArchive();
		$opened = $zip->open( $zip_path );
		if ( true !== $opened ) {
			return array(
				'ok'    => false,
				'error' => __( 'Invalid or corrupted ZIP archive.', 'folder-gallery' ),
			);
		}

		$root_folders = array();
		$has_json     = false;
		$has_template = false;

		for ( $i = 0; $i < $zip->numFiles; $i++ ) {
			$entry_name = $zip->getNameIndex( $i );
			if ( ! is_string( $entry_name ) || '' === $entry_name ) {
				continue;
			}

			$normalized = str_replace( '\\', '/', $entry_name );
			if ( false !== strpos( $normalized, "\0" ) || false !== strpos( $normalized, '../' ) || '..' === $normalized ) {
				$zip->close();
				return array(
					'ok'    => false,
					'error' => __( 'ZIP archive contains unsafe paths.', 'folder-gallery' ),
				);
			}

			$trimmed = trim( $normalized, '/' );
			if ( '' === $trimmed ) {
				continue;
			}

			$parts = explode( '/', $trimmed );
			$top   = $parts[0];
			if ( '' === $top || '.' === $top || '__MACOSX' === $top || '.DS_Store' === $top ) {
				continue;
			}

			$root_folders[ $top ] = true;

			if ( count( $parts ) === 2 && 'theme.json' === $parts[1] ) {
				$has_json = true;
			}
			if ( count( $parts ) === 2 && 'template.php' === $parts[1] ) {
				$has_template = true;
			}
		}

		if ( 1 !== count( $root_folders ) ) {
			$zip->close();
			return array(
				'ok'    => false,
				'error' => __( 'ZIP archive must contain a single theme folder (for example: masonry/theme.json).', 'folder-gallery' ),
			);
		}

		$raw_folder_name = (string) key( $root_folders );
		$slug            = sanitize_key( $raw_folder_name );
		if ( '' === $slug || $slug !== $raw_folder_name ) {
			$zip->close();
			return array(
				'ok'    => false,
				'error' => __( 'Theme folder name must contain only lowercase letters, numbers, or underscores.', 'folder-gallery' ),
			);
		}

		if ( ! $has_json || ! $has_template ) {
			$zip->close();
			return array(
				'ok'    => false,
				'error' => __( 'Theme folder must include theme.json and template.php.', 'folder-gallery' ),
			);
		}

		$real_themes_dir = realpath( $themes_dir );
		if ( false === $real_themes_dir ) {
			$zip->close();
			return array(
				'ok'    => false,
				'error' => __( 'Target themes directory is invalid.', 'folder-gallery' ),
			);
		}

		$target_dir = $real_themes_dir . DIRECTORY_SEPARATOR . $slug;
		if ( file_exists( $target_dir ) ) {
			$zip->close();
			return array(
				'ok'    => false,
				'error' => __( 'A theme with this folder name already exists.', 'folder-gallery' ),
			);
		}

		$extracted = $zip->extractTo( $real_themes_dir );
		$zip->close();

		if ( ! $extracted || ! is_dir( $target_dir ) ) {
			return array(
				'ok'    => false,
				'error' => __( 'Failed to extract theme archive.', 'folder-gallery' ),
			);
		}

		// Remove macOS metadata folder if accidentally extracted inside target.
		$macosx_dir = $target_dir . DIRECTORY_SEPARATOR . '__MACOSX';
		if ( is_dir( $macosx_dir ) ) {
			self::delete_directory_recursive( $macosx_dir );
		}

		return array(
			'ok'   => true,
			'slug' => $slug,
		);
	}

	/**
	 * Delete a non-active theme directory and its saved settings option.
	 *
	 * @param string $slug            Theme slug to delete.
	 * @param string $active_slug     Currently active theme slug.
	 * @param string $themes_dir      Themes base directory.
	 * @param string $settings_prefix Theme settings option prefix.
	 * @return array{ok: bool, error?: string}
	 */
	public static function delete_theme( $slug, $active_slug, $themes_dir, $settings_prefix ) {
		$slug        = sanitize_key( (string) $slug );
		$active_slug = sanitize_key( (string) $active_slug );

		if ( '' === $slug ) {
			return array(
				'ok'    => false,
				'error' => __( 'Invalid theme selected.', 'folder-gallery' ),
			);
		}

		if ( $slug === $active_slug ) {
			return array(
				'ok'    => false,
				'error' => __( 'Cannot delete the currently active theme.', 'folder-gallery' ),
			);
		}

		$real_themes_dir = realpath( $themes_dir );
		if ( false === $real_themes_dir ) {
			return array(
				'ok'    => false,
				'error' => __( 'Themes directory not found.', 'folder-gallery' ),
			);
		}

		$target_dir = realpath( $real_themes_dir . DIRECTORY_SEPARATOR . $slug );
		if (
			false === $target_dir ||
			0 !== strpos( $target_dir, $real_themes_dir . DIRECTORY_SEPARATOR ) ||
			! is_dir( $target_dir )
		) {
			return array(
				'ok'    => false,
				'error' => __( 'Theme folder does not exist.', 'folder-gallery' ),
			);
		}

		if ( ! self::delete_directory_recursive( $target_dir ) ) {
			return array(
				'ok'    => false,
				'error' => __( 'Failed to remove theme directory.', 'folder-gallery' ),
			);
		}

		delete_option( $settings_prefix . $slug );

		return array(
			'ok' => true,
		);
	}

	/**
	 * Recursively remove a directory inside a verified path.
	 *
	 * @param string $dir Directory path.
	 * @return bool
	 */
	public static function delete_directory_recursive( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return false;
		}

		$items = @scandir( $dir );
		if ( false === $items ) {
			return false;
		}

		foreach ( $items as $item ) {
			if ( '.' === $item || '..' === $item ) {
				continue;
			}
			$path = $dir . DIRECTORY_SEPARATOR . $item;
			if ( is_dir( $path ) ) {
				if ( ! self::delete_directory_recursive( $path ) ) {
					return false;
				}
			} elseif ( ! @unlink( $path ) ) {
				return false;
			}
		}

		return @rmdir( $dir );
	}
}