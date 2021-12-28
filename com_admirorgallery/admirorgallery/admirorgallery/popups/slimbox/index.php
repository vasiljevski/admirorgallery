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

// Load JavaScript from current popup folder
$this->loadJS($this->currPopupRoot . 'js/slimbox2.js');

// Load CSS from current popup folder
$this->loadCSS($this->currPopupRoot . 'css/slimbox2.css');

// Set REL attribute needed for Popup engine
$this->popupEngine->rel = 'lightbox[AdmirorGallery' . $this->getGalleryID() . ']';
