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

use Joomla\CMS\MVC\Model\BaseDatabaseModel as JModelLegacy;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Filesystem\File as JFile;
use Joomla\CMS\Filesystem\Folder as JFolder;
use Joomla\Archive\Archive as JArchive;

JLoader::register('SecureImage', dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . "scripts" . DIRECTORY_SEPARATOR . "secureimage.php");

/**
 * AdmirorgalleryModelImagemanager
 *
 * @since 1.0.0
 */
class AdmirorgalleryModelImagemanager extends JModelLegacy
{
	/**
	 * webSafe
	 *
	 * @var array
	 *
	 * @since 1.0.0
	 */
	public $webSafe = array("/", " ", ":", ".", "+", "&");

	/**
	 * bookmarkPath
	 *
	 * @var mixed
	 *
	 * @since 1.0.0
	 */
	public $bookmarkPath;

	/**
	 * __construct
	 *
	 * @param   mixed $config Configuration
	 *
	 * @since 1.0.0
	 */
	public function __construct(array $config = array())
	{
		$this->bookmarkPath = JPATH_SITE . '/administrator/components/com_admirorgallery/assets/bookmarks.xml';
		parent::__construct($config);
	}

	/**
	 * saveBookmark
	 *
	 * @param   SimpleXMLElement $simpleXmlObject 	Loaded XML object
	 * @param   string 			 $value				XML path
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	private function saveBookmark(SimpleXMLElement $simpleXmlObject, string $value): void
	{
		if ($simpleXmlObject->asXML($this->bookmarkPath))
		{
			JFactory::getApplication()->enqueueMessage(JText::_("AG_BOOKMARK_CHANGES_SAVED") . "&nbsp;" . $value, 'message');
		}
		else
		{
			JFactory::getApplication()->enqueueMessage(JText::_("AG_CANNOT_WRITE_GALLERY_LISTING") . "&nbsp;" . $value, 'error');
		}
	}

	/**
	 * saveXml
	 *
	 * @param   string $itemURL      Item URL
	 * @param   string $xmlFilePath  XML file path
	 * @param   string $xmlContent   XML content to save
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	private function saveXml(string $itemURL, string $xmlFilePath, string $xmlContent): void
	{
		if (!empty($xmlContent))
		{
			$handle = fopen($xmlFilePath, "w") or die(JText::_("AG_CANNOT_WRITE_DESCRIPTION_FILE"));

			if (fwrite($handle, $xmlContent))
			{
				JFactory::getApplication()->enqueueMessage(JText::_("AG_DESCRIPTION_FILE_CREATED") . "&nbsp;" . basename($itemURL), 'message');
			}
			else
			{
				JFactory::getApplication()->enqueueMessage(JText::_("AG_CANNOT_WRITE_DESCRIPTION_FILE") . "&nbsp;" . basename($itemURL), 'error');
			}

			fclose($handle);
		}
	}

	/**
	 * renameBookmark
	 *
	 * @param   string $originalPath  Original XML file name/path
	 * @param   string $newPath       New XML file name/path
	 * @param   string $bookmarksPath Bookmarks path
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	private function renameBookmark(string $originalPath, string $newPath, string $bookmarksPath): void
	{
		$bookmarks = simplexml_load_file($bookmarksPath);

		// CHECK IF BOOKMARK ALREADY EXISTS
		if (isset($bookmarks->bookmark))
		{
			foreach ((array) $bookmarks->bookmark as $bookmark)
			{
				if ($bookmark == $originalPath)
				{
					$bookmark = $newPath;
					$this->saveBookmark($bookmarks, $newPath);
					break;
				}
			}
		}
	}

	/**
	 * removeBookmark
	 *
	 * @param   array  $bookmarksToRemove List of bookmarks to be removed
	 * @param   string $bookmarksPath     Bookmark path
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function removeBookmark(array $bookmarksToRemove,  string $bookmarksPath): void
	{
		foreach ($bookmarksToRemove as $bookmark)
		{
			$bookmarks = simplexml_load_file($bookmarkPath);

			if (isset($bookmarks->bookmark))
			{
				for ($i = 0; $i < count($bookmarks->bookmark); $i++)
				{
					if ($bookmarks->bookmark[$i] == $bookmark)
					{
						unset($bookmarks->bookmark[$i]);
					}
				}
			}

			if ($bookmarks->asXML($this->bookmarkPath))
			{
				JFactory::getApplication()->enqueueMessage(JText::_("AG_GALLERY_REMOVED_FROM_LISTING") . "&nbsp;" . $bookmark, 'message');
			}
			else
			{
				JFactory::getApplication()->enqueueMessage(JText::_("AG_CANNOT_WRITE_GALLERY_LISTING") . "&nbsp;" . $bookmark, 'error');
			}
		}
	}

	/**
	 * bookmarkAdd
	 *
	 * @param   array  $bookmarksToAdd Bookmarks to add
	 * @param   string $bookmarksPath  Bookmarks path
	 *
	 * @return boolean
	 *
	 * @since 1.0.0
	 */
	public function bookmarkAdd(array $bookmarksToAdd, string $bookmarksPath): bool
	{
		foreach ($bookmarksToAdd as $key => $value)
		{
			if (is_dir(JPATH_SITE . $value))
			{
				$bookmarks = simplexml_load_file($bookmarksPath);
				$bookmarkExists = false;

				if (isset($bookmarks->bookmark))
				{
					for ($i = 0; $i < count($bookmarks->bookmark); $i++)
					{
						if ($bookmarks->bookmark[$i] == $value)
						{
							$bookmarkExists = true;
						}
					}
				}

				if (!$bookmarkExists)
				{
					// Add a new bookmark
					$bookmarks->addChild('bookmark', $value);
					JFactory::getApplication()->enqueueMessage(JText::_("AG_GALLERY_ADDED") . "&nbsp;" . $value, 'message');
				}
				else
				{
					JFactory::getApplication()->enqueueMessage(JText::_("AG_GALLERY_ALREADY_EXISTS") . "&nbsp;" . $value, 'notice');

					return true;
				}
			}
			else
			{
				JFactory::getApplication()->enqueueMessage(JText::_("AG_GALLERY_NOT_A_FOLDER") . "&nbsp;" . $value, 'error');
			}
		}

		$this->saveBookmark($bookmarks, '');

		return true;
	}

	/**
	 * updatePriority
	 *
	 * @param   array $checkedValues All checked items
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function updatePriority(array $checkedValues): void
	{

		foreach ($checkedValues as $key => $value)
		{
			$itemURL = $key;
			$priority = $value;
			$folderName = dirname($itemURL);

			if (is_numeric($priority))
			{
				// Set Possible Description File Absolute Path // Instant patch for upper and lower case...
				$pathWithStripExt = JPATH_SITE . $folderName . '/' . JFile::stripExt(basename($itemURL));
				$xmlFilePath = $pathWithStripExt . ".xml";

				if (JFile::exists($pathWithStripExt . ".XML"))
				{
					$xmlFilePath = $pathWithStripExt . ".XML";
				}

				$priorityNew = '<priority>' . $priority . '</priority>';

				$xmlPriority = "";

				if (file_exists($xmlFilePath))
				{
					$xmlObject = simplexml_load_file($xmlFilePath);
					$xmlPriority = $xmlObject->priority;
				}

				if ($xmlPriority != $priority)
				{
					if (file_exists($xmlFilePath))
					{
						$file = fopen($xmlFilePath, "r");
						$xmlContent = "";

						while (!feof($file))
						{
							$xmlContent .= fgetc($file);
						}

						fclose($file);
						$xmlContent = preg_replace("#<priority[^}]*>(.*?)</priority>#s", $priorityNew, $xmlContent);
					}
					else
					{
						$xmlContent = '<?xml version="1.0" encoding="utf-8"?>' .
						"\n" . '<image>' .
						"\n" . '<visible>true</visible>' .
						"\n" . $priorityNew .
						"\n" . '<thumb></thumb>' .
						"\n" . '<captions>' .
						"\n" . '</captions>' .
						"\n" . '</image>';
					}

					// Save XML
					$this->saveXml($itemURL, $xmlFilePath, $xmlContent);
				}
			}
			else
			{
				if (!empty($priority))
				{
					JFactory::getApplication()->
						enqueueMessage(JText::_("AG_PRIORITY_MUST_BE_NUMERIC_VALUE_FOR_IMAGE") . "&nbsp;" . basename($itemURL), 'error');
				}
			}
		}
	}

	/**
	 * setVisible
	 *
	 * @param   array  $selectItems Selected items
	 * @param   string $folderName  Folder name
	 * @param   string $visible     Visible show|hide
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function setVisible(array $selectItems, string $folderName, string $visible): void
	{
		foreach ($selectItems as $key => $value)
		{
			$itemURL = $value;

			// Set Possible Description File Absolute Path // Instant patch for upper and lower case...
			$pathWithStripExt = JPATH_SITE . $folderName . JFile::stripExt(basename($itemURL));
			$xmlFilePath = $pathWithStripExt . ".xml";

			if (JFile::exists($pathWithStripExt . ".XML"))
			{
				$xmlFilePath = $pathWithStripExt . ".XML";
			}

			// Set new visible tag
			if ($visible == "show")
			{
				$visibleNew = "<visible>true</visible>";
			}
			else
			{
				$visibleNew = "<visible>false</visible>";
			}

			$xmlContent = '';

			if (file_exists($xmlFilePath))
			{
				$file = fopen($xmlFilePath, "r");

				while (!feof($file))
				{
					$xmlContent .= fgetc($file);
				}

				fclose($file);

				if (preg_match("#<visible[^}]*>(.*?)</visible>#s", $xmlContent))
				{
					$xmlContent = preg_replace("#<visible[^}]*>(.*?)</visible>#s", $visibleNew, $xmlContent);
				}
				else
				{
					$xmlContent = preg_replace("#</image>#s", $visibleNew . "\n </image>", $xmlContent);
				}
			}
			else
			{
				$xmlContent = '<?xml version="1.0" encoding="utf-8"?>' .
								"\n" . '<image>' . "\n" . $visibleNew . "\n" .
								'<priority></priority>' . "\n" . '<thumb></thumb>' .
								"\n" . '<captions></captions>' . "\n" . '</image>';
			}

			// Save XML
			$this->saveXml($itemURL, $xmlFilePath, $xmlContent);
		}
	}

	/**
	 * uploadFile
	 *
	 * @param   string $itemURL Item URL
	 * @param   mixed  $file    File to upload
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function uploadFile(string $itemURL, mixed $file): void
	{
		$config = JFactory::getConfig();
		$tempDestination = $config->get('tmp_path');
		$validExtensions = array("jpg", "jpeg", "gif", "png", "zip");

		// Clean up filename to get rid of strange characters like spaces etc
		$filename = JFile::makeSafe($file['name']);
		$fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

		$src = $file['tmp_name'];
		$dest = $tempDestination . DIRECTORY_SEPARATOR . $filename;

		// FILTER EXTENSION
		$extentionCheck = array_search($fileExtension, $validExtensions);

		if (is_numeric($extentionCheck))
		{
			if (JFile::upload($src, $dest))
			{
				if ($fileExtension == "zip")
				{
					if (JArchive::extract($tempDestination . DIRECTORY_SEPARATOR .
											$filename, $tempDestination . DIRECTORY_SEPARATOR . 'AdmirorImageUpload' .
											DIRECTORY_SEPARATOR . JFile::stripExt($filename)
					)
					)
					{
						$files = JFolder::files($tempDestination . DIRECTORY_SEPARATOR . 'AdmirorImageUpload' .
													DIRECTORY_SEPARATOR . JFile::stripExt($filename), '.', true, true
						);

						foreach ($files as $filePath)
						{
							$image = new SecureImage($filePath);

							if (!$image->CheckIt())
							{
								JFile::delete($filePath);
							}
						}

						JFolder::copy($tempDestination . DIRECTORY_SEPARATOR . 'AdmirorImageUpload' .
										DIRECTORY_SEPARATOR . JFile::stripExt($filename), JPATH_SITE . $itemURL . JFile::stripExt($filename), '', true
						);
						JFile::delete($tempDestination . DIRECTORY_SEPARATOR . $filename);
						JFolder::delete($tempDestination . DIRECTORY_SEPARATOR . 'AdmirorImageUpload' .
										DIRECTORY_SEPARATOR . JFile::stripExt($filename)
						);
						JFactory::getApplication()->
							enqueueMessage(JText::_('AG_ZIP_PACKAGE_IS_UPLOADED_AND_EXTRACTED') . "&nbsp;" . $filename, 'message');
					}
				}
				else
				{
					if (JFile::copy($tempDestination . DIRECTORY_SEPARATOR . $filename, JPATH_SITE . $itemURL . $filename))
					{
						JFile::delete($tempDestination . DIRECTORY_SEPARATOR . $filename);
						JFactory::getApplication()->enqueueMessage(JText::_('AG_IMAGE_IS_UPLOADED') . "&nbsp;" . $filename, 'message');
					}
				}
			}
			else
			{
				$errorMsg[] = array();
				JFactory::getApplication()->enqueueMessage(JText::_('AG_CANNOT_UPLOAD_FILE') . "&nbsp;" . $filename, 'error');
			}
		}
		else
		{
			JFactory::getApplication()->enqueueMessage(JText::_("AG_ONLY_JPG_JPEG_GIF_PNG_AND_ZIP_ARE_VALID_EXTENSIONS"), 'error');
		}
	}

	/**
	 * addFolders
	 *
	 * @param   string $itemURL    Item URL
	 * @param   mixed  $addFolders Folder to be added
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function addFolders(string $itemURL, mixed $addFolders): void
	{
		foreach ($addFolders as $key => $value)
		{
			if (!empty($value))
			{
				$newFolderName = $value;

				// CREATE WEBSAFE TITLES
				if (!empty($this->webSafe))
				{
					foreach ($this->webSafe as $webSafekey => $webSafevalue)
					{
						$newFolderName = str_replace($webSafevalue, "-", $newFolderName);
					}
				}

				$newFolderName = htmlspecialchars(strip_tags($newFolderName));

				if (!file_exists(JPATH_SITE . $itemURL . $newFolderName))
				{
					if (JFolder::create(JPATH_SITE . $itemURL . $newFolderName))
					{
						JFactory::getApplication()->enqueueMessage(JText::_("AG_FOLDER_CREATED") . "&nbsp;" . $newFolderName, 'message');
					}
					else
					{
						JFactory::getApplication()->enqueueMessage(JText::_("AG_CANNOT_CREATE_FOLDER") . "&nbsp;" . $newFolderName, 'error');
					}
				}
				else
				{
					JFactory::getApplication()->enqueueMessage(JText::_("AG_FOLDER_ALREADY_EXISTS") . "&nbsp;" . $newFolderName, 'error');
				}
			}//if(!empty($value))
		}
	}

	/**
	 * copyItems
	 *
	 * @param   array  $selectItems				Selected items
	 * @param   string $operationsTargetFolder  Target folder
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function copyItems(array $selectItems, string $operationsTargetFolder): void
	{
		foreach ($selectItems as $key => $value)
		{
			// Set Possible Description File Absolute Path // Instant patch for upper and lower case...
			$folderName = dirname($value);
			$pathWithStripExt = JPATH_SITE . $folderName . '/' . JFile::stripExt(basename($value));
			$xmlFilePath = $pathWithStripExt . ".XML";

			if (JFile::exists($pathWithStripExt . ".xml"))
			{
				$xmlFilePath = $pathWithStripExt . ".xml";
			}

			if (is_dir(JPATH_SITE . DIRECTORY_SEPARATOR . $value))
			{
				if (JFolder::copy(JPATH_SITE . DIRECTORY_SEPARATOR . $value,
					JPATH_SITE . DIRECTORY_SEPARATOR . $operationsTargetFolder . DIRECTORY_SEPARATOR . basename($value)
				)
				)
				{
					if (JFile::exists($xmlFilePath))
					{
						JFile::copy($xmlFilePath, JPATH_SITE . DIRECTORY_SEPARATOR .
							$operationsTargetFolder . DIRECTORY_SEPARATOR . basename($xmlFilePath)
						);
					}

					JFactory::getApplication()->enqueueMessage(JText::_('AG_ITEM_COPIED') . "&nbsp;" . $value, 'message');
				}
				else
				{
					JFactory::getApplication()->enqueueMessage(JText::_('AG_CANNOT_COPY_ITEM') . "&nbsp;" . $value, 'error');
				}
			}
			else
			{
				if (JFile::copy(JPATH_SITE . DIRECTORY_SEPARATOR . $value, JPATH_SITE .
					DIRECTORY_SEPARATOR . $operationsTargetFolder . DIRECTORY_SEPARATOR . basename($value)
				)
				)
				{
					if (JFile::exists($xmlFilePath))
					{
						JFile::copy($xmlFilePath, JPATH_SITE . DIRECTORY_SEPARATOR .
							$operationsTargetFolder . DIRECTORY_SEPARATOR . basename($xmlFilePath)
						);
					}

					JFactory::getApplication()->enqueueMessage(JText::_('AG_ITEM_COPIED') . "&nbsp;" . $value, 'message');
				}
				else
				{
					JFactory::getApplication()->enqueueMessage(JText::_('AG_CANNOT_COPY_ITEM') . "&nbsp;" . $value, 'error');
				}
			}
		}
	}

	/**
	 * moveItems
	 *
	 * @param   array  $selectItems				Selected items
	 * @param   string $operationsTargetFolder  Target folder
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function moveItems(array $selectItems, string $operationsTargetFolder): void
	{
		foreach ($selectItems as $key => $value)
		{
			// Set Possible Description File Absolute Path // Instant patch for upper and lower case...
			$folderName = dirname($value);
			$pathWithStripExt = JPATH_SITE . $folderName . '/' . JFile::stripExt(basename($value));
			$xmlFilePath = $pathWithStripExt . ".XML";

			if (JFile::exists($pathWithStripExt . ".xml"))
			{
				$xmlFilePath = $pathWithStripExt . ".xml";
			}

			if (is_dir(JPATH_SITE . DIRECTORY_SEPARATOR . $value))
			{
				if (JFolder::move(JPATH_SITE . DIRECTORY_SEPARATOR . $value, JPATH_SITE .
					DIRECTORY_SEPARATOR . $operationsTargetFolder . DIRECTORY_SEPARATOR . basename($value)
				)
				)
				{
					$this->removeBookmark(array($value));

					if (JFile::exists($xmlFilePath))
					{
						JFile::move($xmlFilePath, JPATH_SITE . DIRECTORY_SEPARATOR .
							$operationsTargetFolder . DIRECTORY_SEPARATOR . basename($xmlFilePath)
						);
					}

					JFactory::getApplication()->enqueueMessage(JText::_('AG_ITEM_MOVED') . "&nbsp;" . $value, 'message');
				}
				else
				{
					JFactory::getApplication()->enqueueMessage(JText::_('AG_CANNOT_MOVED_ITEM') . "&nbsp;" . $value, 'error');
				}
			}
			else
			{
				if (JFile::move(JPATH_SITE . DIRECTORY_SEPARATOR . $value,
					JPATH_SITE . DIRECTORY_SEPARATOR . $operationsTargetFolder . DIRECTORY_SEPARATOR . basename($value)
				)
				)
				{
					if (JFile::exists($xmlFilePath))
					{
						JFile::move($xmlFilePath, JPATH_SITE . DIRECTORY_SEPARATOR .
							$operationsTargetFolder . DIRECTORY_SEPARATOR . basename($xmlFilePath)
						);
					}

					JFactory::getApplication()->enqueueMessage(JText::_('AG_ITEM_MOVED') . "&nbsp;" . $value, 'message');
				}
				else
				{
					JFactory::getApplication()->enqueueMessage(JText::_('AG_CANNOT_MOVED_ITEM') . "&nbsp;" . $value, 'error');
				}
			}
		}
	}

	/**
	 * removeItems
	 *
	 * @param   array  $selectItems				Selected items
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function removeItems(array $selectItems): void
	{
		foreach ($selectItems as $key => $value)
		{
			$folderName = dirname($value);

			// Set Possible Description File Absolute Path // Instant patch for upper and lower case...
			$pathWithStripExt = JPATH_SITE . $folderName . '/' . JFile::stripExt(basename($value));
			$xmlFilePath = $pathWithStripExt . ".XML";

			if (JFile::exists($pathWithStripExt . ".xml"))
			{
				$xmlFilePath = $pathWithStripExt . ".xml";
			}

			// DELETE
			if (is_dir(JPATH_SITE . DIRECTORY_SEPARATOR . $value))
			{
				if (JFolder::delete(JPATH_SITE . DIRECTORY_SEPARATOR . $value))
				{
					$this->removeBookmark(array($value));

					if (file_exists($xmlFilePath))
					{
						JFile::delete($xmlFilePath);
					}

					JFactory::getApplication()->enqueueMessage(JText::_('AG_ITEM_DELETED') . "&nbsp;" . $value, 'message');
				}
				else
				{
					JFactory::getApplication()->enqueueMessage(JText::_('AG_CANNOT_DELETE_ITEM') . "&nbsp;" . $value, 'error');
				}
			}
			else
			{
				if (JFile::delete(JPATH_SITE . DIRECTORY_SEPARATOR . $value))
				{
					if (file_exists($xmlFilePath))
					{
						JFile::delete($xmlFilePath);
					}

					JFactory::getApplication()->enqueueMessage(JText::_('AG_ITEM_DELETED') . "&nbsp;" . $value, 'message');
				}
				else
				{
					JFactory::getApplication()->enqueueMessage(JText::_('AG_CANNOT_DELETE_ITEM') . "&nbsp;" . $value, 'error');
				}
			}
		}
	}

	/**
	 * renameItem
	 *
	 * @param   string $itemURL      Item URL
	 * @param   string $originalPath Original file path
	 * @param   string $newName      New name
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function renameItem(string $itemURL, string $originalPath, string $newName): void
	{

		$originalName = basename($originalPath);
		$folderName = dirname($originalPath);

		// CREATE WEBSAFE TITLES
		if (!empty($this->webSafe))
		{
			foreach ($this->webSafe as $webSafekey => $webSafevalue)
			{
				$newName = str_replace($webSafevalue, "-", $newName);
			}
		}

		// Set Possible Description File Absolute Path // Instant patch for upper and lower case...
		$pathWithStripExt = JPATH_SITE . $folderName . DIRECTORY_SEPARATOR . JFile::stripExt($originalName);
		$xmlFilePath = $pathWithStripExt . ".XML";

		if (JFile::exists($pathWithStripExt . ".xml"))
		{
			$xmlFilePath = $pathWithStripExt . ".xml";
		}

		if (!is_dir(JPATH_SITE . $originalPath))
		{
			$fileExtension = JFile::getExt($originalName);
			$newFileName = $folderName . DIRECTORY_SEPARATOR . $newName . '.' . $fileExtension;

			if (!file_exists(JPATH_SITE . $newFileName))
			{
				if (rename(JPATH_SITE . $originalPath, JPATH_SITE . $newFileName))
				{
					if (file_exists($xmlFilePath))
					{
						rename($xmlFilePath, JPATH_SITE . $folderName . DIRECTORY_SEPARATOR . $newName . '.xml');
					}

					JFactory::getApplication()->enqueueMessage(JText::_("AG_IMAGE_RENAMED") . "&nbsp;" . $originalName, 'message');
				}
				else
				{
					JFactory::getApplication()->enqueueMessage(JText::_("AG_CANNOT_RENAME_IMAGE") . "&nbsp;" . $originalName, 'error');
				}
			}
			else
			{
				JFactory::getApplication()->enqueueMessage(JText::_("AG_FOLDER_WITH_THE_SAME_NAME_ALREADY_EXISTS"), 'error');
			}
		}
		else
		{
			if (!file_exists(JPATH_SITE . $folderName . DIRECTORY_SEPARATOR . $newName))
			{
				if (rename(JPATH_SITE . $originalPath, JPATH_SITE . $folderName . DIRECTORY_SEPARATOR . $newName))
				{
					$this->renameBookmark(
						$originalPath . DIRECTORY_SEPARATOR,
						$folderName . DIRECTORY_SEPARATOR . $newName . DIRECTORY_SEPARATOR,
						$this->bookmarkPath
					);

					if (file_exists($xmlFilePath))
					{
						rename($xmlFilePath, JPATH_SITE . $folderName . DIRECTORY_SEPARATOR . $newName . '.xml');
					}

					JFactory::getApplication()->enqueueMessage(JText::_("AG_FOLDER_RENAMED") . "&nbsp;" . $originalName, 'message');
				}
				else
				{
					JFactory::getApplication()->enqueueMessage(JText::_("AG_CANNOT_RENAME_FOLDER") . "&nbsp;" . $originalName, 'error');
				}
			}
			else
			{
				JFactory::getApplication()->enqueueMessage(JText::_("AG_FOLDER_WITH_THE_SAME_NAME_ALREADY_EXISTS"), 'error');
			}
		}
	}

	/**
	 * It creates caption tags with its content.
	 * After that it checks if XML already exists.
	 * If it does exist replaces captions, if not it creates a new XML
	 *
	 * @param   string $itemURL     Item url
	 * @param   string $descContent Description content
	 * @param   string $descTags    Description tag
	 * @param   string $folderThumb Fodler thumb path
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function setFolderDescription(string $itemURL, string $descContent, string $descTags, string $folderThumb)
	{
		$folderName = dirname($itemURL);

		// Set Possible Description File Absolute Path // Instant patch for upper and lower case...
		$pathWithStripExt = JPATH_SITE . $folderName . DIRECTORY_SEPARATOR . JFile::stripExt(basename($itemURL));
		$xmlFilePath = $pathWithStripExt . ".xml";

		if (JFile::exists($pathWithStripExt . ".XML"))
		{
			$xmlFilePath = $pathWithStripExt . ".XML";
		}

		// Set new Captions tag
		$newCaptions = "<captions>\n";

		if (!empty($descContent))
		{
			foreach ($descContent as $key => $value)
			{
				if (!empty($value))
				{
					$newCaptions .= "\t" .
						'<caption lang="' .
						strtolower($descTags[$key]) .
						'">' .
						htmlspecialchars($value, ENT_QUOTES) .
						'</caption>' .
						"\n";
				}
			}
		}

		$newCaptions .= "</captions>";

		// Set new Thumb tag
		$newThumb = "<thumb>" . $folderThumb . "</thumb>";

		$xmlContent = "";

		if (file_exists($xmlFilePath))
		{
			$file = fopen($xmlFilePath, "r");

			while (!feof($file))
			{
				$xmlContent .= fgetc($file);
			}

			fclose($file);

			if (preg_match("#<thumb[^}]*>(.*?)</thumb>#s", $xmlContent))
			{
				$xmlContent = preg_replace("#<thumb[^}]*>(.*?)</thumb>#s", $newThumb, $xmlContent);
			}
			else
			{
						$xmlContent = preg_replace("#</image>#s", $newThumb . "\n </image>", $xmlContent);
			}

			if (preg_match("#<captions[^}]*>(.*?)</captions>#s", $xmlContent))
			{
						$xmlContent = preg_replace("#<captions[^}]*>(.*?)</captions>#s", $newCaptions, $xmlContent);
			}
			else
			{
						$xmlContent = preg_replace("#</image>#s", $newCaptions . "\n </image>", $xmlContent);
			}
		}
		else
		{
			$xmlContent = '<?xml version="1.0" encoding="utf-8"?>' .
				"\n" .
				'<image>' .
				"\n" .
				'<visible>true</visible>' .
				"\n" .
				'<priority></priority>' .
				"\n" .
				'<thumb>' .
				$folderThumb .
				'</thumb>' .
				"\n" .
				$newCaptions .
				"\n" .
				'</image>';
		}

		// Save XML
		$this->saveXml($itemURL, $xmlFilePath, $xmlContent);
	}

	/**
	 * setDescriptionContent
	 *
	 * @param   mixed $itemURL     Item url
	 * @param   mixed $descContent Description content
	 * @param   mixed $descTags    Description tag
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function setDescriptionContent(string $itemURL, array $descContent, array $descTags): void
	{
		$folderName = dirname($itemURL);

		// Set Possible Description File Absolute Path // Instant patch for upper and lower case...
		$pathWithStripExt = JPATH_SITE . $folderName . DIRECTORY_SEPARATOR . JFile::stripExt(basename($itemURL));
		$xmlFilePath = $pathWithStripExt . ".xml";

		if (JFile::exists($pathWithStripExt . ".XML"))
		{
			$xmlFilePath = $pathWithStripExt . ".XML";
		}

		$newCaptions = "<captions> \n";

		if (!empty($descContent))
		{
			foreach ($descContent as $key => $value)
			{
				if (!empty($value))
				{
					$newCaptions .= "\t" . '<caption lang="' .
					strtolower($descTags[$key]) . '">' .
					htmlspecialchars($value, ENT_QUOTES) . '</caption>' . "\n";
				}
			}
		}

		$newCaptions .= "</captions>";

		$xmlContent = "";

		if (file_exists($xmlFilePath))
		{
			$file = fopen($xmlFilePath, "r");

			while (!feof($file))
			{
				$xmlContent .= fgetc($file);
			}

			fclose($file);
			$xmlContent = preg_replace("#<captions[^}]*>(.*?)</captions>#s", $newCaptions, $xmlContent);
		}
		else
		{
			$xmlContent = '<?xml version="1.0" encoding="utf-8"?>' .
			"\n" . '<image>' . "\n" . '<visible>true</visible>' . "\n" .
			'<priority></priority>' . "\n" . $newCaptions . "\n" . '</image>';
		}

		// Save XML
		$this->saveXml($itemURL, $xmlFilePath, $xmlContent);
	}

}

