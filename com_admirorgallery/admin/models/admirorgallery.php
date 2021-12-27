<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\MVC\Model\BaseDatabaseModel as JModelLegacy;

class AdmirorgalleryModelAdmirorgallery extends JModelLegacy
{
    function _update()
    {
        $AG_DB_input = '{';

        foreach ($_POST['params'] as $key => $value) {
            $AG_DB_input .= '"' . $key . '":"' . $value . '",';
        }
        $AG_DB_input = substr_replace($AG_DB_input, '}', -1, 1);

        $db = JFactory::getDBO();
        $query = "UPDATE #__extensions SET params='" . $AG_DB_input . "' WHERE (element = 'admirorgallery') AND (type = 'plugin')";
        $db->setQuery($query);
        if ($db->execute()) {
            JFactory::getApplication()->enqueueMessage(JText::_("AG_PARAMS_UPDATED"), 'message');
        } else {
            JFactory::getApplication()->enqueueMessage(JText::_("AG_CANNOT_ACCESS_TO_DATABASE"), 'error');
        }
    }

}
