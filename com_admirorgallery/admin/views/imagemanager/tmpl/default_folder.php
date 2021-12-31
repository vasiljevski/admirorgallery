<?php
/**
 * @version     6.0.0
 * @package     Admiror.Administrator
 * @subpackage  com_admirorgallery
 * @author      Igor Kekeljevic <igor@admiror.com>
 * @author      Nikola Vasiljevski <nikola83@gmail.com>
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Admiror\Plugin\Content\AdmirorGallery\Helper;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Filesystem\File as JFile;
use Joomla\CMS\Filesystem\Folder as JFolder;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Uri\Uri as JURI;

$xmlThumb = "";

$folderName = dirname($this->initItemURL);
$fileName = basename($this->initItemURL);

Helper::sureRemoveDir($this->thumbsPath, true);

if (!JFolder::create($this->thumbsPath))
{
	JFactory::getApplication()->enqueueMessage(JText::_("AG_CANNOT_CREATE_FOLDER") . "&nbsp;" . $newFolderName, 'error');
}

$previewContent = '
<hr />
' . "\n";

$previewContent .= '
<h1>' . JText::_('AG_CURRENT_FOLDER') . '</h1>

<div class="AG_breadcrumbs_wrapper">
     ' . $this->renderBreadcrumb($this->initItemURL, $this->startingFolder, $folderName, $fileName) . '
</div>
<hr />

<table cellspacing="0" cellpadding="0" border="0" class="AG_fieldset">
     <tbody>
     <tr>
          <td>
                <img src="' . JURI::root(true) . '/administrator/components/com_admirorgallery/templates/' . $this->templateName . '/images/operations.png" style="float:left;" />
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
	  <td><img src="' . JURI::root(true) . '/administrator/components/com_admirorgallery/templates/' . $this->templateName . '/images/upload.png" style="float:left;" /></td><td>&nbsp;' . JText::_('AG_UPLOAD_IMAGES_JPG_JPEG_GIF_PNG_OR_ZIP') . '&nbsp;[ <b>' . JText::_('AG_MAX') . '&nbsp;' . (JComponentHelper::getParams('com_media')->get('upload_maxsize', 0)) . ' MB</b> ]:&nbsp;</td><td><input type="file" name="AG_fileUpload" /></td>
     </tr>
     </tbody>
</table>
<hr />
<table cellspacing="0" cellpadding="0" border="0" class="AG_fieldset">
     <tbody>
     <tr>
	  <td><img src="' . JURI::root(true) . '/administrator/components/com_admirorgallery/templates/' . $this->templateName . '/images/folder-new.png" style="float:left;" /></td><td>&nbsp;' . JText::_('AG_CREATE_FOLDERS') . '&nbsp;</td>
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
$pathWithStripExt = JPATH_SITE . $folderName . '/' . JFile::stripExt($fileName);
$xmlFilePath = $pathWithStripExt . ".XML";

if (JFIle::exists($pathWithStripExt . ".xml"))
{
	$xmlFilePath = $pathWithStripExt . ".xml";
}

// Load if XML exists
if (file_exists($xmlFilePath))
{
	$xmlObject = simplexml_load_file($xmlFilePath);

	if ($xmlObject->thumb)
	{
		$xmlThumb = $xmlObject->thumb;
	}


	if ($xmlObject->captions)
	{
		$xmlCaptions = $xmlObject->captions;
	}
}
else
{
	$xmlCaptions = null;
}

$previewContent .= $this->renderCaptions($xmlCaptions);

$previewContent .= '
</div>
<hr />
';

$previewContent .= $this->renderItems($this->initItemURL, 'folder', $xmlThumb);

$previewContent .= $this->renderItems($this->initItemURL, 'file', $xmlThumb);


$AG_folderDroplist = "<select id='operationsTargetFolder' name='operationsTargetFolder'>";
$AG_folders = JFolder::listFolderTree(JPATH_SITE . $this->rootFolder, "");
$rootFolder_strlen = strlen($this->rootFolder);
$AG_folderDroplist .= "<option value='" . $this->rootFolder . "' >" . JText::_('AG_IMAGES_ROOT_FOLDER') . "</option>";

if (!empty($AG_folders))
{
	foreach ($AG_folders as $AG_folders_key => $AG_folders_value)
	{
		$folderName = substr($AG_folders_value['relname'], $rootFolder_strlen);
		str_replace('\\\\', DIRECTORY_SEPARATOR, $folderName);
		$AG_folderDroplist .= "<option value='" . $this->rootFolder . addslashes($folderName) . "' >" . addslashes($folderName) . "</option>";
	}
}

$AG_folderDroplist .= "</select>";

$previewContent .= '

<script type="text/javascript">
AG_jQuery("#AG_operations").change(function() {
        switch(AG_jQuery(this).val())
        {
        case "delete":
          AG_jQuery("#AG_targetFolder").html("<img src=\'' . JURI::root(true) . '/administrator/components/com_admirorgallery/templates/' . $this->templateName . '/images/alert.png\'  style=\'float:left;\' />&nbsp;' . JText::_('AG_SELECTED_ITEMS_WILL_BE_DELETED') . '");
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
