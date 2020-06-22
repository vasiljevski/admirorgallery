<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2020 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * AdmirorGallery component helper.
 */
abstract class AdmirorGalleryHelper
{
	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($submenu,$type)
	{
		JHtmlSidebar::addEntry(JText::_('COM_ADMIRORGALLERY_CONTROL_PANEL'),
		                         'index.php?option=com_admirorgallery&amp;controller=admirorgallery', $submenu == 'control_panel');
		JHtmlSidebar::addEntry(JText::_('COM_ADMIRORGALLERY_TEMPLATES'),
		                         'index.php?option=com_admirorgallery&amp;view=resourcemanager&amp;AG_resourceType=templates',
		                         $type == 'templates');
		JHtmlSidebar::addEntry(JText::_('COM_ADMIRORGALLERY_POPUPS'),
		                         'index.php?option=com_admirorgallery&amp;view=resourcemanager&amp;AG_resourceType=popups',
		                         $type == 'popups');
		JHtmlSidebar::addEntry(JText::_('COM_ADMIRORGALLERY_IMAGE_MANAGER'),
		                         'index.php?option=com_admirorgallery&amp;view=imagemanager',
		                         $submenu == 'imagemanager');
	}
}

?>
