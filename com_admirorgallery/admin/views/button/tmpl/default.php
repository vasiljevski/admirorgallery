<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();
jimport('joomla.filesystem.folder');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/admirorgallery.php';

$AG_template = AdmirorGalleryHelper::getCmd('AG_template', ''); // Current template for AG Component
$ag_init_itemURL = AdmirorGalleryHelper::getRootPath();

?>
<script type="text/javascript"
        src="<?php echo JURI::root() . 'plugins/content/admirorgallery/admirorgallery/AG_jQuery.js'; ?>"></script>
<link rel="stylesheet"
      href="<?php echo JURI::root() . 'administrator/components/com_admirorgallery/templates/' . $AG_template . '/css/add-trigger.css'; ?>"
      type="text/css"/>

<div style="display:block">
    <form action="index.php" id="AG_form" method="post" enctype="multipart/form-data">

        <div style="float: right">
            <button class="btn" type="button"
                    onclick="AG_createTriggerCode();window.parent.SqueezeBox.close();"><?php echo JText::_("AG_BUTTON_INSERT") ?></button>
            <button class="btn" type="button"
                    onclick="window.parent.SqueezeBox.close();"><?php echo JText::_("AG_BUTTON_CANCEL") ?></button>
        </div>
        <br style="clear:both;"/>
        <hr/>
        <h2><?php echo JText::_("AG_FOLDERS"); ?></h2>
        <?php echo JText::_("AG_SELECT_FOLDER"); ?>&nbsp;
        <select name="AG_form_folderName">
            <?php
            $ag_folders = JFolder::listFolderTree(JPATH_SITE . $ag_init_itemURL, "");
            $ag_init_itemURL_strlen = strlen($ag_init_itemURL);
            if (!empty($ag_folders)) {
                foreach ($ag_folders as $ag_folders_key => $ag_folders_value) {
                    $ag_folderName = substr($ag_folders_value['relname'], $ag_init_itemURL_strlen);
                    echo '<option value="' . $ag_folderName . '" />' . $ag_folderName;
                }
            }
            ?>
        </select>
        <br/>

        <p></p>
        <hr/>
        <h2><input type="CHECKBOX" id="AG_form_insertParams"
                   name="AG_form_insertParams"/> <?php echo JText::_("AG_PARAMETERS"); ?></h2>
        <div id="AG_form_params" style="display:none;">
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
        <script type="text/javascript">
            AG_jQuery(document).ready(function () {

                AG_jQuery(".ag_button_folderName").click(function (event) {
                    event.preventDefault();
                });

                AG_jQuery('#AG_form_insertParams').change(function () {
                    if (AG_jQuery('#AG_form_insertParams').attr('checked')) {
                        AG_jQuery('#AG_form_params').fadeIn("slow");
                    } else {
                        AG_jQuery('#AG_form_params').fadeOut("slow");
                    }
                });

            });

            function AG_createTriggerCode() {

                var AG_params = "";

                var AG_fields = AG_jQuery(".paramlist_value input").serializeArray();
                AG_jQuery.each(AG_fields, function (i, field) {
                    AG_params += " " + field.name.substring(7, (field.name.length - 1)) + '="' + field.value + '"';
                });
                var AG_fields = AG_jQuery(".paramlist_value select").serializeArray();
                AG_jQuery.each(AG_fields, function (i, field) {
                    AG_params += " " + field.name.substring(7, (field.name.length - 1)) + '="' + field.value + '"';
                });
                if (AG_jQuery('#AG_form_insertParams').attr('checked')) {
                    var AG_triggerCode = '{AG' + AG_params + '}' + AG_jQuery('select[name="AG_form_folderName"]').val() + '{/AG}';
                } else {
                    var AG_triggerCode = '{AG}' + AG_jQuery('select[name="AG_form_folderName"]').val() + '{/AG}';
                }
                window.parent.insertTriggerCode(AG_triggerCode);

            }
        </script>

    </form>
</div>
