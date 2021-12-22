<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2020 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Uri\Uri as JURI;

$ag_template = "default"; // Set template to default
$jinput = JFactory::getApplication()->input;
$resources_path = JURI::root() . 'administrator/components/com_admirorgallery/';

if (!JFactory::getUser()->authorise('core.manage', 'com_admirorgallery')) {
    throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}


$jinput->set('AG_template', $ag_template);

// Shared scripts for all views
$doc = JFactory::getDocument();
$doc->addScript(
    JURI::root() . 'plugins/content/admirorgallery/admirorgallery/AG_jQuery.js');
$doc->addScript(
    $resources_path . 'scripts/jquery.hotkeys-0.7.9.min.js');
$doc->addStyleSheet(
    $resources_path . 'templates/' . $ag_template . '/css/template.css');
$doc->addStyleSheet(
    $resources_path . 'templates/' . $ag_template . '/css/toolbar.css');

// Require the base controller
require_once(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'controller.php');

// Require specific controller if requested
$spec_controller = $jinput->get('controller');
if ($spec_controller) {
    $path = JPATH_COMPONENT .
        DIRECTORY_SEPARATOR .
        'controllers' .
        DIRECTORY_SEPARATOR .
        $spec_controller . '.php';
    if (file_exists($path)) {
        require_once $path;
    } else {
        $spec_controller = '';
    }
}

// Create the controller
$classname = 'AdmirorgalleryController' . $spec_controller;
$controller = new $classname();
$controller->execute($jinput->get('task'));
$controller->redirect();