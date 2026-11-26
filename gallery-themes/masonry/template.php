<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$breadcrumbs    = isset( $gallery_data['breadcrumbs'] ) && is_array( $gallery_data['breadcrumbs'] ) ? $gallery_data['breadcrumbs'] : array();
$current_folder = isset( $gallery_data['current_folder'] ) && is_array( $gallery_data['current_folder'] ) ? $gallery_data['current_folder'] : array();
$folders        = isset( $gallery_data['folders'] ) && is_array( $gallery_data['folders'] ) ? $gallery_data['folders'] : array();
$images         = isset( $gallery_data['images'] ) && is_array( $gallery_data['images'] ) ? $gallery_data['images'] : array();
$theme_settings = isset( $gallery_data['theme_settings'] ) && is_array( $gallery_data['theme_settings'] ) ? $gallery_data['theme_settings'] : array();

$fg_cols   = isset( $theme_settings['columns'] ) ? max( 1, min( 6, (int) $theme_settings['columns'] ) ) : 4;
$fg_gap    = isset( $theme_settings['gap'] ) ? max( 0, min( 64, (int) $theme_settings['gap'] ) ) : 16;
$fg_radius = isset( $theme_settings['radius'] ) ? max( 0, min( 40, (int) $theme_settings['radius'] ) ) : 10;

$folder_title  = ! empty( $current_folder['title'] ) ? $current_folder['title'] : '';
$folder_desc   = ! empty( $current_folder['description'] ) ? $current_folder['description'] : '';
$has_header    = ( '' !== $folder_title ) || ( '' !== $folder_desc );
$has_items     = ( ! empty( $folders ) ) || ( ! empty( $images ) );
$lightbox_slug = isset( $gallery_data['lightbox'] ) ? sanitize_key( (string) $gallery_data['lightbox'] ) : '';
$group_id      = isset( $gallery_data['group_id'] ) ? sanitize_key( (string) $gallery_data['group_id'] ) : '';
$has_lightbox  = '' !== $lightbox_slug && class_exists( 'FG_Lightbox_Theme_Manager' );
?>
<div
	class="fg-gallery fg-theme-masonry"
	<?php if ( '' !== $group_id ) : ?>data-fg-gallery-id="<?php echo esc_attr( $group_id ); ?>"<?php endif; ?>
	<?php if ( '' !== $lightbox_slug ) : ?>data-fg-lightbox="<?php echo esc_attr( $lightbox_slug ); ?>"<?php endif; ?>
	style="--fg-masonry-cols: <?php echo esc_attr( (string) $fg_cols ); ?>; --fg-masonry-gap: <?php echo esc_attr( (string) $fg_gap ); ?>px; --fg-masonry-radius: <?php echo esc_attr( (string) $fg_radius ); ?>px;"
>
	<?php if ( ! empty( $breadcrumbs ) ) : ?>
		<nav class="fg-theme-masonry__breadcrumbs" aria-label="<?php esc_attr_e( 'Gallery navigation', 'folder-gallery' ); ?>">
			<ol class="fg-theme-masonry__breadcrumb-list">
				<?php foreach ( $breadcrumbs as $crumb ) : ?>
					<li class="fg-theme-masonry__breadcrumb-item">
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

	<?php if ( $has_header ) : ?>
		<header class="fg-theme-masonry__header">
			<?php if ( '' !== $folder_title ) : ?>
				<h2 class="fg-theme-masonry__heading"><?php echo esc_html( $folder_title ); ?></h2>
			<?php endif; ?>
			<?php if ( '' !== $folder_desc ) : ?>
				<div class="fg-theme-masonry__description"><?php echo wp_kses_post( $folder_desc ); ?></div>
			<?php endif; ?>
		</header>
	<?php endif; ?>

	<?php if ( $has_items ) : ?>
		<div class="fg-theme-masonry__grid" data-fg-masonry="">
			<div class="fg-theme-masonry__sizer"></div>
			<div class="fg-theme-masonry__gutter"></div>

			<?php foreach ( $folders as $folder ) : ?>
				<?php
				$sub_label = ! empty( $folder['title'] ) ? $folder['title'] : $folder['name'];
				$sub_desc  = ! empty( $folder['description'] ) ? $folder['description'] : '';
				?>
				<article class="fg-theme-masonry__item fg-theme-masonry__item--folder">
					<a class="fg-theme-masonry__card fg-theme-masonry__card--folder" href="<?php echo esc_url( $folder['url'] ); ?>">
						<div class="fg-theme-masonry__folder-visual" aria-hidden="true">
							<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
								<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
							</svg>
							<span class="fg-theme-masonry__folder-badge"><?php esc_html_e( 'Folder', 'folder-gallery' ); ?></span>
						</div>

						<div class="fg-theme-masonry__caption">
							<h3 class="fg-theme-masonry__title"><?php echo esc_html( $sub_label ); ?></h3>
							<?php if ( '' !== $sub_desc ) : ?>
								<div class="fg-theme-masonry__item-desc"><?php echo wp_kses_post( $sub_desc ); ?></div>
							<?php endif; ?>
						</div>
					</a>
				</article>
			<?php endforeach; ?>

			<?php foreach ( $images as $image ) : ?>
				<?php
				$display_src = ! empty( $image['variants']['medium']['url'] )
					? $image['variants']['medium']['url']
					: ( ! empty( $image['variants']['thumbnail']['url'] ) ? $image['variants']['thumbnail']['url'] : $image['url'] );
				$alt_text       = ! empty( $image['title'] ) ? $image['title'] : $image['name'];
				$img_title      = ! empty( $image['title'] ) ? $image['title'] : '';
				$img_desc       = ! empty( $image['description'] ) ? $image['description'] : '';
				$has_caption    = ( '' !== $img_title ) || ( '' !== $img_desc );
				$lightbox_attrs = $has_lightbox
					? FG_Lightbox_Theme_Manager::get_link_attributes( $image, $gallery_data )
					: '';
				?>
				<article class="fg-theme-masonry__item">
					<a
						class="fg-theme-masonry__card"
						href="<?php echo esc_url( $image['url'] ); ?>"
						target="_blank"
						rel="noopener noreferrer"
						<?php echo $lightbox_attrs; // Escaped inside FG_Lightbox_Theme_Manager::get_link_attributes(). ?>
					>
						<div class="fg-theme-masonry__media">
							<img class="fg-theme-masonry__img" src="<?php echo esc_url( $display_src ); ?>" alt="<?php echo esc_attr( $alt_text ); ?>" loading="lazy" />
						</div>

						<?php if ( $has_caption ) : ?>
							<div class="fg-theme-masonry__caption">
								<?php if ( '' !== $img_title ) : ?>
									<h3 class="fg-theme-masonry__title"><?php echo esc_html( $img_title ); ?></h3>
								<?php endif; ?>
								<?php if ( '' !== $img_desc ) : ?>
									<div class="fg-theme-masonry__item-desc"><?php echo wp_kses_post( $img_desc ); ?></div>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</a>
				</article>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<p class="fg-theme-masonry__empty"><?php esc_html_e( 'This folder is empty.', 'folder-gallery' ); ?></p>
	<?php endif; ?>
</div>