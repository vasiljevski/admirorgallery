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
	 * Register shortcode hook.
	 */
	public function init() {
		add_shortcode( 'folder_gallery', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Render [folder_gallery path="..."] as a plain debug/test table.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string
	 */
	public function render_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'path' => '',
			),
			$atts,
			'folder_gallery'
		);

		$base_dir = Folder_Gallery_Storage::get_base_dir();
		$base_url = Folder_Gallery_Storage::get_base_url();

		if ( is_wp_error( $base_dir ) || is_wp_error( $base_url ) ) {
			return '<p>' . esc_html__( 'Gallery storage is not available.', 'folder-gallery' ) . '</p>';
		}

		$relative_path = trim( str_replace( '\\', '/', (string) $atts['path'] ), '/' );
		$target_dir    = '' === $relative_path ? $base_dir : trailingslashit( $base_dir ) . $relative_path;
		$target_url    = '' === $relative_path ? $base_url : trailingslashit( $base_url ) . $relative_path;

		$real_base   = realpath( $base_dir );
		$real_target = realpath( $target_dir );

		if (
			false === $real_base ||
			false === $real_target ||
			( $real_target !== $real_base && 0 !== strpos( $real_target, $real_base . DIRECTORY_SEPARATOR ) ) ||
			! is_dir( $real_target )
		) {
			return '<p>' . esc_html__( 'Invalid gallery folder path.', 'folder-gallery' ) . '</p>';
		}

		$items = @scandir( $real_target );
		if ( false === $items ) {
			return '<p>' . esc_html__( 'Could not read gallery folder.', 'folder-gallery' ) . '</p>';
		}

		$manifest     = class_exists( 'Folder_Gallery_Manifest' )
			? Folder_Gallery_Manifest::read_raw_manifest( $real_target )
			: array( 'images' => array() );
		$allowed_tags = class_exists( 'Folder_Gallery_Manifest' )
			? Folder_Gallery_Manifest::get_allowed_description_tags()
			: wp_kses_allowed_html( 'post' );

		$folders = array();
		$images  = array();

		foreach ( $items as $item ) {
			if ( '.' === $item[0] || 'manifest.json' === $item ) {
				continue;
			}

			$full_path = $real_target . DIRECTORY_SEPARATOR . $item;

			if ( is_dir( $full_path ) ) {
				$sub_manifest = class_exists( 'Folder_Gallery_Manifest' )
					? Folder_Gallery_Manifest::read_raw_manifest( $full_path )
					: array();

				$folders[] = array(
					'name'        => $item,
					'title'       => isset( $sub_manifest['title'] ) ? $sub_manifest['title'] : '',
					'description' => isset( $sub_manifest['description'] ) ? $sub_manifest['description'] : '',
				);
			} elseif ( is_file( $full_path ) ) {
				$ext = strtolower( pathinfo( $item, PATHINFO_EXTENSION ) );
				if ( ! in_array( $ext, self::$allowed_extensions, true ) ) {
					continue;
				}

				$img_meta = isset( $manifest['images'][ $item ] ) && is_array( $manifest['images'][ $item ] )
					? $manifest['images'][ $item ]
					: array();

				$images[] = array(
					'name'        => $item,
					'url'         => trailingslashit( $target_url ) . rawurlencode( $item ),
					'title'       => isset( $img_meta['title'] ) ? $img_meta['title'] : '',
					'description' => isset( $img_meta['description'] ) ? $img_meta['description'] : '',
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

		if ( empty( $folders ) && empty( $images ) ) {
			return '<p>' . esc_html__( 'This gallery folder is empty.', 'folder-gallery' ) . '</p>';
		}

		ob_start();
		?>
		<div class="fg-frontend-table-wrap">
			<table class="fg-frontend-table" border="1" cellpadding="8" cellspacing="0" style="width:100%;border-collapse:collapse;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Thumbnail', 'folder-gallery' ); ?></th>
						<th><?php esc_html_e( 'Name', 'folder-gallery' ); ?></th>
						<th><?php esc_html_e( 'Title', 'folder-gallery' ); ?></th>
						<th><?php esc_html_e( 'Description', 'folder-gallery' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $folders as $folder ) : ?>
						<tr>
							<td><?php esc_html_e( 'Folder', 'folder-gallery' ); ?></td>
							<td><?php echo esc_html( $folder['name'] ); ?></td>
							<td><?php echo esc_html( $folder['title'] ); ?></td>
							<td><?php echo wp_kses( $folder['description'], $allowed_tags ); ?></td>
						</tr>
					<?php endforeach; ?>

					<?php foreach ( $images as $image ) : ?>
						<tr>
							<td>
								<img
									src="<?php echo esc_url( $image['url'] ); ?>"
									alt="<?php echo esc_attr( $image['name'] ); ?>"
									style="max-width:96px;height:auto;display:block;"
									loading="lazy"
								/>
							</td>
							<td><?php echo esc_html( $image['name'] ); ?></td>
							<td><?php echo esc_html( $image['title'] ); ?></td>
							<td><?php echo wp_kses( $image['description'], $allowed_tags ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
		return (string) ob_get_clean();
	}
}