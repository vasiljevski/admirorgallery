<?php
/**
 * @version     5.5.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2020 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

// OUTPUT
// echo "POST: "."<br />"; print_r($_POST); echo "<hr />";
// echo "GET: "."<br />"; print_r($_GET); echo "<hr />";

$AG_template = "default";// Set template to default
JRequest::setVar( 'AG_template', $AG_template );
JRequest::setVar( 'ag_front_end', 'true' );
// Shared scripts for all views
$doc = JFactory::getDocument();
$doc->addScript(JURI::root().'plugins/content/admirorgallery/admirorgallery/AG_jQuery.js');
$doc->addScript(JURI::root().'administrator/components/com_admirorgallery/scripts/jquery.hotkeys-0.7.9.min.js');
$doc->addStyleSheet(JURI::root().'administrator/components/com_admirorgallery/templates/'.$AG_template.'/css/template.css');
$doc->addStyleSheet(JURI::root().'administrator/components/com_admirorgallery/templates/'.$AG_template.'/css/toolbar.css');
// Require the base controller
require_once( JPATH_COMPONENT.DIRECTORY_SEPARATOR.'controller.php' );
 
// Require specific controller if requested
if($controller = JRequest::getWord('controller')) {
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
$controller->execute( JRequest::getWord( 'task' ) );
 
// Redirect if set by the controller
$controller->redirect();
