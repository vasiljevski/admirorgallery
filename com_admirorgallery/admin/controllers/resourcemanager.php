<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic <igor@admiror.com>
 * @author      Nikola Vasiljevski <nikola83@gmail.com>
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;

class AdmirorgalleryControllerResourcemanager extends AdmirorgalleryController
{

    var $model;

    function __construct()
    {
        parent::__construct();

        // Register Extra tasks
        $this->registerTask('ag_install', 'ag_install');
        $this->registerTask('ag_uninstall', 'ag_uninstall');
        $this->registerTask('ag_reset', 'ag_reset');

        $this->model = $this->getModel('resourcemanager');
    }

    function ag_install()
    {
        $file =  $this->input->getVar('AG_fileUpload', null, 'files');
        if (isset($file) && !empty($file['name'])) {
            $this->model->_install($file);
        } else {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_ADMIRORGALLERY_NOTICE_MUST_SELECT_FILE'), 'notice');
        }
        parent::display();
    }

    function ag_uninstall()
    {
        $ag_cidArray =  $this->input->getVar('cid');
        if (!empty($ag_cidArray)) {
            $this->model->_uninstall($ag_cidArray);
        }
        parent::display();
    }

    function ag_reset()
    {
        parent::display();
    }

}
