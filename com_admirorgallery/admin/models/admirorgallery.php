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
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\MVC\Model\BaseDatabaseModel as JModelLegacy;

/**
 * AdmirorgalleryModelAdmirorgallery
 *
 * @since 1.0.0
 */
class AdmirorgalleryModelAdmirorgallery extends JModelLegacy
{
	/**
	 * update
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function update(): void
	{
		$dbInput = '{';

		foreach ($_POST['params'] as $key => $value)
		{
			$dbInput .= '"' . $key . '":"' . $value . '",';
		}

		$dbInput = substr_replace($dbInput, '}', -1, 1);

		$db = JFactory::getDBO();
		$query = "UPDATE #__extensions SET params='" . $dbInput . "' WHERE (element = 'admirorgallery') AND (type = 'plugin')";
		$db->setQuery($query);

		if ($db->execute())
		{
			JFactory::getApplication()->enqueueMessage(JText::_("AG_PARAMS_UPDATED"), 'message');
		}
		else
		{
			JFactory::getApplication()->enqueueMessage(JText::_("AG_CANNOT_ACCESS_TO_DATABASE"), 'error');
		}
	}

}
