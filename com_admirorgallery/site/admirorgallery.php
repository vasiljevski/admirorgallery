<?php
/**
 * @version     5.5.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2020 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

$AG_template = "default";// Set template to default
JFactory::getApplication()->input->post->setVar('AG_template', $AG_template);

// Shared scripts for all views
$doc = JFactory::getDocument();
$doc->addScript(JURI::root().'plugins/content/admirorgallery/admirorgallery/AG_jQuery.js');
$doc->addScript(JURI::root().'administrator/components/com_admirorgallery/scripts/jquery.hotkeys-0.7.9.min.js');
$doc->addStyleSheet(JURI::root().'administrator/components/com_admirorgallery/templates/'.$AG_template.'/css/template.css');
$doc->addStyleSheet(JURI::root().'administrator/components/com_admirorgallery/templates/'.$AG_template.'/css/toolbar.css');
// Require the base controller
require_once( JPATH_COMPONENT.DIRECTORY_SEPARATOR.'controller.php' );
 
// Require specific controller if requested
if($controller = JFactory::getApplication()->input->post->getWord('controller')) {
     $path = JPATH_COMPONENT.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.$controller.'.php';
     if (file_exists($path)) {
	  require_once $path;
     } else {
	  $controller = '';
     }
}
 
// Create the controller
$classname    = 'AdmirorgalleryController'.$controller;
$controller   = new $classname( );
 
// Perform the Request task
$controller->execute( JFactory::getApplication()->input->post->getWord( 'task' ) );
 
// Redirect if set by the controller
$controller->redirect();
