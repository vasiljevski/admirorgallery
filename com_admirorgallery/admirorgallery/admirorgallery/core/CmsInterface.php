<?php
/**
 * @version     6.0.0
 * @package     Admiror.Plugin
 * @subpackage  Content.AdmirorGallery
 * @author      Igor Kekeljevic <igor@admiror.com>
 * @author      Nikola Vasiljevski <nikola83@gmail.com>
 * @copyright   Copyright (C) 2010 - 2017 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @since       5.5.0
 */

namespace Admiror\Plugin\Content\AdmirorGallery;

/**
 * CmsInterface
 *
 * @since 5.5.0
 */
interface CmsInterface
{
	/**
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function loadClass(): void;

	/**
	 *
	 * @return string
	 *
	 * @since 5.5.0
	 */
	public function getActiveLanguageTag(): string;

	/**
	 * Context
	 * Input
	 * Document
	 */

	/**
	 * @param   string  $path Path to JavaScript file
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function addJsFile(string $path): void;

	/**
	 * @param   string  $script Script
	 *
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function addJsDeclaration(string $script): void;

	/**
	 * @param   string  $path Path to CSS file
	 *
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function addCss(string $path): void;

	// Pagination

	/**
	 * @param   string  $key Key
	 *
	 * @return integer|null
	 *
	 * @since 5.5.0
	 */
	public function getActivePage(string $key): ?int;

	/**
	 * @param   string  $key Key
	 *
	 * @return string|null
	 *
	 * @since 5.5.0
	 */
	public function getAlbumPath(string $key): ?string;

	// Breadcrumbs

	/**
	 *
	 * @return boolean
	 *
	 * @since 5.5.0
	 */
	public function isBreadcrumbsNeeded(): bool;

	// Public function GetPathway

	/**
	 * @param   string  $title Title to be set
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function setTitle(string $title): void;

	/**
	 * @param   string  $item Item
	 * @param   string  $link Link
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function addToPathway(string $item, string $link): void;

	// File I/O

	/**
	 * @param   string  $path Path
	 *
	 * @return array
	 *
	 * @since 5.5.0
	 */
	public function getFiles(string $path): array;

	/**
	 * @param   string  $path Path
	 *
	 * @return array
	 *
	 * @since 5.5.0
	 */
	public function getFolders(string $path): array;

	// Public function GetActiveLanguage

	/**
	 * @param   string  $path Path
	 *
	 * @return boolean
	 *
	 * @since 5.5.0
	 */
	public function createFolder(string $path): bool;

	// HTMLOutput

	/**
	 * @param   string  $id Text id
	 *
	 * @return string
	 *
	 * @since 5.5.0
	 */
	public function text(string $id): string;

	/**
	 * @param   string  $id    Text id
	 * @param   string  $value Value to add
	 *
	 * @return string
	 *
	 * @since 5.5.0
	 */
	public function textConcat(string $id, string $value): string;
}
