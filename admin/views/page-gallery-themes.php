<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fg_message = isset( $_GET['fg_message'] ) ? sanitize_key( wp_unslash( $_GET['fg_message'] ) ) : '';

$themes_payload = array();
foreach ( $themes as $slug => $theme ) {
	$screenshot_url = '';
	foreach ( array( 'screenshot.png', 'screenshot.jpg', 'screenshot.webp' ) as $shot_file ) {
		if ( is_file( trailingslashit( $theme['dir'] ) . $shot_file ) ) {
			$screenshot_url = trailingslashit( $theme['url'] ) . $shot_file;
			break;
		}
	}

	$themes_payload[ $slug ] = array(
		'slug'           => $theme['slug'],
		'name'           => $theme['name'],
		'description'    => isset( $theme['description'] ) ? $theme['description'] : '',
		'version'        => isset( $theme['version'] ) ? $theme['version'] : '1.0.0',
		'author'         => isset( $theme['author'] ) ? $theme['author'] : '',
		'screenshotUrl'  => $screenshot_url,
		'hasSettings'    => ! empty( $theme['has_settings'] ),
		'settingsSchema' => isset( $theme['settings_schema'] ) && is_array( $theme['settings_schema'] ) ? $theme['settings_schema'] : array(),
		'settingsValues' => FG_Gallery_Theme_Manager::get_theme_settings( $slug ),
	);
}

$selected_theme = ( '' !== $selected_slug && isset( $themes_payload[ $selected_slug ] ) )
	? $themes_payload[ $selected_slug ]
	: null;
?>
<div class="wrap fg-wrap fg-gallery-themes-wrap">
	<h1 class="wp-heading-inline">
		<?php echo esc_html( get_admin_page_title() ); ?>
		<span class="title-count theme-count" id="fg-gt-count"><?php echo esc_html( (string) count( $themes ) ); ?></span>
	</h1>
	<hr class="wp-header-end">

	<p class="description fg-upload-limit">
		<?php
		printf(
			/* translators: %s: maximum upload file size, e.g. 64 MB */
			esc_html__( 'Upload a theme ZIP archive containing a single theme folder (for example: my-theme/theme.json). Maximum upload size: %s', 'folder-gallery' ),
			esc_html( size_format( wp_max_upload_size() ) )
		);
		?>
	</p>

	<?php if ( 'theme_activated' === $fg_message ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Gallery theme activated.', 'folder-gallery' ); ?></p>
		</div>
	<?php elseif ( 'theme_settings_saved' === $fg_message ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Theme settings saved.', 'folder-gallery' ); ?></p>
		</div>
	<?php elseif ( 'theme_installed' === $fg_message ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Gallery theme installed.', 'folder-gallery' ); ?></p>
		</div>
	<?php elseif ( 'theme_deleted' === $fg_message ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Gallery theme deleted.', 'folder-gallery' ); ?></p>
		</div>
	<?php elseif ( 'invalid_theme' === $fg_message ) : ?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'Selected gallery theme could not be found.', 'folder-gallery' ); ?></p>
		</div>
	<?php elseif ( 'install_error' === $fg_message ) : ?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'Could not install the uploaded gallery theme ZIP archive.', 'folder-gallery' ); ?></p>
		</div>
	<?php elseif ( 'delete_error' === $fg_message ) : ?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'Could not delete the selected gallery theme.', 'folder-gallery' ); ?></p>
		</div>
	<?php endif; ?>

	<input
		type="file"
		id="fg-gt-zip-input"
		accept=".zip,application/zip"
		hidden
	>

	<div class="fg-explorer fg-gt-explorer" id="fg-gt-explorer">
		<!-- Top Toolbar -->
		<div class="fg-toolbar" role="toolbar" aria-label="<?php esc_attr_e( 'Gallery theme actions', 'folder-gallery' ); ?>">
			<button type="button" class="button fg-toolbar-btn" data-action="add-theme">
				<span class="dashicons dashicons-upload" aria-hidden="true"></span>
				<span><?php esc_html_e( 'Add Theme', 'folder-gallery' ); ?></span>
			</button>

			<span class="fg-toolbar-separator" aria-hidden="true"></span>

			<button
				type="button"
				class="button fg-toolbar-btn fg-btn-danger"
				data-action="delete-theme"
				title="<?php esc_attr_e( 'Delete selected theme (Delete)', 'folder-gallery' ); ?>"
				<?php disabled( null === $selected_theme || $selected_theme['slug'] === $active_slug ); ?>
			>
				<span class="dashicons dashicons-trash" aria-hidden="true"></span>
				<span><?php esc_html_e( 'Delete', 'folder-gallery' ); ?></span>
			</button>
		</div>

		<!-- Workspace: Left Theme List, Right Details & Settings -->
		<div class="fg-workspace fg-gt-workspace" id="fg-gt-workspace" data-fg-workspace="gallery-themes">
			<div class="fg-pane fg-pane-list fg-gt-pane-list" id="fg-gt-pane-list">
				<?php if ( empty( $themes_payload ) ) : ?>
					<div class="fg-pane-placeholder">
						<?php esc_html_e( 'No gallery themes found in the gallery-themes directory.', 'folder-gallery' ); ?>
					</div>
				<?php else : ?>
					<table class="fg-list-table fg-gt-table">
						<thead>
							<tr>
								<th class="fg-gt-col-active" title="<?php esc_attr_e( 'Active gallery theme', 'folder-gallery' ); ?>">
									<?php esc_html_e( 'Active', 'folder-gallery' ); ?>
								</th>
								<th class="fg-gt-col-name"><?php esc_html_e( 'Theme', 'folder-gallery' ); ?></th>
								<th class="fg-gt-col-version"><?php esc_html_e( 'Version', 'folder-gallery' ); ?></th>
							</tr>
						</thead>
						<tbody id="fg-gt-list-body">
							<?php
							foreach ( $themes_payload as $slug => $item ) :
								$is_active   = ( $slug === $active_slug );
								$is_selected = ( $slug === $selected_slug );
								?>
								<tr
									class="fg-row fg-gt-row <?php echo $is_selected ? 'is-selected' : ''; ?> <?php echo $is_active ? 'is-active-theme' : ''; ?>"
									data-slug="<?php echo esc_attr( $slug ); ?>"
									tabindex="0"
								>
									<td class="fg-gt-col-active">
										<input
											type="radio"
											name="fg_active_gallery_theme_radio"
											class="fg-gt-active-radio"
											value="<?php echo esc_attr( $slug ); ?>"
											aria-label="<?php echo esc_attr( sprintf( /* translators: %s: theme name */ __( 'Activate %s', 'folder-gallery' ), $item['name'] ) ); ?>"
											<?php checked( $is_active ); ?>
										>
									</td>
									<td class="fg-gt-col-name">
										<div class="fg-gt-name-wrap">
											<span class="fg-item-label"><?php echo esc_html( $item['name'] ); ?></span>
											<code class="fg-gt-slug-badge"><?php echo esc_html( $slug ); ?></code>
										</div>
									</td>
									<td class="fg-gt-col-version">
										<?php echo esc_html( 'v' . $item['version'] ); ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>

			<div
				class="fg-resizer"
				id="fg-gt-resizer"
				role="separator"
				aria-orientation="vertical"
				aria-label="<?php esc_attr_e( 'Resize panels', 'folder-gallery' ); ?>"
				tabindex="0"
			></div>

			<div class="fg-pane fg-pane-details fg-gt-pane-details" id="fg-gt-pane-details">
				<div class="fg-pane-placeholder" id="fg-gt-details-placeholder" <?php echo null !== $selected_theme ? 'hidden' : ''; ?>>
					<?php esc_html_e( 'Select a gallery theme to view its details and settings.', 'folder-gallery' ); ?>
				</div>

				<form
					class="fg-details-form fg-gt-details-form"
					id="fg-gt-details-form"
					method="post"
					action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
					data-fg-detail-form="gallery-themes"
					<?php echo null === $selected_theme ? 'hidden' : ''; ?>
				>
					<input type="hidden" name="action" value="fg_save_gallery_theme_settings">
					<input type="hidden" name="theme" id="fg-gt-form-slug" value="<?php echo esc_attr( $selected_theme ? $selected_theme['slug'] : '' ); ?>">
					<?php wp_nonce_field( 'fg_save_gallery_theme_settings_' . ( $selected_theme ? $selected_theme['slug'] : '' ), '_wpnonce', true, true ); ?>

					<div class="fg-details-header">
						<button type="submit" class="button button-primary" id="fg-gt-update-btn" data-fg-save disabled>
							<?php esc_html_e( 'Update', 'folder-gallery' ); ?>
						</button>
						<span class="fg-dirty-badge" id="fg-gt-dirty-badge">
							<?php esc_html_e( 'Unsaved changes', 'folder-gallery' ); ?>
						</span>
						<span class="fg-details-status" id="fg-gt-details-status" aria-live="polite"></span>
					</div>

					<!-- Theme Preview Image (if available) -->
					<div class="fg-details-preview fg-gt-preview" id="fg-gt-preview-wrap" <?php echo ( ! $selected_theme || '' === $selected_theme['screenshotUrl'] ) ? 'hidden' : ''; ?>>
						<img
							id="fg-gt-preview-img"
							src="<?php echo esc_url( $selected_theme ? $selected_theme['screenshotUrl'] : '' ); ?>"
							alt="<?php echo esc_attr( $selected_theme ? $selected_theme['name'] : '' ); ?>"
						>
					</div>

					<!-- Theme Metadata -->
					<div class="fg-gt-meta-block">
						<div class="fg-gt-title-row">
							<h2 class="fg-gt-detail-name" id="fg-gt-detail-name">
								<?php echo esc_html( $selected_theme ? $selected_theme['name'] : '' ); ?>
							</h2>
							<span class="fg-gt-active-pill" id="fg-gt-active-pill" <?php echo ( ! $selected_theme || $selected_theme['slug'] !== $active_slug ) ? 'hidden' : ''; ?>>
								<?php esc_html_e( 'Active', 'folder-gallery' ); ?>
							</span>
						</div>

						<p class="fg-gt-detail-desc" id="fg-gt-detail-desc" <?php echo ( ! $selected_theme || '' === $selected_theme['description'] ) ? 'hidden' : ''; ?>>
							<?php echo esc_html( $selected_theme ? $selected_theme['description'] : '' ); ?>
						</p>

						<div class="fg-gt-meta-grid">
							<div class="fg-gt-meta-item">
								<span class="fg-details-meta-label"><?php esc_html_e( 'Version', 'folder-gallery' ); ?></span>
								<span id="fg-gt-detail-version"><?php echo esc_html( $selected_theme ? 'v' . $selected_theme['version'] : '—' ); ?></span>
							</div>
							<div class="fg-gt-meta-item">
								<span class="fg-details-meta-label"><?php esc_html_e( 'Author', 'folder-gallery' ); ?></span>
								<span id="fg-gt-detail-author"><?php echo esc_html( ( $selected_theme && '' !== $selected_theme['author'] ) ? $selected_theme['author'] : '—' ); ?></span>
							</div>
							<div class="fg-gt-meta-item">
								<span class="fg-details-meta-label"><?php esc_html_e( 'Folder', 'folder-gallery' ); ?></span>
								<code id="fg-gt-detail-slug"><?php echo esc_html( $selected_theme ? 'gallery-themes/' . $selected_theme['slug'] : '—' ); ?></code>
							</div>
						</div>

						<div class="fg-details-field fg-details-shortcode-wrap">
							<label for="fg-gt-shortcode-input"><?php esc_html_e( 'Shortcode Override', 'folder-gallery' ); ?></label>
							<div class="fg-details-shortcode-row">
								<input
									type="text"
									id="fg-gt-shortcode-input"
									class="regular-text code"
									readonly
									value="<?php echo esc_attr( $selected_theme ? '[folder_gallery gallery="' . $selected_theme['slug'] . '"]' : '' ); ?>"
								>
								<button type="button" id="fg-gt-copy-shortcode" class="button button-secondary fg-copy-shortcode-btn">
									<?php esc_html_e( 'Copy', 'folder-gallery' ); ?>
								</button>
							</div>
						</div>
					</div>

					<!-- Dynamic Theme Settings -->
					<div class="fg-gt-settings-section" id="fg-gt-settings-section">
						<h3 class="fg-gt-section-title"><?php esc_html_e( 'Theme Settings', 'folder-gallery' ); ?></h3>
						<div id="fg-gt-settings-fields">
							<?php if ( $selected_theme && ! empty( $selected_theme['settingsSchema'] ) ) : ?>
								<?php
								foreach ( $selected_theme['settingsSchema'] as $key => $field ) :
									$field_id = 'fg-gt-setting-' . $key;
									$val      = isset( $selected_theme['settingsValues'][ $key ] ) ? $selected_theme['settingsValues'][ $key ] : $field['default'];
									$type     = $field['type'];
									?>
									<div class="fg-details-field fg-gt-setting-row">
										<?php if ( 'boolean' === $type ) : ?>
											<label class="fg-gt-checkbox-label" for="<?php echo esc_attr( $field_id ); ?>">
												<input
													id="<?php echo esc_attr( $field_id ); ?>"
													name="settings[<?php echo esc_attr( $key ); ?>]"
													data-setting-key="<?php echo esc_attr( $key ); ?>"
													type="checkbox"
													value="1"
													<?php checked( ! empty( $val ) ); ?>
												>
												<span><?php echo esc_html( $field['label'] ); ?></span>
											</label>
										<?php else : ?>
											<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
											<?php if ( 'select' === $type && ! empty( $field['options'] ) ) : ?>
												<select
													id="<?php echo esc_attr( $field_id ); ?>"
													name="settings[<?php echo esc_attr( $key ); ?>]"
													data-setting-key="<?php echo esc_attr( $key ); ?>"
												>
													<?php foreach ( $field['options'] as $opt_val => $opt_label ) : ?>
														<option value="<?php echo esc_attr( (string) $opt_val ); ?>" <?php selected( (string) $val, (string) $opt_val ); ?>>
															<?php echo esc_html( (string) $opt_label ); ?>
														</option>
													<?php endforeach; ?>
												</select>
											<?php elseif ( in_array( $type, array( 'number', 'integer' ), true ) ) : ?>
												<input
													id="<?php echo esc_attr( $field_id ); ?>"
													name="settings[<?php echo esc_attr( $key ); ?>]"
													data-setting-key="<?php echo esc_attr( $key ); ?>"
													type="number"
													class="small-text"
													value="<?php echo esc_attr( (string) $val ); ?>"
													<?php if ( isset( $field['min'] ) ) : ?>min="<?php echo esc_attr( (string) $field['min'] ); ?>"<?php endif; ?>
													<?php if ( isset( $field['max'] ) ) : ?>max="<?php echo esc_attr( (string) $field['max'] ); ?>"<?php endif; ?>
													step="<?php echo esc_attr( isset( $field['step'] ) ? (string) $field['step'] : ( 'integer' === $type ? '1' : 'any' ) ); ?>"
												>
											<?php else : ?>
												<input
													id="<?php echo esc_attr( $field_id ); ?>"
													name="settings[<?php echo esc_attr( $key ); ?>]"
													data-setting-key="<?php echo esc_attr( $key ); ?>"
													type="text"
													class="regular-text"
													value="<?php echo esc_attr( (string) $val ); ?>"
												>
											<?php endif; ?>
										<?php endif; ?>

										<?php if ( '' !== $field['description'] ) : ?>
											<p class="description"><?php echo esc_html( $field['description'] ); ?></p>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							<?php else : ?>
								<p class="description fg-gt-no-settings">
									<?php esc_html_e( 'This theme does not define configurable settings.', 'folder-gallery' ); ?>
								</p>
							<?php endif; ?>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>

	<script type="application/json" id="fg-gt-initial-data"><?php echo wp_json_encode( array( 'activeSlug' => $active_slug, 'selectedSlug' => $selected_slug, 'themes' => $themes_payload ) ); ?></script>
</div>
