// Folder Gallery Admin Scripts
(function (window, document) {
	'use strict';

	function isEditableTarget(el) {
		if (!el) {
			return false;
		}
		var tag = el.tagName ? el.tagName.toLowerCase() : '';
		return tag === 'input' || tag === 'textarea' || tag === 'select' || el.isContentEditable;
	}

	function escapeHtml(value) {
		return String(value)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function escapeAttr(value) {
		return escapeHtml(value).replace(/'/g, '&#39;');
	}

	function fallbackCopyText(text) {
		var temp = document.createElement('textarea');
		temp.value = text;
		temp.setAttribute('readonly', '');
		temp.style.position = 'fixed';
		temp.style.opacity = '0';
		document.body.appendChild(temp);
		temp.select();
		try {
			document.execCommand('copy');
		} catch (err) {
			// Ignore if blocked.
		}
		document.body.removeChild(temp);
	}

	function copyTextWithFeedback(text, buttonEl, i18n) {
		if (!text) {
			return;
		}

		function showFeedback() {
			if (!buttonEl) {
				return;
			}
			var defaultLabel = (i18n && i18n.copyShortcode) || 'Copy';
			var copiedLabel = (i18n && i18n.copiedShortcode) || 'Copied!';
			buttonEl.textContent = copiedLabel;
			window.clearTimeout(buttonEl._fgCopyTimer);
			buttonEl._fgCopyTimer = window.setTimeout(function () {
				buttonEl.textContent = defaultLabel;
			}, 1500);
		}

		if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
			navigator.clipboard.writeText(text).then(showFeedback).catch(function () {
				fallbackCopyText(text);
				showFeedback();
			});
		} else {
			fallbackCopyText(text);
			showFeedback();
		}
	}

	/**
	 * Shared split-pane resizer helper for master-detail workspaces.
	 */
	function initPanelResizer(options) {
		var opts = options || {};
		var workspace = document.getElementById(opts.workspaceId);
		var leftPane = document.getElementById(opts.leftPaneId);
		var resizer = document.getElementById(opts.resizerId);

		if (!workspace || !leftPane || !resizer) {
			return;
		}

		var storageKey = opts.storageKey || 'fg_workspace_left_ratio';
		var minLeftPx = opts.minLeftPx || 240;
		var minRightPx = opts.minRightPx || 220;

		var savedRatio = parseFloat(window.localStorage.getItem(storageKey));
		if (!Number.isNaN(savedRatio) && savedRatio > 0.15 && savedRatio < 0.85) {
			leftPane.style.flexBasis = (savedRatio * 100).toFixed(2) + '%';
		}

		var isDragging = false;

		resizer.addEventListener('mousedown', function (e) {
			e.preventDefault();
			isDragging = true;
			resizer.classList.add('is-dragging');
			workspace.classList.add('is-resizing');
		});

		document.addEventListener('mousemove', function (e) {
			if (!isDragging) {
				return;
			}

			var rect = workspace.getBoundingClientRect();
			var resizerWidth = resizer.offsetWidth || 5;
			var maxLeftPx = rect.width - minRightPx - resizerWidth;

			var newLeftPx = e.clientX - rect.left;
			newLeftPx = Math.max(minLeftPx, Math.min(newLeftPx, maxLeftPx));

			var ratio = newLeftPx / rect.width;
			leftPane.style.flexBasis = (ratio * 100).toFixed(2) + '%';
		});

		document.addEventListener('mouseup', function () {
			if (!isDragging) {
				return;
			}

			isDragging = false;
			resizer.classList.remove('is-dragging');
			workspace.classList.remove('is-resizing');

			var rect = workspace.getBoundingClientRect();
			if (rect.width > 0) {
				var ratio = leftPane.getBoundingClientRect().width / rect.width;
				window.localStorage.setItem(storageKey, ratio.toFixed(4));
			}
		});
	}

	/**
	 * Shared Master-Detail Workspace Helper (dirty tracking, unsaved prompt, Ctrl/Cmd+S, Up/Down navigation).
	 */
	function createWorkspace(config) {
		var opts = config || {};
		var root = typeof opts.root === 'string' ? document.querySelector(opts.root) : opts.root;
		if (!root) {
			return null;
		}

		var form = root.querySelector('[data-fg-detail-form]');
		var saveBtn = root.querySelector('[data-fg-save]');
		var dirty = false;

		function setDirty(state) {
			dirty = Boolean(state);
			root.classList.toggle('is-dirty', dirty);
			if (saveBtn) {
				saveBtn.disabled = !dirty || (typeof opts.canSave === 'function' && !opts.canSave());
			}
		}

		function recomputeDirty() {
			if (typeof opts.computeDirty === 'function') {
				setDirty(opts.computeDirty());
			}
		}

		function confirmDiscardIfDirty() {
			if (!dirty) {
				return true;
			}
			var msg = opts.unsavedMessage || 'You have unsaved changes. Discard them and continue?';
			return window.confirm(msg);
		}

		if (form) {
			form.addEventListener('input', recomputeDirty);
			form.addEventListener('change', recomputeDirty);
		}

		setDirty(false);

		document.addEventListener('keydown', function (event) {
			if (typeof opts.isBusy === 'function' && opts.isBusy()) {
				return;
			}

			var isSaveShortcut = (event.ctrlKey || event.metaKey) && !event.altKey && (event.key === 's' || event.key === 'S');
			if (isSaveShortcut && form && !form.hidden) {
				event.preventDefault();
				if (dirty && typeof opts.onSave === 'function') {
					opts.onSave();
				}
				return;
			}

			if (isEditableTarget(event.target)) {
				return;
			}

			if ((event.key === 'ArrowDown' || event.key === 'ArrowUp') && typeof opts.onNavigate === 'function') {
				opts.onNavigate(event.key === 'ArrowDown' ? 1 : -1, event);
			}
		});

		return {
			root: root,
			form: form,
			isDirty: function () {
				return dirty;
			},
			setDirty: setDirty,
			recomputeDirty: recomputeDirty,
			confirmDiscardIfDirty: confirmDiscardIfDirty
		};
	}

	window.FGWorkspace = {
		create: createWorkspace,
		initResizer: initPanelResizer,
		copyText: copyTextWithFeedback,
		isEditableTarget: isEditableTarget,
		escapeHtml: escapeHtml,
		escapeAttr: escapeAttr
	};

	document.addEventListener('DOMContentLoaded', function () {
		initDetailsEditor();
		initFolderExplorer();
		initThemeManagerWorkspace({
			prefix: 'fg-gt',
			config: typeof fgGalleryThemesConfig !== 'undefined' ? fgGalleryThemesConfig : null,
			resizerStorageKey: 'fg_gallery_themes_left_ratio',
			folderBasePath: 'gallery-themes/',
			shortcodeAttr: 'gallery',
			noSettingsText: 'This theme does not define configurable settings.',
			actions: {
				activate: 'fg_activate_gallery_theme',
				saveSettings: 'fg_save_gallery_theme_settings',
				installZip: 'fg_install_gallery_theme_zip',
				deleteTheme: 'fg_delete_gallery_theme'
			}
		});
		initThemeManagerWorkspace({
			prefix: 'fg-lt',
			config: typeof fgLightboxThemesConfig !== 'undefined' ? fgLightboxThemesConfig : null,
			resizerStorageKey: 'fg_lightbox_themes_left_ratio',
			folderBasePath: 'lightbox-themes/',
			shortcodeAttr: 'lightbox',
			noSettingsText: 'This lightbox theme does not define configurable settings.',
			actions: {
				activate: 'fg_activate_lightbox_theme',
				saveSettings: 'fg_save_lightbox_theme_settings',
				installZip: 'fg_install_lightbox_theme_zip',
				deleteTheme: 'fg_delete_lightbox_theme'
			}
		});
	});

	function initDetailsEditor() {
		var textarea = document.getElementById('fg-details-description');
		if (!textarea || typeof window.quicktags !== 'function') {
			return;
		}

		window.quicktags({
			id: 'fg-details-description',
			buttons: 'strong,em,link,ul,ol,li,close',
		});

		if (typeof window.QTags !== 'undefined' && typeof window.QTags._buttonsInit === 'function') {
			window.QTags._buttonsInit();
		}
	}

	/* =========================================================================
	 * 1. Galleries Explorer Workspace
	 * ========================================================================= */
	function initFolderExplorer() {
		var explorer = document.getElementById('fg-explorer');
		var leftPane = document.getElementById('fg-pane-list');
		var backBtn = document.getElementById('fg-nav-back');
		var breadcrumbList = document.getElementById('fg-breadcrumb-list');
		var deleteBtn = document.querySelector('#fg-explorer .fg-toolbar-btn[data-action="delete"]');
		var uploadImageBtn = document.querySelector('#fg-explorer .fg-toolbar-btn[data-action="upload-image"]');
		var newFolderBtn = document.querySelector('#fg-explorer .fg-toolbar-btn[data-action="new-folder"]');
		var copyBtn = document.querySelector('#fg-explorer .fg-toolbar-btn[data-action="copy"]');
		var pasteBtn = document.querySelector('#fg-explorer .fg-toolbar-btn[data-action="paste"]');
		var imageInput = document.getElementById('fg-image-input');
		var uploadZipBtn = document.querySelector('#fg-explorer .fg-toolbar-btn[data-action="upload-zip"]');
		var zipInput = document.getElementById('fg-zip-input');
		var renameBtn = document.querySelector('#fg-explorer .fg-toolbar-btn[data-action="rename"]');
		var regenSizesBtn = document.querySelector('#fg-explorer .fg-toolbar-btn[data-action="regenerate-sizes"]');
		var detailsPlaceholder = document.getElementById('fg-details-placeholder');
		var detailsForm = document.getElementById('fg-details-form');
		var detailsUpdateBtn = document.getElementById('fg-details-update-btn');
		var detailsStatus = document.getElementById('fg-details-status');
		var detailsFolderIcon = document.getElementById('fg-details-folder-icon');
		var detailsImage = document.getElementById('fg-details-image');
		var detailsFilename = document.getElementById('fg-details-filename');
		var detailsDimensionsWrap = document.getElementById('fg-details-dimensions-wrap');
		var detailsDimensions = document.getElementById('fg-details-dimensions');
		var detailsTooLargeBadge = document.getElementById('fg-details-too-large-badge');
		var detailsTitleInput = document.getElementById('fg-details-title');
		var detailsDescInput = document.getElementById('fg-details-description');
		var navShortcodeText = document.getElementById('fg-nav-shortcode-text');
		var navCopyShortcodeBtn = document.getElementById('fg-nav-copy-shortcode');
		var detailsShortcodeWrap = document.getElementById('fg-details-shortcode-wrap');
		var detailsShortcodeInput = document.getElementById('fg-details-shortcode-input');
		var detailsCopyShortcodeBtn = document.getElementById('fg-details-copy-shortcode');

		if (!explorer || !leftPane || !backBtn || !breadcrumbList || !deleteBtn || typeof fgAdminConfig === 'undefined') {
			return;
		}

		initPanelResizer({
			workspaceId: 'fg-workspace',
			leftPaneId: 'fg-pane-list',
			resizerId: 'fg-resizer',
			storageKey: 'fg_explorer_left_ratio'
		});

		var currentPath = '';
		var selectedPaths = new Set();
		var anchorPath = null;
		var clipboardItems = [];
		var isLoading = false;
		var detailsInitialTitle = '';
		var detailsInitialDescription = '';

		function isDetailsDirty() {
			if (!detailsTitleInput || !detailsDescInput || selectedPaths.size !== 1) {
				return false;
			}
			return (
				detailsTitleInput.value !== detailsInitialTitle ||
				detailsDescInput.value !== detailsInitialDescription
			);
		}

		var sharedWorkspace = createWorkspace({
			root: '#fg-workspace',
			unsavedMessage: fgAdminConfig.i18n.unsavedConfirm || 'You have unsaved changes. Discard them and continue?',
			isBusy: function () {
				return isLoading;
			},
			canSave: function () {
				return !isLoading && selectedPaths.size === 1;
			},
			computeDirty: function () {
				return isDetailsDirty();
			},
			onSave: function () {
				if (detailsForm) {
					detailsForm.dispatchEvent(new Event('submit', { cancelable: true }));
				}
			},
			onNavigate: function (direction, e) {
				if (selectedPaths.size === 0) {
					return;
				}
				var rows = getOrderedRows();
				if (rows.length === 0) {
					return;
				}
				var currentIndex = rows.findIndex(function (row) {
					return selectedPaths.has(normalizePath(row.dataset.path));
				});
				if (currentIndex === -1) {
					return;
				}
				var nextIndex = currentIndex + direction;
				if (nextIndex < 0 || nextIndex >= rows.length) {
					return;
				}
				e.preventDefault();
				if (!sharedWorkspace.confirmDiscardIfDirty()) {
					return;
				}
				var nextRow = rows[nextIndex];
				var nextPath = normalizePath(nextRow.dataset.path);
				selectedPaths.clear();
				selectedPaths.add(nextPath);
				anchorPath = nextPath;
				applySelectionClasses();
				if (typeof nextRow.scrollIntoView === 'function') {
					nextRow.scrollIntoView({ block: 'nearest' });
				}
			}
		});

		function buildShortcode(path) {
			var clean = normalizePath(path);
			if (!clean) {
				return '[folder_gallery]';
			}
			var safeAttr = clean.replace(/"/g, '&quot;');
			return '[folder_gallery path="' + safeAttr + '"]';
		}

		function syncDetailsDirtyState() {
			var isDirty = isDetailsDirty();
			if (sharedWorkspace) {
				sharedWorkspace.recomputeDirty();
			}
			if (detailsUpdateBtn) {
				detailsUpdateBtn.disabled = isLoading || selectedPaths.size !== 1 || !isDirty;
			}
		}

		function isFileTooLarge(file) {
			var maxBytes = Number(fgAdminConfig.maxUploadBytes) || 0;
			return maxBytes > 0 && file && typeof file.size === 'number' && file.size > maxBytes;
		}

		function formatTooLargeMessage(fileName) {
			var template = fgAdminConfig.i18n.fileTooLarge || 'The file "%1$s" exceeds the maximum allowed upload size of %2$s.';
			return template
				.replace('%1$s', fileName)
				.replace('%2$s', fgAdminConfig.maxUploadLabel || '');
		}

		function normalizePath(rawPath) {
			if (!rawPath) {
				return '';
			}
			return String(rawPath)
				.replace(/\\/g, '/')
				.replace(/^\/+|\/+$/g, '');
		}

		function getParentPath(path) {
			var clean = normalizePath(path);
			if (!clean) {
				return '';
			}
			var parts = clean.split('/');
			parts.pop();
			return parts.join('/');
		}

		function getOrderedRows() {
			return Array.from(leftPane.querySelectorAll('.fg-row'));
		}

		function getRowItemData(row) {
			var path = normalizePath(row.dataset.path);
			var labelEl = row.querySelector('.fg-item-label');
			var thumbEl = row.querySelector('.fg-thumb');
			return {
				path: path,
				kind: row.dataset.kind || 'image',
				name: labelEl ? labelEl.textContent.trim() : path.split('/').pop(),
				url: thumbEl ? thumbEl.getAttribute('src') : '',
				title: row.dataset.title || '',
				description: row.dataset.description || '',
				width: parseInt(row.dataset.width || '0', 10) || 0,
				height: parseInt(row.dataset.height || '0', 10) || 0,
				megapixels: parseFloat(row.dataset.megapixels || '0') || 0,
				tooLarge: row.dataset.tooLarge === '1',
			};
		}

		function syncDetailsPanel() {
			if (!detailsPlaceholder || !detailsForm) {
				return;
			}

			if (detailsStatus) {
				detailsStatus.textContent = '';
			}

			if (selectedPaths.size === 0) {
				detailsInitialTitle = '';
				detailsInitialDescription = '';
				detailsForm.hidden = true;
				detailsPlaceholder.hidden = false;
				detailsPlaceholder.textContent = fgAdminConfig.i18n.detailsEmpty;
				syncDetailsDirtyState();
				return;
			}

			if (selectedPaths.size > 1) {
				detailsInitialTitle = '';
				detailsInitialDescription = '';
				detailsForm.hidden = true;
				detailsPlaceholder.hidden = false;
				detailsPlaceholder.textContent = fgAdminConfig.i18n.detailsMulti.replace('%d', String(selectedPaths.size));
				syncDetailsDirtyState();
				return;
			}

			var items = getSelectedItemsData();
			if (items.length !== 1) {
				return;
			}

			var item = items[0];
			detailsPlaceholder.hidden = true;
			detailsForm.hidden = false;

			if (item.kind === 'folder') {
				detailsFolderIcon.hidden = false;
				detailsImage.hidden = true;
				detailsImage.removeAttribute('src');
				if (detailsDimensionsWrap) {
					detailsDimensionsWrap.hidden = true;
				}
				if (detailsTooLargeBadge) {
					detailsTooLargeBadge.hidden = true;
				}
				if (detailsShortcodeWrap && detailsShortcodeInput) {
					detailsShortcodeInput.value = buildShortcode(item.path);
					detailsShortcodeWrap.hidden = false;
				}
			} else {
				detailsFolderIcon.hidden = true;
				detailsImage.hidden = false;
				detailsImage.src = item.url || '';
				detailsImage.alt = item.name;
				if (detailsDimensionsWrap && detailsDimensions) {
					if (item.width > 0 && item.height > 0) {
						var dimLabel = String(item.width) + ' × ' + String(item.height) + ' px';
						if (item.megapixels > 0) {
							dimLabel += ' (' + String(item.megapixels) + ' MP)';
						}
						detailsDimensions.textContent = dimLabel;
						detailsDimensionsWrap.hidden = false;
					} else {
						detailsDimensions.textContent = '';
						detailsDimensionsWrap.hidden = true;
					}
				}
				if (detailsTooLargeBadge) {
					detailsTooLargeBadge.hidden = !item.tooLarge;
				}
				if (detailsShortcodeWrap && detailsShortcodeInput) {
					detailsShortcodeInput.value = '';
					detailsShortcodeWrap.hidden = true;
				}
			}

			detailsInitialTitle = item.title || '';
			detailsInitialDescription = item.description || '';
			detailsFilename.textContent = item.name;
			detailsTitleInput.value = detailsInitialTitle;
			detailsDescInput.value = detailsInitialDescription;

			syncDetailsDirtyState();
		}

		function syncToolbarState() {
			var hasSelection = selectedPaths.size > 0;
			backBtn.disabled = (currentPath === '') || isLoading;
			deleteBtn.disabled = isLoading || !hasSelection;
			if (uploadImageBtn) {
				uploadImageBtn.disabled = isLoading;
			}
			if (newFolderBtn) {
				newFolderBtn.disabled = isLoading;
			}
			if (copyBtn) {
				copyBtn.disabled = isLoading || !hasSelection;
			}
			if (pasteBtn) {
				pasteBtn.disabled = isLoading || clipboardItems.length === 0;
			}
			if (uploadZipBtn) {
				uploadZipBtn.disabled = isLoading;
			}
			if (renameBtn) {
				renameBtn.disabled = isLoading || selectedPaths.size !== 1;
			}
			if (regenSizesBtn) {
				regenSizesBtn.disabled = isLoading;
			}
			syncDetailsDirtyState();
		}

		function regenerateCurrentFolderSizes() {
			if (isLoading) {
				return;
			}

			isLoading = true;
			syncToolbarState();

			var formData = new URLSearchParams();
			formData.append('action', 'fg_regenerate_sizes');
			formData.append('nonce', fgAdminConfig.nonce);
			formData.append('path', currentPath);

			fetch(fgAdminConfig.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
				},
				body: formData.toString(),
			})
				.then(function (response) {
					return response.json();
				})
				.then(function (payload) {
					if (!payload || !payload.success) {
						var errMsg = (payload && payload.data && payload.data.message)
							? payload.data.message
							: (fgAdminConfig.i18n.regenError || 'Could not regenerate image sizes for this folder.');
						window.alert(errMsg);
						isLoading = false;
						syncToolbarState();
						return;
					}

					navigateTo(currentPath, { force: true, skipDirtyPrompt: true });
				})
				.catch(function () {
					window.alert(fgAdminConfig.i18n.regenError || 'Could not regenerate image sizes for this folder.');
					isLoading = false;
					syncToolbarState();
				});
		}

		function renameSelectedItem() {
			if (selectedPaths.size !== 1 || isLoading) {
				return;
			}

			var items = getSelectedItemsData();
			if (items.length !== 1) {
				return;
			}

			var currentItem = items[0];
			var enteredName = window.prompt(fgAdminConfig.i18n.renamePrompt, currentItem.name);
			if (enteredName === null) {
				return;
			}

			var trimmed = enteredName.trim();
			if (!trimmed || trimmed === currentItem.name) {
				return;
			}

			isLoading = true;
			syncToolbarState();

			var formData = new URLSearchParams();
			formData.append('action', 'fg_rename_item');
			formData.append('nonce', fgAdminConfig.nonce);
			formData.append('path', currentItem.path);
			formData.append('new_name', trimmed);

			fetch(fgAdminConfig.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
				},
				body: formData.toString(),
			})
				.then(function (response) {
					return response.json();
				})
				.then(function (payload) {
					if (!payload || !payload.success) {
						var errMsg = (payload && payload.data && payload.data.message)
							? payload.data.message
							: fgAdminConfig.i18n.renameError;
						window.alert(errMsg);
						isLoading = false;
						syncToolbarState();
						return;
					}

					clipboardItems = clipboardItems.map(function (c) {
						if (c.path === currentItem.path && payload.data && payload.data.path) {
							return {
								path: payload.data.path,
								kind: c.kind,
								name: payload.data.name || trimmed,
							};
						}
						return c;
					});

					navigateTo(currentPath, { force: true, skipDirtyPrompt: true });
				})
				.catch(function () {
					window.alert(fgAdminConfig.i18n.renameError);
					isLoading = false;
					syncToolbarState();
				});
		}

		function applySelectionClasses() {
			var rows = getOrderedRows();
			rows.forEach(function (row) {
				var rowPath = normalizePath(row.dataset.path);
				if (selectedPaths.has(rowPath)) {
					row.classList.add('is-selected');
				} else {
					row.classList.remove('is-selected');
				}
			});
			syncToolbarState();
			syncDetailsPanel();
		}

		function clearSelection() {
			selectedPaths.clear();
			anchorPath = null;
			applySelectionClasses();
		}

		function handleRowClick(row, event) {
			if (!row || !row.dataset.path) {
				return;
			}

			if (sharedWorkspace && !sharedWorkspace.confirmDiscardIfDirty()) {
				return;
			}

			var path = normalizePath(row.dataset.path);
			var isToggle = event.ctrlKey || event.metaKey;
			var isRange = event.shiftKey;
			var rows = getOrderedRows();

			if (isRange) {
				var targetIndex = rows.findIndex(function (r) {
					return normalizePath(r.dataset.path) === path;
				});

				var startIndex = 0;
				if (anchorPath) {
					var foundAnchor = rows.findIndex(function (r) {
						return normalizePath(r.dataset.path) === anchorPath;
					});
					if (foundAnchor !== -1) {
						startIndex = foundAnchor;
					}
				}

				if (targetIndex !== -1) {
					if (!isToggle) {
						selectedPaths.clear();
					}
					var minIdx = Math.min(startIndex, targetIndex);
					var maxIdx = Math.max(startIndex, targetIndex);
					for (var i = minIdx; i <= maxIdx; i++) {
						selectedPaths.add(normalizePath(rows[i].dataset.path));
					}
				}
			} else if (isToggle) {
				if (selectedPaths.has(path)) {
					selectedPaths.delete(path);
				} else {
					selectedPaths.add(path);
				}
				anchorPath = path;
			} else {
				if (selectedPaths.size === 1 && selectedPaths.has(path)) {
					selectedPaths.clear();
					anchorPath = null;
				} else {
					selectedPaths.clear();
					selectedPaths.add(path);
					anchorPath = path;
				}
			}

			applySelectionClasses();
		}

		function getSelectedItemsData() {
			var items = [];
			var rows = getOrderedRows();
			rows.forEach(function (row) {
				var path = normalizePath(row.dataset.path);
				if (selectedPaths.has(path)) {
					items.push(getRowItemData(row));
				}
			});
			return items;
		}

		function copySelectedItems() {
			if (selectedPaths.size === 0 || isLoading) {
				return;
			}

			clipboardItems = getSelectedItemsData();
			syncToolbarState();
		}

		function pasteClipboardItems() {
			if (clipboardItems.length === 0 || isLoading) {
				return;
			}

			isLoading = true;
			syncToolbarState();

			var formData = new URLSearchParams();
			formData.append('action', 'fg_paste_item');
			formData.append('nonce', fgAdminConfig.nonce);
			formData.append('target_path', currentPath);

			clipboardItems.forEach(function (item) {
				formData.append('source_paths[]', item.path);
			});

			fetch(fgAdminConfig.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
				},
				body: formData.toString(),
			})
				.then(function (response) {
					return response.json();
				})
				.then(function (payload) {
					if (!payload || !payload.success) {
						var errMsg = (payload && payload.data && payload.data.message)
							? payload.data.message
							: fgAdminConfig.i18n.pasteError;
						window.alert(errMsg);
						isLoading = false;
						syncToolbarState();
						return;
					}

					if (payload.data && Array.isArray(payload.data.errors) && payload.data.errors.length > 0) {
						window.alert(payload.data.errors.join('\n'));
					}

					navigateTo(currentPath, { force: true, skipDirtyPrompt: true });
				})
				.catch(function () {
					window.alert(fgAdminConfig.i18n.pasteError);
					isLoading = false;
					syncToolbarState();
				});
		}

		function updateNavigationUI(path) {
			currentPath = normalizePath(path);

			if (navShortcodeText) {
				navShortcodeText.textContent = buildShortcode(currentPath);
			}

			syncToolbarState();

			breadcrumbList.innerHTML = '';

			var segments = currentPath ? currentPath.split('/') : [];
			var crumbs = [{ label: 'folder-gallery', path: '' }];

			var acc = '';
			segments.forEach(function (segment) {
				acc = acc ? acc + '/' + segment : segment;
				crumbs.push({ label: segment, path: acc });
			});

			crumbs.forEach(function (crumb, index) {
				var isLast = index === crumbs.length - 1;
				var li = document.createElement('li');
				li.className = 'fg-breadcrumb-item' + (isLast ? ' is-current' : '');

				var link = document.createElement('a');
				link.href = '#';
				link.dataset.path = crumb.path;
				link.textContent = crumb.label;

				li.appendChild(link);
				breadcrumbList.appendChild(li);
			});
		}

		function formatFolderCount(count) {
			var template = count === 1 ? fgAdminConfig.i18n.singleImage : fgAdminConfig.i18n.pluralImages;
			return template.replace('%d', String(count));
		}

		function renderVariantCell(variantData, fallbackLabel) {
			if (variantData && variantData.exists && variantData.url) {
				var dims = (variantData.width && variantData.height)
					? String(variantData.width) + '×' + String(variantData.height)
					: '';
				return '<td class="fg-col-variant">' +
					'<img class="fg-variant-thumb" src="' + escapeAttr(variantData.url) + '" alt="' + escapeAttr(fallbackLabel) + '"' +
					(dims ? ' title="' + escapeAttr(dims) + '"' : '') +
					' loading="lazy">' +
					'</td>';
			}

			return '<td class="fg-col-variant"><span class="fg-variant-missing">—</span></td>';
		}

		function renderList(data) {
			clearSelection();

			var folders = Array.isArray(data.folders) ? data.folders : [];
			var images = Array.isArray(data.images) ? data.images : [];

			if (folders.length === 0 && images.length === 0) {
				leftPane.innerHTML =
					'<div class="fg-pane-placeholder">' +
					escapeHtml(fgAdminConfig.i18n.emptyFolder) +
					'</div>';
				return;
			}

			var rowsHtml = '';

			folders.forEach(function (folder) {
				var folderTitle = typeof folder.title === 'string' ? folder.title : '';
				var folderDesc = typeof folder.description === 'string' ? folder.description : '';

				rowsHtml +=
					'<tr class="fg-row fg-row-folder"' +
						' data-kind="folder"' +
						' data-path="' + escapeAttr(folder.path) + '"' +
						' data-title="' + escapeAttr(folderTitle) + '"' +
						' data-description="' + escapeAttr(folderDesc) + '">' +
						'<td class="fg-col-name">' +
							'<span class="fg-item-name-cell">' +
								'<span class="fg-folder-icon" aria-hidden="true">' +
									'<span class="dashicons dashicons-portfolio"></span>' +
								'</span>' +
								'<span class="fg-item-label">' + escapeHtml(folder.name) + '</span>' +
							'</span>' +
						'</td>' +
						'<td class="fg-col-date">' + escapeHtml(folder.date) + '</td>' +
						'<td class="fg-col-type">' + escapeHtml(folder.type) + '</td>' +
						'<td class="fg-col-size">' + escapeHtml(formatFolderCount(folder.image_count)) + '</td>' +
						'<td class="fg-col-variant">—</td>' +
						'<td class="fg-col-variant">—</td>' +
					'</tr>';
			});

			var smallLabel = (fgAdminConfig.i18n && fgAdminConfig.i18n.smallVariant) || 'Small';
			var mediumLabel = (fgAdminConfig.i18n && fgAdminConfig.i18n.mediumVariant) || 'Medium';
			var tooLargeLabel = (fgAdminConfig.i18n && fgAdminConfig.i18n.imageTooLarge) || 'Image too large';

			images.forEach(function (image) {
				var imageTitle = typeof image.title === 'string' ? image.title : '';
				var imageDesc = typeof image.description === 'string' ? image.description : '';
				var variants = (image && typeof image.variants === 'object' && image.variants !== null) ? image.variants : {};
				var imgW = parseInt(image.width || '0', 10) || 0;
				var imgH = parseInt(image.height || '0', 10) || 0;
				var imgMp = parseFloat(image.megapixels || '0') || 0;
				var isTooLarge = Boolean(image.too_large);
				var thumbSrc = (variants.small && variants.small.exists && variants.small.url) ? variants.small.url : image.url;
				var warningHtml = isTooLarge
					? '<span class="fg-warning-badge" title="' + escapeAttr(tooLargeLabel) + '" aria-label="' + escapeAttr(tooLargeLabel) + '">' +
						'<span class="dashicons dashicons-warning" aria-hidden="true"></span>' +
					  '</span>'
					: '';

				rowsHtml +=
					'<tr class="fg-row fg-row-image' + (isTooLarge ? ' is-too-large' : '') + '"' +
						' data-kind="image"' +
						' data-path="' + escapeAttr(image.path) + '"' +
						' data-title="' + escapeAttr(imageTitle) + '"' +
						' data-description="' + escapeAttr(imageDesc) + '"' +
						' data-width="' + escapeAttr(imgW) + '"' +
						' data-height="' + escapeAttr(imgH) + '"' +
						' data-megapixels="' + escapeAttr(imgMp) + '"' +
						' data-too-large="' + (isTooLarge ? '1' : '0') + '">' +
						'<td class="fg-col-name">' +
							'<span class="fg-item-name-cell">' +
								'<img class="fg-thumb" src="' + escapeAttr(thumbSrc) + '" alt="' + escapeAttr(image.name) + '" loading="lazy">' +
								warningHtml +
								'<span class="fg-item-label">' + escapeHtml(image.name) + '</span>' +
							'</span>' +
						'</td>' +
						'<td class="fg-col-date">' + escapeHtml(image.date) + '</td>' +
						'<td class="fg-col-type">' + escapeHtml(image.type) + '</td>' +
						'<td class="fg-col-size">' + escapeHtml(image.size) + '</td>' +
						renderVariantCell(variants.small, smallLabel) +
						renderVariantCell(variants.medium, mediumLabel) +
					'</tr>';
			});

			leftPane.innerHTML =
				'<table class="fg-list-table">' +
					'<thead>' +
						'<tr>' +
							'<th class="fg-col-name">Name</th>' +
							'<th class="fg-col-date">Date</th>' +
							'<th class="fg-col-type">Type</th>' +
							'<th class="fg-col-size">Size</th>' +
							'<th class="fg-col-variant" title="Small image variant">S</th>' +
							'<th class="fg-col-variant" title="Medium image variant">M</th>' +
						'</tr>' +
					'</thead>' +
					'<tbody>' + rowsHtml + '</tbody>' +
				'</table>';
		}

		function renderError(message) {
			clearSelection();
			leftPane.innerHTML =
				'<div class="fg-pane-placeholder">' + escapeHtml(message) + '</div>';
		}

		function navigateTo(targetPath, options) {
			var opts = options || {};
			if (isLoading && !opts.force) {
				return;
			}

			if (!opts.skipDirtyPrompt && sharedWorkspace && !sharedWorkspace.confirmDiscardIfDirty()) {
				return;
			}

			var cleanTarget = normalizePath(targetPath);
			isLoading = true;
			syncToolbarState();

			var formData = new URLSearchParams();
			formData.append('action', 'fg_scan_folder');
			formData.append('nonce', fgAdminConfig.nonce);
			formData.append('path', cleanTarget);

			fetch(fgAdminConfig.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
				},
				body: formData.toString(),
			})
				.then(function (response) {
					return response.json();
				})
				.then(function (payload) {
					if (!payload || !payload.success) {
						var errMsg = (payload && payload.data && payload.data.message)
							? payload.data.message
							: fgAdminConfig.i18n.unknownError;
						renderError(errMsg);
						return;
					}

					updateNavigationUI(payload.data.path);
					renderList(payload.data);
					leftPane.scrollTop = 0;
				})
				.catch(function () {
					renderError(fgAdminConfig.i18n.unknownError);
				})
				.finally(function () {
					isLoading = false;
					syncToolbarState();
				});
		}

		function createNewFolder() {
			if (isLoading) {
				return;
			}

			var enteredName = window.prompt(fgAdminConfig.i18n.newFolderPrompt, '');
			if (enteredName === null) {
				return;
			}

			var trimmed = enteredName.trim();
			if (!trimmed) {
				return;
			}

			isLoading = true;
			syncToolbarState();

			var formData = new URLSearchParams();
			formData.append('action', 'fg_create_folder');
			formData.append('nonce', fgAdminConfig.nonce);
			formData.append('path', currentPath);
			formData.append('name', trimmed);

			fetch(fgAdminConfig.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
				},
				body: formData.toString(),
			})
				.then(function (response) {
					return response.json();
				})
				.then(function (payload) {
					if (!payload || !payload.success) {
						var errMsg = (payload && payload.data && payload.data.message)
							? payload.data.message
							: fgAdminConfig.i18n.createFolderErr;
						window.alert(errMsg);
						isLoading = false;
						syncToolbarState();
						return;
					}

					navigateTo(currentPath, { force: true, skipDirtyPrompt: true });
				})
				.catch(function () {
					window.alert(fgAdminConfig.i18n.createFolderErr);
					isLoading = false;
					syncToolbarState();
				});
		}

		function uploadSelectedImages(fileList) {
			if (!fileList || fileList.length === 0 || isLoading) {
				return;
			}

			var validFiles = [];
			var oversizeMessages = [];

			Array.from(fileList).forEach(function (file) {
				if (isFileTooLarge(file)) {
					oversizeMessages.push(formatTooLargeMessage(file.name));
				} else {
					validFiles.push(file);
				}
			});

			if (validFiles.length === 0) {
				if (oversizeMessages.length > 0) {
					window.alert(oversizeMessages.join('\n'));
				}
				if (imageInput) {
					imageInput.value = '';
				}
				return;
			}

			isLoading = true;
			syncToolbarState();

			var formData = new FormData();
			formData.append('action', 'fg_upload_images');
			formData.append('nonce', fgAdminConfig.nonce);
			formData.append('path', currentPath);

			validFiles.forEach(function (file) {
				formData.append('images[]', file);
			});

			fetch(fgAdminConfig.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData,
			})
				.then(function (response) {
					return response.json();
				})
				.then(function (payload) {
					if (!payload || !payload.success) {
						var errMsg = (payload && payload.data && payload.data.message)
							? payload.data.message
							: fgAdminConfig.i18n.uploadError;
						var combined = oversizeMessages.concat([errMsg]);
						window.alert(combined.join('\n'));
						isLoading = false;
						syncToolbarState();
						return;
					}

					var serverErrors = (payload.data && Array.isArray(payload.data.errors)) ? payload.data.errors : [];
					var allWarnings = oversizeMessages.concat(serverErrors);
					if (allWarnings.length > 0) {
						window.alert(allWarnings.join('\n'));
					}

					navigateTo(currentPath, { force: true, skipDirtyPrompt: true });
				})
				.catch(function () {
					var combined = oversizeMessages.concat([fgAdminConfig.i18n.uploadError]);
					window.alert(combined.join('\n'));
					isLoading = false;
					syncToolbarState();
				})
				.finally(function () {
					if (imageInput) {
						imageInput.value = '';
					}
				});
		}

		function uploadSelectedZip(file) {
			if (!file || isLoading) {
				return;
			}

			if (isFileTooLarge(file)) {
				window.alert(formatTooLargeMessage(file.name));
				if (zipInput) {
					zipInput.value = '';
				}
				return;
			}

			isLoading = true;
			syncToolbarState();

			var formData = new FormData();
			formData.append('action', 'fg_upload_zip');
			formData.append('nonce', fgAdminConfig.nonce);
			formData.append('path', currentPath);
			formData.append('zip_file', file);

			fetch(fgAdminConfig.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData,
			})
				.then(function (response) {
					return response.json();
				})
				.then(function (payload) {
					if (!payload || !payload.success) {
						var errMsg = (payload && payload.data && payload.data.message)
							? payload.data.message
							: fgAdminConfig.i18n.zipError;
						window.alert(errMsg);
						isLoading = false;
						syncToolbarState();
						return;
					}

					navigateTo(currentPath, { force: true, skipDirtyPrompt: true });
				})
				.catch(function () {
					window.alert(fgAdminConfig.i18n.zipError);
					isLoading = false;
					syncToolbarState();
				})
				.finally(function () {
					if (zipInput) {
						zipInput.value = '';
					}
				});
		}

		function deleteSelectedItems() {
			var items = getSelectedItemsData();
			if (items.length === 0 || isLoading) {
				return;
			}

			var message = '';
			if (items.length > 1) {
				message = fgAdminConfig.i18n.confirmMultiple.replace('%d', String(items.length));
			} else {
				var single = items[0];
				var template = single.kind === 'folder'
					? fgAdminConfig.i18n.confirmFolder
					: fgAdminConfig.i18n.confirmImage;
				message = template.replace('%s', single.name);
			}

			if (!window.confirm(message)) {
				return;
			}

			isLoading = true;
			syncToolbarState();

			var formData = new URLSearchParams();
			formData.append('action', 'fg_delete_item');
			formData.append('nonce', fgAdminConfig.nonce);

			items.forEach(function (item) {
				formData.append('paths[]', item.path);
			});

			fetch(fgAdminConfig.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
				},
				body: formData.toString(),
			})
				.then(function (response) {
					return response.json();
				})
				.then(function (payload) {
					if (!payload || !payload.success) {
						var errMsg = (payload && payload.data && payload.data.message)
							? payload.data.message
							: fgAdminConfig.i18n.deleteError;
						window.alert(errMsg);
						isLoading = false;
						syncToolbarState();
						return;
					}

					if (payload.data && Array.isArray(payload.data.errors) && payload.data.errors.length > 0) {
						window.alert(payload.data.errors.join('\n'));
					}

					var deletedSet = new Set(items.map(function (i) { return i.path; }));
					clipboardItems = clipboardItems.filter(function (c) { return !deletedSet.has(c.path); });

					clearSelection();
					navigateTo(currentPath, { force: true, skipDirtyPrompt: true });
				})
				.catch(function () {
					window.alert(fgAdminConfig.i18n.deleteError);
					isLoading = false;
					syncToolbarState();
				});
		}

		if (copyBtn) {
			copyBtn.addEventListener('click', copySelectedItems);
		}
		if (pasteBtn) {
			pasteBtn.addEventListener('click', pasteClipboardItems);
		}
		if (renameBtn) {
			renameBtn.addEventListener('click', renameSelectedItem);
		}
		if (regenSizesBtn) {
			regenSizesBtn.addEventListener('click', regenerateCurrentFolderSizes);
		}

		if (detailsTitleInput) {
			detailsTitleInput.addEventListener('input', syncDetailsDirtyState);
		}
		if (detailsDescInput) {
			detailsDescInput.addEventListener('input', syncDetailsDirtyState);
			detailsDescInput.addEventListener('change', syncDetailsDirtyState);
		}

		if (detailsForm) {
			detailsForm.addEventListener('click', function (e) {
				if (e.target && e.target.closest('.quicktags-toolbar')) {
					window.setTimeout(syncDetailsDirtyState, 0);
				}
			});

			detailsForm.addEventListener('submit', function (e) {
				e.preventDefault();

				if (isLoading || selectedPaths.size !== 1 || !isDetailsDirty()) {
					return;
				}

				var items = getSelectedItemsData();
				if (items.length !== 1) {
					return;
				}

				var item = items[0];
				var newTitle = detailsTitleInput ? detailsTitleInput.value : '';
				var newDesc = detailsDescInput ? detailsDescInput.value : '';

				isLoading = true;
				syncToolbarState();
				if (detailsStatus) {
					detailsStatus.textContent = '';
				}

				var formData = new URLSearchParams();
				formData.append('action', 'fg_save_details');
				formData.append('nonce', fgAdminConfig.nonce);
				formData.append('path', item.path);
				formData.append('kind', item.kind);
				formData.append('title', newTitle);
				formData.append('description', newDesc);

				fetch(fgAdminConfig.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
					},
					body: formData.toString(),
				})
					.then(function (response) {
						return response.json();
					})
					.then(function (payload) {
						if (!payload || !payload.success) {
							var errMsg = (payload && payload.data && payload.data.message)
								? payload.data.message
								: fgAdminConfig.i18n.updateError;
							window.alert(errMsg);
							return;
						}

						var savedTitle = (payload.data && typeof payload.data.title === 'string')
							? payload.data.title
							: newTitle;
						var savedDesc = (payload.data && typeof payload.data.description === 'string')
							? payload.data.description
							: newDesc;

						var matchingRow = getOrderedRows().find(function (r) {
							return normalizePath(r.dataset.path) === item.path;
						});
						if (matchingRow) {
							matchingRow.dataset.title = savedTitle;
							matchingRow.dataset.description = savedDesc;
						}

						detailsInitialTitle = savedTitle;
						detailsInitialDescription = savedDesc;

						if (detailsTitleInput) {
							detailsTitleInput.value = savedTitle;
						}
						if (detailsDescInput) {
							detailsDescInput.value = savedDesc;
						}
						if (detailsStatus) {
							detailsStatus.textContent = fgAdminConfig.i18n.updateSaved;
						}
					})
					.catch(function () {
						window.alert(fgAdminConfig.i18n.updateError);
					})
					.finally(function () {
						isLoading = false;
						syncToolbarState();
					});
			});
		}

		document.addEventListener('keydown', function (e) {
			if (isLoading || isEditableTarget(e.target)) {
				return;
			}

			if (!e.ctrlKey && !e.metaKey && !e.altKey && e.key === 'Enter') {
				if (selectedPaths.size === 1) {
					var selectedRow = getOrderedRows().find(function (row) {
						return selectedPaths.has(normalizePath(row.dataset.path));
					});
					if (selectedRow && selectedRow.dataset.kind === 'folder' && selectedRow.dataset.path) {
						e.preventDefault();
						navigateTo(selectedRow.dataset.path);
					}
				}
				return;
			}

			if (!explorer.contains(document.activeElement) && selectedPaths.size === 0 && clipboardItems.length === 0) {
				return;
			}

			var hasModifier = e.ctrlKey || e.metaKey;

			if (hasModifier && !e.altKey && (e.key === 'c' || e.key === 'C')) {
				if (selectedPaths.size > 0) {
					e.preventDefault();
					copySelectedItems();
				}
			} else if (hasModifier && !e.altKey && (e.key === 'v' || e.key === 'V')) {
				if (clipboardItems.length > 0) {
					e.preventDefault();
					pasteClipboardItems();
				}
			} else if (!hasModifier && !e.altKey && e.key === 'Delete') {
				if (selectedPaths.size > 0) {
					e.preventDefault();
					deleteSelectedItems();
				}
			} else if (!hasModifier && !e.altKey && e.key === 'F2') {
				if (selectedPaths.size === 1) {
					e.preventDefault();
					renameSelectedItem();
				}
			}
		});

		if (newFolderBtn) {
			newFolderBtn.addEventListener('click', createNewFolder);
		}

		if (uploadImageBtn && imageInput) {
			uploadImageBtn.addEventListener('click', function () {
				if (!isLoading) {
					imageInput.click();
				}
			});

			imageInput.addEventListener('change', function () {
				if (imageInput.files && imageInput.files.length > 0) {
					uploadSelectedImages(imageInput.files);
				}
			});
		}

		if (uploadZipBtn && zipInput) {
			uploadZipBtn.addEventListener('click', function () {
				if (!isLoading) {
					zipInput.click();
				}
			});

			zipInput.addEventListener('change', function () {
				if (zipInput.files && zipInput.files.length > 0) {
					uploadSelectedZip(zipInput.files[0]);
				}
			});
		}

		if (navCopyShortcodeBtn && navShortcodeText) {
			navCopyShortcodeBtn.addEventListener('click', function () {
				copyTextWithFeedback(navShortcodeText.textContent, navCopyShortcodeBtn, fgAdminConfig.i18n);
			});
		}

		if (detailsCopyShortcodeBtn && detailsShortcodeInput) {
			detailsCopyShortcodeBtn.addEventListener('click', function () {
				copyTextWithFeedback(detailsShortcodeInput.value, detailsCopyShortcodeBtn, fgAdminConfig.i18n);
			});
		}

		if (detailsShortcodeInput) {
			detailsShortcodeInput.addEventListener('click', function () {
				detailsShortcodeInput.select();
			});
		}

		leftPane.addEventListener('click', function (e) {
			var row = e.target.closest('.fg-row');
			if (!row) {
				return;
			}
			handleRowClick(row, e);
		});

		leftPane.addEventListener('dblclick', function (e) {
			var row = e.target.closest('.fg-row-folder');
			if (!row || !row.dataset.path) {
				return;
			}
			navigateTo(row.dataset.path);
		});

		deleteBtn.addEventListener('click', deleteSelectedItems);

		backBtn.addEventListener('click', function () {
			if (currentPath !== '') {
				navigateTo(getParentPath(currentPath));
			}
		});

		breadcrumbList.addEventListener('click', function (e) {
			var link = e.target.closest('a[data-path]');
			if (!link) {
				return;
			}
			e.preventDefault();
			navigateTo(link.dataset.path || '');
		});

		updateNavigationUI('');
	}

	/* =========================================================================
	 * 2. Gallery & Lightbox Themes Master-Detail Workspace
	 * ========================================================================= */
	function initThemeManagerWorkspace(opts) {
		var o = opts || {};
		var p = o.prefix;
		var config = o.config;
		var explorer = document.getElementById(p + '-explorer');
		var dataEl = document.getElementById(p + '-initial-data');

		if (!p || !explorer || !dataEl || !config) {
			return;
		}

		var state = {
			activeSlug: '',
			selectedSlug: '',
			themes: {}
		};

		try {
			var parsed = JSON.parse(dataEl.textContent || '{}');
			state.activeSlug = parsed.activeSlug || '';
			state.selectedSlug = parsed.selectedSlug || '';
			state.themes = parsed.themes || {};
		} catch (err) {
			return;
		}

		var addThemeBtn = explorer.querySelector('.fg-toolbar-btn[data-action="add-theme"]');
		var deleteThemeBtn = explorer.querySelector('.fg-toolbar-btn[data-action="delete-theme"]');
		var zipInput = document.getElementById(p + '-zip-input');
		var listBody = document.getElementById(p + '-list-body');
		var placeholder = document.getElementById(p + '-details-placeholder');
		var form = document.getElementById(p + '-details-form');
		var formSlugInput = document.getElementById(p + '-form-slug');
		var updateBtn = document.getElementById(p + '-update-btn');
		var statusEl = document.getElementById(p + '-details-status');
		var previewWrap = document.getElementById(p + '-preview-wrap');
		var previewImg = document.getElementById(p + '-preview-img');
		var detailName = document.getElementById(p + '-detail-name');
		var activePill = document.getElementById(p + '-active-pill');
		var detailDesc = document.getElementById(p + '-detail-desc');
		var detailVersion = document.getElementById(p + '-detail-version');
		var detailAuthor = document.getElementById(p + '-detail-author');
		var detailSlug = document.getElementById(p + '-detail-slug');
		var shortcodeInput = document.getElementById(p + '-shortcode-input');
		var copyShortcodeBtn = document.getElementById(p + '-copy-shortcode');
		var settingsFields = document.getElementById(p + '-settings-fields');

		var isBusy = false;

		initPanelResizer({
			workspaceId: p + '-workspace',
			leftPaneId: p + '-pane-list',
			resizerId: p + '-resizer',
			storageKey: o.resizerStorageKey || (p + '_left_ratio')
		});

		function readCurrentFormSettings(theme) {
			var values = {};
			if (!theme || !theme.settingsSchema || !settingsFields) {
				return values;
			}
			Object.keys(theme.settingsSchema).forEach(function (key) {
				var field = theme.settingsSchema[key];
				var input = settingsFields.querySelector('[data-setting-key="' + key + '"]');
				if (!input) {
					values[key] = field.default;
					return;
				}
				if (field.type === 'boolean') {
					values[key] = Boolean(input.checked);
				} else if (field.type === 'number' || field.type === 'integer') {
					var num = Number(input.value);
					values[key] = Number.isNaN(num) ? field.default : num;
				} else {
					values[key] = String(input.value);
				}
			});
			return values;
		}

		function areSettingsDirty() {
			var theme = state.themes[state.selectedSlug];
			if (!theme || !theme.hasSettings || !theme.settingsSchema) {
				return false;
			}
			var current = readCurrentFormSettings(theme);
			var saved = theme.settingsValues || {};
			return Object.keys(theme.settingsSchema).some(function (key) {
				var field = theme.settingsSchema[key];
				if (field.type === 'boolean') {
					return Boolean(current[key]) !== Boolean(saved[key]);
				}
				if (field.type === 'number' || field.type === 'integer') {
					return Number(current[key]) !== Number(saved[key]);
				}
				return String(current[key] || '') !== String(saved[key] || '');
			});
		}

		var sharedWorkspace = createWorkspace({
			root: '#' + p + '-workspace',
			unsavedMessage: (config.i18n && config.i18n.unsavedConfirm) || 'You have unsaved theme settings. Discard changes and continue?',
			isBusy: function () {
				return isBusy;
			},
			canSave: function () {
				return !isBusy && Boolean(state.themes[state.selectedSlug]);
			},
			computeDirty: function () {
				return areSettingsDirty();
			},
			onSave: function () {
				saveSelectedThemeSettings();
			},
			onNavigate: function (direction, e) {
				var slugs = Object.keys(state.themes);
				if (slugs.length === 0) {
					return;
				}
				var currentIndex = slugs.indexOf(state.selectedSlug);
				var nextIndex = currentIndex === -1 ? 0 : currentIndex + direction;
				if (nextIndex < 0 || nextIndex >= slugs.length) {
					return;
				}
				e.preventDefault();
				selectTheme(slugs[nextIndex]);
			}
		});

		function syncControls() {
			var selected = state.themes[state.selectedSlug] || null;
			var canDelete = Boolean(selected) && selected.slug !== state.activeSlug && !isBusy;

			if (addThemeBtn) {
				addThemeBtn.disabled = isBusy;
			}
			if (deleteThemeBtn) {
				deleteThemeBtn.disabled = !canDelete;
			}
			if (sharedWorkspace) {
				sharedWorkspace.recomputeDirty();
			} else if (updateBtn) {
				updateBtn.disabled = isBusy || !areSettingsDirty();
			}
		}

		function syncListSelectionUI() {
			if (!listBody) {
				return;
			}
			var rows = listBody.querySelectorAll('.' + p + '-row');
			rows.forEach(function (row) {
				var slug = row.dataset.slug || '';
				var isSelected = slug === state.selectedSlug;
				var isActive = slug === state.activeSlug;
				row.classList.toggle('is-selected', isSelected);
				row.classList.toggle('is-active-theme', isActive);
				var radio = row.querySelector('.' + p + '-active-radio');
				if (radio) {
					radio.checked = isActive;
				}
			});
		}

		function renderSettingsFields(theme) {
			if (!settingsFields) {
				return;
			}

			if (!theme || !theme.hasSettings || !theme.settingsSchema) {
				settingsFields.innerHTML =
					'<p class="description ' + p + '-no-settings">' +
					escapeHtml(o.noSettingsText || 'This theme does not define configurable settings.') +
					'</p>';
				return;
			}

			var html = '';
			var values = theme.settingsValues || {};

			Object.keys(theme.settingsSchema).forEach(function (key) {
				var field = theme.settingsSchema[key];
				var fieldId = p + '-setting-' + key;
				var val = Object.prototype.hasOwnProperty.call(values, key) ? values[key] : field.default;
				var type = field.type || 'text';

				html += '<div class="fg-details-field ' + p + '-setting-row">';

				if (type === 'boolean') {
					html +=
						'<label class="' + p + '-checkbox-label" for="' + escapeAttr(fieldId) + '">' +
							'<input id="' + escapeAttr(fieldId) + '" name="settings[' + escapeAttr(key) + ']" data-setting-key="' + escapeAttr(key) + '" type="checkbox" value="1"' + (val ? ' checked' : '') + '>' +
							'<span>' + escapeHtml(field.label) + '</span>' +
						'</label>';
				} else {
					html += '<label for="' + escapeAttr(fieldId) + '">' + escapeHtml(field.label) + '</label>';

					if (type === 'select' && field.options) {
						html += '<select id="' + escapeAttr(fieldId) + '" name="settings[' + escapeAttr(key) + ']" data-setting-key="' + escapeAttr(key) + '">';
						Object.keys(field.options).forEach(function (optVal) {
							var selectedAttr = String(val) === String(optVal) ? ' selected' : '';
							html += '<option value="' + escapeAttr(optVal) + '"' + selectedAttr + '>' + escapeHtml(field.options[optVal]) + '</option>';
						});
						html += '</select>';
					} else if (type === 'number' || type === 'integer') {
						var minAttr = typeof field.min !== 'undefined' ? ' min="' + escapeAttr(field.min) + '"' : '';
						var maxAttr = typeof field.max !== 'undefined' ? ' max="' + escapeAttr(field.max) + '"' : '';
						var stepVal = typeof field.step !== 'undefined' ? field.step : (type === 'integer' ? '1' : 'any');
						html += '<input id="' + escapeAttr(fieldId) + '" name="settings[' + escapeAttr(key) + ']" data-setting-key="' + escapeAttr(key) + '" type="number" class="small-text" value="' + escapeAttr(val) + '"' + minAttr + maxAttr + ' step="' + escapeAttr(stepVal) + '">';
					} else {
						html += '<input id="' + escapeAttr(fieldId) + '" name="settings[' + escapeAttr(key) + ']" data-setting-key="' + escapeAttr(key) + '" type="text" class="regular-text" value="' + escapeAttr(val) + '">';
					}
				}

				if (field.description) {
					html += '<p class="description">' + escapeHtml(field.description) + '</p>';
				}

				html += '</div>';
			});

			settingsFields.innerHTML = html;
		}

		function renderDetailPane() {
			var theme = state.themes[state.selectedSlug] || null;
			if (!form || !placeholder) {
				return;
			}

			if (statusEl) {
				statusEl.textContent = '';
			}

			if (!theme) {
				form.hidden = true;
				placeholder.hidden = false;
				syncControls();
				return;
			}

			placeholder.hidden = true;
			form.hidden = false;

			if (formSlugInput) {
				formSlugInput.value = theme.slug;
			}

			if (previewWrap && previewImg) {
				if (theme.screenshotUrl) {
					previewImg.src = theme.screenshotUrl;
					previewImg.alt = theme.name;
					previewWrap.hidden = false;
				} else {
					previewImg.removeAttribute('src');
					previewWrap.hidden = true;
				}
			}

			if (detailName) {
				detailName.textContent = theme.name;
			}
			if (activePill) {
				activePill.hidden = theme.slug !== state.activeSlug;
			}
			if (detailDesc) {
				if (theme.description) {
					detailDesc.textContent = theme.description;
					detailDesc.hidden = false;
				} else {
					detailDesc.textContent = '';
					detailDesc.hidden = true;
				}
			}
			if (detailVersion) {
				detailVersion.textContent = 'v' + (theme.version || '1.0.0');
			}
			if (detailAuthor) {
				detailAuthor.textContent = theme.author || '—';
			}
			if (detailSlug) {
				detailSlug.textContent = (o.folderBasePath || '') + theme.slug;
			}
			if (shortcodeInput) {
				var attrName = o.shortcodeAttr || 'gallery';
				shortcodeInput.value = '[folder_gallery ' + attrName + '="' + theme.slug + '"]';
			}

			renderSettingsFields(theme);
			syncControls();
		}

		function selectTheme(slug, navOptions) {
			var navOpts = navOptions || {};
			if (!slug || !state.themes[slug]) {
				return;
			}
			if (slug === state.selectedSlug && !navOpts.force) {
				return;
			}
			if (!navOpts.skipDirtyPrompt && sharedWorkspace && !sharedWorkspace.confirmDiscardIfDirty()) {
				return;
			}

			state.selectedSlug = slug;
			syncListSelectionUI();
			renderDetailPane();
		}

		function activateTheme(slug) {
			if (isBusy || !slug || !state.themes[slug]) {
				return;
			}

			if (sharedWorkspace && !sharedWorkspace.confirmDiscardIfDirty()) {
				syncListSelectionUI();
				return;
			}

			state.selectedSlug = slug;
			syncListSelectionUI();
			renderDetailPane();

			if (slug === state.activeSlug) {
				return;
			}

			isBusy = true;
			syncControls();

			var body = new URLSearchParams();
			body.append('action', o.actions.activate);
			body.append('nonce', config.nonce);
			body.append('theme', slug);

			fetch(config.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
				},
				body: body.toString(),
			})
				.then(function (res) {
					return res.json();
				})
				.then(function (payload) {
					if (!payload || !payload.success) {
						var msg = (payload && payload.data && payload.data.message)
							? payload.data.message
							: config.i18n.activateError;
						window.alert(msg);
						return;
					}

					state.activeSlug = (payload.data && payload.data.activeSlug) ? payload.data.activeSlug : slug;
					state.selectedSlug = state.activeSlug;
					syncListSelectionUI();
					renderDetailPane();
					if (statusEl && payload.data && payload.data.message) {
						statusEl.textContent = payload.data.message;
					}
				})
				.catch(function () {
					window.alert(config.i18n.activateError);
				})
				.finally(function () {
					isBusy = false;
					syncListSelectionUI();
					syncControls();
				});
		}

		function saveSelectedThemeSettings() {
			var theme = state.themes[state.selectedSlug];
			if (isBusy || !theme || !areSettingsDirty()) {
				return;
			}

			var currentValues = readCurrentFormSettings(theme);
			isBusy = true;
			syncControls();
			if (statusEl) {
				statusEl.textContent = '';
			}

			var body = new URLSearchParams();
			body.append('action', o.actions.saveSettings);
			body.append('nonce', config.nonce);
			body.append('theme', theme.slug);

			Object.keys(currentValues).forEach(function (key) {
				var val = currentValues[key];
				if (typeof val === 'boolean') {
					body.append('settings[' + key + ']', val ? '1' : '0');
				} else {
					body.append('settings[' + key + ']', String(val));
				}
			});

			fetch(config.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
				},
				body: body.toString(),
			})
				.then(function (res) {
					return res.json();
				})
				.then(function (payload) {
					if (!payload || !payload.success) {
						var msg = (payload && payload.data && payload.data.message)
							? payload.data.message
							: config.i18n.saveError;
						window.alert(msg);
						return;
					}

					theme.settingsValues = (payload.data && payload.data.settings) ? payload.data.settings : currentValues;
					renderSettingsFields(theme);
					if (statusEl) {
						statusEl.textContent = (payload.data && payload.data.message) || config.i18n.saveSuccess;
					}
				})
				.catch(function () {
					window.alert(config.i18n.saveError);
				})
				.finally(function () {
					isBusy = false;
					syncControls();
				});
		}

		function installThemeZip(file) {
			if (isBusy || !file) {
				return;
			}

			if (sharedWorkspace && !sharedWorkspace.confirmDiscardIfDirty()) {
				if (zipInput) {
					zipInput.value = '';
				}
				return;
			}

			var maxBytes = Number(config.maxUploadBytes) || 0;
			if (maxBytes > 0 && typeof file.size === 'number' && file.size > maxBytes) {
				var msg = (config.i18n.fileTooLarge || 'File "%1$s" exceeds %2$s.')
					.replace('%1$s', file.name)
					.replace('%2$s', config.maxUploadLabel || '');
				window.alert(msg);
				if (zipInput) {
					zipInput.value = '';
				}
				return;
			}

			isBusy = true;
			syncControls();

			var formData = new FormData();
			formData.append('action', o.actions.installZip);
			formData.append('nonce', config.nonce);
			formData.append('theme_zip', file);

			fetch(config.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData,
			})
				.then(function (res) {
					return res.json();
				})
				.then(function (payload) {
					if (!payload || !payload.success) {
						var errMsg = (payload && payload.data && payload.data.message)
							? payload.data.message
							: config.i18n.installError;
						window.alert(errMsg);
						isBusy = false;
						syncControls();
						return;
					}

					var installedSlug = (payload.data && payload.data.slug) ? payload.data.slug : '';
					var nextUrl = new URL(window.location.href);
					nextUrl.searchParams.set('fg_message', 'theme_installed');
					if (installedSlug) {
						nextUrl.searchParams.set('theme', installedSlug);
					}
					window.location.href = nextUrl.toString();
				})
				.catch(function () {
					window.alert(config.i18n.installError);
					isBusy = false;
					syncControls();
				})
				.finally(function () {
					if (zipInput) {
						zipInput.value = '';
					}
				});
		}

		function deleteSelectedTheme() {
			var theme = state.themes[state.selectedSlug];
			if (isBusy || !theme) {
				return;
			}

			if (theme.slug === state.activeSlug) {
				window.alert(config.i18n.cannotDeleteAct);
				return;
			}

			var confirmMsg = (config.i18n.confirmDelete || 'Delete "%s"?').replace('%s', theme.name);
			if (!window.confirm(confirmMsg)) {
				return;
			}

			isBusy = true;
			syncControls();

			var body = new URLSearchParams();
			body.append('action', o.actions.deleteTheme);
			body.append('nonce', config.nonce);
			body.append('theme', theme.slug);

			fetch(config.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
				},
				body: body.toString(),
			})
				.then(function (res) {
					return res.json();
				})
				.then(function (payload) {
					if (!payload || !payload.success) {
						var errMsg = (payload && payload.data && payload.data.message)
							? payload.data.message
							: config.i18n.deleteError;
						window.alert(errMsg);
						isBusy = false;
						syncControls();
						return;
					}

					var nextUrl = new URL(window.location.href);
					nextUrl.searchParams.delete('theme');
					nextUrl.searchParams.set('fg_message', 'theme_deleted');
					window.location.href = nextUrl.toString();
				})
				.catch(function () {
					window.alert(config.i18n.deleteError);
					isBusy = false;
					syncControls();
				});
		}

		var lastRadioActionAt = 0;

		function handleRadioActivation(radio) {
			if (!radio || isBusy) {
				return;
			}
			var now = Date.now();
			if (now - lastRadioActionAt < 150) {
				return;
			}
			lastRadioActionAt = now;

			var targetSlug = radio.value || '';
			radio.checked = (targetSlug === state.activeSlug);
			activateTheme(targetSlug);
		}

		if (addThemeBtn && zipInput) {
			addThemeBtn.addEventListener('click', function () {
				if (!isBusy) {
					zipInput.click();
				}
			});

			zipInput.addEventListener('change', function () {
				if (zipInput.files && zipInput.files.length > 0) {
					installThemeZip(zipInput.files[0]);
				}
			});
		}

		if (deleteThemeBtn) {
			deleteThemeBtn.addEventListener('click', deleteSelectedTheme);
		}

		if (listBody) {
			listBody.addEventListener('click', function (e) {
				var radio = e.target.closest('.' + p + '-active-radio');
				if (radio) {
					e.stopPropagation();
					handleRadioActivation(radio);
					return;
				}

				var row = e.target.closest('.' + p + '-row');
				if (row && row.dataset.slug) {
					selectTheme(row.dataset.slug);
				}
			});

			listBody.addEventListener('change', function (e) {
				var radio = e.target.closest('.' + p + '-active-radio');
				if (radio) {
					e.stopPropagation();
					handleRadioActivation(radio);
				}
			});
		}

		if (form) {
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				saveSelectedThemeSettings();
			});
		}

		if (copyShortcodeBtn && shortcodeInput) {
			copyShortcodeBtn.addEventListener('click', function () {
				copyTextWithFeedback(shortcodeInput.value, copyShortcodeBtn, config.i18n);
			});
		}

		if (shortcodeInput) {
			shortcodeInput.addEventListener('click', function () {
				shortcodeInput.select();
			});
		}

		document.addEventListener('keydown', function (e) {
			if (isBusy || isEditableTarget(e.target)) {
				return;
			}
			if (!e.ctrlKey && !e.metaKey && !e.altKey && e.key === 'Delete') {
				var theme = state.themes[state.selectedSlug];
				if (theme && theme.slug !== state.activeSlug) {
					e.preventDefault();
					deleteSelectedTheme();
				}
			}
		});

		syncListSelectionUI();
		syncControls();
	}
})(window, document);
