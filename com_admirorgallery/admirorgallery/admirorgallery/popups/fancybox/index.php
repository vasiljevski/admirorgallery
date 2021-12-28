<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (plugin)
 * @subpackage  admirorgallery
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

// Load JavaScript files from current popup folder
$this->loadJS($this->currPopupRoot . 'js/fancybox.umd.js');

// Load CSS from current popup folder
$this->loadCSS($this->currPopupRoot . 'css/fancybox.css');

// Set REL attribute needed for Popup engine
$this->popupEngine->rel = 'fancybox';
$this->popupEngine->className = 'rounded';
$this->popupEngine->customAttr = 'data-fancybox="AdmirorGallery'.$this->getGalleryID().'" data-caption="'.  $this->title.'"';

// Insert JavaScript code needed to be loaded after gallery is formed
$this->popupEngine->endCode='
<script type="text/javascript" charset="utf-8">
Fancybox.bind(\'[data-fancybox="AdmirorGallery'.$this->getGalleryID().'"]\', {
	Image: {
		zoom: true,
	},
	caption: function (fancybox, carousel, slide) {
		return (
		  `${slide.index + 1} / ${carousel.slides.length} <br />` + slide.$trigger.title
	);
	},
  });
</script>
';
