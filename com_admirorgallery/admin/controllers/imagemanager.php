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

use Joomla\CMS\Filesystem\File as JFile;

/**
 * AdmirorgalleryControllerImagemanager
 *
 * @since 1.0.0
 */
class AdmirorgalleryControllerImagemanager extends AdmirorgalleryController
{
	/**
	 * model
	 *
	 * @var   ?JModelLegacy
	 */
	private ?JModelLegacy $model;

	/**
	 * __construct
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask('agApply', 'agApply');
		$this->registerTask('agReset', 'agReset');
	}

	/**
	 * agApply
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function agApply(): void
	{

		$model = $this->getModel('imagemanager');

		$itemURL = $this->input->getPath('itemURL');

		if (is_dir(JPATH_SITE . $itemURL))
		{
			// FOLDER MODELS
			// BOOKMARK REMOVE
			$cbBookmarkRemove = $this->input->getVar('cbBookmarkRemove');

			if (!empty($cbBookmarkRemove))
			{
				$model->_bookmarkRemove($cbBookmarkRemove);
			}

			// PRIORITY
			$cbPriority = $this->input->getVar('cbPriority');

			if (!empty($cbPriority))
			{
				$model->_cbox_priority($cbPriority);
			}

			// UPLOAD
			$file = $this->input->getVar('AG_fileUpload', null, 'files');

			if (isset($file) && !empty($file['name']))
			{
						$model->_fileUpload($itemURL, $file);
			}

			// ADD FOLDERS
			$addFolders = $this->input->getVar('addFolders');

			if (!empty($addFolders))
			{
						$model->_addFolders($itemURL, $addFolders);
			}

			// REMOVE // BOOKMARK ADD
			$cbSelectItem = $this->input->getVar('cbSelectItem');
			$operationsTargetFolder = $this->input->getVar('operationsTargetFolder');

			if (!empty($cbSelectItem))
			{
				switch ($this->input->getVar('AG_operations'))
				{
					case "move":
								$model->_move($cbSelectItem, $operationsTargetFolder);
			break;
					case "copy":
						$model->_copy($cbSelectItem, $operationsTargetFolder);
			break;
					case "bookmark":
						$model->_bookmarkAdd($cbSelectItem);
						break;
					case "delete":
						$model->_remove($cbSelectItem);
						break;
					case "hide":
						$model->_set_visible($cbSelectItem, $itemURL, "hide");
						break;
					case "show":
						$model->_set_visible($cbSelectItem, $itemURL, "show");
						break;
				}
			}

			// RENAME
			$rename = $this->input->getVar('rename');
			$webSafe = array("/", " ", ":", ".", "+", "&");

			if (!empty($rename))
			{
				foreach ($rename as $renameKey => $renameValue)
				{
					$originalName = JFile::stripExt(basename($renameKey));

					// CREATE WEBSAFE TITLES
					foreach ($webSafe as $key => $value)
					{
								$newName = str_replace($value, "-", $renameValue);
					}

					if ($originalName != $newName && !empty($renameValue))
					{
						$model->_rename($itemURL, $renameKey, $newName);
					}
				}
			}

			// FOLDER DESCRIPTIONS
			$descContent = $this->input->getVar('descContent', '', 'POST', 'ARRAY', 'JREQUEST_ALLOWHTML');
			$descTags = $this->input->getVar('descTags');
			$folderThumb = $this->input->getVar('folderThumb');

			if ($this->input->getVar('AG_folderSettings_status') == "edit")
			{
				$model->_folder_desc_content($itemURL, $descContent, $descTags, $folderThumb);
			}
		}
		else
		{
			// FILE MODELS
			// FILE DESCRIPTIONS
			$descContent = $this->input->getVar('descContent', '', 'POST', 'ARRAY', 'JREQUEST_ALLOWHTML');
			$descTags = $this->input->getVar('descTags');

			if (!empty($descContent))
			{
				$model->_desc_content($itemURL, $descContent, $descTags);
			}
		}

		parent::display();
	}

	/**
	 * agReset
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function agReset(): void
	{
		parent::display();
	}

}
