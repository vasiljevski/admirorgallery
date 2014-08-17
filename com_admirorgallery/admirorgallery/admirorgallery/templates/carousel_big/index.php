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
defined('_JEXEC') or die('Restricted access');

$AG->loadJS($AG->currTemplateRoot . 'jquery.jcarousel.js');
$AG->loadCSS($AG->currTemplateRoot . 'jquery.jcarousel.css');
$AG->loadCSS($AG->currTemplateRoot . 'albums/albums.css');
$AG->loadCSS($AG->currTemplateRoot . 'pagination/pagination.css');

// Form HTML code
$html = "";

$html .= $AG->albumParentLink;

$html .= '
<div id="AG_' . $AG->getGalleryID() . '" class="ag_reseter AG_' . $AG->params['template'] . ' ag_wrap">
<ul>
';

foreach ($AG->images as $imagesKey => $imageValue) {
    $html .= '
<li>
<img id="slide-img-' . ($imagesKey + 1) . '" src="' . $AG->sitePath . $AG->params['rootFolder'] . $AG->imagesFolderName . '/' . $imageValue . '" class="slide" alt=""  style="width:' . $AG->params['frameWidth'] . 'px; height:' . $AG->params['frameHeight'] . 'px;"/>
</li>
';
}

$html .= '
</ul>
';

$html .= '
    <div class="jcarousel-control">
';

foreach ($AG->images as $imagesKey => $imagesValue) {
    $html .= '
        <a href="#" rel="' . ($imagesKey + 1) . '">&nbsp;</a>
  ';
}
$html .= '
    </div>
';

$html .= '
</div>

<script type="text/javascript">

AG_jQuery(function(){

function mycarousel_initCallback(carousel)
		{
      AG_jQuery(\'#AG_' . $AG->getGalleryID() . ' .jcarousel-control a\').bind(\'click\', function() {
        carousel.stopAuto();
        carousel.scroll(AG_jQuery.jcarousel.intval(AG_jQuery(this).attr("rel")));
        carousel.startAuto();
        return false;
      });

		
			// Pause autoscrolling if the user moves with the cursor over the clip.
			carousel.clip.hover(function() {
			carousel.stopAuto();
			}, function() {
			carousel.startAuto();
			});
		};
    
AG_jQuery(\'#AG_' . $AG->getGalleryID() . '\').jcarousel({
			scroll: 1,
			auto: 10,
			wrap: \'last\',
			initCallback: mycarousel_initCallback

		});

AG_jQuery("#AG_' . $AG->getGalleryID() . ' .jcarousel-container-horizontal").css({width:"' . $AG->params['frameWidth'] . 'px"})

AG_jQuery("#AG_' . $AG->getGalleryID() . ' .jcarousel-clip-horizontal").css({width:"' . $AG->params['frameWidth'] . 'px"})

AG_jQuery(\'.jcarousel-control a[rel="1"]\').css({backgroundPosition:"left -20px", cursor:"default"});

});

</script>

<style type="text/css">

/* PAGINATION AND ALBUM STYLE DEFINITIONS */
#AG_' . $AG->getGalleryID() . ' a.AG_album_thumb, #AG_' . $AG->getGalleryID() . ' div.AG_album_wrap, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_link, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_prev, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_next {border-color:#' . $AG->params['foregroundColor'] . '}
#AG_' . $AG->getGalleryID() . ' a.AG_album_thumb:hover, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_link:hover, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_prev:hover, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_next:hover {background-color:#' . $AG->params['highliteColor'] . '}
#AG_' . $AG->getGalleryID() . ' div.AG_album_wrap h1, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_link, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_prev, #AG_' . $AG->getGalleryID() . ' a.AG_pagin_next{color:#' . $AG->params['foregroundColor'] . '}

#AG_' . $AG->getGalleryID() . ' .jcarousel-list li,
#AG_' . $AG->getGalleryID() . ' .jcarousel-item,
#AG_' . $AG->getGalleryID() . '
{
	width:' . $AG->params['frameWidth'] . 'px;
}

#AG_' . $AG->getGalleryID() . ' .jcarousel-list li,
#AG_' . $AG->getGalleryID() . ' .jcarousel-item,
#AG_' . $AG->getGalleryID() . ' .jcarousel-clip
{
	height:' . $AG->params['frameHeight'] . 'px;
}
#AG_' . $AG->getGalleryID() . ' ul,
#AG_' . $AG->getGalleryID() . ' li
{
  background-image:none;
  padding:0;
}

</style>

';

// Support for Pagination
$html.= $AG->writePagination();

// Support for Albums
if (!empty($AG->folders) && $AG->params['albumUse']) {
    $html.= '<h1>' . JText::_('Albums') . '</h1>' . "\n";
    $html.= $AG->writeFolderThumb("albums/album.png", $AG->params['thumbHeight']);
}

?>
