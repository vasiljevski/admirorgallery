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

$template = new Template($AG, "galleryview.css");

// Load CSS from current template folder
$template->loadStyle($AG->currTemplateRoot . 'galleryview.css');

$template->insertScript(' var full_loader_path = "' . $AG->domainPluginPath . $AG->currTemplateRoot . '";');
$template->loadScript($AG->currTemplateRoot . 'jquery.timers-1.2.js');
$template->loadScript($AG->currTemplateRoot . 'jquery.easing.1.3.js');
$template->loadScript($AG->currTemplateRoot . 'jquery.galleryview-2.1.1.js');

$template->insertScript('
AG_jQuery(document).ready(function(){
  AG_jQuery("#AG_' . $AG->getGalleryID() . ' #photos").galleryView({
	  panel_width: ' . $AG->params['frame_width'] . ',
	  panel_height: ' . $AG->params['frame_height'] . ',
	  frame_width: ' . $AG->params['thumbWidth'] . ',
	  frame_height: ' . $AG->params['thumbHeight'] . ',
	  nav_theme: "light",
	  pause_on_hover: true
  });
  AG_jQuery("#AG_' . $AG->getGalleryID() . ' div#photos").css({backgroundColor:"black"});
  AG_jQuery("#AG_' . $AG->getGalleryID() . ' div#photos .panel-content img").css({width:"' .
	$AG->params['frame_width'] . 'px",height:"' . $AG->params['frame_height'] . 'px"});
});
'
);

// Form HTML code, with unique ID and Class Name
$template->appendContent('<div id="AG_' . $AG->getGalleryID() . '" class="ag_reseter AG_galleryView">
<ul id="photos" class="galleryview">'
);

foreach ($AG->images as $imageKey => $imageName)
{
	$template->appendContent( '
      <li>
	    ' . $AG->writeThumb($imageName) . '
	    <div class="panel-content">
		  <img src="' . $AG->sitePath . $AG->params['rootFolder'] . $AG->imagesFolderName . '/' . $imageName . '" alt="big image" />
		  <div class="panel-overlay">
			' . $AG->writeDescription($imageName) . '
		  </div>
	    </div>
      </li>
    '
	);
}

$template->appendContent( '
</ul>
</div>
'
);

// Support for Pagination
$template->appendContent($AG->writePagination());

// Support for Albums
$template->addAlbumSupport();

// Render HTML for this template
$html = $template->render();


