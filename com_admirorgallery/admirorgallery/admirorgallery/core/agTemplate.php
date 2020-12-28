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
 *
 * @since 5.0.0
 */
class agTemplate
{

    //Output HTML
    public $html = '';
    //Gallery instance reference
    public $AG;
    // Album support
    public $album_support;
    //Root folder of the template
    public $template_root;

    public $template_style;
    public $pagination_style = 'pagination/pagination.css';
    public $album_style = 'albums/albums.css';

    public function __construct($ag, $template = 'template.css')
    {
        $this->AG = $ag;
        $this->album_support = $ag->params['albumUse'];
        $this->template_root = $ag->currTemplateRoot;
        $this->template_style = $template;
    }

    public function addAlbumSupport()
    {
        // Support for Albums
        if (!empty($this->AG->folders) && $this->album_support) {
            $this->AG->loadCSS($this->template_root.$this->album_style);
            $this->html .= '<h1>' . JText::_('AG_ALBUMS') . '</h1>' . "\n";
            $this->html .= $this->AG->writeFolderThumb("albums/album.png", $this->AG->params['thumbHeight']);
        }
    }

    //Load CSS style file
    public function loadStyle($cssFile)
    {
        $this->AG->loadCSS($cssFile);
    }

    //Load multiple CSS style files
    public function loadStyles(array $cssFiles)
    {
        foreach ($cssFiles as $file) {
            $this->AG->loadCSS($file);
        }
    }
    
    //Load JavaScript file
    public function loadScript($jsFile)
    {
        $this->AG->loadJS($jsFile);
    }

    //Load multiple CSS style files
    public function insertScript($script)
    {
        $this->AG->insertJSCode($script);
    }
    
    //Load multiple CSS style files
    public function loadScripts(array $jsFiles)
    {
        foreach ($jsFiles as $file) {
            $this->AG->loadJS($file);
        }
    }

    // Loads scripts needed for Popups, before gallery is created
    public function preContent()
    {
        $this->html.= $this->AG->initPopup();
    }

    // Loads scripts needed for Popups, after gallery is created
    public function postContent()
    {
        $this->html.= $this->AG->endPopup();
    }

    public function appendContent($content)
    {
        $this->html .= $content;
    }
    
    public function generatePaginationStyle()
    {
        return '/* PAGINATION AND ALBUM STYLE DEFINITIONS */
#AG_' . $this->AG->getGalleryID() . ' a.AG_album_thumb, #AG_' .
        $this->AG->getGalleryID() . ' div.AG_album_wrap, #AG_' .
        $this->AG->getGalleryID() . ' a.AG_pagin_link, #AG_' .
        $this->AG->getGalleryID() . ' a.AG_pagin_prev, #AG_' .
        $this->AG->getGalleryID() . ' a.AG_pagin_next {border-color:#' . $this->AG->params['foregroundColor'] . '}
#AG_' . $this->AG->getGalleryID() . ' a.AG_album_thumb:hover, #AG_' .
        $this->AG->getGalleryID() . ' a.AG_pagin_link:hover, #AG_' .
        
        $this->AG->getGalleryID() . ' a.AG_pagin_prev:hover, #AG_' .
        $this->AG->getGalleryID() . ' a.AG_pagin_next:hover {background-color:#' .
        $this->AG->params['highlightColor'] . '}
#AG_' . $this->AG->getGalleryID() . ' div.AG_album_wrap h1, #AG_' .
        $this->AG->getGalleryID() . ' a.AG_pagin_link, #AG_' .
        $this->AG->getGalleryID() . ' a.AG_pagin_prev, #AG_' .
        $this->AG->getGalleryID() . ' a.AG_pagin_next{color:#' .
        $this->AG->params['foregroundColor'].'}';
    }

    public function render(): string
    {
        $this->AG->loadCSS($this->template_root.$this->template_style);
        $this->AG->loadCSS($this->template_root.$this->pagination_style);
        $this->postContent();
        return $this->html;
    }
}
