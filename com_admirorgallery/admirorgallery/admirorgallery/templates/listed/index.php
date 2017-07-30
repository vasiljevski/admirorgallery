<?php
/**
 * @version     5.1.2
 * @package     Admiror Gallery (plugin)
 * @subpackage  admirorgallery
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2017 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// Joomla security code
defined('_JEXEC') or die();

// Reset $html variable from previous entery and load it with scripts needed for Popups
$html = $AG->initPopup();

// Load CSS from current template folder
$AG->loadCSS($AG->currTemplateRoot . 'listed.css');
$AG->loadCSS($AG->currTemplateRoot . 'albums/albums.css');
$AG->loadCSS($AG->currTemplateRoot . 'pagination/pagination.css');

$html.=$AG->albumParentLink;

// Form HTML code, with unique ID and Class Name
$html.='
<style type="text/css">

.AG_listed .ag_thumbTd a:hover{border-bottom:2px solid #' . $AG->params['highliteColor'] . ';}
.AG_listed a .ag_imageThumb{background-color:#' . $AG->params['foregroundColor'] . ';}
.AG_listed .ag_description, .AG_listed .ag_imageStat span{color:#' . $AG->params['foregroundColor'] . ';}

/* PAGINATION AND ALBUM STYLE DEFINITIONS */
#AG_' . $AG->getGalleryID() . ' a.AG_album_thumb, #AG_' . $AG->getGalleryID() . ' div.AG_album_wrap, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_link, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_prev, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_next {border-color:#' . $AG->params['foregroundColor'] . '}
#AG_' . $AG->getGalleryID() . ' a.AG_album_thumb:hover, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_link:hover, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_prev:hover, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_next:hover {background-color:#' . $AG->params['highliteColor'] . '}
#AG_' . $AG->getGalleryID() . ' div.AG_album_wrap h1, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_link, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_prev, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_next{color:#' . $AG->params['foregroundColor'] . '}

</style>
<div id="AG_' . $AG->getGalleryID() . '" class="ag_reseter AG_' . $AG->params['template'] . '">
';
foreach ($AG->images as $imageKey => $imageName) {

// Loads values into $AG->imageInfo array for target image
    $AG->getImageInfo($imageName);

    $html .= '
    <table border="0" cellspacing="0" cellpadding="0" width="100%" class="ag_item">
    <tbody>
    <tr><td class="ag_thumbTd">
    <span class="ag_thumb' . $AG->params['template'] . '">';
    $html.= $AG->writePopupThumb($imageName);
    $html .='</span></td>
    <td class="ag_info">
    <table border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr><td class="ag_description">
    ' . $AG->writeDescription($imageName) . '
    <tr><td class="ag_imageStat">
    <span>W:' . $AG->imageInfo["width"] . 'px</span>
    <span>H:' . $AG->imageInfo["height"] . 'px</span>
    <span>S:' . $AG->imageInfo["size"] . '</span>
    </td></tr>
    </td></tr></tbody></table>
    </td></tr></tbody></table>';
}

$html .='<!-- Admiror Gallery --></div>';

// Support for Pagination
$html.= $AG->writePagination();

// Support for Albums
if (!empty($AG->folders) && $AG->params['albumUse']) {
    $html.= '<h1>' . JText::_('Albums') . '</h1>' . "\n";
    $html.= $AG->writeFolderThumb("albums/album.png", $AG->params['thumbHeight']);
}

// Loads scripts needed for Popups, after gallery is created
$html.=$AG->endPopup();
?>

