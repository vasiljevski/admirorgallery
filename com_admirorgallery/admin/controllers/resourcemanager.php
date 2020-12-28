<?php
/**
 * @version     5.5.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2018 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

class AdmirorgalleryControllerResourcemanager extends AdmirorgalleryController {

    var $model;

    function __construct() {
        parent::__construct();

        // Register Extra tasks
        $this->registerTask('ag_install', 'ag_install');
        $this->registerTask('ag_uninstall', 'ag_uninstall');
        $this->registerTask('ag_reset', 'ag_reset');

        $this->model = $this->getModel('resourcemanager');
    }

    function ag_install() {
        $file = JRequest::getVar('AG_fileUpload', null, 'files');
        if (isset($file) && !empty($file['name'])) {
            $this->model->_install($file);
        } else {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_ADMIRORGALLERY_NOTICE_MUST_SELECT_FILE'), 'notice');
        }
        parent::display();
    }

    function ag_uninstall() {
        $ag_cidArray = JRequest::getVar('cid');
        if (!empty($ag_cidArray)) {
            $this->model->_uninstall($ag_cidArray);
        }
        parent::display();
    }

    function ag_reset() {
        parent::display();
    }

}
