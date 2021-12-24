<?php

/**
 * @version     6.0.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Uri\Uri as JURI;

$doc = JFactory::getApplication()->getDocument();
$template = JFactory::getApplication()->input->getString('AG_template');

// Shared scripts for all views
$doc->addScript(JURI::root(true) . '/plugins/content/admirorgallery/admirorgallery/AG_jQuery.js');
$doc->addScript(JURI::root(true) . '/administrator/components/com_admirorgallery/scripts/jquery.hotkeys-0.7.9.min.js');
$doc->addStyleSheet(JURI::root(true) . '/administrator/components/com_admirorgallery/templates/' . $template . '/css/template.css');
$doc->addStyleSheet(JURI::root(true) . '/administrator/components/com_admirorgallery/templates/' . $template . '/css/toolbar.css');

require_once(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'imagemanager' . DIRECTORY_SEPARATOR . 'view.html.php');
