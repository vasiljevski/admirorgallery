<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (plugin)
 * @subpackage  admirorgallery
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/**
 * Provides support for common template functions
 * $ag - reference to the gallery instance
 * $template - template style file name
 *
 * @since 5.5.0
 */

class agTemplate
{
    private string $html = ''; //Output HTML
    private agGallery $AG; //Gallery instance reference
    private bool $album_support; // Album support
    private string $template_root; //Root folder of the template
    private string $template_style;
    private string $pagination_style;
    private string $album_style;
    private string $default_thumb;

    /**
     * agTemplate constructor.
     *
     * @param agGallery $ag
     * @param string $template
     *
     * @since 5.5.0
     */
    public function __construct(agGallery $ag,
                                string $template = 'template.css',
                                string $album_style = 'albums/albums.css',
                                string $pagination_style = 'pagination/pagination.css',
                                string $default_thumb = '../../albums/album.png')
    {
        $this->AG = $ag;
        $this->album_support = (bool)$ag->params['albumUse'];
        $this->template_root = $ag->currTemplateRoot;
        $this->template_style = $this->template_root.$template;
        $this->album_style = $album_style;
        $this->pagination_style = $pagination_style;
        $this->default_thumb = $default_thumb;
    }

    /**
     * Add album support
     *
     * @since 5.5.0
     */
    public function addAlbumSupport(): void
    {
        // Support for Albums
        if (!empty($this->AG->folders) && $this->album_support) {
            $this->AG->loadCSS($this->album_style);
            $this->html .= '<h1>' . $this->AG->getText("AG_ALBUMS") . '</h1>' . "\n";
            $this->html .= $this->AG->writeFolderThumb($this->default_thumb, $this->AG->params['thumbHeight']);
        }
    }

    /**
     * Load CSS style file
     *
     * @param string $cssFile
     *
     * @since 5.5.0
     */
    public function loadStyle(string $cssFile): void
    {
        $this->AG->loadCSS($cssFile);
    }

    /**
     * Load multiple CSS style files
     *
     * @param array $cssFiles
     *
     * @since 5.5.0
     */
    public function loadStyles(array $cssFiles): void
    {
        foreach ($cssFiles as $file) {
            $this->AG->loadCSS($file);
        }
    }

    /**
     * Load JavaScript file
     *
     * @param $jsFile
     *
     * @since 5.5.0
     */
    public function loadScript($jsFile): void
    {
        $this->AG->loadJS($jsFile);
    }

    /**
     * Load multiple CSS style files
     *
     * @param $script
     *
     * @since 5.5.0
     */
    public function insertScript($script): void
    {
        $this->AG->insertJSCode($script);
    }

    /**
     * Load multiple CSS style files
     *
     * @param array $jsFiles
     *
     * @since 5.5.0
     */
    public function loadScripts(array $jsFiles): void
    {
        foreach ($jsFiles as $file) {
            $this->AG->loadJS($file);
        }
    }

    /**
     * Loads scripts needed for Popups, before gallery is created
     *
     * @since 5.5.0
     */
    public function preContent(): void
    {
        $this->html.= $this->AG->initPopup();
    }

    /**
     * Loads scripts needed for Popups, after gallery is created
     *
     * @since 5.5.0
     */
    public function postContent(): void
    {
        $this->html.= $this->AG->endPopup();
    }

    /**
     * Append content
     *
     * @param string $content
     *
     *
     * @since 5.5.0
     */
    public function appendContent(string $content): void
    {
        $this->html .= $content;
    }

    /**
     * Generate pagination style
     *
     * @return string
     *
     * @since 5.5.0
     */
    public function generatePaginationStyle(): string
    {
        return '/* PAGINATION AND ALBUM STYLE DEFINITIONS */
#AG_' . $this->AG->getGalleryID() . ' a.AG_album_thumb, #AG_' .
        $this->AG->getGalleryID() . ' div.AG_album_wrap, #AG_' .
        $this->AG->getGalleryID() . ' a.AG_pagin_link, #AG_' .
        $this->AG->getGalleryID() . ' a.AG_pagin_prev, #AG_' .
        $this->AG->getGalleryID() . ' a.AG_pagin_next {border-color:' . $this->AG->params['foregroundColor'] . '}
#AG_' . $this->AG->getGalleryID() . ' a.AG_album_thumb:hover, #AG_' .
        $this->AG->getGalleryID() . ' a.AG_pagin_link:hover, #AG_' .
        
        $this->AG->getGalleryID() . ' a.AG_pagin_prev:hover, #AG_' .
        $this->AG->getGalleryID() . ' a.AG_pagin_next:hover {background-color:' . $this->AG->params['highlightColor'] . '}
#AG_' . $this->AG->getGalleryID() . ' div.AG_album_wrap h1, #AG_' .
        $this->AG->getGalleryID() . ' a.AG_pagin_link, #AG_' .
        $this->AG->getGalleryID() . ' a.AG_pagin_prev, #AG_' .
        $this->AG->getGalleryID() . ' a.AG_pagin_next{color:' . $this->AG->params['foregroundColor'].'}';
    }

    /**
     * Render HTML of the template
     *
     * @return string
     *
     * @since 5.5.0
     */
    public function render(): string
    {
        $this->AG->loadCSS($this->template_style);
        $this->AG->loadCSS($this->pagination_style);
        $this->postContent();
        return $this->html;
    }
}
