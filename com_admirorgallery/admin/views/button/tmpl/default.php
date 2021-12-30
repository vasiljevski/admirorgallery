<?php
/**
 * @version     6.0.0
 * @package     Admiror.Administrator
 * @subpackage  com_admirorgallery
 * @author      Igor Kekeljevic <igor@admiror.com>
 * @author      Nikola Vasiljevski <nikola83@gmail.com>
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Filesystem\Folder as JFolder;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Uri\Uri as JUri;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Form\Form as JForm;

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/admirorgallery.php';

$doc = JFactory::getApplication()->getDocument();
$template = AdmirorGalleryHelper::getCmd('AG_template', ''); // Current template for AG Component
$item_url = AdmirorGalleryHelper::getRootPath();
$path = JURI::root(true) . "/";

$doc->addStyleSheet($path . 'administrator/components/com_admirorgallery/templates/' . $template . '/css/add-trigger.css');
$doc->addScript($path . 'plugins/content/admirorgallery/admirorgallery/AG_jQuery.js');
$doc->addScript($path . 'administrator/components/com_admirorgallery/scripts/ag_button.js');

?>
<div style="display:block">
    <form action="index.php" id="AG_form" method="post" enctype="multipart/form-data">

        <div style="float: right">
            <button class="btn" type="button"
                    onclick="AG_createTriggerCode('<?php echo JFactory::getApplication()->input->get('e_name');?>');">
                    <?php echo JText::_("AG_BUTTON_INSERT") ?>
            </button>
            <button class="btn" type="button"
                    onclick="closeAdmirorButton('<?php echo JFactory::getApplication()->input->get('e_name');?>');">
                    <?php echo JText::_("AG_BUTTON_CANCEL") ?>
            </button>
        </div>
        <br style="clear:both;"/>
        <hr/>
        <h2><?php echo JText::_("AG_FOLDERS"); ?></h2>
        <?php echo JText::_("AG_SELECT_FOLDER"); ?>&nbsp;
        <select name="ag_form_folder_name">
            <?php
            $folders = JFolder::listFolderTree(JPATH_SITE . $item_url, "");
            $item_url_strlen = strlen($item_url);
            if (!empty($folders)) {
                foreach ($folders as $folders_key => $folders_value) {
                    $folder_name = substr($folders_value['relname'], $item_url_strlen);
                    echo '<option value="' . $folder_name . '" />' . $folder_name;
                }
            }
            ?>
        </select>
        <br/>

        <p></p>
        <hr/>
        <h2><input type="CHECKBOX" id="ag_form_insertParams"
                   name="ag_form_insertParams"/> <?php echo JText::_("AG_PARAMETERS"); ?></h2>
        <div id="ag_form_params" style="display:none;">
            <?php
            $db = JFactory::getDBO();
            $query = "SELECT * FROM #__extensions WHERE (element = 'admirorgallery') AND (type = 'plugin')";
            $db->setQuery($query);
            $row = $db->loadAssoc();

            $paramsdefs = JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'button' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . 'default.xml';
            $myparams = JForm::getInstance('AG_Settings', $paramsdefs);

            $values = array('params' => json_decode($row['params']));
            $myparams->bind($values);

            $fieldSets = $myparams->getFieldsets();

            foreach ($fieldSets as $name => $fieldSet) :
                $label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_PLUGINS_' . $name . '_FIELDSET_LABEL';
                //JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));
                //echo JHtml::_('sliders.panel', JText::_($label), $name.'-options');
                if (isset($fieldSet->description) && trim($fieldSet->description)) :
                    //TODO: Fix tip
                    //echo '<p class="tip">'.$this->escape(JText::_($fieldSet->description)).'</p>';
                endif;
                ?>
                <fieldset class="panelform">
                    <?php $hidden_fields = ''; ?>
                    <ul class="adminformlist" style="list-style: none;">
                        <?php foreach ($myparams->getFieldset($name) as $field) : ?>
                            <?php if (!$field->hidden) : ?>
                                <li class="paramlist_value">
                                    <?php echo $field->label; ?>
                                    <?php echo $field->input; ?>
                                </li>
                            <?php else : $hidden_fields .= $field->input; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                    <?php echo $hidden_fields; ?>
                </fieldset>
            <?php endforeach; ?>

        </div>
    </form>
</div>
