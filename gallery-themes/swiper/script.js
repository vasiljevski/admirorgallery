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

	function initCarousel(el) {
		if (!el || el.dataset.fgSwiperReady === '1' || typeof window.Swiper !== 'function') {
			return;
		}

		var cfg = parseConfig(el.dataset.fgSwiper);
		var slideCount = el.querySelectorAll('.swiper-slide').length;

		var options = {
			slidesPerView: 1,
			spaceBetween: 0,
			loop: slideCount > 1,
			watchOverflow: true,
			navigation: {
				nextEl: el.querySelector('.swiper-button-next'),
				prevEl: el.querySelector('.swiper-button-prev')
			}
		};

		if (cfg.dots) {
			options.pagination = {
				el: el.querySelector('.swiper-pagination'),
				clickable: true
			};
		}

		if (cfg.autoplay && slideCount > 1) {
			options.autoplay = {
				delay: 4000,
				disableOnInteraction: false
			};
		}

		new window.Swiper(el, options);
		el.dataset.fgSwiperReady = '1';
	}

	function initAll() {
		document.querySelectorAll('[data-fg-swiper]').forEach(initCarousel);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAll);
	} else {
		initAll();
	}
})(window, document);