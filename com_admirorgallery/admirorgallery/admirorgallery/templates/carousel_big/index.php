<?php
/**
 * @version     5.5.0
 * @package     Admiror Gallery (plugin)
 * @subpackage  admirorgallery
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2020 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
// Joomla security code
defined('_JEXEC') or die('Restricted access');

$template = new agTemplate($AG, 'jquery.jcarousel.css');

$template->loadScript($AG->currTemplateRoot . 'jquery.jcarousel.js');

// Form HTML code
$template->appendContent($AG->albumParentLink . '
<div id="AG_' . $AG->getGalleryID() .
        '" class="ag_reseter AG_' . $AG->params['template'] . ' ag_wrap">
<ul>
');

foreach ($AG->images as $imagesKey => $imageValue) {
    $template->appendContent('
<li>
<img id="slide-img-' . ($imagesKey + 1) .
            '" src="' . $AG->sitePath .
                $AG->params['rootFolder'] .
                $AG->imagesFolderName .
                '/' .
                $imageValue .
            '" class="slide" '.
            'alt=""  '.
            'style="width:' . $AG->params['frameWidth'] . 'px; '.
            'height:' . $AG->params['frameHeight'] . 'px;"/>
</li>
');
}

$template->appendContent('
</ul>
    <div class="jcarousel-control">
');

foreach ($AG->images as $imagesKey => $imagesValue) {
    $template->appendContent('
        <a href="#" rel="' . ($imagesKey + 1) . '">&nbsp;</a>
  ');
}
$template->appendContent('
    </div>
</div>');

$template->appendContent('
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

' . $template->generatePaginationStyle() . '

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

');

// Support for Pagination
$template->appendContent($AG->writePagination());

$html = $template->render();

