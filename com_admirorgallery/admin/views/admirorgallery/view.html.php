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

class AdmirorgalleryViewAdmirorgallery extends JViewLegacy
{

	function display($tpl = null)
	{
		JToolBarHelper::title(JText::_('COM_ADMIRORGALLERY_CONTROL_PANEL'), 'controlpanel');
		parent::display($tpl);
	}

}
