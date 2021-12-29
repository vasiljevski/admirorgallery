/**
 * @version     6.0.0
 * @package     Admiror.Plugin
 * @subpackage  Content.AdmirorGallery
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

AG_jQuery(document).ready(function() {
    AG_jQuery().piroBox({
        my_speed: 400, //animation speed
        bg_alpha: 0.5, //background opacity
        slideShow: true, // true == slideshow on, false == slideshow off
        slideSpeed: 4, //slideshow
        close_all: '.piro_close, .piro_overlay' // add class .piro_overlay(with comma)if you want overlay click close piroBox
    });
});