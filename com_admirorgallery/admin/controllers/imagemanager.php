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
	 * webSafe
	 *
	 * @var array
	 *
	 * @since 1.0.0
	 */
	public $webSafe = array("/", " ", ":", ".", "+", "&");
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

	public function getModel($name = 'Imagemanager', $prefix = 'AdmirorgalleryModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
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

		$model = $this->getModel();

		$itemURL = $this->input->getPath('itemURL');

		if (is_dir(JPATH_SITE . $itemURL))
		{
			// FOLDER MODELS
			// BOOKMARK REMOVE
			$bookmarksToRemove = $this->input->getVar('bookmarksToRemove');

			if (!empty($bookmarksToRemove))
			{
				$model->removeBookmark($bookmarksToRemove, $model->bookmarkPath);
			}

			// PRIORITY
			$cbPriority = $this->input->getVar('cbPriority');

			if (!empty($cbPriority))
			{
				$model->updatePriority($cbPriority);
			}

			// UPLOAD
			$file = $this->input->getVar('AG_fileUpload', null, 'files');

			if (isset($file) && !empty($file['name']))
			{
				$model->uploadFile($itemURL, $file);
			}

			// ADD FOLDERS
			$addFolders = $this->input->getVar('addFolders');

			if (!empty($addFolders))
			{
				$model->addFolders($itemURL, $addFolders);
			}

			// REMOVE // BOOKMARK ADD
			$selectItem = $this->input->getVar('selectItem');
			$operationsTargetFolder = $this->input->getVar('operationsTargetFolder');

			if (!empty($selectItem))
			{
				switch ($this->input->getVar('AG_operations'))
				{
					case "move":
						$model->moveItems($selectItem, $operationsTargetFolder);
						break;
					case "copy":
						$model->copyItems($selectItem, $operationsTargetFolder);
						break;
					case "bookmark":
						$model->bookmarkAdd($selectItem, $model->bookmarkPath);
						break;
					case "delete":
						$model->removeItems($selectItem);
						break;
					case "hide":
						$model->setVisible($selectItem, $itemURL, "hide");
						break;
					case "show":
						$model->setVisible($selectItem, $itemURL, "show");
						break;
				}
			}

			// RENAME
			$rename = $this->input->getVar('rename');

			if (!empty($rename))
			{
				foreach ($rename as $renameKey => $renameValue)
				{
					$originalName = JFile::stripExt(basename($renameKey));

					// CREATE WEBSAFE TITLES
					foreach ($this->webSafe as $key => $value)
					{
						$newName = str_replace($value, "-", $renameValue);
					}

					if ($originalName != $newName && !empty($renameValue))
					{
						$model->renameItem($itemURL, $renameKey, $newName);
					}
				}
			}

			// FOLDER DESCRIPTIONS
			$descContent = $this->input->getVar('descContent', '', 'POST', 'ARRAY', 'JREQUEST_ALLOWHTML');
			$descTags = $this->input->getVar('descTags');
			$folderThumb = $this->input->getVar('folderThumb');

			if ($this->input->getVar('AG_folderSettings_status') == "edit")
			{
				$model->setFolderDescription($itemURL, $descContent, $descTags, $folderThumb);
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
				$model->setDescriptionContent($itemURL, $descContent, $descTags);
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
