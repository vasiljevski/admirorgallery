<?php
 /*------------------------------------------------------------------------
# admirorgallery - Admiror Gallery Plugin
# ------------------------------------------------------------------------
# author   Igor Kekeljevic & Nikola Vasiljevski
# copyright Copyright (C) 2014 admiror-design-studio.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.admiror-design-studio.com/joomla-extensions
# Technical Support:  Forum - http://www.vasiljevski.com/forum/index.php
# Version: 5.0.0
-------------------------------------------------------------------------*/
// Joomla security code
defined('_JEXEC') or die();

// Load AG_jQuery Flash library from current template folder
$AG->loadJS($AG->currTemplateRoot . 'jquery.swfobject.1-1-1.min.js');
$AG->loadCSS($AG->currTemplateRoot . 'albums/albums.css');
$AG->loadCSS($AG->currTemplateRoot . 'pagination/pagination.css');

// Reset $html variable from previous entery
$html = '';

$html.=$AG->albumParentLink;

// Generate XML string needed for Flash gallery
$xmlGen = '';
$xmlGen.='<?xml version="1.0" encoding="utf-8"?>';
$xmlGen.='<photos>';
foreach ($AG->images as $imageKey => $imageName) {
    $AG->getImageInfo($imageName);
    $xmlGen.='<photo url="' . $AG->imagesFolderPath . $imageName . '" desc="' . $AG->writeDescription($imageName) . '" width="' . $AG->imageInfo["width"] . '" height="' . $AG->imageInfo["height"] . '" />';
}
$xmlGen.='</photos>';

// Insert JavaScript code needed for AG_jQuery Flash library
$AG->insertJSCode('
AG_jQuery(function(){
	AG_jQuery("#AG_' . $AG->getGalleryID() . '").flash({
		swf: "' . $AG->pluginPath . $AG->currTemplateRoot . 'simple_flash_gallery.swf",
		width: ' . $AG->params['frameWidth'] . ',
		height: ' . $AG->params['frameHeight'] . ',
		flashvars: {
			xmlString:\'' . $xmlGen . '\',
			thumbPercentage:' . $AG->getParameter("thumbPercentage", 30) . '
		}
	});
});
');

// Add wrapper with unique ID name,used by AG_jQuery Flash library for embeding SWF file
$html.='
<style type="text/css">
/* PAGINATION AND ALBUM STYLE DEFINITIONS */
#AG_' . $AG->getGalleryID() . ' a.AG_album_thumb, #AG_' . $AG->getGalleryID() . ' div.AG_album_wrap, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_link, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_prev, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_next {border-color:#' . $AG->params['foregroundColor'] . '}
#AG_' . $AG->getGalleryID() . ' a.AG_album_thumb:hover, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_link:hover, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_prev:hover, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_next:hover {background-color:#' . $AG->params['highliteColor'] . '}
#AG_' . $AG->getGalleryID() . ' div.AG_album_wrap h1, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_link, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_prev, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_next{color:#' . $AG->params['foregroundColor'] . '}
</style>

<div class="ag_reseter" id="AG_' . $AG->getGalleryID() . '"></div>
';

// Support for Pagination
$html.= $AG->writePagination();

// Support for Albums
if (!empty($AG->folders) && $AG->params['albumUse']) {
    $html.= '<h1>' . JText::_('Albums') . '</h1>' . "\n";
    $html.= $AG->writeFolderThumb("albums/album.png", $AG->params['thumbHeight']);
}
?>
