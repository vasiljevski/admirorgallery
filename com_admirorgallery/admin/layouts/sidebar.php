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

// Don't show navigation in front-end
//if ($this->get("show")) { return; }
$sidebar = JHtmlSidebar::render();
$xmlObject = simplexml_load_file(JPATH_COMPONENT_ADMINISTRATOR . '/com_admirorgallery.xml');

if (!$xmlObject){ return; }

?>
<div id="j-sidebar-container" class="span2">
	<div class="j-toggle-sidebar-header">
		<h3><?php echo JText::_('COM_ADMIRORGALLERY_MENU'); ?></h3>
	</div>
	<?php echo $sidebar; ?>
	<div class="well well-small">
		<div class="module-title nav-header">
			<?php echo JText::_('AG_VERSION'); ?>
		</div>
		<ul class="unstyled list-striped">
			<li><?php echo JText::_('COM_ADMIRORGALLERY_COMPONENT_VERSION') . "&nbsp;{$xmlObject->version}"; ?> </li>
			<li><?php echo JText::_('COM_ADMIRORGALLERY_PLUGIN_VERSION') . "&nbsp;{$xmlObject->pluginVersion}"; ?> </li>
			<li><?php echo JText::_('COM_ADMIRORGALLERY_BUTTON_VERSION') . "&nbsp;{$xmlObject->buttonVersion}"; ?> </li>
		</ul>
	</div>
</div>