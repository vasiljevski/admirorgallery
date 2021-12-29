<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic <igor@admiror.com>
 * @author      Nikola Vasiljevski <nikola83@gmail.com>
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Form\Form as JForm;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Uri\Uri as JURI;

//Check if plugin is installed, otherwise don't show view
if (!is_dir(JPATH_SITE . '/plugins/content/admirorgallery/')) {
    return;
}
$db = JFactory::getDBO();
$query = "SELECT * FROM #__extensions WHERE (element = 'admirorgallery') AND (type = 'plugin')";
$db->setQuery($query);
$row = $db->loadAssoc();
//print_r($paramsdata);
$paramsdefs = JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'button' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . 'default.xml';
//$paramsdefs = JPATH_SITE.'/plugins/content/admirorgallery/admirorgallery.xml';
$myparams = JForm::getInstance('AG_Settings', $paramsdefs);

$values = array('params' => json_decode($row['params']));
$myparams->bind($values);
?>

<script type="text/javascript">
    Joomla.submitbutton = function (task) {
        Joomla.submitform(task, document.getElementById('component-form'));
    }
</script>
<form action=" <?php echo JURI::getInstance()->toString(); ?>" id="component-form" method="post" name="adminForm"
      autocomplete="off" class="form-validate form-horizontal">
    <div class="row-fluid">
        <!-- Begin Sidebar -->
        <div id="sidebar" class="span2">
            <div class="sidebar-nav">
                <div class="well well-small">
                    <div class="module-title nav-header"><?php echo JText::_('COM_ADMIRORGALLERY_MENU'); ?></div>
                    <?php echo $this->sidebar; ?>
                </div>
            </div>
            <div class="well well-small">
                <div class="module-title nav-header"> <?php echo JText::_('AG_VERSION'); ?> </div>
                <ul class="unstyled list-striped">
                    <?php
                    $ag_admirorgallery_xml = simplexml_load_file(JPATH_COMPONENT_ADMINISTRATOR . '/com_admirorgallery.xml');
                    if ($ag_admirorgallery_xml) {
                        echo '<li>' . JText::_('COM_ADMIRORGALLERY_COMPONENT_VERSION') . '&nbsp;' . $ag_admirorgallery_xml->version . "</li>";
                        echo '<li>' . JText::_('COM_ADMIRORGALLERY_PLUGIN_VERSION') . '&nbsp;' . $ag_admirorgallery_xml->plugin_version . "</li>";
                        echo '<li>' . JText::_('COM_ADMIRORGALLERY_BUTTON_VERSION') . '&nbsp;' . $ag_admirorgallery_xml->button_version . "</li>";
                    }
                    ?>
                </ul>
            </div>
        </div>
        <!-- End Sidebar -->
        <div class="span5">
            <ul class="nav nav-tabs" id="configTabs">
                <?php
                $fieldSets = $myparams->getFieldsets();
                foreach ($fieldSets as $name => $fieldSet) :
                    $label = empty($fieldSet->label) ? 'COM_CONFIG_' . $name . '_FIELDSET_LABEL' : $fieldSet->label;
                    ?>
                    <li><a href="#<?php echo $name; ?>" data-toggle="tab"><?php echo JText::_($label); ?></a></li>
                <?php
                endforeach;
                ?>
            </ul>
            <div class="tab-content">
                <?php
                $fieldSets = $myparams->getFieldsets();
                foreach ($fieldSets as $name => $fieldSet) :
                    ?>
                    <div class="tab-pane" id="<?php echo $name; ?>">
                        <?php
                        if (isset($fieldSet->description) && !empty($fieldSet->description)) :
                            echo '<p class="tab-description">' . JText::_($fieldSet->description) . '</p>';
                        endif;
                        foreach ($myparams->getFieldset($name) as $field):
                            ?>
                            <div class="control-group">
                                <?php if (!$field->hidden && $name != "permissions") : ?>
                                    <div class="control-label">
                                        <?php echo $field->label; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="<?php if ($name != "permissions") : ?>controls<?php endif; ?>">
                                    <?php echo $field->input; ?>
                                </div>
                            </div>
                        <?php
                        endforeach;
                        ?>
                    </div>
                <?php
                endforeach;
                ?>
            </div>
        </div>
        <div class="span5">
            <div class="well well-small">
                <?php echo JText::_('COM_ADMIRORGALLERY_DESCRIPTION'); ?>
            </div>
        </div>
    </div>
    <div>
        <input type="hidden" name="pressbutton" value="" id="pressbutton"/>
        <input type="hidden" name="controller" value="admirorgallery"/>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="option" value="com_admirorgallery"/>
        <input type="hidden" name="view" value=""/>
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
<script type="text/javascript">
    jQuery('#configTabs a:first').tab('show'); // Select first tab
</script>
