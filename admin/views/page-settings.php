<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$small_height  = FG_Image_Sizes::get_small_height();
$medium_height = FG_Image_Sizes::get_medium_height();
$message       = isset( $_GET['fg_message'] ) ? sanitize_key( wp_unslash( $_GET['fg_message'] ) ) : '';
?>
<div class="wrap fg-wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<?php if ( 'settings_saved' === $message ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Settings saved.', 'folder-gallery' ); ?></p>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="fg_save_settings">
		<?php wp_nonce_field( 'fg_save_settings_nonce' ); ?>

		<h2 class="title"><?php esc_html_e( 'Image Sizes', 'folder-gallery' ); ?></h2>
		<p class="description">
			<?php esc_html_e( 'Configure maximum height in pixels (aspect ratio is preserved; smaller images are not upscaled). Changes apply when images are generated or regenerated in a folder.', 'folder-gallery' ); ?>
		</p>

		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">
						<label for="fg-small-height"><?php esc_html_e( 'Small Image Max Height (px)', 'folder-gallery' ); ?></label>
					</th>
					<td>
						<input
							type="number"
							id="fg-small-height"
							name="small_height"
							class="small-text"
							min="50"
							max="4000"
							step="1"
							value="<?php echo esc_attr( (int) $small_height ); ?>"
						>
						<p class="description">
							<?php
							printf(
								/* translators: %d: default height in pixels */
								esc_html__( 'Used for thumbnails and compact views (default: %d px).', 'folder-gallery' ),
								(int) FG_Image_Sizes::DEFAULT_SMALL_HEIGHT
							);
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="fg-medium-height"><?php esc_html_e( 'Medium Image Max Height (px)', 'folder-gallery' ); ?></label>
					</th>
					<td>
						<input
							type="number"
							id="fg-medium-height"
							name="medium_height"
							class="small-text"
							min="50"
							max="4000"
							step="1"
							value="<?php echo esc_attr( (int) $medium_height ); ?>"
						>
						<p class="description">
							<?php
							printf(
								/* translators: %d: default height in pixels */
								esc_html__( 'Used for carousels and intermediate previews (default: %d px).', 'folder-gallery' ),
								(int) FG_Image_Sizes::DEFAULT_MEDIUM_HEIGHT
							);
							?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>

		<?php submit_button( __( 'Save Changes', 'folder-gallery' ) ); ?>
	</form>
</div>