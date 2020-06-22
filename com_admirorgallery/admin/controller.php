<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2020 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');
jimport('joomla.html.parameter');

class AdmirorgalleryController extends JControllerLegacy {

    function display($cachable = false, $urlparams = false) {
        $jinput = JFactory::getApplication()->input;
        require_once JPATH_COMPONENT . '/helpers/admirorgallery.php';
        if (!is_dir(JPATH_SITE . '/plugins/content/admirorgallery/')) {
            JFactory::getApplication()->enqueueMessage(
                    JText::_('COM_PLUGIN_NOT_INSTALLED'), 'warning');
        }
        AdmirorGalleryHelper::addSubmenu(
                $jinput->getCmd('view', 'control_panel'), $jinput->getCmd('AG_resourceType', ''));

        $doc = JFactory::getDocument();
        $viewType = $doc->getType();
        $viewName = $jinput->get('view', $this->default_view);
        $viewLayout = $jinput->get('layout', 'default', 'string');

        $view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));

        JToolBarHelper::help("", false, "http://www.admiror-design-studio.com/admiror-joomla-extensions/admiror-gallery/user-manuals");

        if (JFactory::getUser()->authorise('core.admin', 'com_admirorgallery')) {
            JToolbarHelper::preferences('com_admirorgallery');
            if ($viewName == 'resourcemanager') {
                JToolbarHelper::custom('ag_install', 'ag_install', 'ag_install', 'JTOOLBAR_INSTALL', false, false);
                JToolbarHelper::deleteList('COM_ADMIRORGALLERY_ARE_YOU_SURE', 'ag_uninstall', 'JTOOLBAR_UNINSTALL');
            } else {
                JToolbarHelper::custom('AG_apply', 'publish', 'publish', 'COM_ADMIRORGALLERY_APPLY_DESC', false, false);
                JToolbarHelper::custom('AG_reset', 'unpublish', 'unpublish', 'COM_ADMIRORGALLERY_RESET_DESC', false, false);
            }
        }
        $view->sidebar = JHtmlSidebar::render();
        $doc->addScriptDeclaration('
	       AG_jQuery(function(){

		    // SET SHORCUTS
		    AG_jQuery(document).bind("keydown", "ctrl+return", function (){submitbutton("AG_apply");return false;});
		    AG_jQuery(document).bind("keydown", "ctrl+backspace", function (){submitbutton("AG_reset");return false;});

	       });//AG_jQuery(function()
	  ');
        parent::display();
    }

}
