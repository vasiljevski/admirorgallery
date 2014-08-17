<?php
 /*------------------------------------------------------------------------
# admirorgallery - Admiror Gallery Plugin
# ------------------------------------------------------------------------
# author   Igor Kekeljevic & Nikola Vasiljevski
# copyright Copyright (C) 2014 admiror-design-studio.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.admiror-design-studio.com/joomla-extensions
# Technical Support:  Forum - http://www.vasiljevski.com/forum/index.php
# Version: 5.0.0
-------------------------------------------------------------------------*/
// Joomla security code
defined('_JEXEC') or die('Restricted access');

// Load JavaScript files from current popup folder
$this->loadJS($this->currPopupRoot.'js/jquery.fancybox-1.3.4.js');
$this->loadJS($this->currPopupRoot.'js/jquery.easing-1.3.pack.js');
$this->loadJS($this->currPopupRoot.'js/jquery.mousewheel-3.0.4.pack.js');

// Load CSS from current popup folder
$this->loadCSS($this->currPopupRoot.'css/jquery.fancybox-1.3.4.css');

// Set REL attribute needed for Popup engine
$this->popupEngine->rel = 'fancybox[AdmirorGallery'.$this->getGalleryID().']';

// Insert JavaScript code needed to be loaded after gallery is formed
$this->popupEngine->endCode='
<script type="text/javascript" charset="utf-8">
        AG_jQuery("a[rel='.$this->popupEngine->rel.']").fancybox({
		 \'transitionIn\' : \'elastic\',
		 \'transitionOut\' : \'elastic\',
		 \'easingIn\' : \'easeOutBack\',
		 \'easingOut\' : \'easeInBack\'
        });
</script>
';

?>