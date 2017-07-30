<?php
/**
 * @version     5.1.2
 * @package     Admiror Gallery (plugin)
 * @subpackage  admirorgallery
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2017 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// Joomla security code
defined('_JEXEC') or die('Restricted access');

// Load JavaScript from current popup folder
$this->loadJS($this->currPopupRoot . 'js/slimbox2.js');

// Load CSS from current popup folder
$this->loadCSS($this->currPopupRoot . 'css/slimbox2.css');

// Set REL attribute needed for Popup engine
$this->popupEngine->rel = 'lightbox[AdmirorGallery' . $this->getGalleryID() . ']';
?>