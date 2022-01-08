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

use Joomla\CMS\Language\Text as JText;

$operationsIcon = "{$this->juriRoot}/administrator/components/com_admirorgallery/templates/{$this->templateName}/images/operations.png";
$uploadIcon = "{$this->juriRoot}/administrator/components/com_admirorgallery/templates/{$this->templateName}/images/upload.png";
$newFolderIcon = "{$this->juriRoot}/administrator/components/com_admirorgallery/templates/{$this->templateName}/images/folder-new.png";
$alertIcon = "{$this->juriRoot}/administrator/components/com_admirorgallery/templates/{$this->templateName}/images/alert.png";

?>
<hr />
<h1><?php echo JText::_('AG_CURRENT_FOLDER'); ?></h1>

<div class="AG_breadcrumbs_wrapper">
	<?php echo $this->renderBreadcrumb($this->initItemURL, $this->startingFolder); ?>
</div>
<hr />

<table cellspacing="0" cellpadding="0" class="AG_fieldset">
	<tbody>
		<tr>
			<td>
				<img src="<?php echo $operationsIcon;?>" style="float:left;" />
			</td>
			<td>
				<?php echo JText::_('AG_OPERATION_WITH_SELECTED_ITEMS'); ?>
			</td>
			<td>
				<select id="AG_operations" name="AG_operations">
					<option value="none"><?php echo JText::_('AG_NONE');?></option>
					<option value="delete"><?php echo JText::_('AG_DELETE');?></option>
					<option value="copy"><?php echo JText::_('AG_COPY_TO'); ?></option>
					<option value="move"><?php echo JText::_('AG_MOVE_TO'); ?></option>
					<option value="bookmark"><?php echo JText::_('AG_BOOKMARK'); ?></option>
					<option value="show"><?php echo JText::_('AG_SHOW'); ?></option>
					<option value="hide"><?php echo JText::_('AG_HIDE'); ?></option>
				</select>
			</td>
			<td id="AG_targetFolder">
			</td>
		</tr>
	</tbody>
</table>
<hr />

<table cellspacing="0" cellpadding="0" class="AG_fieldset">
	<tbody>
		<tr>
			<td><img src=<?php echo $uploadIcon; ?> style="float:left;" /></td>
			<td>&nbsp;<?php echo JText::_('AG_UPLOAD_IMAGES_JPG_JPEG_GIF_PNG_OR_ZIP') . '&nbsp;[ <b>' . JText::_('AG_MAX') .
					'&nbsp;' . $this->uploadMaxSize; ?> MB</b> ]:&nbsp;
			</td>
			<td><input type="file" name="AG_fileUpload" /></td>
		</tr>
	</tbody>
</table>
<hr />
<table cellspacing="0" cellpadding="0" class="AG_fieldset">
	<tbody>
		<tr>
			<td><img src=<?php echo $newFolderIcon; ?> style="float:left;" /></td>
			<td>&nbsp;<?php echo JText::_('AG_CREATE_FOLDERS'); ?>&nbsp;</td>
			<td id="AG_folder_add">
				<a href="" id="ag_add_new_folder_input" class="AG_common_button">
					<span><span>
							<?php echo JText::_('AG_ADD'); ?>
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
			<?php echo JText::_('AG_EDIT_FOLDER_CAPTIONS'); ?>
		</span></span>
</a>
<div id="AG_folderSettings_wrapper" style="display:none;">
	<br />
	<?php echo $this->renderCaptions($xmlCaptions); ?>
</div>
<hr />

<?php echo $this->renderItems($this->initItemURL, 'folder', true) ?>

<script type="text/javascript">
AG_jQuery("#AG_operations").change(function() {
	switch (AG_jQuery(this).val()) {
		case "delete":
			AG_jQuery("#AG_targetFolder").html(
				"<img src=\'<?php echo $alertIcon; ?>\' style=\'float:left;\' />&nbsp;'<?php echo JText::_('AG_SELECTED_ITEMS_WILL_BE_DELETED');?> '"
			);
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