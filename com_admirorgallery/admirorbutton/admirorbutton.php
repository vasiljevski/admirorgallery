<?php

/**
 * @version     6.0.0
 * @package     Admiror Gallery (plugin)
 * @subpackage  admirorbutton
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2021 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri as JUri;

/**
 * Editor Image button
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
     * Display the button.
     *
     * @param   string   $name    The name of the button to display.
     * @param   string   $asset   The name of the asset being edited.
     * @param   integer  $author  The id of the author owning the asset being edited.
     *
     * @return  CMSObject|false
     *
     * @since   5.5.0
     */
    //function onDisplay(string $name): JObject
    public function onDisplay($name, $asset, $author)
    {
        $app       = Factory::getApplication();
        $doc       = $app->getDocument();
        $user      = $app->getIdentity();
        $extension = $app->input->get('option');

        // For categories we check the extension (ex: component.section)
        if ($extension === 'com_categories') {
            $parts     = explode('.', $app->input->get('extension', 'com_content'));
            $extension = $parts[0];
        }

        $asset = $asset !== '' ? $asset : $extension;

        if (
            $user->authorise('core.edit', $asset)
            || $user->authorise('core.create', $asset)
            || (count($user->getAuthorisedCategories($asset, 'core.create')) > 0)
            || ($user->authorise('core.edit.own', $asset) && $author === $user->id)
            || (count($user->getAuthorisedCategories($extension, 'core.edit')) > 0)
            || (count($user->getAuthorisedCategories($extension, 'core.edit.own')) > 0 && $author === $user->id)
        ) {
            $doc->addStyleSheet(JUri::root() . 'administrator/components/com_admirorgallery/templates/default/css/add-trigger.css');
            $doc->addScriptDeclaration("            
            function insertTriggerCode(txt) {
                if(!txt) return;
                jInsertEditorText(txt, '" . $name . "');
            }
            ");

            $link = 'index.php?option=com_admirorgallery&amp;view=button&amp;tmpl=component&amp;e_name=' . $name;

            $button = new CMSObject();
            $button->modal   = true;
            $button->link    = $link;
            $button->text    = Text::_('COM_ADMIRORGALLERY');
            $button->name    = $this->_type . '_' . $this->_name;
            $button->icon    = 'pictures';
            $button->iconSVG = '<svg width="24" height="24" viewBox="0 0 512 512"><path d="M464 64H48C21.49 64 0 85.49 0 112v288c0 26.51 21.49 48'
                . ' 48 48h416c26.51 0 48-21.49 48-48V112c0-26.51-21.49-48-48-48zm-6 336H54a6 6 0 0 1-6-6V118a6 6 0 0 1 6-6h404a6 6'
                . ' 0 0 1 6 6v276a6 6 0 0 1-6 6zM128 152c-22.091 0-40 17.909-40 40s17.909 40 40 40 40-17.909 40-40-17.909-40-40-40'
                . 'zM96 352h320v-80l-87.515-87.515c-4.686-4.686-12.284-4.686-16.971 0L192 304l-39.515-39.515c-4.686-4.686-12.284-4'
                . '.686-16.971 0L96 304v48z"></path></svg>';
            $button->options = [
                'height'          => '300px',
                'width'           => '400px',
                'bodyHeight'      => '70',
                'modalWidth'      => '80',
                'tinyPath'        => $link,
                'handler'         => 'iframe',
            ];

            return $button;
        }
        return false;
    }
}
