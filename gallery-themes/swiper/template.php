<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$breadcrumbs    = isset( $gallery_data['breadcrumbs'] ) && is_array( $gallery_data['breadcrumbs'] ) ? $gallery_data['breadcrumbs'] : array();
$current_folder = isset( $gallery_data['current_folder'] ) && is_array( $gallery_data['current_folder'] ) ? $gallery_data['current_folder'] : array();
$folders        = isset( $gallery_data['folders'] ) && is_array( $gallery_data['folders'] ) ? $gallery_data['folders'] : array();
$images         = isset( $gallery_data['images'] ) && is_array( $gallery_data['images'] ) ? $gallery_data['images'] : array();
$theme_settings = isset( $gallery_data['theme_settings'] ) && is_array( $gallery_data['theme_settings'] ) ? $gallery_data['theme_settings'] : array();

$raw_height = isset( $theme_settings['height'] ) ? strtolower( trim( (string) $theme_settings['height'] ) ) : 'auto';
if ( '' === $raw_height || 'auto' === $raw_height ) {
	$fg_height = 'auto';
} elseif ( preg_match( '/^\d+(?:\.\d+)?$/', $raw_height ) ) {
	$fg_height = $raw_height . 'px';
} elseif ( preg_match( '/^\d+(?:\.\d+)?(?:px|rem|em|vh|svh|dvh|%)$/', $raw_height ) ) {
	$fg_height = $raw_height;
} else {
	$fg_height = 'auto';
}

$fg_is_fixed_height = ( 'auto' !== $fg_height );
$show_title          = isset( $theme_settings['showTitle'] ) ? (bool) $theme_settings['showTitle'] : true;
$show_desc           = isset( $theme_settings['showDescription'] ) ? (bool) $theme_settings['showDescription'] : true;
$dots                = isset( $theme_settings['dots'] ) ? (bool) $theme_settings['dots'] : true;
$autoplay            = isset( $theme_settings['autoplay'] ) ? (bool) $theme_settings['autoplay'] : false;

$folder_title = ! empty( $current_folder['title'] ) ? $current_folder['title'] : '';
$folder_desc  = ! empty( $current_folder['description'] ) ? $current_folder['description'] : '';
$has_header   = ( '' !== $folder_title ) || ( '' !== $folder_desc );

$swiper_config = array(
	'dots'       => $dots,
	'autoplay'   => $autoplay,
	'autoHeight' => ! $fg_is_fixed_height,
);

$wrapper_classes = 'fg-gallery fg-theme-swiper' . ( $fg_is_fixed_height ? ' fg-theme-swiper--fixed-height' : '' );
?>
<div
	class="<?php echo esc_attr( $wrapper_classes ); ?>"
	style="--fg-swiper-height: <?php echo esc_attr( $fg_height ); ?>;"
>
	<?php if ( ! empty( $breadcrumbs ) ) : ?>
		<nav class="fg-theme-swiper__breadcrumbs" aria-label="<?php esc_attr_e( 'Gallery navigation', 'folder-gallery' ); ?>">
			<ol class="fg-theme-swiper__breadcrumb-list">
				<?php foreach ( $breadcrumbs as $crumb ) : ?>
					<li class="fg-theme-swiper__breadcrumb-item">
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
		<header class="fg-theme-swiper__header">
			<?php if ( '' !== $folder_title ) : ?>
				<h2 class="fg-theme-swiper__heading"><?php echo esc_html( $folder_title ); ?></h2>
			<?php endif; ?>
			<?php if ( '' !== $folder_desc ) : ?>
				<div class="fg-theme-swiper__description"><?php echo wp_kses_post( $folder_desc ); ?></div>
			<?php endif; ?>
		</header>
	<?php endif; ?>

	<?php if ( ! empty( $folders ) ) : ?>
		<div class="fg-theme-swiper__folders">
			<?php foreach ( $folders as $folder ) : ?>
				<?php
				$sub_label = ! empty( $folder['title'] ) ? $folder['title'] : $folder['name'];
				$sub_desc  = ! empty( $folder['description'] ) ? $folder['description'] : '';
				?>
				<a class="fg-theme-swiper__folder-chip" href="<?php echo esc_url( $folder['url'] ); ?>">
					<span class="fg-theme-swiper__folder-title"><?php echo esc_html( $sub_label ); ?></span>
					<?php if ( '' !== $sub_desc ) : ?>
						<span class="fg-theme-swiper__folder-desc"><?php echo wp_kses_post( $sub_desc ); ?></span>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $images ) ) : ?>
		<div class="swiper fg-theme-swiper__container" data-fg-swiper="<?php echo esc_attr( wp_json_encode( $swiper_config ) ); ?>">
			<div class="swiper-wrapper">
				<?php foreach ( $images as $image ) : ?>
					<?php
					$img_title = ! empty( $image['title'] ) ? $image['title'] : $image['name'];
					$img_desc  = ! empty( $image['description'] ) ? $image['description'] : '';
					$has_title = $show_title && ( '' !== $img_title );
					$has_desc  = $show_desc && ( '' !== $img_desc );
					?>
					<div class="swiper-slide fg-theme-swiper__slide">
						<figure class="fg-theme-swiper__card">
							<img
								class="fg-theme-swiper__img"
								src="<?php echo esc_url( $image['url'] ); ?>"
								alt="<?php echo esc_attr( $img_title ); ?>"
								loading="lazy"
							/>
							<?php if ( $has_title || $has_desc ) : ?>
								<figcaption class="fg-theme-swiper__caption">
									<?php if ( $has_title ) : ?>
										<h3 class="fg-theme-swiper__title"><?php echo esc_html( $img_title ); ?></h3>
									<?php endif; ?>
									<?php if ( $has_desc ) : ?>
										<div class="fg-theme-swiper__item-desc"><?php echo wp_kses_post( $img_desc ); ?></div>
									<?php endif; ?>
								</figcaption>
							<?php endif; ?>
						</figure>
					</div>
				<?php endforeach; ?>
			</div>

			<?php if ( $dots ) : ?>
				<div class="swiper-pagination"></div>
			<?php endif; ?>

			<div class="swiper-button-prev" aria-label="<?php esc_attr_e( 'Previous slide', 'folder-gallery' ); ?>"></div>
			<div class="swiper-button-next" aria-label="<?php esc_attr_e( 'Next slide', 'folder-gallery' ); ?>"></div>
		</div>
	<?php elseif ( empty( $folders ) ) : ?>
		<p class="fg-theme-swiper__empty"><?php esc_html_e( 'This folder is empty.', 'folder-gallery' ); ?></p>
	<?php endif; ?>
</div>
