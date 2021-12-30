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

$folderName = dirname($this->ag_init_itemURL);
$ag_fileName = basename($this->ag_init_itemURL);
$AG_imgInfo = Helper::ag_imageInfo(JPATH_SITE . $this->ag_init_itemURL);

Helper::ag_sureRemoveDir($this->thumbsPath, true);

if (!JFolder::create($this->thumbsPath))
{
	JFactory::getApplication()->enqueueMessage(JText::_("AG_CANNOT_CREATE_FOLDER") . "&nbsp;" . $newFolderName, 'error');
}

$ag_hasXML = "";
$ag_hasThumb = "";

// Set Possible Description File Absolute Path // Instant patch for upper and lower case...
$pathWithStripExt = JPATH_SITE . $folderName . '/' . JFile::stripExt(basename($this->ag_init_itemURL));
$ag_imgXML_path = $pathWithStripExt . ".XML";

if (JFIle::exists($pathWithStripExt . ".xml"))
{
	$ag_imgXML_path = $pathWithStripExt . ".xml";
}

if (file_exists(JPATH_SITE . "/plugins/content/admirorgallery/admirorgallery/thumbs/" . basename($folderName) . "/" . basename($ag_fileName)))
{
	$ag_hasThumb = '<img src="' . JURI::root() . 'administrator/components/com_admirorgallery/templates/' . $this->ag_template_id . '/images/icon-hasThumb.png" class="ag_hasThumb" />';
}

if (file_exists($ag_imgXML_path))
{
	$ag_hasXML = '<img src="' . JURI::root() . 'administrator/components/com_admirorgallery/templates/' . $this->ag_template_id . '/images/icon-hasXML.png" class="ag_hasXML" />';
	$ag_imgXML_xml = simplexml_load_file($ag_imgXML_path);
	$ag_imgXML_captions = $ag_imgXML_xml->captions;
}
else
{
	$ag_imgXML_captions = null;
}

$ag_preview_content = '';

// GET IMAGES FOR NEXT AND PREV IMAGES FUNCTIONS
$ag_files = JFolder::files(JPATH_SITE . $folderName);

if (!empty($ag_files))
{
	$validExtensions = array("jpg", "jpeg", "gif", "png");// SET VALID IMAGE EXTENSION
	$ag_images = array();

	foreach ($ag_files as $key => $value)
	{
		if (is_numeric(array_search(strtolower(JFile::getExt(basename($value))), $validExtensions)))
		{
			$ag_images[] = $value;
		}
	}


	if (array_search($ag_fileName, $ag_images) != 0)
	{
		$ag_fileName_prev = $ag_images[array_search($ag_fileName, $ag_images) - 1];
	}


	if (array_search($ag_fileName, $ag_images) < count($ag_images) - 1)
	{
		$ag_fileName_next = $ag_images[array_search($ag_fileName, $ag_images) + 1];
	}


	if (!empty($ag_fileName_prev))
	{
		$ag_preview_content .= '<a class="AG_common_button" href="" onclick="AG_jQuery(\'#AG_input_itemURL\').val(\'' . $folderName . '/' . $ag_fileName_prev . '\');submitbutton(\'agReset\');return false;"><span><span>' . JText::_("AG_PREVIOUS_IMAGE") . '</span></span></a>' . "\n";
	}


	if (!empty($ag_fileName_next))
	{
		$ag_preview_content .= '<a class="AG_common_button" href="" onclick="AG_jQuery(\'#AG_input_itemURL\').val(\'' . $folderName . '/' . $ag_fileName_next . '\');submitbutton(\'agReset\');return false;"><span><span>' . JText::_("AG_NEXT_IMAGE") . '</span></span></a>' . "\n";
	}
}

$ag_preview_content .= '<hr />';

$ag_preview_content .= '
<h1>' . JText::_('AG_IMAGE_DETAILS_FOR_FILE') . '</h1>
<div class="AG_border_color AG_border_width AG_margin_bottom AG_breadcrumbs_wrapper">
' . $this->ag_render_breadcrumb($this->ag_init_itemURL, $this->ag_starting_folder, $folderName, $ag_fileName) . '
</div>
';

Helper::ag_createThumb(JPATH_SITE . $this->ag_init_itemURL, $this->thumbsPath . DIRECTORY_SEPARATOR . basename($this->ag_init_itemURL), 145, 80, "none");

// Image and image details
$ag_preview_content .= $this->ag_render_image_info($this->ag_init_itemURL, $AG_imgInfo, $ag_hasXML, $ag_hasThumb);

require_once JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_admirorgallery' . DIRECTORY_SEPARATOR . 'slimbox' . DIRECTORY_SEPARATOR . 'index.php';

$ag_preview_content .= $this->ag_render_captions($ag_imgXML_captions);

$ag_preview_content .= $this->ag_render_file_footer();
