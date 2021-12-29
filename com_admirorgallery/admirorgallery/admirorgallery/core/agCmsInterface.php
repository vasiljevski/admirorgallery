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
	public function LoadClass(): void;
	public function GetActiveLanguageTag(): string;

	// Context
	// Input
	// Document
	public function AddJsFile(string $path): void;
	public function AddJsDeclaration(string $script): void;
	public function AddCss(string $path): void;

	// Pagination
	public function GetActivePage(string $key): ?int;
	public function GetAlbumPath(string $key): ?string;

	// Breadcrumbs
	public function BreadcrumbsNeeded(): bool;

	// Public function GetPathway
	public function SetTitle(string $title): void;
	public function AddToPathway(string $item, string $link): void;

	// File I/O
	public function GetFiles(string $path): array;
	public function GetFolders(string $path): array;

	// Public function GetActiveLanguage
	public function CreateFolder(string $path): bool;

	// HTMLOutput
	public function Text(string $string_id): string;
	public function TextConcat(string $string_id, $value): string;
}
