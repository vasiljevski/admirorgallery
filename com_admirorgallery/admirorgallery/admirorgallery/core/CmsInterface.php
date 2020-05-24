<?php
namespace com_admirorgallery\admirorgallery\admirorgallery\core;

interface CmsInterface
{
    public static function SecurityCheck(): void;
    public function LoadClass(): void;
    public function GetActiveLanguageTag(string $path): string;
    //Context
    //Input
    //Document
    public function AddJsFile(string $path): void;
    public function AddJsDeclaration(string $script): void;
    public function AddCss(string $path): void;
    //Pagination
    public function GetActivePage(string $key): int;
    public function GetAlbumPath(string $key): string;
    //Breadcrumbs
    public function BreadcrumbsNeeded(): bool;
    //public function GetPathway
    public function SetTitle(string $title): void;
    public function AddToPathway(string $item): void;
    //File I/O
    public function GetFiles(string $path): array;
    public function GetFolders(string $path): array;
    //public function GetActiveLanguage
    public function CreateFolder(string $path): bool;
    //HTMLOutput
    public function Text(int $string_id): string;
    public function TextConcat(int $string_id, int $value): string;
    
}

