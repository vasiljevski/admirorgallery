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

// Load CSS from current template folder
$AG->loadCSS($AG->currTemplateRoot . 'template.css');
$AG->loadCSS($AG->currTemplateRoot . 'albums/albums.css');
$AG->loadCSS($AG->currTemplateRoot . 'pagination/pagination.css');

// Reset $html variable from previous entery and load it with scripts needed for Popups
$html = $AG->initPopup();

// Form HTML code, with unique ID and Class Name
$html.='
<style type="text/css">

    .AG_classic .ag_imageThumb {border-color:#' . $AG->params['foregroundColor'] . '}
    .AG_classic .ag_imageThumb:hover {background-color:#' . $AG->params['highliteColor'] . '}

    /* PAGINATION AND ALBUM STYLE DEFINITIONS */
      #AG_' . $AG->getGalleryID() . ' a.AG_album_thumb, #AG_' . $AG->getGalleryID() . ' div.AG_album_wrap, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_link, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_prev, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_next {border-color:#' . $AG->params['foregroundColor'] . '}
      #AG_' . $AG->getGalleryID() . ' a.AG_album_thumb:hover, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_link:hover, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_prev:hover, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_next:hover {background-color:#' . $AG->params['highliteColor'] . '}
      #AG_' . $AG->getGalleryID() . ' div.AG_album_wrap h1, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_link, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_prev, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_next{color:#' . $AG->params['foregroundColor'] . '}

</style>
<div id="AG_' . $AG->getGalleryID() . '" class="ag_reseter AG_' . $AG->params['template'] . '">';

$html.=$AG->albumParentLink;

$html.='
  <table cellspacing="0" cellpadding="0" border="0">
    <tbody>
      <tr>
	<td>';

// Loops over the array of images inside target gallery folder, adding wrapper with SPAN tag and write Popup thumbs inside this wrapper
if (!empty($AG->images)) {
    foreach ($AG->images as $imageKey => $imageName) {
        $html.= '<span class="ag_thumb' . $AG->params['template'] . '">';
        $html.= $AG->writePopupThumb($imageName);
        $html.='</span>';
    }
}

$html .='
	</td>
      </tr>
    </tbody>
  </table>';

// Support for Pagination
$html.= $AG->writePagination();

// Support for Albums
if (!empty($AG->folders) && $AG->params['albumUse']) {
    $html.= '<h1>' . JText::_('Albums') . '</h1>' . "\n";
    $html.= $AG->writeFolderThumb("albums/album.png", $AG->params['thumbHeight']);
}

$html.='
</div>
';

// Loads scripts needed for Popups, after gallery is created
$html.=$AG->endPopup();
?>
