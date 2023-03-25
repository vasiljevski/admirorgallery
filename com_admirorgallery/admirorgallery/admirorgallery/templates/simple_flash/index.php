<?php
/**
 * @version     6.0.0
 * @package     Admiror.Plugin
 * @subpackage  Content.AdmirorGallery
 * @author      Igor Kekeljevic <igor@admiror.com>
 * @author      Nikola Vasiljevski <nikola83@gmail.com>
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Admiror\Plugin\Content\AdmirorGallery\Template;

$template = new Template($AG);

// Load AG_jQuery Flash library from current template folder
$template->loadScript($AG->currTemplateRoot . 'jquery.swfobject.1-1-1.min.js');

$template->appendContent($AG->albumParentLink);

// Generate XML string needed for Flash gallery
$xmlGen = '<?xml version="1.0" encoding="utf-8"?>';
$xmlGen .= '<photos>';

foreach ($AG->images as $imageKey => $imageName)
{
	$AG->getImageInfo($imageName);
	$xmlGen .= '<photo url="' .
			$AG->imagesFolderPath . $imageName . '" desc="' .
			$AG->writeDescription($imageName) . '" width="' .
			$AG->imageInfo["width"] . '" height="' .
			$AG->imageInfo["height"] . '" />';
}

$xmlGen .= '</photos>';

// Insert JavaScript code needed for AG_jQuery Flash library
$template->insertScript('
AG_jQuery(function(){
	AG_jQuery("#AG_' . $AG->getGalleryID() . '").flash({
		swf: "' . $AG->domainPluginPath . $AG->currTemplateRoot . 'simple_flash_gallery.swf",
		width: ' . $AG->params['frame_width'] . ',
		height: ' . $AG->params['frame_height'] . ',
		flashvars: {
			xmlString:\'' . $xmlGen . '\',
			thumbPercentage:' . $AG->getParameter("thumbPercentage", 30) . '
		}
	});
});
'
);

// Add wrapper with unique ID name,used by AG_jQuery Flash library for embedding SWF file
$template->appendContent('
<style type="text/css">
' . $template->generatePaginationStyle() . '
</style>

<div class="ag_reseter" id="AG_' . $AG->getGalleryID() . '"></div>
'
);

// Support for Pagination
$template->appendContent($AG->writePagination());

// Support for Albums
$template->addAlbumSupport();

// Render HTML for this template
$html = $template->render();

