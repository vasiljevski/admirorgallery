<?php
namespace com_admirorgallery\admirorgallery\admirorgallery\core;

defined('_JEXEC') or die();

// Import library dependencies
jimport('joomla.event.plugin');
jimport('joomla.plugin.plugin');
jimport( 'joomla.filesystem.folder' );

class agJoomla implements CmsInterface
{
    private $doc;
    
    function __construct($document) 
    {
        $this->doc = \JFactory::getDocument();;
    }
    public function LoadClass()
    {}

    public function AddJsFile($path)
    {
        $this->doc->addScript($this->sitePath .  $this->plugin_path . $path);
    }

    public function GetFiles($path)
    {}

    public function AddToPathway($item)
    {}

    public function GetFolders($path)
    {}

    public function Text($string_id)
    {}

    public function GetAlbumPath($key)
    {}

    public function SetTitle($title)
    {}

    public function GetActiveLanguageTag($path)
    {}

    public function CreateFolder($path)
    {}

    public function AddCss($path)
    {
        $this->doc->addStyleSheet($path);
    }

    public function GetActivePage($key)
    {}

    public function BreadcrumbsNeeded()
    {}

    public function TextConcat($string_id, $value)
    {}

    public static function SecurityCheck()
    {
        defined('_JEXEC') or die();
    }

    public function AddJsDeclaration($script)
    {
        $this->doc->addScriptDeclaration($script);
    }
}

