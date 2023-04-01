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
use Joomla\CMS\MVC\View\HtmlView as JViewLegacy;
use Joomla\CMS\Toolbar\ToolbarHelper as JToolBarHelper;

/**
 * AdmirorgalleryViewAdmirorgallery
 *
 * @since 1.0.0
 */
class AdmirorgalleryViewAdmirorgallery extends JViewLegacy
{
	/**
	 * parameters
	 *
	 * @var mixed
	 */
	public $parameters;
	/**
	 * display
	 *
	 * @param   mixed $tpl Template to load
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function display($tpl = null): void
	{
		// Check if plugin is installed, otherwise don't show view
		if (!is_dir(JPATH_SITE . '/plugins/content/admirorgallery/'))
		{
			return;
		}

		$this->loadConfiguration();

		JToolBarHelper::title(JText::_('COM_ADMIRORGALLERY_CONTROL_PANEL'), 'controlpanel');
		parent::display($tpl);
	}

	/**
	 * loadConfiguration
	 *
	 * @return  void
	 */
	private function loadConfiguration()
	{
		$db = JFactory::getDBO();
		$query = "SELECT * FROM #__extensions WHERE (element = 'admirorgallery') AND (type = 'plugin')";
		$db->setQuery($query);
		$row = $db->loadAssoc();

		$paramsdefs = JPATH_COMPONENT_ADMINISTRATOR .
						DIRECTORY_SEPARATOR . 'views' .
						DIRECTORY_SEPARATOR . 'button' .
						DIRECTORY_SEPARATOR . 'tmpl' .
						DIRECTORY_SEPARATOR . 'default.xml';
		$this->parameters = JForm::getInstance('AG_Settings', $paramsdefs);

		$values = array('params' => json_decode($row['params']));
		$this->parameters->bind($values);
	}

}
