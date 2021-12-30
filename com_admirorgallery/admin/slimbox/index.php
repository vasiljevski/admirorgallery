<?php
/**
 * @version     6.0.0
 * @package     Admiror.Administrator
 * @subpackage  com_admirorgallery
 * @author      Igor Kekeljevic <igor@admiror.com>
 * @author      Nikola Vasiljevski <nikola83@gmail.com>
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

$doc = JFactory::getDocument();

// Load JavaScript from current popup folder
$doc->addScript(JURI::root(true) . 'plugins/content/admirorgallery/admirorgallery/popups/slimbox/js/slimbox2.js');

// Load CSS from current popup folder
$doc->addStyleSheet(JURI::root(true) . 'plugins/content/admirorgallery/admirorgallery/popups/slimbox/css/slimbox2.css');
