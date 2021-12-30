<?php

/**
 * @version     6.0.0
 * @package     Admiror.Site
 * @subpackage  com_admirorgallery
 * @author      Igor Kekeljevic <igor@admiror.com>
 * @author      Nikola Vasiljevski <nikola83@gmail.com>
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Toolbar\Toolbar as JToolBar;


/**
 * AdmirorgalleryHelperToolbar
 *
 * @since 1.0.0
 */
class AdmirorgalleryHelperToolbar extends JToolBar
{
	/**
	 * getToolbar
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function getToolbar(): string
	{
		$bar = new JToolBar('toolbar');

		$bar->appendButton('Standard', 'unpublish', 'COM_ADMIRORGALLERY_RESET_DESC', 'agReset', false);
		$bar->appendButton('Standard', 'publish', 'COM_ADMIRORGALLERY_APPLY_DESC', 'agApply', false);

		return $bar->render();
	}
}
