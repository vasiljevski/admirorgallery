<?php

/**
 * @version     5.2.0
 * @package     Admiror Gallery (plugin)
 * @subpackage  admirorgallery
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2018 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/**
 * Provides support for common template functions
 * $ag - reference to the gallery instance
 * $albumSupport - true: enable album support, false: disable album support
 */
class agTemplate {

    //Output HTML
    var $html = '';
    //Gallery instance reference
    var $AG;
    // Album support
    var $album_support;
    //Popup scripts and style files that should be loaded before the content
    var $popupInit;
    //Popup scripts and style files that should be loaded after the content
    var $popupEnd;

    function __construct($ag, $albumSupport) {
        $this->AG = $ag;
        $this->album_support = $albumSupport;
    }

    public function addAlbumSupport() {
        // Support for Albums
        if (!empty($this->AG->folders) && $this->album_support) {
            $this->html .= '<h1>' . JText::_('AG_ALBUMS') . '</h1>' . "\n";
            $this->html .= $this->AG->writeFolderThumb("albums/album.png", $this->AG->params['thumbHeight']);
        }
    }

    //Load CSS style file
    public function loadStyle($cssFile) {
        $this->AG->loadCSS($cssFile);
    }

    //Load multiple CSS style files
    public function loadStyles(array $cssFiles) {
        foreach ($cssFiles as $file) {
            $this->AG->loadCSS($file);
        }
    }

    // Loads scripts needed for Popups, before gallery is created
    public function preContent() {
        $this->popupInit = $this->AG->initPopup();
    }

    // Loads scripts needed for Popups, after gallery is created
    public function postContet() {
        $this->popupEnd = $this->AG->endPopup();
    }

    public function appendContent($content) {
        $this->html .= $content;
    }

    public function render() {
        $content = $this->preContent();
        $content .= $this->html;
        $content .= $this->postContet();
        return $content;
    }

}
