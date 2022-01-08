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

$bookmarkIcon = "{$this->templateBasePath}/images/bookmark.png";
$removeIcon = "{$this->templateBasePath}/images/bookmarkRemove.png";

?>
<div class="AG_bookmarks_wrapper" style="display:none;">
	<h1>
		<img src="<?php echo $bookmarkIcon; ?>"
			style="float:left;" />&nbsp;<?php echo JText::_('AG_GALLERIES'); ?>
	</h1>
	<ul>
		<?php foreach ($this->bookmarks->bookmark as $value) : ?>
		<li id="banners" class="folder">
			<img src="<?php echo $removeIcon; ?>" style="float:left;" />
			<input type="checkbox" value="<?php echo $value; ?>" name="bookmarksToRemove[]">
			<a href="<?php echo $value ?>" class="AG_folderLink AG_common_button" title="<?php echo $value ?>">
				<span>
					<span>
						<?php echo Helper::shrinkString(basename($value), 20); ?>
					</span>
				</span>
			</a>
		</li>
		<?php endforeach;?>
	</ul>
	<div style="clear:both" class="AG_margin_bottom"></div>
	<hr />
	<div class="AG_legend">
		<h2><?php echo JText::_('AG_LEGEND') ?></h2>
		<img src="<?php echo $removeIcon; ?>" style="float:left;" />
		<?php echo JText::_('AG_SELECT_TO_REMOVE_BOOKMARK') ?>
	</div>
</div>