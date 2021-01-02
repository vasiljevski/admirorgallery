<?php
/**
 * @version     5.5.0
 * @package     Admiror Gallery (plugin)
 * @subpackage  admirorgallery
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2020 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

$template = new agTemplate($AG, 'listed');

$template->preContent();

$template->appendContent($AG->albumParentLink);

// Form HTML code, with unique ID and Class Name
$template->appendContent('
<style type="text/css">

.AG_listed .ag_thumbTd a:hover{border-bottom:2px solid ' . $AG->params['highlightColor'] . ';}
.AG_listed a .ag_imageThumb{background-color:' . $AG->params['foregroundColor'] . ';}
.AG_listed .ag_description, .AG_listed .ag_imageStat span{color:' . $AG->params['foregroundColor'] . ';}

' . $template->generatePaginationStyle() . '
</style>
<div id="AG_' . $AG->getGalleryID() . '" class="ag_reseter AG_' . $AG->params['template'] . '">
');
foreach ($AG->images as $imageKey => $imageName) {

// Loads values into $AG->imageInfo array for target image
    $AG->getImageInfo($imageName);

    $template->appendContent('
    <table class="ag_item">
    <tbody>
    <tr><td class="ag_thumbTd">
    <span class="ag_thumb' . $AG->params['template'] . '">' .
            $AG->writePopupThumb($imageName). 
    '</span></td>
    <td class="ag_info">
    <table>
    <tbody>
    <tr><td class="ag_description">
    ' . $AG->writeDescription($imageName) . '
    <tr><td class="ag_imageStat">
    <span>W:' . $AG->imageInfo["width"] . 'px</span>
    <span>H:' . $AG->imageInfo["height"] . 'px</span>
    <span>S:' . $AG->imageInfo["size"] . '</span>
    </td></tr>
    </td></tr></tbody></table>
    </td></tr></tbody></table>');
}

$template->appendContent('<!-- Admiror Gallery --></div>');

// Support for Pagination
$template->appendContent($AG->writePagination());

// Support for Albums
$template->addAlbumSupport();

// Render HTML for this template
$html = $template->render();


