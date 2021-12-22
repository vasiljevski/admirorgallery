<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2020 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

jimport('joomla.application.component.model');
// Preloading joomla tools
jimport('joomla.installer.helper');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.archive');
jimport('joomla.filesystem.folder');

class AdmirorgalleryModelResourcemanager extends JModelLegacy
{

    function _install($file)
    {

        $AG_resourceType = JRequest::getVar('AG_resourceType'); // Current resource type
        $config = JFactory::getConfig();
        $tmp_dest = $config->get('tmp_path');
        $resourceType = substr($AG_resourceType, 0, strlen($AG_resourceType) - 1);

        $file_type = "zip";

        if (isset($file) && !empty($file['name'])) {
            //Clean up filename to get rid of strange characters like spaces etc
            $filename = JFile::makeSafe($file['name']);
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            $src = $file['tmp_name'];
            $dest = $tmp_dest . DIRECTORY_SEPARATOR . $filename;

            //First check if the file has the right extension
            if ($ext == $file_type) {
                if (JFile::upload($src, $dest)) {

                    if (JArchive::extract($tmp_dest . DIRECTORY_SEPARATOR . $filename, $tmp_dest . DIRECTORY_SEPARATOR . $AG_resourceType)) {
                        JFile::delete($tmp_dest . DIRECTORY_SEPARATOR . $filename);
                    }

                    // TEMPLATE DETAILS PARSING
                    if (JFIle::exists($tmp_dest . DIRECTORY_SEPARATOR . $AG_resourceType . DIRECTORY_SEPARATOR . JFile::stripExt($filename) . DIRECTORY_SEPARATOR . 'details.xml')) {
                        $ag_resourceManager_xml = simplexml_load_file($tmp_dest . DIRECTORY_SEPARATOR . $AG_resourceType . DIRECTORY_SEPARATOR . JFile::stripExt($filename) . DIRECTORY_SEPARATOR . 'details.xml');
                        if (isset($ag_resourceManager_xml->type)) {
                            $ag_resourceManager_type = $ag_resourceManager_xml->type;
                        } else {
                            JFolder::delete($tmp_dest . DIRECTORY_SEPARATOR . $AG_resourceType);
                            JFactory::getApplication()->enqueueMessage(JText::_('AG_ZIP_PACKAGE_IS_NOT_VALID_RESOURCE_TYPE') . "&nbsp;" . $filename, 'error');
                            return;
                        }
                    } else {
                        JFolder::delete($tmp_dest . DIRECTORY_SEPARATOR . $AG_resourceType);
                        JFactory::getApplication()->enqueueMessage(JText::_('AG_ZIP_PACKAGE_IS_NOT_VALID_RESOURCE_TYPE') . "&nbsp;" . $filename, 'error');
                        return;
                    }
                    if (($ag_resourceManager_type) && ($ag_resourceManager_type == $resourceType)) {
                        $result = JFolder::move($tmp_dest . DIRECTORY_SEPARATOR . $AG_resourceType . DIRECTORY_SEPARATOR . JFile::stripExt($filename), JPATH_SITE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'admirorgallery' . DIRECTORY_SEPARATOR . 'admirorgallery' . DIRECTORY_SEPARATOR . $AG_resourceType . DIRECTORY_SEPARATOR . JFile::stripExt($filename));
                        if ($result) {
                            JFactory::getApplication()->enqueueMessage(JText::_('AG_ZIP_PACKAGE_IS_INSTALLED') . "&nbsp;" . $filename, 'message');
                        } else {
                            JFactory::getApplication()->enqueueMessage(JText::_('AG_CANNOT_MOVED_ITEM') . "&nbsp;" . $result, 'message');
                        }
                    } else {
                        JFolder::delete($tmp_dest . DIRECTORY_SEPARATOR . $AG_resourceType);
                        JFactory::getApplication()->enqueueMessage(JText::_('AG_ZIP_PACKAGE_IS_NOT_VALID_RESOURCE_TYPE') . "&nbsp;" . $filename, 'error');
                    }
                } else {
                    JFactory::getApplication()->enqueueMessage(JText::_('AG_CANNOT_UPLOAD_FILE_TO_TEMP_FOLDER_PLEASE_CHECK_PERMISSIONS'), 'error');
                }
            } else {
                JFactory::getApplication()->enqueueMessage(JText::_('AG_ONLY_ZIP_ARCHIVES_CAN_BE_INSTALLED'), 'error');
            }
        }
    }

    function _uninstall($ag_cidArray)
    {
        $AG_resourceType = JRequest::getVar('AG_resourceType'); // Current resource type
        foreach ($ag_cidArray as $ag_cidArrayKey => $ag_cidArrayValue) {
            if (!empty($ag_cidArrayValue)) {
                if (JFolder::delete(JPATH_SITE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'admirorgallery' . DIRECTORY_SEPARATOR . 'admirorgallery' . DIRECTORY_SEPARATOR . $AG_resourceType . DIRECTORY_SEPARATOR . $ag_cidArrayValue)) {
                    JFactory::getApplication()->enqueueMessage(JText::_('AG_PACKAGE_REMOVED') . "&nbsp;" . $ag_cidArrayValue, 'message');
                } else {
                    JFactory::getApplication()->enqueueMessage(JText::_('AG_PACKAGE_CANNOT_BE_REMOVED') . "&nbsp;" . $ag_cidArrayValue, 'error');
                }
            }
        }
    }

}
