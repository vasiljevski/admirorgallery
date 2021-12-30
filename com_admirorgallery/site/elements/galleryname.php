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

use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('folderlist');

/**
 * JFormFieldGalleryName
 *
 * @since 1.0.0
 */
class JFormFieldGalleryName extends JFormFieldFolderList
{
	/**
	 * getOptions
	 *
	 * @return  array  The field option objects.
	 *
	 * @since 1.0.0
	 */
	public function getOptions()
	{
		$this->directory = (!empty($this->form->getValue("params")->rootFolder)) ? $this->form->getValue("params")->rootFolder : $this->directory;

		return parent::getOptions();
	}
}

