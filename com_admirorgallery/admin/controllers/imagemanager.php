<?php
/**
 * @version     5.2.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2018 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.language.language');
jimport('joomla.filesystem.archive');

class AdmirorgalleryControllerImagemanager extends AdmirorgalleryController {

    /**
     * Constructor
     */
    var $model = null;

    function __construct() {
        parent::__construct();

        // Register Extra tasks
        $this->registerTask('AG_apply', 'AG_apply');
        $this->registerTask('AG_reset', 'AG_reset');
    }

    function AG_apply() {

        $model = $this->getModel('imagemanager');

        $AG_itemURL = JRequest::getVar('AG_itemURL');
        if (is_dir(JPATH_SITE . $AG_itemURL)) {

            // FOLDER MODELS
            // BOOKMARK REMOVE
            $AG_cbox_bookmarkRemove = JRequest::getVar('AG_cbox_bookmarkRemove');
            if (!empty($AG_cbox_bookmarkRemove)) {
                $model->_bookmarkRemove($AG_cbox_bookmarkRemove);
            }

            // PRIORITY
            $AG_cbox_priority = JRequest::getVar('AG_cbox_priority');
            if (!empty($AG_cbox_priority)) {
                $model->_cbox_priority($AG_cbox_priority);
            }

            // UPLOAD
            $file = JRequest::getVar('AG_fileUpload', null, 'files');
            if (isset($file) && !empty($file['name'])) {
                $model->_fileUpload($AG_itemURL, $file);
            }

            // ADD FOLDERS
            $AG_addFolders = JRequest::getVar('AG_addFolders');
            if (!empty($AG_addFolders)) {
                $model->_addFolders($AG_itemURL, $AG_addFolders);
            }

            // REMOVE // BOOKMARK ADD
            $AG_cbox_selectItem = JRequest::getVar('AG_cbox_selectItem');
            $AG_operations_targetFolder = JRequest::getVar('AG_operations_targetFolder');
            if (!empty($AG_cbox_selectItem)) {
                switch (JRequest::getVar('AG_operations')) {
                    case "move":
                        $model->_move($AG_cbox_selectItem, $AG_operations_targetFolder);
                        break;
                    case "copy":
                        $model->_copy($AG_cbox_selectItem, $AG_operations_targetFolder);
                        break;
                    case "bookmark":
                        $model->_bookmarkAdd($AG_cbox_selectItem);
                        break;
                    case "delete":
                        $model->_remove($AG_cbox_selectItem);
                        break;
                    case "hide":
                        $model->_set_visible($AG_cbox_selectItem, $AG_itemURL, "hide");
                        break;
                    case "show":
                        $model->_set_visible($AG_cbox_selectItem, $AG_itemURL, "show");
                        break;
                }
            }

            // RENAME
            $AG_rename = JRequest::getVar('AG_rename');
            $webSafe = Array("/", " ", ":", ".", "+", "&");
            if (!empty($AG_rename)) {
                foreach ($AG_rename as $ren_key => $ren_value) {
                    $AG_originalName = JFile::stripExt(basename($ren_key));
                    // CREATE WEBSAFE TITLES
                    foreach ($webSafe as $key => $value) {
                        $AG_newName = str_replace($value, "-", $ren_value);
                    }
                    if ($AG_originalName != $AG_newName && !empty($ren_value)) {
                        $model->_rename($AG_itemURL, $ren_key, $AG_newName);
                    }
                }
            }

            // FOLDER DESCRIPTIONS
            $AG_desc_content = JRequest::getVar('AG_desc_content', '', 'POST', 'ARRAY', 'JREQUEST_ALLOWHTML');
            $AG_desc_tags = JRequest::getVar('AG_desc_tags');
            $AG_folder_thumb = JRequest::getVar('AG_folder_thumb');
            if (JRequest::getVar('AG_folderSettings_status') == "edit") {
                $model->_folder_desc_content($AG_itemURL, $AG_desc_content, $AG_desc_tags, $AG_folder_thumb);
            }
        } else {
            // FILE MODELS
            // FILE DESCRIPTIONS
            $AG_desc_content = JRequest::getVar('AG_desc_content', '', 'POST', 'ARRAY', 'JREQUEST_ALLOWHTML');
            $AG_desc_tags = JRequest::getVar('AG_desc_tags');
            if (!empty($AG_desc_content)) {
                $model->_desc_content($AG_itemURL, $AG_desc_content, $AG_desc_tags);
            }
        }
        parent::display();
    }

    function AG_reset() {
        parent::display();
    }

}
