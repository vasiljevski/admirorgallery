<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (plugin)
 * @subpackage  admirorgallery
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2021 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

$template = new agTemplate($AG);

$template->preContent();
// Form HTML code, with unique ID and Class Name
$template->appendContent('
<style type="text/css">

    .AG_classic .ag_imageThumb {border-color:' . $AG->params['foregroundColor'] . '}
    .AG_classic .ag_imageThumb:hover {background-color:' . $AG->params['highlightColor'] . '}

' . $template->generatePaginationStyle() . '

</style>
<div id="AG_' . $AG->getGalleryID() .
    '" class="ag_reseter AG_' . $AG->params['template'] . '">');

$template->appendContent($AG->albumParentLink);

$template->appendContent('
  <table>
    <tbody>
      <tr>
	<td>');

// Loops over the array of images inside target gallery folder, 
// adding wrapper with SPAN tag and write Popup thumbs inside this wrapper
if (!empty($AG->images)) {
    foreach ($AG->images as $imageKey => $imageName) {
        $template->appendContent('<span class="ag_thumb' . $AG->params['template'] . '">');
        $template->appendContent($AG->writePopupThumb($imageName));
        $template->appendContent('</span>');
    }
}

$template->appendContent('
	</td>
      </tr>
    </tbody>
  </table>');

// Support for Pagination
$template->appendContent($AG->writePagination() . '
</div>
');

// Support for Albums
$template->addAlbumSupport();

// Render HTML for this template
$html = $template->render();

