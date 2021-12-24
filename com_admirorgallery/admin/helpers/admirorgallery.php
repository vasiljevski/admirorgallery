<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

/**
 * AdmirorGallery component helper.
 *
 * @since 5.5.0
 */
abstract class AdmirorGalleryHelper
{
	/**
	 * Configure the Linkbar
	 *
	 * @param string $submenu
	 * @param string $type
	 *
	 * @since 5.5.0
	 */
	public static function addSubmenu(string $submenu, string $type)
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

	/**
	 * Read JInput values
	 *
	 * @param string $name
	 * @param $default
	 *
	 * @return string|null
	 *
	 * @since 5.5.0
	 */
	public static function getCmd(string $name, $default): ?string
	{
		try {
			$input = JFactory::getApplication()->input;
		} catch (Exception $e) {
			trigger_error($e);
			return null;
		}
		return $input->getCmd($name, $default);
	}

	/**
	 * Image root path
	 *
	 * @return string
	 *
	 * @since 5.5.0
	 */
	public static function getRootPath(): string
	{
		$plugin = JPluginHelper::getPlugin('content', 'admirorgallery');
		$pluginParams = new JRegistry($plugin->params);
		return $pluginParams->get('rootFolder', '/images/sampledata/');
	}
}

