<?php

/**
 * @version     6.0.0
 * @package     Admiror.Plugin
 * @subpackage  Content.AdmirorGallery
 * @author      Igor Kekeljevic <igor@admiror.com>
 * @author      Nikola Vasiljevski <nikola83@gmail.com>
 * @copyright   Copyright (C) 2010 - 2017 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Admiror\Plugin\Content\AdmirorGallery;

defined('_JEXEC') or die();

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Document\Document as JDocument;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Filesystem\Folder as JFolder;
use Joomla\CMS\Language\Text as JText;

/**
 * @package     Admiror\Plugin\Content\AdmirorGallery
 *
 * @since       5.5.0
 */
class Joomla implements CmsInterface
{
	/**
	 * @var JDocument|null
	 *
	 * @since 5.5.0
	 */
	private ?JDocument $doc;

	/**
	 * @var CMSApplication|\Joomla\CMS\Application\CMSApplicationInterface|null
	 *
	 * @since 5.5.0
	 */
	private ?CMSApplication $app;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		try
		{
			$this->app = JFactory::getApplication();
			$this->doc = $this->app->getDocument();
		}
		catch (\Exception $e)
		{
			trigger_error($e, E_ERROR);
		}
	}

	/**
	 * loadClass
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function loadClass(): void
	{
	}

	/**
	 * @param   string  $path Path to the file
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function addJsFile(string $path): void
	{
		$this->doc->addScript($path);
	}

	/**
	 * @param   string  $path Path to the file
	 *
	 * @return array
	 *
	 * @since 5.5.0
	 */
	public function getFiles(string $path): array
	{
		return JFolder::files($path);
	}

	/**
	 * @param   string  $item Selected item
	 * @param   string  $link Link
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function addToPathway(string $item, string $link): void
	{
		$this->app->getPathway()->addItem($item, $link);
	}

	/**
	 * @param   string  $path Folder path
	 *
	 * @return array
	 *
	 * @since 5.5.0
	 */
	public function getFolders(string $path): array
	{
		return JFolder::folders($path);
	}

	/**
	 * @param   string  $key Album path
	 *
	 * @return string|null
	 *
	 * @since 5.5.0
	 */
	public function getAlbumPath(string $key): ?string
	{
		return $this->app->input->getPath($key);
	}

	/**
	 * @param   string  $title Title of the article
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function setTitle(string $title): void
	{
		$this->doc->setTitle($title);
	}

	/**
	 *
	 * @return string
	 *
	 * @since 5.5.0
	 */
	public function getActiveLanguageTag(): string
	{
		return strtolower(JFactory::getLanguage()->getTag());
	}

	/**
	 * @param   string  $path Path where to create a folder
	 *
	 * @return boolean
	 *
	 * @since 5.5.0
	 */
	public function createFolder(string $path): bool
	{
		return JFolder::create($path);
	}

	/**
	 * @param   string  $path Style file path
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function addCss(string $path): void
	{
		$this->doc->addStyleSheet($path);
	}

	/**
	 * @param   string  $key Key
	 *
	 * @return integer|null
	 *
	 * @since 5.5.0
	 */
	public function getActivePage(string $key): ?int
	{
		return $this->app->input->getPath($key);
	}

	/**
	 *
	 * @return boolean
	 *
	 * @since 5.5.0
	 */
	public function isBreadcrumbsNeeded(): bool
	{
		$active = $this->app->getMenu()->getActive();

		return (isset($active) && $active->query['view'] == 'layout');
	}

	/**
	 * @param   string  $id Text id
	 *
	 * @return string
	 *
	 * @since 5.5.0
	 */
	public function text(string $id): string
	{
		return JText::_($id);
	}

	/**
	 * @param   string  $id    Text id
	 * @param   string  $value Value
	 *
	 * @return string
	 *
	 * @since 5.5.0
	 */
	public function textConcat(string $id, string $value): string
	{
		return JText::sprintf($id, $value);
	}

	/**
	 * @param   string  $script Script to add
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function addJsDeclaration(string $script): void
	{
		$this->doc->addScriptDeclaration($script);
	}
}
