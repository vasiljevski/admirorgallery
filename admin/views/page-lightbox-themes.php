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
		'settingsValues' => FG_Lightbox_Theme_Manager::get_theme_settings( $slug ),
	);
}

$selected_theme = ( '' !== $selected_slug && isset( $themes_payload[ $selected_slug ] ) )
	? $themes_payload[ $selected_slug ]
	: null;
?>
<div class="wrap fg-wrap fg-lightbox-themes-wrap">
	<h1 class="wp-heading-inline">
		<?php echo esc_html( get_admin_page_title() ); ?>
		<span class="title-count theme-count" id="fg-lt-count"><?php echo esc_html( (string) count( $themes ) ); ?></span>
	</h1>
	<hr class="wp-header-end">

	<p class="description fg-upload-limit">
		<?php
		printf(
			/* translators: %s: maximum upload file size, e.g. 64 MB */
			esc_html__( 'Upload a lightbox theme ZIP archive containing a single theme folder (for example: my-lightbox/theme.json). Maximum upload size: %s', 'folder-gallery' ),
			esc_html( size_format( wp_max_upload_size() ) )
		);
		?>
	</p>

	<?php if ( 'theme_activated' === $fg_message ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Lightbox theme activated.', 'folder-gallery' ); ?></p>
		</div>
	<?php elseif ( 'theme_settings_saved' === $fg_message ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Lightbox theme settings saved.', 'folder-gallery' ); ?></p>
		</div>
	<?php elseif ( 'theme_installed' === $fg_message ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Lightbox theme installed.', 'folder-gallery' ); ?></p>
		</div>
	<?php elseif ( 'theme_deleted' === $fg_message ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Lightbox theme deleted.', 'folder-gallery' ); ?></p>
		</div>
	<?php elseif ( 'invalid_theme' === $fg_message ) : ?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'Selected lightbox theme could not be found.', 'folder-gallery' ); ?></p>
		</div>
	<?php elseif ( 'install_error' === $fg_message ) : ?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'Could not install the uploaded lightbox theme ZIP archive.', 'folder-gallery' ); ?></p>
		</div>
	<?php elseif ( 'delete_error' === $fg_message ) : ?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'Could not delete the selected lightbox theme.', 'folder-gallery' ); ?></p>
		</div>
	<?php endif; ?>

	<input
		type="file"
		id="fg-lt-zip-input"
		accept=".zip,application/zip"
		hidden
	>

	<div class="fg-explorer fg-lt-explorer" id="fg-lt-explorer">
		<!-- Top Toolbar -->
		<div class="fg-toolbar" role="toolbar" aria-label="<?php esc_attr_e( 'Lightbox theme actions', 'folder-gallery' ); ?>">
			<button type="button" class="button fg-toolbar-btn" data-action="add-theme">
				<span class="dashicons dashicons-upload" aria-hidden="true"></span>
				<span><?php esc_html_e( 'Add Theme', 'folder-gallery' ); ?></span>
			</button>

			<span class="fg-toolbar-separator" aria-hidden="true"></span>

			<button
				type="button"
				class="button fg-toolbar-btn fg-btn-danger"
				data-action="delete-theme"
				title="<?php esc_attr_e( 'Delete selected lightbox theme (Delete)', 'folder-gallery' ); ?>"
				<?php disabled( null === $selected_theme || $selected_theme['slug'] === $active_slug ); ?>
			>
				<span class="dashicons dashicons-trash" aria-hidden="true"></span>
				<span><?php esc_html_e( 'Delete', 'folder-gallery' ); ?></span>
			</button>
		</div>

		<!-- Workspace: Left Theme List, Right Details & Settings -->
		<div class="fg-workspace fg-lt-workspace" id="fg-lt-workspace" data-fg-workspace="lightbox-themes">
			<div class="fg-pane fg-pane-list fg-lt-pane-list" id="fg-lt-pane-list">
				<?php if ( empty( $themes_payload ) ) : ?>
					<div class="fg-pane-placeholder">
						<?php esc_html_e( 'No lightbox themes found in the lightbox-themes directory.', 'folder-gallery' ); ?>
					</div>
				<?php else : ?>
					<table class="fg-list-table fg-lt-table">
						<thead>
							<tr>
								<th class="fg-lt-col-active" title="<?php esc_attr_e( 'Active lightbox theme', 'folder-gallery' ); ?>">
									<?php esc_html_e( 'Active', 'folder-gallery' ); ?>
								</th>
								<th class="fg-lt-col-name"><?php esc_html_e( 'Theme', 'folder-gallery' ); ?></th>
								<th class="fg-lt-col-version"><?php esc_html_e( 'Version', 'folder-gallery' ); ?></th>
							</tr>
						</thead>
						<tbody id="fg-lt-list-body">
							<?php
							foreach ( $themes_payload as $slug => $item ) :
								$is_active   = ( $slug === $active_slug );
								$is_selected = ( $slug === $selected_slug );
								?>
								<tr
									class="fg-row fg-lt-row <?php echo $is_selected ? 'is-selected' : ''; ?> <?php echo $is_active ? 'is-active-theme' : ''; ?>"
									data-slug="<?php echo esc_attr( $slug ); ?>"
									tabindex="0"
								>
									<td class="fg-lt-col-active">
										<input
											type="radio"
											name="fg_active_lightbox_theme_radio"
											class="fg-lt-active-radio"
											value="<?php echo esc_attr( $slug ); ?>"
											aria-label="<?php echo esc_attr( sprintf( /* translators: %s: theme name */ __( 'Activate %s', 'folder-gallery' ), $item['name'] ) ); ?>"
											<?php checked( $is_active ); ?>
										>
									</td>
									<td class="fg-lt-col-name">
										<div class="fg-lt-name-wrap">
											<span class="fg-item-label"><?php echo esc_html( $item['name'] ); ?></span>
											<code class="fg-lt-slug-badge"><?php echo esc_html( $slug ); ?></code>
										</div>
									</td>
									<td class="fg-lt-col-version">
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
				id="fg-lt-resizer"
				role="separator"
				aria-orientation="vertical"
				aria-label="<?php esc_attr_e( 'Resize panels', 'folder-gallery' ); ?>"
				tabindex="0"
			></div>

			<div class="fg-pane fg-pane-details fg-lt-pane-details" id="fg-lt-pane-details">
				<div class="fg-pane-placeholder" id="fg-lt-details-placeholder" <?php echo null !== $selected_theme ? 'hidden' : ''; ?>>
					<?php esc_html_e( 'Select a lightbox theme to view its details and settings.', 'folder-gallery' ); ?>
				</div>

				<form
					class="fg-details-form fg-lt-details-form"
					id="fg-lt-details-form"
					method="post"
					action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
					data-fg-detail-form="lightbox-themes"
					<?php echo null === $selected_theme ? 'hidden' : ''; ?>
				>
					<input type="hidden" name="action" value="fg_save_lightbox_theme_settings">
					<input type="hidden" name="theme" id="fg-lt-form-slug" value="<?php echo esc_attr( $selected_theme ? $selected_theme['slug'] : '' ); ?>">
					<?php wp_nonce_field( 'fg_save_lightbox_theme_settings_' . ( $selected_theme ? $selected_theme['slug'] : '' ), '_wpnonce', true, true ); ?>

					<div class="fg-details-header">
						<button type="submit" class="button button-primary" id="fg-lt-update-btn" data-fg-save disabled>
							<?php esc_html_e( 'Update', 'folder-gallery' ); ?>
						</button>
						<span class="fg-dirty-badge" id="fg-lt-dirty-badge">
							<?php esc_html_e( 'Unsaved changes', 'folder-gallery' ); ?>
						</span>
						<span class="fg-details-status" id="fg-lt-details-status" aria-live="polite"></span>
					</div>

					<!-- Theme Preview Image (if available) -->
					<div class="fg-details-preview fg-lt-preview" id="fg-lt-preview-wrap" <?php echo ( ! $selected_theme || '' === $selected_theme['screenshotUrl'] ) ? 'hidden' : ''; ?>>
						<img
							id="fg-lt-preview-img"
							src="<?php echo esc_url( $selected_theme ? $selected_theme['screenshotUrl'] : '' ); ?>"
							alt="<?php echo esc_attr( $selected_theme ? $selected_theme['name'] : '' ); ?>"
						>
					</div>

					<!-- Theme Metadata -->
					<div class="fg-lt-meta-block">
						<div class="fg-lt-title-row">
							<h2 class="fg-lt-detail-name" id="fg-lt-detail-name">
								<?php echo esc_html( $selected_theme ? $selected_theme['name'] : '' ); ?>
							</h2>
							<span class="fg-lt-active-pill" id="fg-lt-active-pill" <?php echo ( ! $selected_theme || $selected_theme['slug'] !== $active_slug ) ? 'hidden' : ''; ?>>
								<?php esc_html_e( 'Active', 'folder-gallery' ); ?>
							</span>
						</div>

						<p class="fg-lt-detail-desc" id="fg-lt-detail-desc" <?php echo ( ! $selected_theme || '' === $selected_theme['description'] ) ? 'hidden' : ''; ?>>
							<?php echo esc_html( $selected_theme ? $selected_theme['description'] : '' ); ?>
						</p>

						<div class="fg-lt-meta-grid">
							<div class="fg-lt-meta-item">
								<span class="fg-details-meta-label"><?php esc_html_e( 'Version', 'folder-gallery' ); ?></span>
								<span id="fg-lt-detail-version"><?php echo esc_html( $selected_theme ? 'v' . $selected_theme['version'] : '—' ); ?></span>
							</div>
							<div class="fg-lt-meta-item">
								<span class="fg-details-meta-label"><?php esc_html_e( 'Author', 'folder-gallery' ); ?></span>
								<span id="fg-lt-detail-author"><?php echo esc_html( ( $selected_theme && '' !== $selected_theme['author'] ) ? $selected_theme['author'] : '—' ); ?></span>
							</div>
							<div class="fg-lt-meta-item">
								<span class="fg-details-meta-label"><?php esc_html_e( 'Folder', 'folder-gallery' ); ?></span>
								<code id="fg-lt-detail-slug"><?php echo esc_html( $selected_theme ? 'lightbox-themes/' . $selected_theme['slug'] : '—' ); ?></code>
							</div>
						</div>

						<div class="fg-details-field fg-details-shortcode-wrap">
							<label for="fg-lt-shortcode-input"><?php esc_html_e( 'Shortcode Override', 'folder-gallery' ); ?></label>
							<div class="fg-details-shortcode-row">
								<input
									type="text"
									id="fg-lt-shortcode-input"
									class="regular-text code"
									readonly
									value="<?php echo esc_attr( $selected_theme ? '[folder_gallery lightbox="' . $selected_theme['slug'] . '"]' : '' ); ?>"
								>
								<button type="button" id="fg-lt-copy-shortcode" class="button button-secondary fg-copy-shortcode-btn">
									<?php esc_html_e( 'Copy', 'folder-gallery' ); ?>
								</button>
							</div>
						</div>
					</div>

					<!-- Dynamic Theme Settings -->
					<div class="fg-lt-settings-section" id="fg-lt-settings-section">
						<h3 class="fg-lt-section-title"><?php esc_html_e( 'Theme Settings', 'folder-gallery' ); ?></h3>
						<div id="fg-lt-settings-fields">
							<?php if ( $selected_theme && ! empty( $selected_theme['settingsSchema'] ) ) : ?>
								<?php
								foreach ( $selected_theme['settingsSchema'] as $key => $field ) :
									$field_id = 'fg-lt-setting-' . $key;
									$val      = isset( $selected_theme['settingsValues'][ $key ] ) ? $selected_theme['settingsValues'][ $key ] : $field['default'];
									$type     = $field['type'];
									?>
									<div class="fg-details-field fg-lt-setting-row">
										<?php if ( 'boolean' === $type ) : ?>
											<label class="fg-lt-checkbox-label" for="<?php echo esc_attr( $field_id ); ?>">
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
								<p class="description fg-lt-no-settings">
									<?php esc_html_e( 'This lightbox theme does not define configurable settings.', 'folder-gallery' ); ?>
								</p>
							<?php endif; ?>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>

	<script type="application/json" id="fg-lt-initial-data"><?php echo wp_json_encode( array( 'activeSlug' => $active_slug, 'selectedSlug' => $selected_slug, 'themes' => $themes_payload ) ); ?></script>
</div>