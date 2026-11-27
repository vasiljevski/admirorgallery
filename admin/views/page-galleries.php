<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$date_format  = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
$max_image_mp = FG_Image_Sizes::get_max_image_mp();
?>
<div class="wrap fg-wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<p class="description fg-upload-limit">
		<?php
		printf(
			/* translators: 1: maximum upload file size, e.g. 64 MB, 2: large image megapixel limit, e.g. 12 */
			esc_html__( 'Maximum upload file size: %1$s | Large image resize limit: %2$d MP', 'folder-gallery' ),
			esc_html( size_format( wp_max_upload_size() ) ),
			(int) $max_image_mp
		);
		?>
	</p>

	<?php if ( is_wp_error( $storage_status ) ) : ?>
		<div class="notice notice-error">
			<p><?php echo esc_html( $storage_status->get_error_message() ); ?></p>
		</div>
	<?php elseif ( is_wp_error( $scan_result ) ) : ?>
		<div class="notice notice-error">
			<p><?php echo esc_html( $scan_result->get_error_message() ); ?></p>
		</div>
	<?php else : ?>
		<?php
		$folders = $scan_result['folders'];
		$images  = $scan_result['images'];
		?>

		<input
			type="file"
			id="fg-image-input"
			accept="image/jpeg,image/png,image/webp,image/gif"
			multiple
			hidden
		>
		<input
			type="file"
			id="fg-zip-input"
			accept=".zip,application/zip"
			hidden
		>

		<div class="fg-explorer" id="fg-explorer">

			<!-- Navigation Bar (Back + Breadcrumbs) -->
			<div class="fg-nav-bar">
				<button type="button" class="button fg-nav-back" id="fg-nav-back" title="<?php esc_attr_e( 'Back', 'folder-gallery' ); ?>" disabled>
					<span class="dashicons dashicons-arrow-left-alt" aria-hidden="true"></span>
				</button>

				<nav class="fg-breadcrumbs" aria-label="<?php esc_attr_e( 'Current folder path', 'folder-gallery' ); ?>">
					<span class="dashicons dashicons-admin-home fg-breadcrumb-root-icon" aria-hidden="true"></span>
					<ol class="fg-breadcrumb-list" id="fg-breadcrumb-list">
						<li class="fg-breadcrumb-item is-current">
							<a href="#" data-path=""><?php esc_html_e( 'folder-gallery', 'folder-gallery' ); ?></a>
						</li>
					</ol>
				</nav>
				<div class="fg-nav-shortcode">
					<code id="fg-nav-shortcode-text" class="fg-shortcode-code">[folder_gallery]</code>
					<button type="button" id="fg-nav-copy-shortcode" class="button button-small fg-copy-shortcode-btn">
						<?php esc_html_e( 'Copy', 'folder-gallery' ); ?>
					</button>
				</div>
			</div>

			<!-- Top Toolbar -->
			<div class="fg-toolbar" role="toolbar" aria-label="<?php esc_attr_e( 'Gallery actions', 'folder-gallery' ); ?>">
				<button type="button" class="button fg-toolbar-btn" data-action="upload-image">
					<span class="dashicons dashicons-format-image" aria-hidden="true"></span>
					<span><?php esc_html_e( 'New Image', 'folder-gallery' ); ?></span>
				</button>

				<button type="button" class="button fg-toolbar-btn" data-action="upload-zip">
					<span class="dashicons dashicons-media-archive" aria-hidden="true"></span>
					<span><?php esc_html_e( 'Upload ZIP', 'folder-gallery' ); ?></span>
				</button>

				<span class="fg-toolbar-separator" aria-hidden="true"></span>

				<button type="button" class="button fg-toolbar-btn" data-action="new-folder">
					<span class="dashicons dashicons-portfolio" aria-hidden="true"></span>
					<span><?php esc_html_e( 'New Folder', 'folder-gallery' ); ?></span>
				</button>

				<span class="fg-toolbar-separator" aria-hidden="true"></span>

				<button type="button" class="button fg-toolbar-btn" data-action="copy" title="<?php esc_attr_e( 'Copy (Ctrl+C)', 'folder-gallery' ); ?>" disabled>
					<span class="dashicons dashicons-admin-page" aria-hidden="true"></span>
					<span><?php esc_html_e( 'Copy', 'folder-gallery' ); ?></span>
				</button>

				<button type="button" class="button fg-toolbar-btn" data-action="paste" title="<?php esc_attr_e( 'Paste (Ctrl+V)', 'folder-gallery' ); ?>" disabled>
					<span class="dashicons dashicons-clipboard" aria-hidden="true"></span>
					<span><?php esc_html_e( 'Paste', 'folder-gallery' ); ?></span>
				</button>

				<button type="button" class="button fg-toolbar-btn" data-action="rename" title="<?php esc_attr_e( 'Rename (F2)', 'folder-gallery' ); ?>" disabled>
					<span class="dashicons dashicons-edit" aria-hidden="true"></span>
					<span><?php esc_html_e( 'Rename', 'folder-gallery' ); ?></span>
				</button>

				<span class="fg-toolbar-separator" aria-hidden="true"></span>

				<button type="button" class="button fg-toolbar-btn" data-action="regenerate-sizes" title="<?php esc_attr_e( 'Regenerate small and medium images in current folder', 'folder-gallery' ); ?>">
					<span class="dashicons dashicons-image-rotate" aria-hidden="true"></span>
					<span><?php esc_html_e( 'Generate Sizes', 'folder-gallery' ); ?></span>
				</button>

				<span class="fg-toolbar-separator" aria-hidden="true"></span>

				<button type="button" class="button fg-toolbar-btn fg-btn-danger" data-action="delete" disabled>
					<span class="dashicons dashicons-trash" aria-hidden="true"></span>
					<span><?php esc_html_e( 'Delete', 'folder-gallery' ); ?></span>
				</button>
			</div>

			<!-- Workspace: Left Listing Pane, Right Details Pane -->
			<div class="fg-workspace" id="fg-workspace">
				<div class="fg-pane fg-pane-list" id="fg-pane-list">
					<?php if ( empty( $folders ) && empty( $images ) ) : ?>
						<div class="fg-pane-placeholder">
							<?php esc_html_e( 'This folder is empty.', 'folder-gallery' ); ?>
						</div>
					<?php else : ?>
						<table class="fg-list-table">
							<thead>
								<tr>
									<th class="fg-col-name"><?php esc_html_e( 'Name', 'folder-gallery' ); ?></th>
									<th class="fg-col-date"><?php esc_html_e( 'Date', 'folder-gallery' ); ?></th>
									<th class="fg-col-type"><?php esc_html_e( 'Type', 'folder-gallery' ); ?></th>
									<th class="fg-col-size"><?php esc_html_e( 'Size', 'folder-gallery' ); ?></th>
									<th class="fg-col-variant" title="<?php esc_attr_e( 'Small image variant', 'folder-gallery' ); ?>"><?php esc_html_e( 'S', 'folder-gallery' ); ?></th>
									<th class="fg-col-variant" title="<?php esc_attr_e( 'Medium image variant', 'folder-gallery' ); ?>"><?php esc_html_e( 'M', 'folder-gallery' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $folders as $folder ) : ?>
									<tr
										class="fg-row fg-row-folder"
										data-kind="folder"
										data-path="<?php echo esc_attr( $folder['path'] ); ?>"
										data-title="<?php echo esc_attr( isset( $folder['title'] ) ? $folder['title'] : '' ); ?>"
										data-description="<?php echo esc_attr( isset( $folder['description'] ) ? $folder['description'] : '' ); ?>"
									>
										<td class="fg-col-name">
											<span class="fg-item-name-cell">
												<span class="fg-folder-icon" aria-hidden="true">
													<span class="dashicons dashicons-portfolio"></span>
												</span>
												<span class="fg-item-label"><?php echo esc_html( $folder['name'] ); ?></span>
											</span>
										</td>
										<td class="fg-col-date">
											<?php echo esc_html( $folder['mtime'] ? wp_date( $date_format, $folder['mtime'] ) : '—' ); ?>
										</td>
										<td class="fg-col-type">
											<?php echo esc_html( $folder['type'] ); ?>
										</td>
										<td class="fg-col-size">
											<?php
											printf(
												/* translators: %d: number of images in folder */
												esc_html( _n( '%d image', '%d images', $folder['image_count'], 'folder-gallery' ) ),
												(int) $folder['image_count']
											);
											?>
										</td>
										<td class="fg-col-variant">—</td>
										<td class="fg-col-variant">—</td>
									</tr>
								<?php endforeach; ?>

								<?php
								foreach ( $images as $image ) :
									$small_v   = isset( $image['variants']['small'] ) ? $image['variants']['small'] : array( 'exists' => false );
									$medium_v  = isset( $image['variants']['medium'] ) ? $image['variants']['medium'] : array( 'exists' => false );
									$img_w     = isset( $image['width'] ) ? (int) $image['width'] : 0;
									$img_h     = isset( $image['height'] ) ? (int) $image['height'] : 0;
									$img_mp    = isset( $image['megapixels'] ) ? (float) $image['megapixels'] : 0.0;
									$too_large = ! empty( $image['too_large'] );
									$thumb_src = ( ! empty( $small_v['exists'] ) && ! empty( $small_v['url'] ) ) ? $small_v['url'] : $image['url'];
									?>
									<tr
										class="fg-row fg-row-image<?php echo $too_large ? ' is-too-large' : ''; ?>"
										data-kind="image"
										data-path="<?php echo esc_attr( $image['path'] ); ?>"
										data-title="<?php echo esc_attr( isset( $image['title'] ) ? $image['title'] : '' ); ?>"
										data-description="<?php echo esc_attr( isset( $image['description'] ) ? $image['description'] : '' ); ?>"
										data-width="<?php echo esc_attr( $img_w ); ?>"
										data-height="<?php echo esc_attr( $img_h ); ?>"
										data-megapixels="<?php echo esc_attr( $img_mp ); ?>"
										data-too-large="<?php echo $too_large ? '1' : '0'; ?>"
									>
										<td class="fg-col-name">
											<span class="fg-item-name-cell">
												<img
													class="fg-thumb"
													src="<?php echo esc_url( $thumb_src ); ?>"
													alt="<?php echo esc_attr( $image['name'] ); ?>"
													loading="lazy"
												>
												<?php if ( $too_large ) : ?>
													<span class="fg-warning-badge" title="<?php esc_attr_e( 'Image too large', 'folder-gallery' ); ?>" aria-label="<?php esc_attr_e( 'Image too large', 'folder-gallery' ); ?>">
														<span class="dashicons dashicons-warning" aria-hidden="true"></span>
													</span>
												<?php endif; ?>
												<span class="fg-item-label"><?php echo esc_html( $image['name'] ); ?></span>
											</span>
										</td>
										<td class="fg-col-date">
											<?php echo esc_html( $image['mtime'] ? wp_date( $date_format, $image['mtime'] ) : '—' ); ?>
										</td>
										<td class="fg-col-type">
											<?php echo esc_html( $image['type'] ); ?>
										</td>
										<td class="fg-col-size">
											<?php echo esc_html( size_format( $image['filesize'], 1 ) ); ?>
										</td>
										<td class="fg-col-variant">
											<?php if ( ! empty( $small_v['exists'] ) && ! empty( $small_v['url'] ) ) : ?>
												<img
													class="fg-variant-thumb"
													src="<?php echo esc_url( $small_v['url'] ); ?>"
													alt="<?php esc_attr_e( 'Small', 'folder-gallery' ); ?>"
													title="<?php echo esc_attr( $small_v['width'] . '×' . $small_v['height'] ); ?>"
													loading="lazy"
												>
											<?php else : ?>
												<span class="fg-variant-missing">—</span>
											<?php endif; ?>
										</td>
										<td class="fg-col-variant">
											<?php if ( ! empty( $medium_v['exists'] ) && ! empty( $medium_v['url'] ) ) : ?>
												<img
													class="fg-variant-thumb"
													src="<?php echo esc_url( $medium_v['url'] ); ?>"
													alt="<?php esc_attr_e( 'Medium', 'folder-gallery' ); ?>"
													title="<?php echo esc_attr( $medium_v['width'] . '×' . $medium_v['height'] ); ?>"
													loading="lazy"
												>
											<?php else : ?>
												<span class="fg-variant-missing">—</span>
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				</div>

				<div
					class="fg-resizer"
					id="fg-resizer"
					role="separator"
					aria-orientation="vertical"
					aria-label="<?php esc_attr_e( 'Resize panels', 'folder-gallery' ); ?>"
					tabindex="0"
				></div>

				<div class="fg-pane fg-pane-details" id="fg-pane-details">
					<div class="fg-pane-placeholder" id="fg-details-placeholder">
						<?php esc_html_e( 'Select a folder or image to view and edit its details.', 'folder-gallery' ); ?>
					</div>

					<form class="fg-details-form" id="fg-details-form" hidden>
						<div class="fg-details-header">
							<button type="submit" class="button button-primary" id="fg-details-update-btn" disabled>
								<?php esc_html_e( 'Update', 'folder-gallery' ); ?>
							</button>
							<span class="fg-details-status" id="fg-details-status" aria-live="polite"></span>
						</div>

						<div class="fg-details-preview" id="fg-details-preview">
							<div class="fg-details-folder-icon" id="fg-details-folder-icon" hidden>
								<span class="dashicons dashicons-portfolio" aria-hidden="true"></span>
							</div>
							<img id="fg-details-image" src="" alt="" hidden>
						</div>

						<div class="fg-details-field">
							<span class="fg-details-meta-label"><?php esc_html_e( 'File / Folder Name', 'folder-gallery' ); ?></span>
							<div class="fg-details-filename" id="fg-details-filename"></div>
						</div>

						<div class="fg-details-field" id="fg-details-dimensions-wrap" hidden>
							<span class="fg-details-meta-label"><?php esc_html_e( 'Dimensions', 'folder-gallery' ); ?></span>
							<div class="fg-details-dimensions-row">
								<span class="fg-details-dimensions" id="fg-details-dimensions"></span>
								<span class="fg-details-too-large-badge" id="fg-details-too-large-badge" hidden>
									<span class="dashicons dashicons-warning" aria-hidden="true"></span>
									<span><?php esc_html_e( 'Image too large', 'folder-gallery' ); ?></span>
								</span>
							</div>
						</div>

						<div id="fg-details-shortcode-wrap" class="fg-details-field fg-details-shortcode-wrap" hidden>
							<label for="fg-details-shortcode-input"><?php esc_html_e( 'Shortcode', 'folder-gallery' ); ?></label>
							<div class="fg-details-shortcode-row">
								<input type="text" id="fg-details-shortcode-input" class="regular-text code" readonly value="" />
								<button type="button" id="fg-details-copy-shortcode" class="button button-secondary fg-copy-shortcode-btn">
									<?php esc_html_e( 'Copy', 'folder-gallery' ); ?>
								</button>
							</div>
						</div>

						<div class="fg-details-field">
							<label for="fg-details-title"><?php esc_html_e( 'Title', 'folder-gallery' ); ?></label>
							<input type="text" id="fg-details-title" class="regular-text" value="">
						</div>

						<div class="fg-details-field">
							<label for="fg-details-description"><?php esc_html_e( 'Description', 'folder-gallery' ); ?></label>
							<div class="wp-editor-wrap html-active" id="fg-details-editor-wrap">
								<textarea id="fg-details-description" class="wp-editor-area" rows="8"></textarea>
							</div>
						</div>
					</form>
				</div>
			</div>

		</div>
	<?php endif; ?>
</div>
