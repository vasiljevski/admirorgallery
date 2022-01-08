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
	<?php echo JLayoutHelper::render('sidebar', array ("show", $this->app->isClient('administrator'))); ?>
	<div id="j-main-container" class="span12">
		<div class="well well-small">
			<form action="<?php echo JRoute::_('index.php?option=com_admirorgallery&view=imagemanager'); ?>"
				method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">

				<input type="hidden" name="option" value="com_admirorgallery" />
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="view" value="imagemanager" />
				<input type="hidden" name="controller" value="imagemanager" />
				<input type="hidden" name="itemURL" value="<?php echo $this->initItemURL; ?>" id="AG_input_itemURL" />
				<?php echo $this->loadTemplate('navigation'); ?>
				<?php echo $this->toolbar; ?>
				<?php echo $this->loadTemplate('bookmarks'); ?>
				<?php echo $this->loadTemplate('media'); ?>
			</form>
		</div>
	</div>
</div>
<!--Include JavaScript-->
<?php echo $this->loadTemplate('script'); ?>