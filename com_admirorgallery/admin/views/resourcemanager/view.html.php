<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2020 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

jimport('joomla.application.component.view');

class AdmirorgalleryViewResourcemanager extends JViewLegacy {

    var $ag_resourceManager_installed=null;
    var $limitstart=0;
    var $limit=0;
    var $ag_resource_type='templates';
    
    function display($tpl = null) {
        
        // Preloading joomla tools
        jimport('joomla.installer.helper');
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.archive');
        jimport('joomla.html.pagination');
        jimport('joomla.filesystem.folder');
        //JHTML::_('behavior.tooltip');
        
        $mainframe = Factory::getApplication();
        $jinput = $mainframe->input;
        $option = $jinput->getCmd('option');
        $this->ag_resource_type = $jinput->getVar('AG_resourceType'); // Current resource type
        
        JToolBarHelper::title(Text::_('COM_ADMIRORGALLERY_' . strtoupper($this->ag_resource_type)), $this->ag_resource_type);
        
        // Loading JPagination vars
        $this->limitstart = $mainframe->getUserStateFromRequest($option . '.limitstart', 'limitstart', 0, 'int');
        $this->limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');

        // Read folder depending on $AG_resourceType
        $this->ag_resourceManager_installed = JFolder::folders(JPATH_SITE . '/plugins/content/admirorgallery/admirorgallery/' . $this->ag_resource_type); // N U
        sort($this->ag_resourceManager_installed);
        
        parent::display($tpl);
    }

}
