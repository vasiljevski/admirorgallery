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
?>
<div class="row-fluid">
<?php
if ($this->app->isClient('administrator'))
:
	?>
	<div class="span2">
		<div class="well well-small">
			<div class="module-title nav-header"><?php echo JText::_('COM_ADMIRORGALLERY_MENU'); ?></div>
			<?php echo $this->sidebar; ?>
		</div>
		<div class="well well-small">
			<div class="module-title nav-header"> <?php echo JText::_('AG_VERSION'); ?> </div>
			<ul class="unstyled list-striped">
				<?php echo $this->getVersionInfoHTML(); ?>
			</ul>
		</div>
	</div>
	<div class="span10">
	<?php
else : 
	?>
		<div class="span12">
	<?php
endif;
?>
		<div class="well well-small">
			<form action="<?php echo JRoute::_('index.php?option=com_admirorgallery&view=imagemanager'); ?>"
					method="post"
					name="adminForm"
					id="adminForm"
					enctype="multipart/form-data">

				<input type="hidden" name="option" value="com_admirorgallery"/>
				<input type="hidden" name="task" value=""/>
				<input type="hidden" name="boxchecked" value="0"/>
				<input type="hidden" name="view" value="imagemanager"/>
				<input type="hidden" name="controller" value="imagemanager"/>
				<input type="hidden" name="itemURL" value="<?php echo $this->initItemURL; ?>"
						id="AG_input_itemURL"/>

<?php
if (file_exists(JPATH_SITE . $this->initItemURL))
{
	if (is_dir(JPATH_SITE . $this->initItemURL))
	{
		$itemType = "folder";
	}
	else
	{
		$itemType = "file";
	}

	require_once JPATH_ROOT . DIRECTORY_SEPARATOR .
		'administrator' . DIRECTORY_SEPARATOR .
		'components' . DIRECTORY_SEPARATOR .
		'com_admirorgallery' . DIRECTORY_SEPARATOR .
		'views' . DIRECTORY_SEPARATOR .
		'imagemanager' . DIRECTORY_SEPARATOR .
		'tmpl' . DIRECTORY_SEPARATOR .
		'default_' . $itemType . '.php';
}
else
{
	$errorMsg[] = array(JText::_('AG_FOLDER_OR_IMAGE_NOT_FOUND'), $this->initItemURL);
	$this->app->enqueueMessage(JText::_('AG_FOLDER_OR_IMAGE_NOT_FOUND') . '<br>' . $this->initItemURL, 'warning');
	$previewContent = '
				<div class="ag_screenSection_title">
						' . $this->initItemURL . '
				</div>';

	return;
}
?>
					<!--Include JavaScript-->
					<?php require_once JPATH_ROOT . DIRECTORY_SEPARATOR .
						'administrator' . DIRECTORY_SEPARATOR .
						'components' . DIRECTORY_SEPARATOR .
						'com_admirorgallery' . DIRECTORY_SEPARATOR .
						'views' . DIRECTORY_SEPARATOR .
						'imagemanager' . DIRECTORY_SEPARATOR .
						'tmpl' . DIRECTORY_SEPARATOR .
						'default_script.php'; ?>
					<!--In front end add toolbar-->
<?php
if ($this->app->isClient('site'))
{
	require_once JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'toolbar.php';
	?>
						<div class="AG_border_color AG_border_width AG_toolbar">
							<?php echo AdmirorgalleryHelperToolbar::getToolbar(); ?>
						</div>
<?php } ?>
<!--Include panel HTML-->
<?php require_once JPATH_ROOT . DIRECTORY_SEPARATOR .
'administrator' . DIRECTORY_SEPARATOR .
'components' . DIRECTORY_SEPARATOR .
'com_admirorgallery' . DIRECTORY_SEPARATOR .
'views' . DIRECTORY_SEPARATOR .
'imagemanager' . DIRECTORY_SEPARATOR .
'tmpl' . DIRECTORY_SEPARATOR .
'default_panel.php';
?>
				</form>
			</div>
		</div>
	</div>
