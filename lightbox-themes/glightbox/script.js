(function (window, document) {
	'use strict';

	function parseConfig(raw) {
		if (!raw) {
			return {};
		}
		try {
			var parsed = JSON.parse(raw);
			return parsed && typeof parsed === 'object' ? parsed : {};
		} catch (err) {
			return {};
		}
	}

	function initGalleryLightbox(galleryEl) {
		if (!galleryEl || galleryEl.dataset.fgGlightboxReady === '1') {
			return;
		}

		if (typeof window.GLightbox !== 'function') {
			return;
		}

		var groupId = galleryEl.dataset.fgGalleryId || '';
		var items = Array.from(galleryEl.querySelectorAll('a[data-fg-lightbox-item="1"]'));
		if (items.length === 0) {
			return;
		}

		var configEl = groupId
			? document.querySelector('.fg-lightbox-config[data-fg-provider="glightbox"][data-fg-group="' + groupId + '"], .fg-lightbox-config[data-fg-lightbox-provider="glightbox"][data-fg-group="' + groupId + '"]')
			: null;
		var opts = parseConfig(configEl ? configEl.dataset.fgConfig : '');

		var slides = [];
		var indexBySrc = new Map();

		items.forEach(function (link) {
			var src = link.dataset.fgLightboxSrc || link.getAttribute('href') || '';
			if (!src) {
				return;
			}

			var slideIndex;
			if (indexBySrc.has(src)) {
				slideIndex = indexBySrc.get(src);
			} else {
				slideIndex = slides.length;
				indexBySrc.set(src, slideIndex);
				slides.push({
					href: src,
					type: 'image',
					title: link.dataset.fgLightboxTitle || '',
					description: link.dataset.fgLightboxDescription || '',
					descPosition: opts.descPosition || 'bottom'
				});
			}

			link.dataset.fgSlideIndex = String(slideIndex);
		});

		if (slides.length === 0) {
			return;
		}

		var lightbox = window.GLightbox({
			elements: slides,
			loop: opts.loop !== false,
			zoomable: opts.zoomable !== false,
			draggable: opts.draggable !== false,
			closeOnOutsideClick: opts.closeOnOutsideClick !== false,
			openEffect: opts.openEffect || 'zoom',
			closeEffect: opts.openEffect || 'zoom',
			slideEffect: opts.slideEffect || 'slide',
			moreLength: 0
		});

		galleryEl.addEventListener('click', function (e) {
			if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
				return;
			}

			var link = e.target.closest('a[data-fg-lightbox-item="1"]');
			if (!link || !galleryEl.contains(link)) {
				return;
			}

			var idx = parseInt(link.dataset.fgSlideIndex || '0', 10);
			if (Number.isNaN(idx) || idx < 0 || idx >= slides.length) {
				idx = 0;
			}

			e.preventDefault();
			lightbox.openAt(idx);
		});

		galleryEl.dataset.fgGlightboxReady = '1';
	}

	function initAll() {
		var galleries = document.querySelectorAll('.fg-gallery[data-fg-lightbox="glightbox"]');
		galleries.forEach(initGalleryLightbox);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAll);
	} else {
		initAll();
	}
})(window, document);
