<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

$doc = JFactory::getDocument();

// Load JavaScript from current popup folder
$doc->addScript(JURI::root() . 'administrator/components/com_admirorgallery/slimbox/js/slimbox2.js');

// Load CSS from current popup folder
$doc->addStyleSheet(JURI::root() . 'administrator/components/com_admirorgallery/slimbox/css/slimbox2.css');
