<?php
/**
 * @version     6.0.0
 * @package     Admiror.Plugin
 * @subpackage  Content.AdmirorGallery
 * @author      Igor Kekeljevic <igor@admiror.com>
 * @author      Nikola Vasiljevski <nikola83@gmail.com>
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

// Load JavaScript files from current popup folder
$this->loadJS($this->currPopupRoot . 'js/jquery.fancybox-1.3.1.pack.js');
$this->loadJS($this->currPopupRoot . 'js/jquery.easing-1.3.pack.js');
$this->loadJS($this->currPopupRoot . 'js/jquery.mousewheel-3.0.2.pack.js');

// Load CSS from current popup folder
$this->loadCSS($this->currPopupRoot . 'css/jquery.fancybox-1.3.1.css');

// Set REL attribute needed for Popup engine
$this->popupEngine->rel = 'fancybox[AdmirorGallery' . $this->getGalleryID() . ']';

// Insert JavaScript code needed to be loaded after gallery is formed
$this->popupEngine->endCode = '
<script type="text/javascript" charset="utf-8">
        AG_jQuery("a[rel=' . $this->popupEngine->rel . ']").fancybox({
		 \'transitionIn\' : \'elastic\',
		 \'transitionOut\' : \'elastic\',
		 \'easingIn\' : \'easeOutBack\',
		 \'easingOut\' : \'easeInBack\'
                 //,titleShow : false
        });
</script>
';
