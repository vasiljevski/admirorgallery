<?php
/**
 * Plain Table theme template for Folder Gallery.
 *
 * Available variable:
 * - $gallery_data (array)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$breadcrumbs = isset( $gallery_data['breadcrumbs'] ) && is_array( $gallery_data['breadcrumbs'] ) ? $gallery_data['breadcrumbs'] : array();
$folders     = isset( $gallery_data['folders'] ) && is_array( $gallery_data['folders'] ) ? $gallery_data['folders'] : array();
$images      = isset( $gallery_data['images'] ) && is_array( $gallery_data['images'] ) ? $gallery_data['images'] : array();
?>
<div class="fg-gallery fg-theme-plain">
	<?php if ( ! empty( $breadcrumbs ) ) : ?>
		<nav class="fg-theme-plain__breadcrumbs" aria-label="<?php esc_attr_e( 'Gallery navigation', 'folder-gallery' ); ?>">
			<ol class="fg-theme-plain__breadcrumb-list">
				<?php foreach ( $breadcrumbs as $crumb ) : ?>
					<li class="fg-theme-plain__breadcrumb-item">
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
		<p class="fg-theme-plain__empty"><?php esc_html_e( 'This gallery folder is empty.', 'folder-gallery' ); ?></p>
	<?php else : ?>
		<table class="fg-theme-plain__table">
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
							<span class="fg-theme-plain__folder-badge"><?php esc_html_e( 'Folder', 'folder-gallery' ); ?></span>
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
					<tr>
						<td>
							<a href="<?php echo esc_url( $image['url'] ); ?>" target="_blank" rel="noopener noreferrer">
								<img
									class="fg-theme-plain__thumb"
									src="<?php echo esc_url( $image['url'] ); ?>"
									alt="<?php echo esc_attr( '' !== $image['title'] ? $image['title'] : $image['name'] ); ?>"
									loading="lazy"
								/>
							</a>
						</td>
						<td>
							<a href="<?php echo esc_url( $image['url'] ); ?>" target="_blank" rel="noopener noreferrer">
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
