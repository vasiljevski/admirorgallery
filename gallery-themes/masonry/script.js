(function () {
	'use strict';

	function initMasonryGrid(grid) {
		if (!grid || typeof window.Masonry !== 'function') {
			return;
		}

		var msnry = new window.Masonry(grid, {
			itemSelector: '.fg-theme-masonry__item',
			columnWidth: '.fg-theme-masonry__sizer',
			gutter: '.fg-theme-masonry__gutter',
			percentPosition: true
		});

		var rafId = null;
		function relayout() {
			if (rafId) {
				window.cancelAnimationFrame(rafId);
			}
			rafId = window.requestAnimationFrame(function () {
				rafId = null;
				if (typeof msnry.reloadItems === 'function') {
					msnry.reloadItems();
				}
				msnry.layout();
			});
		}

		var imgs = grid.querySelectorAll('img');
		imgs.forEach(function (img) {
			if (img.complete) {
				return;
			}
			img.addEventListener('load', relayout, { once: true });
			img.addEventListener('error', relayout, { once: true });
			if (typeof img.decode === 'function') {
				img.decode().then(relayout).catch(function () {});
			}
		});

		if (typeof window.ResizeObserver === 'function') {
			var ro = new window.ResizeObserver(function () {
				relayout();
			});
			ro.observe(grid);
		}

		window.addEventListener('load', function () {
			relayout();
			window.setTimeout(relayout, 120);
		});

		relayout();
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-fg-masonry]').forEach(initMasonryGrid);
	});
})();