<?php
/**
 * @version     5.2.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2018 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

$doc = JFactory::getDocument();

// Load JavaScript from current popup folder
$doc->addScript(JURI::root().'administrator/components/com_admirorgallery/slimbox/js/slimbox2.js');

// Load CSS from current popup folder
$doc->addStyleSheet(JURI::root().'administrator/components/com_admirorgallery/slimbox/css/slimbox2.css');

?>