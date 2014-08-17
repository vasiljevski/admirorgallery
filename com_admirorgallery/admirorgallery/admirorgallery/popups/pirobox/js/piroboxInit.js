 /*------------------------------------------------------------------------
# plg_admirorgallery - Admiror Gallery Plugin
# ------------------------------------------------------------------------
# author   Igor Kekeljevic & Nikola Vasiljevski
# copyright Copyright (C) 2014 admiror-design-studio.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.admiror-design-studio.com/joomla-extensions
# Technical Support:  Forum - http://www.vasiljevski.com/forum/index.php
# Version: 4.1.1
-------------------------------------------------------------------------*/
AG_jQuery(document).ready(function(){
	AG_jQuery().piroBox({
		  my_speed: 400, //animation speed
		  bg_alpha: 0.5, //background opacity
		  slideShow : true, // true == slideshow on, false == slideshow off
		  slideSpeed : 4, //slideshow
		  close_all : '.piro_close, .piro_overlay' // add class .piro_overlay(with comma)if you want overlay click close piroBox
		  });
	});