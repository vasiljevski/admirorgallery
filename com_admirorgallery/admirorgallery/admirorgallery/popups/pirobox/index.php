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
$this->loadJS($this->currPopupRoot . 'js/pirobox.js');
$this->loadJS($this->currPopupRoot . 'js/piroboxInit.js');

// Load CSS from current popup folder
$this->loadCSS($this->currPopupRoot . 'css/style.css');

// Set REL attribute needed for Popup engine
$this->popupEngine->rel = 'pirobox[AdmirorGallery' . $this->getGalleryID() . ']';

// Set CLASS attribute needed for Popup engine
$this->popupEngine->className = 'pirobox_AdmirorGallery' . $this->getGalleryID();
?>
