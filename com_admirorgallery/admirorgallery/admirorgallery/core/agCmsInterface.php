<?php
/**
 * @version     5.5.0
 * @package     Admiror Gallery (plugin)
 * @subpackage  admirorgallery
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2017 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/**
 * CmsInterface
 *
 * @since 5.5.0
 */
interface agCmsInterface
{
    public static function SecurityCheck(): void;
    public function LoadClass(): void;
    public function GetActiveLanguageTag(): string;
    //Context
    //Input
    //Document
    public function AddJsFile(string $path): void;
    public function AddJsDeclaration(string $script): void;
    public function AddCss(string $path): void;
    //Pagination
    public function GetActivePage(string $key): ?int;
    public function GetAlbumPath(string $key): ?string;
    //Breadcrumbs
    public function BreadcrumbsNeeded(): bool;
    //public function GetPathway
    public function SetTitle(string $title): void;
    public function AddToPathway(string $item, string $link): void;
    //File I/O
    public function GetFiles(string $path): array;
    public function GetFolders(string $path): array;
    //public function GetActiveLanguage
    public function CreateFolder(string $path): bool;
    //HTMLOutput
    public function Text(int $string_id): string;
    public function TextConcat(string $string_id, $value): string;
}
