<?php
/**
 * @version     5.5.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2020 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

//Deprecated
jimport('joomla.application.component.model');

JLoader::register('agHelper', JPATH_SITE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'admirorgallery' . DIRECTORY_SEPARATOR . 'admirorgallery' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'agHelper.php');
JLoader::register('SecureImage', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "scripts" . DIRECTORY_SEPARATOR . "secureimage.php");

class AdmirorgalleryModelImagemanager extends JModelLegacy
{

    public $webSafe = array("/", " ", ":", ".", "+", "&");
    public $ag_bookmark_path;

    function __construct($config = array())
    {
        $this->ag_bookmark_path = JPATH_SITE . '/administrator/components/com_admirorgallery/assets/bookmarks.xml';
        parent::__construct($config);
    }

    function _save_bookmark($simple_xml_object, $value)
    {
        if ($simple_xml_object->asXML($this->ag_bookmark_path)) {
            JFactory::getApplication()->enqueueMessage(JText::_("AG_BOOKMARK_CHANGES_SAVED") . "&nbsp;" . $value, 'message');
        } else {
            JFactory::getApplication()->enqueueMessage(JText::_("AG_CANNOT_WRITE_GALLERY_LISTING") . "&nbsp;" . $value, 'error');
        }
    }

    function _saveXML($ag_itemURL, $ag_XML_path, $ag_XML_content)
    {
        if (!empty($ag_XML_content)) {
            $handle = fopen($ag_XML_path, "w") or die(JText::_("AG_CANNOT_WRITE_DESCRIPTION_FILE"));
            if (fwrite($handle, $ag_XML_content)) {
                JFactory::getApplication()->enqueueMessage(JText::_("AG_DESCRIPTION_FILE_CREATED") . "&nbsp;" . basename($ag_itemURL), 'message');
            } else {
                JFactory::getApplication()->enqueueMessage(JText::_("AG_CANNOT_WRITE_DESCRIPTION_FILE") . "&nbsp;" . basename($ag_itemURL), 'error');
            }
            fclose($handle);
        }
    }

    function _bookmarkRename($AG_originalPath, $AG_newPath)
    {
        $ag_old_bookmark = $AG_originalPath . '/';
        $ag_new_bookmark = $AG_newPath . '/';
        $ag_bookmarks_xml = simplexml_load_file($this->ag_bookmark_path);
        // CHECK IF BOOKMARK ALREADY EXISTS
        if (isset($ag_bookmarks_xml->bookmark)) {
            foreach ((array)$ag_bookmarks_xml->bookmark as $ag_bookmarks_key => $ag_bookmarks_value) {
                if ($ag_bookmarks_value == $ag_old_bookmark) {
                    $ag_bookmarks_value = $ag_new_bookmark;
                    $this->_save_bookmark($ag_bookmarks_xml, $ag_new_bookmark);
                    break;
                }
            }
        }
    }

    function _bookmarkRemove($AG_cbox_bookmarkRemove)
    {
        foreach ($AG_cbox_bookmarkRemove as $key => $AG_bookmark_ID) {
            $ag_bookmarks_xml = simplexml_load_file($this->ag_bookmark_path);
            if (isset($ag_bookmarks_xml->bookmark)) {
                for ($i = 0; $i < count($ag_bookmarks_xml->bookmark); $i++) {
                    if ($ag_bookmarks_xml->bookmark[$i] == $AG_bookmark_ID) {
                        unset($ag_bookmarks_xml->bookmark[$i]);
                    }
                }
            }
            if ($ag_bookmarks_xml->asXML($this->ag_bookmark_path)) {
                JFactory::getApplication()->enqueueMessage(JText::_("AG_GALLERY_REMOVED_FROM_LISTING") . "&nbsp;" . $AG_bookmark_ID, 'message');
            } else {
                JFactory::getApplication()->enqueueMessage(JText::_("AG_CANNOT_WRITE_GALLERY_LISTING") . "&nbsp;" . $AG_bookmark_ID, 'error');
            }
        }
    }

    function _bookmarkAdd($AG_cbox_bookmarkAdd)
    {
        foreach ($AG_cbox_bookmarkAdd as $key => $value) {
            if (is_dir(JPATH_SITE . $value)) {
                $ag_bookmarks_xml = simplexml_load_file($this->ag_bookmark_path);
                $bookmarkExists = false;
                if (isset($ag_bookmarks_xml->bookmark)) {
                    for ($i = 0; $i < count($ag_bookmarks_xml->bookmark); $i++) {
                        if ($ag_bookmarks_xml->bookmark[$i] == $value) {
                            $bookmarkExists = true;
                        }
                    }
                }
                if (!$bookmarkExists) {
                    // Add a new bookmark
                    $ag_bookmarks_xml->addChild('bookmark', $value);
                    JFactory::getApplication()->enqueueMessage(JText::_("AG_GALLERY_ADDED") . "&nbsp;" . $value, 'message');
                } else {
                    JFactory::getApplication()->enqueueMessage(JText::_("AG_GALLERY_ALREADY_EXISTS") . "&nbsp;" . $value, 'notice');
                    return true;
                }
            } else {
                JFactory::getApplication()->enqueueMessage(JText::_("AG_GALLERY_NOT_A_FOLDER") . "&nbsp;" . $value, 'error');
            }
        }
        $this->_save_bookmark($ag_bookmarks_xml, '');
    }

    function _cbox_priority($ag_preview_checked_array)
    {

        foreach ($ag_preview_checked_array as $key => $value) {

            $ag_itemURL = $key;
            $ag_priority = $value;
            $ag_folderName = dirname($ag_itemURL);

            if (is_numeric($ag_priority)) {

                // Set Possible Description File Apsolute Path // Instant patch for upper and lower case...
                $ag_pathWithStripExt = JPATH_SITE . $ag_folderName . '/' . JFile::stripExt(basename($ag_itemURL));
                $ag_XML_path = $ag_pathWithStripExt . ".xml";
                if (JFIle::exists($ag_pathWithStripExt . ".XML")) {
                    $ag_XML_path = $ag_pathWithStripExt . ".XML";
                }

                $ag_priority_new = '<priority>' . $ag_priority . '</priority>';

                $ag_XML_priority = "";
                if (file_exists($ag_XML_path)) {
                    $ag_XML_xml = simplexml_load_file($ag_XML_path);
                    $ag_XML_priority = $ag_XML_xml->priority;
                }

                if ($ag_XML_priority != $ag_priority) {
                    if (file_exists($ag_XML_path)) {
                        $file = fopen($ag_XML_path, "r");
                        $ag_XML_content = "";
                        while (!feof($file)) {
                            $ag_XML_content .= fgetc($file);
                        }
                        fclose($file);
                        $ag_XML_content = preg_replace("#<priority[^}]*>(.*?)</priority>#s", $ag_priority_new, $ag_XML_content);
                    } else {
                        $ag_XML_content = '<?xml version="1.0" encoding="utf-8"?>' . "\n" . '<image>' . "\n" . '<visible>true</visible>' . "\n" . $ag_priority_new . "\n" . '<thumb></thumb>' . "\n" . '<captions>' . "\n" . '</captions>' . "\n" . '</image>';
                    }

                    // Save XML
                    $this->_saveXML($ag_itemURL, $ag_XML_path, $ag_XML_content);
                }
            } else {
                if (!empty($ag_priority)) {
                    JFactory::getApplication()->enqueueMessage(JText::_("AG_PRIORITY_MUST_BE_NUMERIC_VALUE_FOR_IMAGE") . "&nbsp;" . basename($ag_itemURL), 'error');
                }
            }
        }
    }

    function _set_visible($AG_cbox_selectItem, $ag_folderName, $AG_visible)
    {
        foreach ($AG_cbox_selectItem as $key => $value) {

            $ag_itemURL = $value;

            // Set Possible Description File Apsolute Path // Instant patch for upper and lower case...
            $ag_pathWithStripExt = JPATH_SITE . $ag_folderName . JFile::stripExt(basename($ag_itemURL));
            $ag_XML_path = $ag_pathWithStripExt . ".xml";
            if (JFIle::exists($ag_pathWithStripExt . ".XML")) {
                $ag_XML_path = $ag_pathWithStripExt . ".XML";
            }

            // Set new visible tag
            if ($AG_visible == "show") {
                $ag_visible_new = "<visible>true</visible>";
            } else {
                $ag_visible_new = "<visible>false</visible>";
            }

            $ag_XML_content = '';
            if (file_exists($ag_XML_path)) {
                $file = fopen($ag_XML_path, "r");
                while (!feof($file)) {
                    $ag_XML_content .= fgetc($file);
                }
                fclose($file);
                if (preg_match("#<visible[^}]*>(.*?)</visible>#s", $ag_XML_content)) {
                    $ag_XML_content = preg_replace("#<visible[^}]*>(.*?)</visible>#s", $ag_visible_new, $ag_XML_content);
                } else {
                    $ag_XML_content = preg_replace("#</image>#s", $ag_visible_new . "\n" . "</image>", $ag_XML_content);
                }
            } else {
                $ag_XML_content = '<?xml version="1.0" encoding="utf-8"?>' . "\n" . '<image>' . "\n" . $ag_visible_new . "\n" . '<priority></priority>' . "\n" . '<thumb></thumb>' . "\n" . '<captions></captions>' . "\n" . '</image>';
            }

            // Save XML
            $this->_saveXML($ag_itemURL, $ag_XML_path, $ag_XML_content);
        }
    }

    function _fileUpload($AG_itemURL, $file)
    {
        $config = JFactory::getConfig();
        $tmp_dest = $config->get('tmp_path');
        $ag_ext_valid = array("jpg", "jpeg", "gif", "png", "zip");

        //Clean up filename to get rid of strange characters like spaces etc
        $filename = JFile::makeSafe($file['name']);
        $ag_file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $src = $file['tmp_name'];
        $dest = $tmp_dest . DIRECTORY_SEPARATOR . $filename;

        // FILTER EXTENSION
        $ag_ext_check = array_search($ag_file_ext, $ag_ext_valid);
        if (is_numeric($ag_ext_check)) {
            if (JFile::upload($src, $dest)) {
                if ($ag_file_ext == "zip") {
                    //
                    if (JArchive::extract($tmp_dest . DIRECTORY_SEPARATOR . $filename, $tmp_dest . DIRECTORY_SEPARATOR . 'AdmirorImageUpload' . DIRECTORY_SEPARATOR . JFile::stripExt($filename))) {
                        $files = JFolder::files($tmp_dest . DIRECTORY_SEPARATOR . 'AdmirorImageUpload' . DIRECTORY_SEPARATOR . JFile::stripExt($filename), '.', true, true);
                        foreach ($files as $file_path) {
                            $image = new SecureImage($file_path);
                            if (!$image->CheckIt()) {
                                JFile::delete($file_path);
                            }
                        }
                        JFolder::copy($tmp_dest . DIRECTORY_SEPARATOR . 'AdmirorImageUpload' . DIRECTORY_SEPARATOR . JFile::stripExt($filename), JPATH_SITE . $AG_itemURL . JFile::stripExt($filename), '', true);
                        JFile::delete($tmp_dest . DIRECTORY_SEPARATOR . $filename);
                        JFolder::delete($tmp_dest . DIRECTORY_SEPARATOR . 'AdmirorImageUpload' . DIRECTORY_SEPARATOR . JFile::stripExt($filename));
                        JFactory::getApplication()->enqueueMessage(JText::_('AG_ZIP_PACKAGE_IS_UPLOADED_AND_EXTRACTED') . "&nbsp;" . $filename, 'message');
                    }
                } else {
                    if (JFile::copy($tmp_dest . DIRECTORY_SEPARATOR . $filename, JPATH_SITE . $AG_itemURL . $filename)) {
                        JFile::delete($tmp_dest . DIRECTORY_SEPARATOR . $filename);
                        JFactory::getApplication()->enqueueMessage(JText::_('AG_IMAGE_IS_UPLOADED') . "&nbsp;" . $filename, 'message');
                    }
                }
            } else {
                $ag_error[] = array();
                JFactory::getApplication()->enqueueMessage(JText::_('AG_CANNOT_UPLOAD_FILE') . "&nbsp;" . $filename, 'error');
            }
        } else {
            JFactory::getApplication()->enqueueMessage(JText::_("AG_ONLY_JPG_JPEG_GIF_PNG_AND_ZIP_ARE_VALID_EXTENSIONS"), 'error');
        }
    }

    function _addFolders($AG_itemURL, $AG_addFolders)
    {
        foreach ($AG_addFolders as $key => $value) {
            if (!empty($value)) {
                $newFolderName = $value;
                // CREATE WEBSAFE TITLES
                if (!empty($this->webSafe)) {
                    foreach ($this->webSafe as $webSafekey => $webSafevalue) {
                        $newFolderName = str_replace($webSafevalue, "-", $newFolderName);
                    }
                }
                $newFolderName = htmlspecialchars(strip_tags($newFolderName));
                if (!file_exists(JPATH_SITE . $AG_itemURL . $newFolderName)) {
                    if (JFolder::create(JPATH_SITE . $AG_itemURL . $newFolderName, 0755)) {
                        JFactory::getApplication()->enqueueMessage(JText::_("AG_FOLDER_CREATED") . "&nbsp;" . $newFolderName, 'message');
                    } else {
                        JFactory::getApplication()->enqueueMessage(JText::_("AG_CANNOT_CREATE_FOLDER") . "&nbsp;" . $newFolderName, 'error');
                    }
                } else {
                    JFactory::getApplication()->enqueueMessage(JText::_("AG_FOLDER_ALREADY_EXISTS") . "&nbsp;" . $newFolderName, 'error');
                }
            }//if(!empty($value))
        }
    }

    // COPY
    function _copy($AG_cbox_selectItem, $AG_operations_targetFolder)
    {
        foreach ($AG_cbox_selectItem as $key => $value) {
            // Set Possible Description File Apsolute Path // Instant patch for upper and lower case...
            $AG_folderName = dirname($value);
            $AG_pathWithStripExt = JPATH_SITE . $AG_folderName . '/' . JFile::stripExt(basename($value));
            $ag_XML_path = $AG_pathWithStripExt . ".XML";
            if (JFIle::exists($AG_pathWithStripExt . ".xml")) {
                $ag_XML_path = $AG_pathWithStripExt . ".xml";
            }
            if (is_dir(JPATH_SITE . DIRECTORY_SEPARATOR . $value)) {
                if (JFolder::copy(JPATH_SITE . DIRECTORY_SEPARATOR . $value, JPATH_SITE . DIRECTORY_SEPARATOR . $AG_operations_targetFolder . DIRECTORY_SEPARATOR . basename($value))) {
                    if (JFIle::exists($ag_XML_path)) {
                        JFile::copy($ag_XML_path, JPATH_SITE . DIRECTORY_SEPARATOR . $AG_operations_targetFolder . DIRECTORY_SEPARATOR . basename($ag_XML_path));
                    }
                    JFactory::getApplication()->enqueueMessage(JText::_('AG_ITEM_COPIED') . "&nbsp;" . $value, 'message');
                } else {
                    JFactory::getApplication()->enqueueMessage(JText::_('AG_CANNOT_COPY_ITEM') . "&nbsp;" . $value, 'error');
                }
            } else {
                if (JFile::copy(JPATH_SITE . DIRECTORY_SEPARATOR . $value, JPATH_SITE . DIRECTORY_SEPARATOR . $AG_operations_targetFolder . DIRECTORY_SEPARATOR . basename($value))) {
                    if (JFIle::exists($ag_XML_path)) {
                        JFile::copy($ag_XML_path, JPATH_SITE . DIRECTORY_SEPARATOR . $AG_operations_targetFolder . DIRECTORY_SEPARATOR . basename($ag_XML_path));
                    }
                    JFactory::getApplication()->enqueueMessage(JText::_('AG_ITEM_COPIED') . "&nbsp;" . $value, 'message');
                } else {
                    JFactory::getApplication()->enqueueMessage(JText::_('AG_CANNOT_COPY_ITEM') . "&nbsp;" . $value, 'error');
                }
            }
        }
    }

    // MOVE
    function _move($AG_cbox_selectItem, $AG_operations_targetFolder)
    {
        foreach ($AG_cbox_selectItem as $key => $value) {
            // Set Possible Description File Apsolute Path // Instant patch for upper and lower case...
            $AG_folderName = dirname($value);
            $AG_pathWithStripExt = JPATH_SITE . $AG_folderName . '/' . JFile::stripExt(basename($value));
            $ag_XML_path = $AG_pathWithStripExt . ".XML";
            if (JFIle::exists($AG_pathWithStripExt . ".xml")) {
                $ag_XML_path = $AG_pathWithStripExt . ".xml";
            }
            if (is_dir(JPATH_SITE . DIRECTORY_SEPARATOR . $value)) {
                if (JFolder::move(JPATH_SITE . DIRECTORY_SEPARATOR . $value, JPATH_SITE . DIRECTORY_SEPARATOR . $AG_operations_targetFolder . DIRECTORY_SEPARATOR . basename($value))) {
                    $this->_bookmarkRemove(array($value));
                    if (JFIle::exists($ag_XML_path)) {
                        JFile::move($ag_XML_path, JPATH_SITE . DIRECTORY_SEPARATOR . $AG_operations_targetFolder . DIRECTORY_SEPARATOR . basename($ag_XML_path));
                    }
                    JFactory::getApplication()->enqueueMessage(JText::_('AG_ITEM_MOVED') . "&nbsp;" . $value, 'message');
                } else {
                    JFactory::getApplication()->enqueueMessage(JText::_('AG_CANNOT_MOVED_ITEM') . "&nbsp;" . $value, 'error');
                }
            } else {
                if (JFile::move(JPATH_SITE . DIRECTORY_SEPARATOR . $value, JPATH_SITE . DIRECTORY_SEPARATOR . $AG_operations_targetFolder . DIRECTORY_SEPARATOR . basename($value))) {
                    if (JFIle::exists($ag_XML_path)) {
                        JFile::move($ag_XML_path, JPATH_SITE . DIRECTORY_SEPARATOR . $AG_operations_targetFolder . DIRECTORY_SEPARATOR . basename($ag_XML_path));
                    }
                    JFactory::getApplication()->enqueueMessage(JText::_('AG_ITEM_MOVED') . "&nbsp;" . $value, 'message');
                } else {
                    JFactory::getApplication()->enqueueMessage(JText::_('AG_CANNOT_MOVED_ITEM') . "&nbsp;" . $value, 'error');
                }
            }
        }
    }

    function _remove($AG_cbox_remove)
    {
        foreach ($AG_cbox_remove as $key => $value) {
            $AG_folderName = dirname($value);
            // Set Possible Description File Apsolute Path // Instant patch for upper and lower case...
            $AG_pathWithStripExt = JPATH_SITE . $AG_folderName . '/' . JFile::stripExt(basename($value));
            $ag_XML_path = $AG_pathWithStripExt . ".XML";
            if (JFIle::exists($AG_pathWithStripExt . ".xml")) {
                $ag_XML_path = $AG_pathWithStripExt . ".xml";
            }
            // DELETE
            if (is_dir(JPATH_SITE . DIRECTORY_SEPARATOR . $value)) {
                if (JFolder::delete(JPATH_SITE . DIRECTORY_SEPARATOR . $value)) {
                    $this->_bookmarkRemove(array($value));
                    if (file_exists($ag_XML_path)) {
                        JFile::delete($ag_XML_path);
                    }
                    JFactory::getApplication()->enqueueMessage(JText::_('AG_ITEM_DELETED') . "&nbsp;" . $value, 'message');
                } else {
                    JFactory::getApplication()->enqueueMessage(JText::_('AG_CANNOT_DELETE_ITEM') . "&nbsp;" . $value, 'error');
                }
            } else {
                if (JFile::delete(JPATH_SITE . DIRECTORY_SEPARATOR . $value)) {
                    if (file_exists($ag_XML_path)) {
                        JFile::delete($ag_XML_path);
                    }
                    JFactory::getApplication()->enqueueMessage(JText::_('AG_ITEM_DELETED') . "&nbsp;" . $value, 'message');
                } else {
                    JFactory::getApplication()->enqueueMessage(JText::_('AG_CANNOT_DELETE_ITEM') . "&nbsp;" . $value, 'error');
                }
            }
        }
    }

    function _rename($AG_itemURL, $AG_originalPath, $AG_newName)
    {

        $AG_originalName = basename($AG_originalPath);
        $AG_folderName = dirname($AG_originalPath);
        // CREATE WEBSAFE TITLES
        if (!empty($this->webSafe)) {
            foreach ($this->webSafe as $webSafekey => $webSafevalue) {
                $AG_newName = str_replace($webSafevalue, "-", $AG_newName);
            }
        }
        // Set Possible Description File Apsolute Path // Instant patch for upper and lower case...
        $ag_pathWithStripExt = JPATH_SITE . $AG_folderName . DIRECTORY_SEPARATOR . JFile::stripExt($AG_originalName);
        $ag_XML_path = $ag_pathWithStripExt . ".XML";
        if (JFIle::exists($ag_pathWithStripExt . ".xml")) {
            $ag_XML_path = $ag_pathWithStripExt . ".xml";
        }

        if (!is_dir(JPATH_SITE . $AG_originalPath)) {

            $ag_file_ext = JFile::getExt($AG_originalName);
            $ag_file_new_name = $AG_folderName . DIRECTORY_SEPARATOR . $AG_newName . '.' . $ag_file_ext;
            if (!file_exists(JPATH_SITE . $ag_file_new_name)) {
                if (rename(JPATH_SITE . $AG_originalPath, JPATH_SITE . $ag_file_new_name)) {
                    if (file_exists($ag_XML_path)) {
                        rename($ag_XML_path, JPATH_SITE . $AG_folderName . DIRECTORY_SEPARATOR . $AG_newName . '.xml');
                    }
                    JFactory::getApplication()->enqueueMessage(JText::_("AG_IMAGE_RENAMED") . "&nbsp;" . $AG_originalName, 'message');
                } else {
                    JFactory::getApplication()->enqueueMessage(JText::_("AG_CANNOT_RENAME_IMAGE") . "&nbsp;" . $AG_originalName, 'error');
                }
            } else {
                JFactory::getApplication()->enqueueMessage(JText::_("AG_FOLDER_WITH_THE_SAME_NAME_ALREADY_EXISTS"), 'error');
            }
        } else {
            if (!file_exists(JPATH_SITE . $AG_folderName . DIRECTORY_SEPARATOR . $AG_newName)) {
                if (rename(JPATH_SITE . $AG_originalPath, JPATH_SITE . $AG_folderName . DIRECTORY_SEPARATOR . $AG_newName)) {
                    $this->_bookmarkRename($AG_originalPath, $AG_folderName . DIRECTORY_SEPARATOR . $AG_newName);
                    if (file_exists($ag_XML_path)) {
                        rename($ag_XML_path, JPATH_SITE . $AG_folderName . DIRECTORY_SEPARATOR . $AG_newName . '.xml');
                    }
                    JFactory::getApplication()->enqueueMessage(JText::_("AG_FOLDER_RENAMED") . "&nbsp;" . $AG_originalName, 'message');
                } else {
                    JFactory::getApplication()->enqueueMessage(JText::_("AG_CANNOT_RENAME_FOLDER") . "&nbsp;" . $AG_originalName, 'error');
                }
            } else {
                JFactory::getApplication()->enqueueMessage(JText::_("AG_FOLDER_WITH_THE_SAME_NAME_ALREADY_EXISTS"), 'error');
            }
        }
    }

    // =================================== _FOLDER_DESC_CONTENT
    // It creates caption tags with its content. After that it checks if XML already exists. If is it replace captions, if not it creates a new XML
    function _folder_desc_content($ag_itemURL, $AG_desc_content, $AG_desc_tags, $AG_folder_thumb)
    {
        $ag_folderName = dirname($ag_itemURL);

        // Set Possible Description File Apsolute Path // Instant patch for upper and lower case...
        $ag_pathWithStripExt = JPATH_SITE . $ag_folderName . DIRECTORY_SEPARATOR . JFile::stripExt(basename($ag_itemURL));
        $ag_XML_path = $ag_pathWithStripExt . ".xml";
        if (JFIle::exists($ag_pathWithStripExt . ".XML")) {
            $ag_XML_path = $ag_pathWithStripExt . ".XML";
        }

        // Set new Captions tag
        $ag_captions_new = "";
        $ag_captions_new .= "<captions>" . "\n";
        if (!empty($AG_desc_content)) {
            foreach ($AG_desc_content as $key => $value) {
                if (!empty($value)) {
                    $ag_captions_new .= "\t" . '<caption lang="' . strtolower($AG_desc_tags[$key]) . '">' . htmlspecialchars($value, ENT_QUOTES, "UTF-8") . '</caption>' . "\n";
                }
            }
        }
        $ag_captions_new .= "</captions>";

        // Set new Thumb tag
        $ag_thumb_new = "<thumb>" . $AG_folder_thumb . "</thumb>";

        $ag_XML_content = "";
        if (file_exists($ag_XML_path)) {
            $file = fopen($ag_XML_path, "r");
            while (!feof($file)) {
                $ag_XML_content .= fgetc($file);
            }
            fclose($file);
            if (preg_match("#<thumb[^}]*>(.*?)</thumb>#s", $ag_XML_content)) {
                $ag_XML_content = preg_replace("#<thumb[^}]*>(.*?)</thumb>#s", $ag_thumb_new, $ag_XML_content);
            } else {
                $ag_XML_content = preg_replace("#</image>#s", $ag_thumb_new . "\n" . "</image>", $ag_XML_content);
            }
            if (preg_match("#<captions[^}]*>(.*?)</captions>#s", $ag_XML_content)) {
                $ag_XML_content = preg_replace("#<captions[^}]*>(.*?)</captions>#s", $ag_captions_new, $ag_XML_content);
            } else {
                $ag_XML_content = preg_replace("#</image>#s", $ag_captions_new . "\n" . "</image>", $ag_XML_content);
            }
        } else {
            $ag_XML_content = '<?xml version="1.0" encoding="utf-8"?>' . "\n" . '<image>' . "\n" . '<visible>true</visible>' . "\n" . '<priority></priority>' . "\n" . '<thumb>' . $AG_folder_thumb . '</thumb>' . "\n" . $ag_captions_new . "\n" . '</image>';
        }

        // Save XML
        $this->_saveXML($ag_itemURL, $ag_XML_path, $ag_XML_content);
    }

    function _desc_content($ag_itemURL, $AG_desc_content, $AG_desc_tags)
    {
        $ag_folderName = dirname($ag_itemURL);

        // Set Possible Description File Apsolute Path // Instant patch for upper and lower case...
        $ag_pathWithStripExt = JPATH_SITE . $ag_folderName . DIRECTORY_SEPARATOR . JFile::stripExt(basename($ag_itemURL));
        $ag_XML_path = $ag_pathWithStripExt . ".xml";
        if (JFIle::exists($ag_pathWithStripExt . ".XML")) {
            $ag_XML_path = $ag_pathWithStripExt . ".XML";
        }

        $ag_captions_new = "";

        $ag_captions_new .= "<captions>" . "\n";
        if (!empty($AG_desc_content)) {
            foreach ($AG_desc_content as $key => $value) {
                if (!empty($value)) {
                    $ag_captions_new .= "\t" . '<caption lang="' . strtolower($AG_desc_tags[$key]) . '">' . htmlspecialchars($value, ENT_QUOTES, "UTF-8") . '</caption>' . "\n";
                }
            }
        }
        $ag_captions_new .= "</captions>";

        $ag_XML_content = "";
        if (file_exists($ag_XML_path)) {
            $file = fopen($ag_XML_path, "r");
            while (!feof($file)) {
                $ag_XML_content .= fgetc($file);
            }
            fclose($file);
            $ag_XML_content = preg_replace("#<captions[^}]*>(.*?)</captions>#s", $ag_captions_new, $ag_XML_content);
        } else {
            $ag_XML_content = '<?xml version="1.0" encoding="utf-8"?>' . "\n" . '<image>' . "\n" . '<visible>true</visible>' . "\n" . '<priority></priority>' . "\n" . $ag_captions_new . "\n" . '</image>';
        }

        // Save XML
        $this->_saveXML($ag_itemURL, $ag_XML_path, $ag_XML_content);
    }

}

