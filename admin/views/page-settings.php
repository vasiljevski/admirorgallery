<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$small_height  = FG_Image_Sizes::get_small_height();
$medium_height = FG_Image_Sizes::get_medium_height();
$max_image_mp  = FG_Image_Sizes::get_max_image_mp();
$mp_options    = FG_Image_Sizes::get_allowed_max_mp_options();
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
				<tr>
					<th scope="row">
						<label for="fg-max-image-mp"><?php esc_html_e( 'Large Image Limit (MP)', 'folder-gallery' ); ?></label>
					</th>
					<td>
						<select id="fg-max-image-mp" name="max_image_mp">
							<?php foreach ( $mp_options as $mp_val ) : ?>
								<option value="<?php echo esc_attr( (int) $mp_val ); ?>" <?php selected( (int) $max_image_mp, (int) $mp_val ); ?>>
									<?php
									printf(
										/* translators: %d: megapixels */
										esc_html__( '%d MP', 'folder-gallery' ),
										(int) $mp_val
									);
									?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description">
							<?php
							printf(
								/* translators: %d: default megapixels */
								esc_html__( 'Images larger than this resolution are skipped when generating small and medium variants to prevent PHP memory exhaustion (default: %d MP).', 'folder-gallery' ),
								(int) FG_Image_Sizes::DEFAULT_MAX_IMAGE_MP
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
