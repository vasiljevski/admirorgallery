<?php

/**
 * @version     6.0.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('folderlist');

class JFormFieldGalleryName extends JFormFieldFolderList
{
  public function getOptions()
	{
		$this->directory = (!empty($this->form->getValue("params")->rootFolder)) ? $this->form->getValue("params")->rootFolder : $this->directory;
		return parent::getOptions();
	}
}

