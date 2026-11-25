<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Folder_Gallery_Storage {

	const FOLDER_NAME = 'folder-gallery';

	/**
	 * Vraća apsolutnu putanju na disku do korenskog foldera galerija.
	 *
	 * @return string|WP_Error
	 */
	public static function get_base_dir() {
		$upload_dir = wp_upload_dir();

		if ( ! empty( $upload_dir['error'] ) ) {
			return new WP_Error( 'folder_gallery_upload_dir_error', $upload_dir['error'] );
		}

		return trailingslashit( $upload_dir['basedir'] ) . self::FOLDER_NAME;
	}

	/**
	 * Vraća javni URL do korenskog foldera galerija.
	 *
	 * @return string|WP_Error
	 */
	public static function get_base_url() {
		$upload_dir = wp_upload_dir();

		if ( ! empty( $upload_dir['error'] ) ) {
			return new WP_Error( 'folder_gallery_upload_dir_error', $upload_dir['error'] );
		}

		return trailingslashit( $upload_dir['baseurl'] ) . self::FOLDER_NAME;
	}

	/**
	 * Obezbeđuje da korenski folder i zaštitni index.html postoje.
	 *
	 * @return true|WP_Error
	 */
	public static function ensure_base_dir_exists() {
		$base_dir = self::get_base_dir();

		if ( is_wp_error( $base_dir ) ) {
			return $base_dir;
		}

		if ( ! file_exists( $base_dir ) ) {
			if ( ! wp_mkdir_p( $base_dir ) ) {
				return new WP_Error(
					'folder_gallery_mkdir_failed',
					sprintf(
						/* translators: %s: directory path */
						__( 'Could not create gallery directory: %s', 'folder-gallery' ),
						$base_dir
					)
				);
			}
		}

		$index_file = trailingslashit( $base_dir ) . 'index.html';
		if ( ! file_exists( $index_file ) ) {
			// Prazan index.html sprečava directory listing na serverima gde je uključen.
			@file_put_contents( $index_file, '' );
		}

		return true;
	}
}