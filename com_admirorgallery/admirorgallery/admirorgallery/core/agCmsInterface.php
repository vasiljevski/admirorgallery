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
interface agCmsInterface
{
	public function loadClass(): void;
	public function getActiveLanguageTag(): string;

	// Context
	// Input
	// Document
	public function addJsFile(string $path): void;
	public function addJsDeclaration(string $script): void;
	public function addCss(string $path): void;

	// Pagination
	public function getActivePage(string $key): ?int;
	public function getAlbumPath(string $key): ?string;

	// Breadcrumbs
	public function isBreadcrumbsNeeded(): bool;

	// Public function GetPathway
	public function setTitle(string $title): void;
	public function addToPathway(string $item, string $link): void;

	// File I/O
	public function getFiles(string $path): array;
	public function getFolders(string $path): array;

	// Public function GetActiveLanguage
	public function createFolder(string $path): bool;

	// HTMLOutput
	public function text(string $id): string;
	public function textConcat(string $id, string $value): string
}
