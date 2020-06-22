<?php

/**
 * @version     6.0.0
 * @package     Admiror Gallery (plugin)
 * @subpackage  admirorbutton
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2020 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Editor Admiror Gallery button
 *
 * @package Editors-xtd
 * @since 1.5
 */
class plgButtonAdmirorbutton extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Constructor
     *
     * For php4 compatibility we must not use the __constructor as a constructor for plugins
     * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
     * This causes problems with cross-referencing necessary for the observer design pattern.
     *
     * @param 	object $subject The object to observe
     * @param 	array  $config  An array that holds the plugin configuration
     * @since 1.5
     */
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage('com_admirorgallery');
        $this->loadLanguage('plg_editors-xtd_admirorbutton');
    }

    /**
     * Display the button
     *
     * @param string $name The name of the button to add
     *
     * @return  CMSObject  The button options as JObject
     *
     * @throws Exception
     * @since   1.5
     */
    public function onDisplay($name)
    {
        $doc =  Factory::getApplication()->getDocument();
        $doc->addStyleSheet(JURI::root() . 'administrator/components/com_admirorgallery/templates/default/css/add-trigger.css');
        $doc->addScriptDeclaration("            
                                        function insertTriggerCode(txt) {
                                            if(!txt) return;
                                            jInsertEditorText(txt, '" . $name . "');
                                        }
                                      ");

        $link = 'index.php?option=com_admirorgallery&amp;view=button&amp;tmpl=component&amp;e_name=' . $name;

        $button = new CMSObject();
        $button->class = 'btn';
        $button->modal = true;
        $button->link = $link;
        $button->text = Text::_('COM_ADMIRORGALLERY');
        $button->name = 'admirorgallery';
        $button->iconSVG = '<svg viewBox="0 0 32 32" width="24" height="24"><path d="M4 8v20h28v-20h-28zM30 24.667l-4-6.667-4.533 3.778-3.46'
                . '7-5.778-12 10v-16h24v14.667zM8 15c0-1.657 1.343-3 3-3s3 1.343 3 3v0c0 1.657-1.343 3-3 3s-3-1.343-3-3v0zM28 4h-'
                . '28v20h2v-18h26z"></path></svg>';
        $button->options = [
                'height'     => '300px',
                'width'      => '400px',
            ];
        return $button;
    }
}
