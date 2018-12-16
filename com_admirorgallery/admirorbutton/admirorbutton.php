<?php
/**
 * @version     5.2.0
 * @package     Admiror Gallery (plugin)
 * @subpackage  admirorbutton
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2018 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Editor Image buton
 *
 * @package Editors-xtd
 * @since 1.5
 */
class plgButtonAdmirorbutton extends JPlugin {
    protected $autoloadLanguage = true;
    /**
     * Constructor
     *
     * For php4 compatability we must not use the __constructor as a constructor for plugins
     * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
     * This causes problems with cross-referencing necessary for the observer design pattern.
     *
     * @param 	object $subject The object to observe
     * @param 	array  $config  An array that holds the plugin configuration
     * @since 1.5
     */
    public function __construct(& $subject, $config) {
        parent::__construct($subject, $config);
        $this->loadLanguage('com_admirorgallery');
        $this->loadLanguage('plg_editors-xtd_admirorbutton');   
    }

    /**
     * Display the button
     * @name 
     */
    function onDisplay($name) {
        $doc = JFactory::getDocument();
        $doc->addStyleSheet(JURI::root() . 'administrator/components/com_admirorgallery/templates/default/css/add-trigger.css');
        $doc->addScriptDeclaration("            
		function insertTriggerCode(txt) {
		    if(!txt) return;
		    jInsertEditorText(txt, '" . $name . "');
		}
		");

        $link = 'index.php?option=com_admirorgallery&amp;view=button&amp;tmpl=component&amp;e_name=' . $name;

        JHTML::_('behavior.modal');

        $button = new JObject();
        $button->set('class','btn');
        $button->set('modal', true); // modal dialog
        $button->set('link', $link); //link to open on click
        $button->set('text', JText::_('COM_ADMIRORGALLERY')); //button text
        $button->set('name', 'admirorgallery'); //div class
        $button->set('options', "{handler: 'iframe', size: {x: 400, y: 300}}"); //need to work

        return $button;
    }
}

