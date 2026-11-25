<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fg_message = isset( $_GET['fg_message'] ) ? sanitize_key( wp_unslash( $_GET['fg_message'] ) ) : '';
?>
<div class="wrap fg-themes-wrap">
	<h1 class="wp-heading-inline">
		<?php echo esc_html( get_admin_page_title() ); ?>
		<span class="title-count theme-count"><?php echo esc_html( (string) count( $themes ) ); ?></span>
	</h1>
	<hr class="wp-header-end">

	<?php if ( 'theme_activated' === $fg_message ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Gallery theme activated.', 'folder-gallery' ); ?></p>
		</div>
	<?php elseif ( 'invalid_theme' === $fg_message ) : ?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'Selected gallery theme could not be found.', 'folder-gallery' ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( empty( $themes ) ) : ?>
		<div class="notice notice-warning">
			<p><?php esc_html_e( 'No gallery themes found in the themes directory.', 'folder-gallery' ); ?></p>
		</div>
	<?php else : ?>
		<div class="fg-themes-grid">
			<?php
			foreach ( $themes as $theme ) :
				$is_active      = ( $theme['slug'] === $active_slug );
				$screenshot_url = '';
				foreach ( array( 'screenshot.png', 'screenshot.jpg', 'screenshot.webp' ) as $shot_file ) {
					if ( is_file( trailingslashit( $theme['dir'] ) . $shot_file ) ) {
						$screenshot_url = trailingslashit( $theme['url'] ) . $shot_file;
						break;
					}
				}
				?>
				<div class="fg-theme-card <?php echo $is_active ? 'is-active' : ''; ?>">
					<div class="fg-theme-card__preview">
						<?php if ( '' !== $screenshot_url ) : ?>
							<img src="<?php echo esc_url( $screenshot_url ); ?>" alt="<?php echo esc_attr( $theme['name'] ); ?>" />
						<?php else : ?>
							<div class="fg-theme-card__placeholder">
								<span class="dashicons dashicons-layout"></span>
								<span class="fg-theme-card__slug"><?php echo esc_html( $theme['slug'] ); ?></span>
							</div>
						<?php endif; ?>
					</div>

					<div class="fg-theme-card__body">
						<div class="fg-theme-card__header">
							<h2 class="fg-theme-card__title">
								<?php if ( $is_active ) : ?>
									<span class="fg-theme-card__active-label"><?php esc_html_e( 'Active:', 'folder-gallery' ); ?></span>
								<?php endif; ?>
								<?php echo esc_html( $theme['name'] ); ?>
							</h2>
							<?php if ( ! empty( $theme['version'] ) ) : ?>
								<span class="fg-theme-card__version">
									<?php
									printf(
										/* translators: %s: theme version */
										esc_html__( 'v%s', 'folder-gallery' ),
										esc_html( $theme['version'] )
									);
									?>
								</span>
							<?php endif; ?>
						</div>

						<?php if ( ! empty( $theme['description'] ) ) : ?>
							<p class="fg-theme-card__desc"><?php echo esc_html( $theme['description'] ); ?></p>
						<?php endif; ?>

						<div class="fg-theme-card__meta">
							<?php if ( ! empty( $theme['author'] ) ) : ?>
								<span class="fg-theme-card__author">
									<?php
									printf(
										/* translators: %s: theme author */
										esc_html__( 'By %s', 'folder-gallery' ),
										esc_html( $theme['author'] )
									);
									?>
								</span>
							<?php endif; ?>
							<code class="fg-theme-card__shortcode">[folder_gallery theme="<?php echo esc_attr( $theme['slug'] ); ?>"]</code>
						</div>
					</div>

					<div class="fg-theme-card__actions">
						<?php if ( $is_active ) : ?>
							<span class="button button-primary disabled" aria-disabled="true">
								<?php esc_html_e( 'Active', 'folder-gallery' ); ?>
							</span>
						<?php else : ?>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
								<input type="hidden" name="action" value="fg_activate_theme" />
								<input type="hidden" name="theme" value="<?php echo esc_attr( $theme['slug'] ); ?>" />
								<?php wp_nonce_field( 'fg_activate_theme_nonce' ); ?>
								<button type="submit" class="button button-secondary">
									<?php esc_html_e( 'Activate', 'folder-gallery' ); ?>
								</button>
							</form>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>