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
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Uri\Uri as JURI;

$ag_template = "default"; // Set template to default
$input = JFactory::getApplication()->input;

if (JFactory::getApplication()->isClient('administrator')) {
    if (!JFactory::getUser()->authorise('core.manage', 'com_admirorgallery')) {
        throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
    }
}

$input->set('AG_template', $ag_template);

if (JFactory::getApplication()->isClient('administrator')) {
    $resources_path = JURI::root(true) . '/administrator/components/com_admirorgallery/';

    // Shared scripts for all views
    $doc = JFactory::getDocument();
    $doc->addScript(
        JURI::root(true) . '/plugins/content/admirorgallery/admirorgallery/AG_jQuery.js'
    );
    $doc->addScript(
        $resources_path . 'scripts/jquery.hotkeys-0.7.9.min.js'
    );
    $doc->addStyleSheet(
        $resources_path . 'templates/' . $ag_template . '/css/template.css'
    );
    $doc->addStyleSheet(
        $resources_path . 'templates/' . $ag_template . '/css/toolbar.css'
    );
}

// Require the base controller
require_once(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'controller.php');

// Require specific controller if requested
if ($controller = $input->getWord('controller')) {
    $path = JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $controller . '.php';
    if (file_exists($path)) {
        require_once $path;
    } else {
        $controller = '';
    }
}

// Create the controller
$classname    = 'AdmirorgalleryController' . $controller;
$controller   = new $classname();
$controller->execute($input->getWord('task'));
$controller->redirect();
