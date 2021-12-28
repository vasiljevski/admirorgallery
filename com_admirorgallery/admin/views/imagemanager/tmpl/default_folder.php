<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Admiror\Plugin\Content\AdmirorGallery\agHelper;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Filesystem\File as JFile;
use Joomla\CMS\Filesystem\Folder as JFolder;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Uri\Uri as JURI;

$ag_XML_thumb = "";

$ag_folderName = dirname($this->ag_init_itemURL);
$ag_fileName = basename($this->ag_init_itemURL);

agHelper::ag_sureRemoveDir($this->thumbsPath, true);
if (!JFolder::create($this->thumbsPath)) {
    JFactory::getApplication()->enqueueMessage(JText::_("AG_CANNOT_CREATE_FOLDER") . "&nbsp;" . $newFolderName, 'error');
}

$ag_preview_content = '
<hr />
' . "\n";

$ag_preview_content .= '
<h1>' . JText::_('AG_CURRENT_FOLDER') . '</h1>

<div class="AG_breadcrumbs_wrapper">
     ' . $this->ag_render_breadcrumb($this->ag_init_itemURL, $this->ag_starting_folder, $ag_folderName, $ag_fileName) . '
</div>
<hr />

<table cellspacing="0" cellpadding="0" border="0" class="AG_fieldset">
     <tbody>
     <tr>
          <td>
                <img src="' . JURI::root() . 'administrator/components/com_admirorgallery/templates/' . $this->ag_template_id . '/images/operations.png" style="float:left;" />
          </td>
	      <td>
	            ' . JText::_('AG_OPERATION_WITH_SELECTED_ITEMS') . '
	      </td>
	      <td>
            <select id="AG_operations" name="AG_operations">
                <option value="none" >' . JText::_('AG_NONE') . '</option>
                <option value="delete" >' . JText::_('AG_DELETE') . '</option>
                <option value="copy">' . JText::_('AG_COPY_TO') . '</option>
                <option value="move">' . JText::_('AG_MOVE_TO') . '</option>
                <option value="bookmark">' . JText::_('AG_BOOKMARK') . '</option>
                <option value="show">' . JText::_('AG_SHOW') . '</option>
                <option value="hide">' . JText::_('AG_HIDE') . '</option>
            </select>
	      </td>
      	  <td id="AG_targetFolder">
      	  </td>
     </tr>
     </tbody>
</table>
<hr />

<table cellspacing="0" cellpadding="0" border="0" class="AG_fieldset">
     <tbody>
     <tr>
	  <td><img src="' . JURI::root() . 'administrator/components/com_admirorgallery/templates/' . $this->ag_template_id . '/images/upload.png" style="float:left;" /></td><td>&nbsp;' . JText::_('AG_UPLOAD_IMAGES_JPG_JPEG_GIF_PNG_OR_ZIP') . '&nbsp;[ <b>' . JText::_('AG_MAX') . '&nbsp;' . (JComponentHelper::getParams('com_media')->get('upload_maxsize', 0)) . ' MB</b> ]:&nbsp;</td><td><input type="file" name="AG_fileUpload" /></td>
     </tr>
     </tbody>
</table>
<hr />
<table cellspacing="0" cellpadding="0" border="0" class="AG_fieldset">
     <tbody>
     <tr>
	  <td><img src="' . JURI::root() . 'administrator/components/com_admirorgallery/templates/' . $this->ag_template_id . '/images/folder-new.png" style="float:left;" /></td><td>&nbsp;' . JText::_('AG_CREATE_FOLDERS') . '&nbsp;</td>
<td id="AG_folder_add">
    <a href=""  id="ag_add_new_folder_input" class="AG_common_button">
    <span><span>
	' . JText::_('AG_ADD') . '
    </span></span>
    </a>
</td>
     </tr>
     </tbody>
</table>
<hr />

<input type="hidden" name="AG_folderSettings_status" id="AG_folderSettings_status" />
<a href="" id="AG_btn_showFolderSettings" class="AG_common_button">
    <span><span>
    ' . JText::_('AG_EDIT_FOLDER_CAPTIONS') . '
    </span></span>
</a>
<div id="AG_folderSettings_wrapper" style="display:none;">

<br />
' . "\n";

// Set Possible Description File Absolute Path // Instant patch for upper and lower case...
$ag_pathWithStripExt = JPATH_SITE . $ag_folderName . '/' . JFile::stripExt($ag_fileName);
$ag_XML_path = $ag_pathWithStripExt . ".XML";
if (JFIle::exists($ag_pathWithStripExt . ".xml")) {
    $ag_XML_path = $ag_pathWithStripExt . ".xml";
}

// Load if XML exists
if (file_exists($ag_XML_path)) {
    $ag_XML_xml = simplexml_load_file($ag_XML_path);
    if ($ag_XML_xml->thumb) {
        $ag_XML_thumb = $ag_XML_xml->thumb;
    }
    if ($ag_XML_xml->captions) {
        $ag_XML_captions = $ag_XML_xml->captions;
    }
} else {
    $ag_XML_captions = null;
}

$ag_preview_content .= $this->ag_render_captions($ag_XML_captions);

$ag_preview_content .= '
</div>
<hr />
';

// RENDER FOLDERS
// CREATED SORTED ARRAY OF FOLDERS
$ag_files = JFolder::folders(JPATH_SITE . $this->ag_init_itemURL);

if (!empty($ag_files)) {

    $ag_folders_priority = array();
    $ag_folders_noPriority = array();
    $ag_folders = array();

    foreach ($ag_files as $key => $value) {
        $ag_folderName = $this->ag_init_itemURL;
        $ag_fileName = basename($value);
        // Set Possible Description File Absolute Path // Instant patch for upper and lower case...
        $ag_pathWithStripExt = JPATH_SITE . $ag_folderName . JFile::stripExt($ag_fileName);
        $ag_XML_path = $ag_pathWithStripExt . ".XML";
        if (JFIle::exists($ag_pathWithStripExt . ".xml")) {
            $ag_XML_path = $ag_pathWithStripExt . ".xml";
        }
        if (file_exists($ag_XML_path)) {
            $ag_XML_xml = simplexml_load_file($ag_XML_path);
            $ag_XML_priority = $ag_XML_xml->priority;
        }

        if (!empty($ag_XML_priority) && file_exists($ag_XML_path)) {
            $ag_folders_priority[$value] = $ag_XML_priority; // PRIORITIES IMAGES
        } else {
            $ag_folders_noPriority[] = $value; // NON PRIORITIES IMAGES
        }
    }
}

if (!empty($ag_folders_priority)) {
    asort($ag_folders_priority);
    foreach ($ag_folders_priority as $key => $value) {
        $ag_folders[] = $key;
    }
}

if (!empty($ag_folders_noPriority)) {
    natcasesort($ag_folders_noPriority);
    foreach ($ag_folders_noPriority as $key => $value) {
        $ag_folders[] = $value;
    }
}

if (!empty($ag_folders)) {
    foreach ($ag_folders as $key => $value) {

        $ag_hasXML = "";
        $ag_hasThumb = "";

        // Set Possible Description File Absolute Path // Instant patch for upper and lower case...
        $ag_pathWithStripExt = JPATH_SITE . $this->ag_init_itemURL . JFile::stripExt(basename($value));
        $ag_XML_path = $ag_pathWithStripExt . ".xml";
        if (JFIle::exists($ag_pathWithStripExt . ".XML")) {
            $ag_XML_path = $ag_pathWithStripExt . ".XML";
        }

        $ag_XML_visible = "AG_VISIBLE";
        $ag_XML_priority = "";
        if (file_exists($ag_XML_path)) {
            $ag_hasXML = '<img src="' . JURI::root() . 'administrator/components/com_admirorgallery/templates/' . $this->ag_template_id . '/images/icon-hasXML.png"  class="ag_hasXML" />';
            $ag_XML_xml = simplexml_load_file($ag_XML_path);
            if (isset($ag_XML_xml->priority)) {
                $ag_XML_priority = $ag_XML_xml->priority;
            }
            if (isset($ag_XML_xml->visible)) {
                if ((string)$ag_XML_xml->visible == "false") {
                    $ag_XML_visible = "AG_HIDDEN";
                }
            }
        }


        $ag_preview_content .= '
    <div class="AG_border_color AG_border_width AG_item_wrapper">
	    <a href="' . $this->ag_init_itemURL . $value . '/" class="AG_folderLink AG_item_link" title="' . $value . '">
	        <div style="display:block; text-align:center;" class="AG_item_img_wrapper">
		    <img src="' . JURI::root() . 'administrator/components/com_admirorgallery/templates/' . $this->ag_template_id . '/images/folder.png" />
	        </div>
	    </a>
	    <div class="AG_border_color AG_border_width AG_item_controls_wrapper">
	        <input type="text" value="' . $value . '" name="AG_rename[' . $this->ag_init_itemURL . $value . ']" class="AG_input" style="width:95%" /><hr />
	        ' . JText::_($ag_XML_visible) . '<hr />
	        <img src="' . JURI::root() . 'administrator/components/com_admirorgallery/templates/' . $this->ag_template_id . '/images/operations.png" style="float:left;" /><input type="checkbox" value="' . $this->ag_init_itemURL . $value . '/" name="AG_cbox_selectItem[]" class="AG_cbox_selectItem"><hr />
	        ' . JText::_('AG_PRIORITY') . ':&nbsp;<input type="text" size="3" value="' . $ag_XML_priority . '" name="AG_cbox_priority[' . $this->ag_init_itemURL . $value . ']" class="AG_input" />
	    </div>
    </div>
    ';
    }
}

// RENDER IMAGES
// CREATED SORTED ARRAY OF IMAGES
$ag_files = JFolder::files(JPATH_SITE . $this->ag_init_itemURL);
$ag_ext_valid = array("jpg", "jpeg", "gif", "png"); // SET VALID IMAGE EXTENSION

if (!empty($ag_files)) {

    $ag_images_priority = array();
    $ag_images_noPriority = array();
    $ag_images = array();

    foreach ($ag_files as $key => $value) {
        if (is_numeric(array_search(strtolower(JFile::getExt(basename($value))), $ag_ext_valid))) {

            $ag_folderName = $this->ag_init_itemURL;
            $ag_fileName = basename($value);

            // Set Possible Description File Absolute Path // Instant patch for upper and lower case...
            $ag_pathWithStripExt = JPATH_SITE . $ag_folderName . JFile::stripExt($ag_fileName);
            $ag_XML_path = $ag_pathWithStripExt . ".XML";
            if (JFIle::exists($ag_pathWithStripExt . ".xml")) {
                $ag_XML_path = $ag_pathWithStripExt . ".xml";
            }
            if (file_exists($ag_XML_path)) {
                $ag_XML_xml = simplexml_load_file($ag_XML_path);
                $ag_XML_priority = $ag_XML_xml->priority;
            }

            if (!empty($ag_XML_priority) && file_exists($ag_XML_path)) {
                $ag_images_priority[$value] = $ag_XML_priority; // PRIORITIES IMAGES
            } else {
                $ag_images_noPriority[] = $value; // NON PRIORITIES IMAGES
            }
        }
    }
}

if (!empty($ag_images_priority)) {
    asort($ag_images_priority);
    foreach ($ag_images_priority as $key => $value) {
        $ag_images[] = $key;
    }
}

if (!empty($ag_images_noPriority)) {
    natcasesort($ag_images_noPriority);
    foreach ($ag_images_noPriority as $key => $value) {
        $ag_images[] = $value;
    }
}

if (!empty($ag_images)) {
    foreach ($ag_images as $key => $value) {


        $ag_hasXML = "";
        $ag_hasThumb = "";

        // Set Possible Description File Absolute Path // Instant patch for upper and lower case...
        $ag_pathWithStripExt = JPATH_SITE . $this->ag_init_itemURL . JFile::stripExt(basename($value));
        $ag_XML_path = $ag_pathWithStripExt . ".xml";
        if (JFIle::exists($ag_pathWithStripExt . ".XML")) {
            $ag_XML_path = $ag_pathWithStripExt . ".XML";
        }

        $ag_XML_visible = "AG_VISIBLE";
        $ag_XML_priority = "";
        if (file_exists($ag_XML_path)) {
            $ag_hasXML = '<img src="' . JURI::root() . 'administrator/components/com_admirorgallery/templates/' . $this->ag_template_id . '/images/icon-hasXML.png"  class="ag_hasXML" />';
            $ag_XML_xml = simplexml_load_file($ag_XML_path);
            if (isset($ag_XML_xml->priority)) {
                $ag_XML_priority = $ag_XML_xml->priority;
            }
            if (isset($ag_XML_xml->visible)) {
                if ((string)$ag_XML_xml->visible == "false") {
                    $ag_XML_visible = "AG_HIDDEN";
                }
            }
        }

        if (file_exists(JPATH_SITE . "/plugins/content/admirorgallery/admirorgallery/thumbs/" . basename($ag_folderName) . "/" . basename($value))) {
            $ag_hasThumb = '<img src="' . JURI::root() . 'administrator/components/com_admirorgallery/templates/' . $this->ag_template_id . '/images/icon-hasThumb.png"  class="ag_hasThumb" />';
        }


        agHelper::ag_createThumb(JPATH_SITE . $this->ag_init_itemURL . $value, $this->thumbsPath . DIRECTORY_SEPARATOR . $value, 145, 80, "none");

        $AG_thumb_checked = "";
        if ($ag_XML_thumb == $value) {
            $AG_thumb_checked = " CHECKED";
        }

        $ag_preview_content .= '
     <div class="AG_border_color AG_border_width AG_item_wrapper">
	<a href="' . $this->ag_init_itemURL . $value . '" class="AG_fileLink AG_item_link" title="' . $value . '">
	      <div style="display:block; text-align:center;" class="AG_item_img_wrapper">
	      <img src="' . JURI::root() . 'administrator/components/com_admirorgallery/assets/thumbs/' . $value . '" class="ag_imgThumb" />
	      </div>
	</a>
	<div class="AG_border_color AG_border_width AG_item_controls_wrapper">
	    <input type="text" value="' . JFile::stripExt(basename($value)) . '" name="AG_rename[' . $this->ag_init_itemURL . $value . ']" class="AG_input" style="width:95%" /><hr />
	    ' . JText::_($ag_XML_visible) . '<hr />
        <img src="' . JURI::root() . 'administrator/components/com_admirorgallery/templates/' . $this->ag_template_id . '/images/operations.png" style="float:left;" /><input type="checkbox" value="' . $this->ag_init_itemURL . $value . '" name="AG_cbox_selectItem[]" class="AG_cbox_selectItem"><hr />
	    ' . JText::_('AG_PRIORITY') . ':&nbsp;<input type="text" size="3" value="' . $ag_XML_priority . '" name="AG_cbox_priority[' . $this->ag_init_itemURL . $value . ']" class="AG_input" /><hr />
        <input type="radio" value="' . $value . '" name="AG_folder_thumb" class="AG_folder_thumb" class="AG_input"' . $AG_thumb_checked . ' />&nbsp;' . JText::_('AG_FOLDER_THUMB') . '
	</div>
     </div>
     ';
    }
}

if (empty($ag_folders) && empty($ag_images)) {
    $ag_preview_content .= JText::_('AG_NO_FOLDERS_OR_IMAGES_FOUND_IN_CURRENT_FOLDER');
}


$AG_folderDroplist = "<select id='AG_operations_targetFolder' name='AG_operations_targetFolder'>";
$AG_folders = JFolder::listFolderTree(JPATH_SITE . $this->ag_rootFolder, "");
$AG_rootFolder_strlen = strlen($this->ag_rootFolder);
$AG_folderDroplist .= "<option value='" . $this->ag_rootFolder . "' >" . JText::_('AG_IMAGES_ROOT_FOLDER') . "</option>";
if (!empty($AG_folders)) {
    foreach ($AG_folders as $AG_folders_key => $AG_folders_value) {
        $AG_folderName = substr($AG_folders_value['relname'], $AG_rootFolder_strlen);
        str_replace('\\\\', DIRECTORY_SEPARATOR, $AG_folderName);
        $AG_folderDroplist .= "<option value='" . $this->ag_rootFolder . addslashes($AG_folderName) . "' >" . addslashes($AG_folderName) . "</option>";
    }
}
$AG_folderDroplist .= "</select>";

$ag_preview_content .= '

<script type="text/javascript">
AG_jQuery("#AG_operations").change(function() {
        switch(AG_jQuery(this).val())
        {
        case "delete":
          AG_jQuery("#AG_targetFolder").html("<img src=\'' . JURI::root() . 'administrator/components/com_admirorgallery/templates/' . $this->ag_template_id . '/images/alert.png\'  style=\'float:left;\' />&nbsp;' . JText::_('AG_SELECTED_ITEMS_WILL_BE_DELETED') . '");
          break;
        case "move":
          AG_jQuery("#AG_targetFolder").html("' . $AG_folderDroplist . '");
          break;
        case "copy":
          AG_jQuery("#AG_targetFolder").html("' . $AG_folderDroplist . '");
          break;
        default:
          AG_jQuery("#AG_targetFolder").html("");
        }
});


</script>

';
