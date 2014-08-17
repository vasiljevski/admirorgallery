<?php

/* ------------------------------------------------------------------------
  # com_admirorgallery - Admiror Gallery Component
  # ------------------------------------------------------------------------
  # author   Igor Kekeljevic & Nikola Vasiljevski
  # copyright Copyright (C) 2014 admiror-design-studio.com. All Rights Reserved.
  # @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.admiror-design-studio.com/joomla-extensions
  # Technical Support:  Forum - http://www.vasiljevski.com/forum/index.php
  # Version: 5.0.0
  ------------------------------------------------------------------------- */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');
jimport('joomla.html.parameter');

class AdmirorgalleryController extends JControllerLegacy {

    function display($cachable = false, $urlparams = false) {
        require_once JPATH_COMPONENT . '/helpers/admirorgallery.php';
        if (!is_dir(JPATH_SITE . '/plugins/content/admirorgallery/')) {
            JError::raiseWarning('2', JText::_('COM_PLUGIN_NOT_INSTALLED'));
        }
        AdmirorGalleryHelper::addSubmenu(JRequest::getCmd('view', 'control_panel'), JRequest::getCmd('AG_resourceType', ''));

        $doc = JFactory::getDocument();
        $viewType = $doc->getType();
        $viewName = $this->input->get('view', $this->default_view);
        $viewLayout = $this->input->get('layout', 'default', 'string');

        $view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));

        JToolBarHelper::help("", false, "http://www.admiror-design-studio.com/admiror-joomla-extensions/admiror-gallery/user-manuals");

        if (JFactory::getUser()->authorise('core.admin', 'com_admirorgallery')) {
            JToolbarHelper::preferences('com_admirorgallery');
            if($viewName == 'resourcemanager')
            {
                JToolbarHelper::custom('ag_install', 'ag_install', 'ag_install', 'JTOOLBAR_INSTALL', false, false);
                JToolbarHelper::deleteList('COM_ADMIRORGALLERY_ARE_YOU_SURE', 'ag_uninstall', 'JTOOLBAR_UNINSTALL');
            }
            else
            {
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