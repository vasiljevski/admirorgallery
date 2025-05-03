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

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Uri\Uri as JURI;

// Set template to default
$template = "default";

try
{
	$app = JFactory::getApplication();
	$input  = $app->input;
	$user = JFactory::getUser();
	$isAdmin = $app->isClient('administrator');
}
catch (Exception $e)
{
	trigger_error($e, E_ERROR);

	return;
}

if ($isAdmin && !$user->authorise('core.manage', 'com_admirorgallery'))
{
	throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

$input->set('template', $template);

if ($isAdmin)
{
	$resourcesPath = JURI::root(true) . '/administrator/components/com_admirorgallery/';

	// Shared scripts for all views
	$doc = JFactory::getDocument();
	$doc->addScript(
		JURI::root(true) . '/plugins/content/admirorgallery/admirorgallery/AG_jQuery.js'
	);
	$doc->addScript(
		$resourcesPath . 'scripts/jquery.hotkeys-0.7.9.min.js'
	);
	$doc->addStyleSheet(
		$resourcesPath . 'templates/' . $template . '/css/template.css'
	);
	$doc->addStyleSheet(
		$resourcesPath . 'templates/' . $template . '/css/toolbar.css'
	);
}

// Require the base controller
require_once JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'controller.php';

// Require specific controller if requested
if ($controller = $input->getWord('controller'))
{
	$path = JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $controller . '.php';

	if (file_exists($path))
	{
		require_once $path;
	}
	else
	{
		$controller = '';
	}
}

// Create the controller
$classname    = 'AdmirorgalleryController' . $controller;
$controller   = new $classname;
$controller->execute($input->getWord('task'));
$controller->redirect();
