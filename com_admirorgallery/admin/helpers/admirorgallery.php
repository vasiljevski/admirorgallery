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

use Joomla\CMS\Factory as JFactory;
use Joomla\Registry\Registry as JRegistry;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;

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
	 * @param   string $submenu Submenu
	 * @param   string $type    Type
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public static function addSubmenu(string $submenu, string $type): void
	{
		JHtmlSidebar::addEntry(JText::_('COM_ADMIRORGALLERY_CONTROL_PANEL'),
			'index.php?option=com_admirorgallery&amp;controller=admirorgallery', $submenu == 'control_panel'
		);
		JHtmlSidebar::addEntry(JText::_('COM_ADMIRORGALLERY_TEMPLATES'),
			'index.php?option=com_admirorgallery&amp;view=resourcemanager&amp;resourceType=templates',
			$type == 'templates'
		);
		JHtmlSidebar::addEntry(JText::_('COM_ADMIRORGALLERY_POPUPS'),
			'index.php?option=com_admirorgallery&amp;view=resourcemanager&amp;resourceType=popups',
			$type == 'popups'
		);
		JHtmlSidebar::addEntry(JText::_('COM_ADMIRORGALLERY_IMAGE_MANAGER'),
			'index.php?option=com_admirorgallery&amp;view=imagemanager',
			$submenu == 'imagemanager'
		);
	}

	/**
	 * Read JInput values
	 *
	 * @param   string $name    Name of the fieald we need walue from
	 * @param   mixed  $default Default value in case none is found
	 *
	 * @return string|null
	 *
	 * @since 5.5.0
	 */
	public static function getCmd(string $name, string $default): ?string
	{
		try
		{
			$input = JFactory::getApplication()->input;
		}
		catch (Exception $e)
		{
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

