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

// Load CSS from current template folder
$AG->loadCSS($AG->currTemplateRoot.'galleryview.css');
$AG->loadCSS($AG->currTemplateRoot.'albums/albums.css');
$AG->loadCSS($AG->currTemplateRoot.'pagination/pagination.css');

$AG->insertJSCode(' var full_loader_path = "'.$AG->pluginPath . $AG->currTemplateRoot.'";');
$AG->loadJS($AG->currTemplateRoot.'jquery.timers-1.2.js');
$AG->loadJS($AG->currTemplateRoot.'jquery.easing.1.3.js');
$AG->loadJS($AG->currTemplateRoot.'jquery.galleryview-2.1.1.js');


$AG->insertJSCode('
AG_jQuery(document).ready(function(){
  AG_jQuery("#AG_'.$AG->getGalleryID().' #photos").galleryView({
	  panel_width: '.$AG->params['frameWidth'].',
	  panel_height: '.$AG->params['frameHeight'].',
	  frame_width: '.$AG->params['thumbWidth'].',
	  frame_height: '.$AG->params['thumbHeight'].',
	  nav_theme: "light",
	  pause_on_hover: true
  });
  AG_jQuery("#AG_'.$AG->getGalleryID().' div#photos").css({backgroundColor:"black"});
  AG_jQuery("#AG_'.$AG->getGalleryID().' div#photos .panel-content img").css({width:"'.$AG->params['frameWidth'].'px",height:"'.$AG->params['frameHeight'].'px"});
});
');

// Form HTML code, with unique ID and Class Name
$html='<div id="AG_'.$AG->getGalleryID().'" class="ag_reseter AG_galleryView">
<ul id="photos" class="galleryview">
';

foreach ($AG->images as $imageKey => $imageName)
{
    $html.= '

      <li>
	    '.$AG->writeThumb($imageName).'
	    <div class="panel-content">
		  <img src="'.$AG->sitePath.$AG->params['rootFolder'].$AG->imagesFolderName.'/'.$imageName.'" alt="big image" />
		  <div class="panel-overlay">
			'.$AG->writeDescription($imageName).'
		  </div>
	    </div>
      </li>
    ';
}
$html .='
</ul>
</div>
';

// Support for Pagination
$html.= $AG->writePagination();

// Support for Albums
if(!empty($AG->folders) && $AG->params['albumUse']){
     $html.= '<h1>'.JText::_( 'AG_ALBUMS' ).'</h1>'."\n";
     $html.= $AG->writeFolderThumb("albums/album.png",$AG->params['thumbHeight']);
}

?>

