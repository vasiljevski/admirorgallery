<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (plugin)
 * @subpackage  admirorgallery
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2017 https://www.admiror-design-studio.com All Rights Reserved.
 * @since       5.5.0
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Admiror\Plugin\Content\AdmirorGallery;

defined('_JEXEC') or die();

use Joomla\CMS\Document\Document as JDocument;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Filesystem\Folder as JFolder;
use Joomla\CMS\Language\Text as JText;

class agJoomla implements agCmsInterface
{
    private ?JDocument $doc;

    function __construct()
    {
        $this->doc = JFactory::getDocument();
    }

    public function LoadClass(): void
    {}

    public function AddJsFile(string $path): void
    {
        $this->doc->addScript($path);
    }

    public function GetFiles(string $path): array
    {
        return JFolder::files($path);
    }

    public function AddToPathway(string $item, string $link): void
    {
        try {
            JFactory::getApplication()->getPathway()->addItem($item, $link);
        } catch (Exception $e) {
            trigger_error($e);
        }
    }

    public function GetFolders(string $path): array
    {
        return JFolder::folders($path);
    }

    public function GetAlbumPath(string $key): ?string
    {
        try {
            return JFactory::getApplication()->input->getPath($key);
        } catch (Exception $e) {
            trigger_error($e);
        }
        return null;
    }

    public function SetTitle(string $title): void
    {
        JFactory::getDocument()->setTitle($title);
    }

    public function GetActiveLanguageTag(): string
    {
        return strtolower(JFactory::getLanguage()->getTag());
    }

    public function CreateFolder(string $path): bool
    {
        return JFolder::create($path, 0755);
    }

    public function AddCss(string $path): void
    {
        $this->doc->addStyleSheet($path);
    }

    public function GetActivePage(string $key): ?int
    {
        try {
            return JFactory::getApplication()->input->getInt($key);
        } catch (Exception $e) {
            trigger_error($e);
        }
        return null;
    }

    public function BreadcrumbsNeeded(): bool
    {
        try {
            $active = JFactory::getApplication()->getMenu()->getActive();
        } catch (Exception $e) {
            trigger_error($e);
        }
        return (isset($active) && $active->query['view'] == 'layout');
    }

    public function Text(string $string_id): string
    {
        return JText::_($string_id);
    }
    
    public function TextConcat(string $string_id, $value): string
    {
        return JText::sprintf($string_id, $value);
    }

    public function AddJsDeclaration(string $script): void
    {
        $this->doc->addScriptDeclaration($script);
    }
}

