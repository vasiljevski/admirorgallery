// Folder Gallery Admin Scripts
document.addEventListener('DOMContentLoaded', function () {
	initPanelResizer();
	initFolderExplorer();
});

function initPanelResizer() {
	const workspace = document.getElementById('fg-workspace');
	const leftPane = document.getElementById('fg-pane-list');
	const resizer = document.getElementById('fg-resizer');

	if (!workspace || !leftPane || !resizer) {
		return;
	}

	const STORAGE_KEY = 'fg_explorer_left_ratio';
	const MIN_LEFT_PX = 240;
	const MIN_RIGHT_PX = 220;

	// Restore saved width ratio if available.
	const savedRatio = parseFloat(localStorage.getItem(STORAGE_KEY));
	if (!Number.isNaN(savedRatio) && savedRatio > 0.15 && savedRatio < 0.85) {
		leftPane.style.flexBasis = (savedRatio * 100).toFixed(2) + '%';
	}

	let isDragging = false;

	function onMouseDown(e) {
		e.preventDefault();
		isDragging = true;
		resizer.classList.add('is-dragging');
		workspace.classList.add('is-resizing');
	}

	function onMouseMove(e) {
		if (!isDragging) {
			return;
		}

		const rect = workspace.getBoundingClientRect();
		const resizerWidth = resizer.offsetWidth || 5;
		const maxLeftPx = rect.width - MIN_RIGHT_PX - resizerWidth;

		let newLeftPx = e.clientX - rect.left;
		newLeftPx = Math.max(MIN_LEFT_PX, Math.min(newLeftPx, maxLeftPx));

		const ratio = newLeftPx / rect.width;
		leftPane.style.flexBasis = (ratio * 100).toFixed(2) + '%';
	}

	function onMouseUp() {
		if (!isDragging) {
			return;
		}

		isDragging = false;
		resizer.classList.remove('is-dragging');
		workspace.classList.remove('is-resizing');

		const rect = workspace.getBoundingClientRect();
		if (rect.width > 0) {
			const ratio = leftPane.getBoundingClientRect().width / rect.width;
			localStorage.setItem(STORAGE_KEY, ratio.toFixed(4));
		}
	}

	resizer.addEventListener('mousedown', onMouseDown);
	document.addEventListener('mousemove', onMouseMove);
	document.addEventListener('mouseup', onMouseUp);
}

function initFolderExplorer() {
	const explorer = document.getElementById('fg-explorer');
	const leftPane = document.getElementById('fg-pane-list');
	const backBtn = document.getElementById('fg-nav-back');
	const breadcrumbList = document.getElementById('fg-breadcrumb-list');
	const deleteBtn = document.querySelector('.fg-toolbar-btn[data-action="delete"]');
	const uploadImageBtn = document.querySelector('.fg-toolbar-btn[data-action="upload-image"]');
	const newFolderBtn = document.querySelector('.fg-toolbar-btn[data-action="new-folder"]');
	const copyBtn = document.querySelector('.fg-toolbar-btn[data-action="copy"]');
	const pasteBtn = document.querySelector('.fg-toolbar-btn[data-action="paste"]');
	const imageInput = document.getElementById('fg-image-input');
    const uploadZipBtn = document.querySelector('.fg-toolbar-btn[data-action="upload-zip"]');
	const zipInput = document.getElementById('fg-zip-input');
    const renameBtn = document.querySelector('.fg-toolbar-btn[data-action="rename"]');

	if (!explorer || !leftPane || !backBtn || !breadcrumbList || !deleteBtn || typeof fgAdminConfig === 'undefined') {
		return;
	}

	let currentPath = '';
	let selectedPaths = new Set();
	let anchorPath = null;
	let clipboardItems = []; // Array of { path, kind, name }
	let isLoading = false;

    function isFileTooLarge(file) {
		const maxBytes = Number(fgAdminConfig.maxUploadBytes) || 0;
		return maxBytes > 0 && file && typeof file.size === 'number' && file.size > maxBytes;
	}

	function formatTooLargeMessage(fileName) {
		const template = fgAdminConfig.i18n.fileTooLarge || 'The file "%1$s" exceeds the maximum allowed upload size of %2$s.';
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
		const clean = normalizePath(path);
		if (!clean) {
			return '';
		}
		const parts = clean.split('/');
		parts.pop();
		return parts.join('/');
	}

	function getOrderedRows() {
		return Array.from(leftPane.querySelectorAll('.fg-row'));
	}

	function getRowItemData(row) {
		const path = normalizePath(row.dataset.path);
		const labelEl = row.querySelector('.fg-item-label');
		return {
			path: path,
			kind: row.dataset.kind || 'image',
			name: labelEl ? labelEl.textContent.trim() : path.split('/').pop(),
		};
	}

	function syncToolbarState() {
		const hasSelection = selectedPaths.size > 0;
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
	}

    function renameSelectedItem() {
		if (selectedPaths.size !== 1 || isLoading) {
			return;
		}

		const items = getSelectedItemsData();
		if (items.length !== 1) {
			return;
		}

		const currentItem = items[0];
		const enteredName = window.prompt(fgAdminConfig.i18n.renamePrompt, currentItem.name);
		if (enteredName === null) {
			return;
		}

		const trimmed = enteredName.trim();
		if (!trimmed || trimmed === currentItem.name) {
			return;
		}

		isLoading = true;
		syncToolbarState();

		const formData = new URLSearchParams();
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
					const errMsg = (payload && payload.data && payload.data.message)
						? payload.data.message
						: fgAdminConfig.i18n.renameError;
					window.alert(errMsg);
					isLoading = false;
					syncToolbarState();
					return;
				}

				// Keep copied path in sync if the renamed item was in clipboard.
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

				navigateTo(currentPath, { force: true });
			})
			.catch(function () {
				window.alert(fgAdminConfig.i18n.renameError);
				isLoading = false;
				syncToolbarState();
			});
	}

	function applySelectionClasses() {
		const rows = getOrderedRows();
		rows.forEach(function (row) {
			const rowPath = normalizePath(row.dataset.path);
			if (selectedPaths.has(rowPath)) {
				row.classList.add('is-selected');
			} else {
				row.classList.remove('is-selected');
			}
		});
		syncToolbarState();
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

		const path = normalizePath(row.dataset.path);
		const isToggle = event.ctrlKey || event.metaKey;
		const isRange = event.shiftKey;
		const rows = getOrderedRows();

		if (isRange) {
			const targetIndex = rows.findIndex(function (r) {
				return normalizePath(r.dataset.path) === path;
			});

			let startIndex = 0;
			if (anchorPath) {
				const foundAnchor = rows.findIndex(function (r) {
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
				const minIdx = Math.min(startIndex, targetIndex);
				const maxIdx = Math.max(startIndex, targetIndex);
				for (let i = minIdx; i <= maxIdx; i++) {
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
			// Single click without modifiers: toggle if it's the sole selected item, otherwise select only it.
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
		const items = [];
		const rows = getOrderedRows();
		rows.forEach(function (row) {
			const path = normalizePath(row.dataset.path);
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

		const formData = new URLSearchParams();
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
					const errMsg = (payload && payload.data && payload.data.message)
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

				navigateTo(currentPath, { force: true });
			})
			.catch(function () {
				window.alert(fgAdminConfig.i18n.pasteError);
				isLoading = false;
				syncToolbarState();
			});
	}

	function updateNavigationUI(path) {
		currentPath = normalizePath(path);
		syncToolbarState();

		breadcrumbList.innerHTML = '';

		const segments = currentPath ? currentPath.split('/') : [];
		const crumbs = [{ label: 'folder-gallery', path: '' }];

		let acc = '';
		segments.forEach(function (segment) {
			acc = acc ? acc + '/' + segment : segment;
			crumbs.push({ label: segment, path: acc });
		});

		crumbs.forEach(function (crumb, index) {
			const isLast = index === crumbs.length - 1;
			const li = document.createElement('li');
			li.className = 'fg-breadcrumb-item' + (isLast ? ' is-current' : '');

			const link = document.createElement('a');
			link.href = '#';
			link.dataset.path = crumb.path;
			link.textContent = crumb.label;

			li.appendChild(link);
			breadcrumbList.appendChild(li);
		});
	}

	function formatFolderCount(count) {
		const template = count === 1 ? fgAdminConfig.i18n.singleImage : fgAdminConfig.i18n.pluralImages;
		return template.replace('%d', String(count));
	}

	function renderList(data) {
		clearSelection();

		const folders = Array.isArray(data.folders) ? data.folders : [];
		const images = Array.isArray(data.images) ? data.images : [];

		if (folders.length === 0 && images.length === 0) {
			leftPane.innerHTML =
				'<div class="fg-pane-placeholder">' +
				escapeHtml(fgAdminConfig.i18n.emptyFolder) +
				'</div>';
			return;
		}

		let rowsHtml = '';

		folders.forEach(function (folder) {
			rowsHtml +=
				'<tr class="fg-row fg-row-folder" data-kind="folder" data-path="' + escapeAttr(folder.path) + '">' +
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
				'</tr>';
		});

		images.forEach(function (image) {
			rowsHtml +=
				'<tr class="fg-row fg-row-image" data-kind="image" data-path="' + escapeAttr(image.path) + '">' +
					'<td class="fg-col-name">' +
						'<span class="fg-item-name-cell">' +
							'<img class="fg-thumb" src="' + escapeAttr(image.url) + '" alt="' + escapeAttr(image.name) + '" loading="lazy">' +
							'<span class="fg-item-label">' + escapeHtml(image.name) + '</span>' +
						'</span>' +
					'</td>' +
					'<td class="fg-col-date">' + escapeHtml(image.date) + '</td>' +
					'<td class="fg-col-type">' + escapeHtml(image.type) + '</td>' +
					'<td class="fg-col-size">' + escapeHtml(image.size) + '</td>' +
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
		const opts = options || {};
		if (isLoading && !opts.force) {
			return;
		}

		const cleanTarget = normalizePath(targetPath);
		isLoading = true;
		syncToolbarState();

		const formData = new URLSearchParams();
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
					const errMsg = (payload && payload.data && payload.data.message)
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

		const enteredName = window.prompt(fgAdminConfig.i18n.newFolderPrompt, '');
		if (enteredName === null) {
			return;
		}

		const trimmed = enteredName.trim();
		if (!trimmed) {
			return;
		}

		isLoading = true;
		syncToolbarState();

		const formData = new URLSearchParams();
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
					const errMsg = (payload && payload.data && payload.data.message)
						? payload.data.message
						: fgAdminConfig.i18n.createFolderErr;
					window.alert(errMsg);
					isLoading = false;
					syncToolbarState();
					return;
				}

				navigateTo(currentPath, { force: true });
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

		const validFiles = [];
		const oversizeMessages = [];

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

		const formData = new FormData();
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
					const errMsg = (payload && payload.data && payload.data.message)
						? payload.data.message
						: fgAdminConfig.i18n.uploadError;
					const combined = oversizeMessages.concat([errMsg]);
					window.alert(combined.join('\n'));
					isLoading = false;
					syncToolbarState();
					return;
				}

				const serverErrors = (payload.data && Array.isArray(payload.data.errors)) ? payload.data.errors : [];
				const allWarnings = oversizeMessages.concat(serverErrors);
				if (allWarnings.length > 0) {
					window.alert(allWarnings.join('\n'));
				}

				navigateTo(currentPath, { force: true });
			})
			.catch(function () {
				const combined = oversizeMessages.concat([fgAdminConfig.i18n.uploadError]);
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

		const formData = new FormData();
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
                const errMsg = (payload && payload.data && payload.data.message)
                    ? payload.data.message
                    : fgAdminConfig.i18n.zipError;
                window.alert(errMsg);
                isLoading = false;
                syncToolbarState();
                return;
            }

            navigateTo(currentPath, { force: true });
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
		const items = getSelectedItemsData();
		if (items.length === 0 || isLoading) {
			return;
		}

		let message = '';
		if (items.length > 1) {
			message = fgAdminConfig.i18n.confirmMultiple.replace('%d', String(items.length));
		} else {
			const single = items[0];
			const template = single.kind === 'folder'
				? fgAdminConfig.i18n.confirmFolder
				: fgAdminConfig.i18n.confirmImage;
			message = template.replace('%s', single.name);
		}

		if (!window.confirm(message)) {
			return;
		}

		isLoading = true;
		syncToolbarState();

		const formData = new URLSearchParams();
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
					const errMsg = (payload && payload.data && payload.data.message)
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

				const deletedSet = new Set(items.map(function (i) { return i.path; }));
				clipboardItems = clipboardItems.filter(function (c) { return !deletedSet.has(c.path); });

				clearSelection();
				navigateTo(currentPath, { force: true });
			})
			.catch(function () {
				window.alert(fgAdminConfig.i18n.deleteError);
				isLoading = false;
				syncToolbarState();
			});
	}

	function isEditableTarget(el) {
		if (!el) {
			return false;
		}
		const tag = el.tagName ? el.tagName.toLowerCase() : '';
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

	// Toolbar actions: Copy, Paste, and Rename.
	if (copyBtn) {
		copyBtn.addEventListener('click', copySelectedItems);
	}

	if (pasteBtn) {
		pasteBtn.addEventListener('click', pasteClipboardItems);
	}

	if (renameBtn) {
		renameBtn.addEventListener('click', renameSelectedItem);
	}

	// Keyboard shortcuts: Ctrl/Cmd+C, Ctrl/Cmd+V, Delete, and F2.
	document.addEventListener('keydown', function (e) {
		if (isLoading || isEditableTarget(e.target)) {
			return;
		}

		if (!explorer.contains(document.activeElement) && selectedPaths.size === 0 && clipboardItems.length === 0) {
			return;
		}

		const hasModifier = e.ctrlKey || e.metaKey;

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

	// Create a new folder when New Folder button is clicked.
	if (newFolderBtn) {
		newFolderBtn.addEventListener('click', createNewFolder);
	}

	// Open file chooser when New Image button is clicked.
	if (uploadImageBtn && imageInput) {
		uploadImageBtn.addEventListener('click', function () {
			if (isLoading) {
				return;
			}
			imageInput.click();
		});

		imageInput.addEventListener('change', function () {
			if (imageInput.files && imageInput.files.length > 0) {
				uploadSelectedImages(imageInput.files);
			}
		});
	}

    // Open ZIP chooser when Upload ZIP button is clicked.
	if (uploadZipBtn && zipInput) {
        uploadZipBtn.addEventListener('click', function () {
            if (isLoading) {
                return;
            }
            zipInput.click();
        });

        zipInput.addEventListener('change', function () {
            if (zipInput.files && zipInput.files.length > 0) {
                uploadSelectedZip(zipInput.files[0]);
            }
        });
	}

	// Row selection with single click, Ctrl/Cmd toggle, and Shift range select.
	leftPane.addEventListener('click', function (e) {
		const row = e.target.closest('.fg-row');
		if (!row) {
			return;
		}
		handleRowClick(row, e);
	});

	// Double-click a folder row to enter it.
	leftPane.addEventListener('dblclick', function (e) {
		const row = e.target.closest('.fg-row-folder');
		if (!row || !row.dataset.path) {
			return;
		}
		navigateTo(row.dataset.path);
	});

	// Delete selected file(s) or folder(s).
	deleteBtn.addEventListener('click', deleteSelectedItems);

	// Navigate one level up via Back button.
	backBtn.addEventListener('click', function () {
		if (currentPath === '') {
			return;
		}
		navigateTo(getParentPath(currentPath));
	});

	// Navigate to any ancestor or root via breadcrumb links.
	breadcrumbList.addEventListener('click', function (e) {
		const link = e.target.closest('a[data-path]');
		if (!link) {
			return;
		}
		e.preventDefault();
		navigateTo(link.dataset.path || '');
	});

	updateNavigationUI('');
}