<?php
/**
 * Plain Table theme template for Folder Gallery (reference custom theme example).
 *
 * Available variables in template scope:
 * - $gallery_data (array):
 *   - 'group_id'    (string): unique gallery group identifier
 *   - 'lightbox'    (string): active lightbox theme slug or empty string
 *   - 'breadcrumbs' (array[]): list of ['label' => string, 'url' => string, 'current' => bool]
 *   - 'folders'     (array[]): list of ['name' => string, 'title' => string, 'description' => string, 'url' => string]
 *   - 'images'      (array[]): list of [
 *       'name'        => string,
 *       'title'       => string,
 *       'description' => string,
 *       'url'         => string, // Full-size image URL
 *       'variants'    => array,  // Optional ['small' => ['exists' => bool, 'url' => string, ...], 'medium' => [...]]
 *     ]
 *
 * Variant usage pattern:
 * Prefer $image['variants']['small']['url'] when available, and always fall back to $image['url'].
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$breadcrumbs    = isset( $gallery_data['breadcrumbs'] ) && is_array( $gallery_data['breadcrumbs'] ) ? $gallery_data['breadcrumbs'] : array();
$folders        = isset( $gallery_data['folders'] ) && is_array( $gallery_data['folders'] ) ? $gallery_data['folders'] : array();
$images         = isset( $gallery_data['images'] ) && is_array( $gallery_data['images'] ) ? $gallery_data['images'] : array();
$lightbox_slug  = isset( $gallery_data['lightbox'] ) ? sanitize_key( (string) $gallery_data['lightbox'] ) : '';
$group_id       = isset( $gallery_data['group_id'] ) ? sanitize_key( (string) $gallery_data['group_id'] ) : '';
$has_lightbox   = '' !== $lightbox_slug && class_exists( 'FG_Lightbox_Theme_Manager' );
?>
<div
	class="fg-gallery fg-theme-simple"
	<?php if ( '' !== $group_id ) : ?>data-fg-gallery-id="<?php echo esc_attr( $group_id ); ?>"<?php endif; ?>
	<?php if ( '' !== $lightbox_slug ) : ?>data-fg-lightbox="<?php echo esc_attr( $lightbox_slug ); ?>"<?php endif; ?>
>
	<?php if ( ! empty( $breadcrumbs ) ) : ?>
		<nav class="fg-theme-simple__breadcrumbs" aria-label="<?php esc_attr_e( 'Gallery navigation', 'folder-gallery' ); ?>">
			<ol class="fg-theme-simple__breadcrumb-list">
				<?php foreach ( $breadcrumbs as $crumb ) : ?>
					<li class="fg-theme-simple__breadcrumb-item">
						<?php if ( ! empty( $crumb['current'] ) ) : ?>
							<span><?php echo esc_html( $crumb['label'] ); ?></span>
						<?php else : ?>
							<a href="<?php echo esc_url( $crumb['url'] ); ?>"><?php echo esc_html( $crumb['label'] ); ?></a>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ol>
		</nav>
	<?php endif; ?>

	<?php if ( empty( $folders ) && empty( $images ) ) : ?>
		<p class="fg-theme-simple__empty"><?php esc_html_e( 'This gallery folder is empty.', 'folder-gallery' ); ?></p>
	<?php else : ?>
		<table class="fg-theme-simple__table">
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
						<td>
							<span class="fg-theme-simple__folder-badge"><?php esc_html_e( 'Folder', 'folder-gallery' ); ?></span>
						</td>
						<td>
							<a href="<?php echo esc_url( $folder['url'] ); ?>">
								<?php echo esc_html( $folder['name'] ); ?>
							</a>
						</td>
						<td><?php echo esc_html( $folder['title'] ); ?></td>
						<td><?php echo $folder['description']; // Already sanitized via wp_kses in shortcode presenter. ?></td>
					</tr>
				<?php endforeach; ?>

				<?php foreach ( $images as $image ) : ?>
					<?php
					$full_url  = isset( $image['url'] ) ? $image['url'] : '';
					$small_url = $full_url;

					if (
						isset( $image['variants']['small'] ) &&
						is_array( $image['variants']['small'] ) &&
						! empty( $image['variants']['small']['url'] ) &&
						( ! isset( $image['variants']['small']['exists'] ) || ! empty( $image['variants']['small']['exists'] ) )
					) {
						$small_url = $image['variants']['small']['url'];
					}

					$alt_text       = '' !== $image['title'] ? $image['title'] : $image['name'];
					$lightbox_attrs = $has_lightbox
						? FG_Lightbox_Theme_Manager::get_link_attributes( $image, $gallery_data )
						: '';
					?>
					<tr>
						<td>
							<a
								href="<?php echo esc_url( $full_url ); ?>"
								target="_blank"
								rel="noopener noreferrer"
								<?php echo $lightbox_attrs; // Escaped inside FG_Lightbox_Theme_Manager::get_link_attributes(). ?>
							>
								<img
									class="fg-theme-simple__thumb"
									src="<?php echo esc_url( $small_url ); ?>"
									alt="<?php echo esc_attr( $alt_text ); ?>"
									loading="lazy"
								/>
							</a>
						</td>
						<td>
							<a
								href="<?php echo esc_url( $full_url ); ?>"
								target="_blank"
								rel="noopener noreferrer"
								<?php echo $lightbox_attrs; // Escaped inside FG_Lightbox_Theme_Manager::get_link_attributes(). ?>
							>
								<?php echo esc_html( $image['name'] ); ?>
							</a>
						</td>
						<td><?php echo esc_html( $image['title'] ); ?></td>
						<td><?php echo $image['description']; // Already sanitized via wp_kses in shortcode presenter. ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>