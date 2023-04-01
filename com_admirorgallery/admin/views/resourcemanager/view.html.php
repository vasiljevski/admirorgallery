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
use Joomla\CMS\Filesystem\Folder as JFolder;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\MVC\View\HtmlView as JViewLegacy;
use Joomla\CMS\Toolbar\ToolbarHelper as JToolBarHelper;

/**
 * AdmirorgalleryViewResourcemanager
 *
 * @since 1.0.0
 */
class AdmirorgalleryViewResourcemanager extends JViewLegacy
{
	/**
	 * @var array
	 *
	 * @since 1.0.0
	 */
	public array $resourceManagerInstalled = array();

	/**
	 * @var integer
	 *
	 * @since 1.0.0
	 */
	public int $limitstart;

	/**
	 * @var integer
	 *
	 * @since 1.0.0
	 */
	public int $limit;

	/**
	 * @var string
	 *
	 * @since 1.0.0
	 */
	public string $resourceType = 'templates';

	/**
	 * display
	 *
	 * @param   mixed $tpl Template to be displayed
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function display($tpl = null)
	{
		// Check if plugin is installed, otherwise don't show view
		if (!is_dir(JPATH_SITE . '/plugins/content/admirorgallery/'))
		{
			return;
		}

		$app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		// Current resource type
		$this->resourceType = $jinput->getVar('resourceType');

		JToolBarHelper::title(JText::_('COM_ADMIRORGALLERY_' . strtoupper($this->resourceType)), $this->resourceType);

		// Loading JPagination vars
		$this->limitstart = $app->getUserStateFromRequest($option . '.limitstart', 'limitstart', 0, 'int');
		$this->limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');

		// Read folder depending on $resourceType
		$this->resourceManagerInstalled = JFolder::folders(JPATH_SITE . '/plugins/content/admirorgallery/admirorgallery/' . $this->resourceType);
		sort($this->resourceManagerInstalled);

		parent::display($tpl);
	}
}
