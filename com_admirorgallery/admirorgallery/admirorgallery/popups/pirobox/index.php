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
$this->loadJS($this->currPopupRoot . 'js/pirobox.js');
$this->loadJS($this->currPopupRoot . 'js/piroboxInit.js');

// Load CSS from current popup folder
$this->loadCSS($this->currPopupRoot . 'css/style.css');

// Set REL attribute needed for Popup engine
$this->popupEngine->rel = 'pirobox[AdmirorGallery' . $this->getGalleryID() . ']';

// Set CLASS attribute needed for Popup engine
$this->popupEngine->className = 'pirobox_AdmirorGallery' . $this->getGalleryID();

