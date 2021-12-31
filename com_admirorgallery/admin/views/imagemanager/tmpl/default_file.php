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

use Admiror\Plugin\Content\AdmirorGallery\Helper;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Filesystem\File as JFile;
use Joomla\CMS\Filesystem\Folder as JFolder;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Uri\Uri as JURI;

$folderName = dirname($this->initItemURL);
$fileName = basename($this->initItemURL);
$imgInfo = Helper::imageInfo(JPATH_SITE . $this->initItemURL);

Helper::sureRemoveDir($this->thumbsPath, true);

if (!JFolder::create($this->thumbsPath))
{
	JFactory::getApplication()->enqueueMessage(JText::_("AG_CANNOT_CREATE_FOLDER") . "&nbsp;" . $newFolderName, 'error');
}

$hasXML = "";
$hasThumb = "";

// Set Possible Description File Absolute Path // Instant patch for upper and lower case...
$pathWithStripExt = JPATH_SITE . $folderName . '/' . JFile::stripExt(basename($this->initItemURL));
$ag_imgXML_path = $pathWithStripExt . ".XML";

if (JFIle::exists($pathWithStripExt . ".xml"))
{
	$ag_imgXML_path = $pathWithStripExt . ".xml";
}

if (file_exists(JPATH_SITE . "/plugins/content/admirorgallery/admirorgallery/thumbs/" . basename($folderName) . "/" . basename($fileName)))
{
	$hasThumb = '<img src="' . JURI::root() . 'administrator/components/com_admirorgallery/templates/' . $this->templateName . '/images/icon-hasThumb.png" class="hasThumb" />';
}

if (file_exists($ag_imgXML_path))
{
	$hasXML = '<img src="' . JURI::root() . 'administrator/components/com_admirorgallery/templates/' . $this->templateName . '/images/icon-hasXML.png" class="hasXML" />';
	$ag_imgXML_xml = simplexml_load_file($ag_imgXML_path);
	$imgCaptions = $ag_imgXML_xml->captions;
}
else
{
	$imgCaptions = null;
}

$previewContent = '';

// GET IMAGES FOR NEXT AND PREV IMAGES FUNCTIONS
$files = JFolder::files(JPATH_SITE . $folderName);

if (!empty($files))
{
	$validExtensions = array("jpg", "jpeg", "gif", "png");// SET VALID IMAGE EXTENSION
	$ag_images = array();

	foreach ($files as $key => $value)
	{
		if (is_numeric(array_search(strtolower(JFile::getExt(basename($value))), $validExtensions)))
		{
			$ag_images[] = $value;
		}
	}


	if (array_search($fileName, $ag_images) != 0)
	{
		$fileName_prev = $ag_images[array_search($fileName, $ag_images) - 1];
	}


	if (array_search($fileName, $ag_images) < count($ag_images) - 1)
	{
		$fileName_next = $ag_images[array_search($fileName, $ag_images) + 1];
	}


	if (!empty($fileName_prev))
	{
		$previewContent .= '<a class="AG_common_button" href="" onclick="AG_jQuery(\'#AG_input_itemURL\').val(\'' . $folderName . '/' . $fileName_prev . '\');submitbutton(\'agReset\');return false;"><span><span>' . JText::_("AG_PREVIOUS_IMAGE") . '</span></span></a>' . "\n";
	}


	if (!empty($fileName_next))
	{
		$previewContent .= '<a class="AG_common_button" href="" onclick="AG_jQuery(\'#AG_input_itemURL\').val(\'' . $folderName . '/' . $fileName_next . '\');submitbutton(\'agReset\');return false;"><span><span>' . JText::_("AG_NEXT_IMAGE") . '</span></span></a>' . "\n";
	}
}

$previewContent .= '<hr />';

$previewContent .= '
<h1>' . JText::_('AG_IMAGE_DETAILS_FOR_FILE') . '</h1>
<div class="AG_border_color AG_border_width AG_margin_bottom AG_breadcrumbs_wrapper">
' . $this->renderBreadcrumb($this->initItemURL, $this->startingFolder, $folderName, $fileName) . '
</div>
';

Helper::createThumbnail(JPATH_SITE . $this->initItemURL, $this->thumbsPath . DIRECTORY_SEPARATOR . basename($this->initItemURL), 145, 80, "none");

// Image and image details
$previewContent .= $this->renderImageInfo($this->initItemURL, $imgInfo, $hasXML, $hasThumb);

require_once JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_admirorgallery' . DIRECTORY_SEPARATOR . 'slimbox' . DIRECTORY_SEPARATOR . 'index.php';

$previewContent .= $this->renderCaptions($imgCaptions);

$previewContent .= $this->renderFileFooter();
