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

namespace Admiror\Plugin\Content\AdmirorGallery;

/**
 * Provides support for common template functions
 *
 * @since 5.5.0
 */

class Template
{
	/**
	 * Output HTML
	 *
	 * @var string
	 * @since 5.5.0
	 */
	private string $html = '';

	/**
	 * Gallery instance reference
	 *
	 * @var agGallery
	 * @since 5.5.0
	 */
	private agGallery $AG;

	/**
	 * Album support
	 *
	 * @var boolean
	 * @since 5.5.0
	 */
	private bool $albumSupport;

	/**
	 * Root folder of the template
	 *
	 * @var string
	 * @since 5.5.0
	 */
	private string $rootPath;

	/**
	 * @var string
	 * @since 5.5.0
	 */
	private string $style;

	/**
	 * @var string
	 * @since 5.5.0
	 */
	private string $paginationStyle;

	/**
	 * @var string
	 * @since 5.5.0
	 */
	private string $albumStyle;

	/**
	 * @var string
	 * @since 5.5.0
	 */
	private string $defaultThumb;

	/**
	 * Template constructor.
	 *
	 * @param   agGallery  $ag               Gallery reference
	 * @param   string     $template         Template name
	 * @param   string     $albumStyle       Album style
	 * @param   string     $paginationStyle  Pagination style
	 * @param   string     $defaultThumb     Default thumbnail to be used
	 *
	 * @since 5.5.0
	 */
	public function __construct(agGallery $ag,
		string $template = 'template.css',
		string $albumStyle = 'albums/albums.css',
		string $paginationStyle = 'pagination/pagination.css',
		string $defaultThumb = '../../albums/album.png'
	)
	{
		$this->AG = $ag;
		$this->albumSupport = (bool) $ag->params['albumUse'];
		$this->rootPath = $ag->currTemplateRoot;
		$this->style = $this->rootPath . $template;
		$this->albumStyle = $albumStyle;
		$this->paginationStyle = $paginationStyle;
		$this->defaultThumb = $defaultThumb;
	}

	/**
	 * Add album support
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function addAlbumSupport(): void
	{
		// Support for Albums
		if (!empty($this->AG->folders) && $this->albumSupport)
		{
			$this->AG->loadCSS($this->albumStyle);
			$this->html .= '<h1>' . $this->AG->getText("AG_ALBUMS") . '</h1>' . "\n";
			$this->html .= $this->AG->writeFolderThumb($this->defaultThumb, $this->AG->params['thumbHeight']);
		}
	}

	/**
	 * Load CSS style file
	 *
	 * @param   string $filePath File path to the CSS file
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function loadStyle(string $filePath): void
	{
		$this->AG->loadCSS($filePath);
	}

	/**
	 * Load multiple CSS style files
	 *
	 * @param   array $filesPath Array of file paths of CSS files
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function loadStyles(array $filesPath): void
	{
		foreach ($filesPath as $file)
		{
			$this->AG->loadCSS($file);
		}
	}

	/**
	 * Load JavaScript file
	 *
	 * @param   string $filePath File path to the JS file
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function loadScript(string $filePath): void
	{
		$this->AG->loadJS($filePath);
	}

	/**
	 * Inserts JavaScript code
	 *
	 * @param   string $script Script to be inserted
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function insertScript(string $script): void
	{
		$this->AG->insertJSCode($script);
	}

	/**
	 * Load multiple JavaScript files
	 *
	 * @param   array $filesPath Array of file paths of CSS files
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function loadScripts(array $filesPath): void
	{
		foreach ($filesPath as $file)
		{
			$this->AG->loadJS($file);
		}
	}

	/**
	 * Loads scripts needed for Popups, before gallery is created
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function preContent(): void
	{
		$this->html .= $this->AG->initPopup();
	}

	/**
	 * Loads scripts needed for Popups, after gallery is created
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function postContent(): void
	{
		$this->html .= $this->AG->endPopup();
	}

	/**
	 * Append content
	 *
	 * @param   string $content Conntent to be added
	 *
	 * @return void
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
		$this->AG->getGalleryID() . ' a.AG_pagin_next{color:' . $this->AG->params['foregroundColor'] . '}';
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
		$this->AG->loadCSS($this->style);
		$this->AG->loadCSS($this->paginationStyle);
		$this->postContent();

		return $this->html;
	}
}
