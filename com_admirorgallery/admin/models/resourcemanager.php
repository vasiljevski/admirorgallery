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

/**
 * AdmirorgalleryModelResourcemanager
 *
 * @since 1.0.0
 */
class AdmirorgalleryModelResourcemanager extends JModelLegacy
{
	/**
	 * installResource
	 *
	 * @param   string $file            Manifest file of the resource
	 * @param   string $resourceType    Current resource type (used to locate folder) popup|template
	 * @param   string $fileType        Supported file type : zip
	 * @param   string $tempDestination Temp folder location
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function installResource(string $file, string $resourceType, string $fileType, string $tempDestination): void
	{
		if (isset($file) && !empty($file['name']))
		{
			// Clean up filename to get rid of strange characters like spaces etc
			$filename = JFile::makeSafe($file['name']);
			$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

			$src = $file['tmp_name'];
			$dest = $tempDestination . DIRECTORY_SEPARATOR . $filename;

			// First check if the file has the right extension
			if ($ext == $fileType)
			{
				if (JFile::upload($src, $dest))
				{
					if (JArchive::extract($tempDestination . DIRECTORY_SEPARATOR . $filename, $tempDestination . DIRECTORY_SEPARATOR . $resourceType))
					{
						JFile::delete($tempDestination . DIRECTORY_SEPARATOR . $filename);
					}

					// TEMPLATE DETAILS PARSING
					if (JFile::exists(
						$tempDestination . DIRECTORY_SEPARATOR .
						$resourceType . DIRECTORY_SEPARATOR .
						JFile::stripExt($filename) . DIRECTORY_SEPARATOR . 'details.xml'
					)
					)
					{
						$resourceManagerXmlObject = simplexml_load_file(
							$tempDestination . DIRECTORY_SEPARATOR . $resourceType .
							DIRECTORY_SEPARATOR . JFile::stripExt($filename) . DIRECTORY_SEPARATOR . 'details.xml'
						);

						if (isset($resourceManagerXmlObject->type))
						{
							$resourceManagerType = $resourceManagerXmlObject->type;
						}
						else
						{
							JFolder::delete($tempDestination . DIRECTORY_SEPARATOR . $resourceType);
							JFactory::getApplication()->
								enqueueMessage(JText::_('AG_ZIP_PACKAGE_IS_NOT_VALID_RESOURCE_TYPE') . "&nbsp;" . $filename, 'error');

							return;
						}
					}
					else
					{
						JFolder::delete($tempDestination . DIRECTORY_SEPARATOR . $resourceType);
						JFactory::getApplication()->
							enqueueMessage(JText::_('AG_ZIP_PACKAGE_IS_NOT_VALID_RESOURCE_TYPE') . "&nbsp;" . $filename, 'error');

						return;
					}

					if (($resourceManagerType) && ($resourceManagerType == $resourceType))
					{
						$result = JFolder::move(
							$tempDestination . DIRECTORY_SEPARATOR . $resourceType .
							DIRECTORY_SEPARATOR . JFile::stripExt($filename), JPATH_SITE .
							DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'content' .
							DIRECTORY_SEPARATOR . 'admirorgallery' . DIRECTORY_SEPARATOR . 'admirorgallery' .
							DIRECTORY_SEPARATOR . $resourceType . DIRECTORY_SEPARATOR . JFile::stripExt($filename)
						);

						if ($result)
						{
							JFactory::getApplication()->enqueueMessage(JText::_('AG_ZIP_PACKAGE_IS_INSTALLED') . "&nbsp;" . $filename, 'message');
						}
						else
						{
							JFactory::getApplication()->enqueueMessage(JText::_('AG_CANNOT_MOVED_ITEM') . "&nbsp;" . $result, 'message');
						}
					}
					else
					{
						JFolder::delete($tempDestination . DIRECTORY_SEPARATOR . $resourceType);
						JFactory::getApplication()->
							enqueueMessage(JText::_('AG_ZIP_PACKAGE_IS_NOT_VALID_RESOURCE_TYPE') . "&nbsp;" . $filename, 'error');
					}
				}
				else
				{
					JFactory::getApplication()->enqueueMessage(JText::_('AG_CANNOT_UPLOAD_FILE_TO_TEMP_FOLDER_PLEASE_CHECK_PERMISSIONS'), 'error');
				}
			}
			else
			{
				JFactory::getApplication()->enqueueMessage(JText::_('AG_ONLY_ZIP_ARCHIVES_CAN_BE_INSTALLED'), 'error');
			}
		}
	}

	/**
	 * uninstallResource
	 *
	 * @param   mixed $idsToRemove  Array of resource id's to be uninstalled
	 * @param   mixed $resourceType Current resource type (used to locate folder) popup|template
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function uninstallResource(array $idsToRemove, string $resourceType): void
	{
		foreach ($idsToRemove as $idValue)
		{
			if (!empty($idValue))
			{
				if (JFolder::delete(
					JPATH_SITE . DIRECTORY_SEPARATOR . 'plugins' .
					DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'admirorgallery' .
					DIRECTORY_SEPARATOR . 'admirorgallery' . DIRECTORY_SEPARATOR . $resourceType .
					DIRECTORY_SEPARATOR . $idValue
				)
				)
				{
					JFactory::getApplication()->enqueueMessage(JText::_('AG_PACKAGE_REMOVED') . "&nbsp;" . $idValue, 'message');
				}
				else
				{
					JFactory::getApplication()->enqueueMessage(JText::_('AG_PACKAGE_CANNOT_BE_REMOVED') . "&nbsp;" . $idValue, 'error');
				}
			}
		}
	}

}
