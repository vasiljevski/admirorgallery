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

$AG_template = "default";// Set template to default
$app = JFactory::getApplication();
$post = $app->input->post;

$post->set('AG_template', $AG_template);

// Require the base controller
require_once( JPATH_COMPONENT.DIRECTORY_SEPARATOR.'controller.php' );
 
// Require specific controller if requested
if($controller == $post->getWord('controller')) {
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
$controller->execute( $post->getWord( 'task' ) );
 
// Redirect if set by the controller
$controller->redirect();
