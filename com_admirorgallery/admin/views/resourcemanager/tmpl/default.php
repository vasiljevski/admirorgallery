<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2020 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;

//Check if plugin is installed, otherwise don't show view
if (!is_dir(JPATH_SITE . '/plugins/content/admirorgallery/')) {
    return;
}
?>
<div class="row-fluid">
    <div class="span2">
        <div class="well well-small">
            <div class="module-title nav-header"><?php echo JText::_('COM_ADMIRORGALLERY_MENU'); ?></div>
            <?php echo $this->sidebar; ?>
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
    <div class="span10">
        <div class="well well-small">
            <form action="<?php echo JRoute::_('index.php?option=com_admirorgallery&task=' . $this->ag_resource_type); ?>"
                  method="post"
                  name="adminForm"
                  id="adminForm"
                  enctype="multipart/form-data">

                <input type="hidden" name="option" value="com_admirorgallery"/>
                <input type="hidden" name="task" value=""/>
                <input type="hidden" name="boxchecked" value="0"/>
                <input type="hidden" name="view" value="resourcemanager"/>
                <input type="hidden" name="controller" value="resourcemanager"/>
                <input type="hidden" name="AG_resourceType" value="<?php echo $this->ag_resource_type; ?>"/>
                <?php echo JHTML::_('form.token'); ?>

                <script type="text/javascript">
                    AG_jQuery(function () {
                        AG_jQuery(".ag_title_link").click(function (e) {
                            e.preventDefault();
                            if (AG_jQuery(this).closest("tr").find('input:checkbox').attr("checked") == true) {
                                AG_jQuery(this).closest("tr").find('input:checkbox').attr("checked", false);
                            } else {
                                AG_jQuery(this).closest("tr").find('input:checkbox').attr("checked", true);
                            }
                        });
                    });//AG_jQuery
                </script>
                <div>
                    <p><?php echo JText::_('AG_SELECT_TEMPLATE_TO_INSTALL'); ?>
                        [ <b><?php echo JText::_('AG_MAX'); ?>
                            <?php echo(JComponentHelper::getParams('com_media')->get('upload_maxsize', 0)) ?> MB</b> ]:
                        <input type="file" name="AG_fileUpload"/></p>
                </div>
                <table class="table table-striped" id="categoryList" cellspacing="1">
                    <thead>
                    <tr>
                        <th width="1%" class="nowrap center">#</th>
                        <th class="center">
                            <?php echo JHtml::_('grid.checkall'); ?>
                        </th>
                        <th width="1%" class="nowrap center">
                            <?php echo JText::_('AG_TITLE'); ?></th>
                        <th width="1%" class="nowrap center">
                            <?php echo JText::_('AG_ID'); ?></th>
                        <th class="center hidden-phone">
                            <?php echo JText::_('AG_DESCRIPTION'); ?></th>
                        <th class="center">
                            <?php echo JText::_('AG_VERSION'); ?></th>
                        <th class="center hidden-phone">
                            <?php echo JText::_('AG_DATE'); ?></th>
                        <th class="center hidden-phone">
                            <?php echo JText::_('AG_AUTHOR'); ?></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php
                    $total = count($this->ag_resourceManager_installed);
                    $pageNav = new JPagination($total, $this->limitstart, $this->limit);
                    if ($this->limit == "all") {
                        $this->limit = $total;
                    }

                    if ($total > 0) {
                        foreach ($this->ag_resourceManager_installed as $ag_resourceManager_Key => $ag_resourceManager_Value) {
                            if ($ag_resourceManager_Key >= $this->limitstart && $ag_resourceManager_Key < ($this->limitstart + $this->limit)) {

                                // TEMPLATE DETAILS PARSING
                                $ag_resourceManager_id = $ag_resourceManager_Value;
                                $ag_resourceManager_name = $ag_resourceManager_id;
                                $ag_resourceManager_creationDate = JText::_("AG_UNDATED");
                                $ag_resourceManager_author = JText::_("AG_UNKNOWN_AUTHOR");
                                $ag_resourceManager_version = JText::_("AG_UNKNOWN_VERSION");
                                $ag_resourceManager_description = JText::_("AG_NO_DESCRIPTION");

                                if (JFIle::exists(JPATH_SITE . '/plugins/content/admirorgallery/admirorgallery/' . $this->ag_resource_type . '/' . $ag_resourceManager_id . '/details.xml')) {// N U
                                    $ag_resourceManager_xml = simplexml_load_file(JPATH_SITE . '/plugins/content/admirorgallery/admirorgallery/' . $this->ag_resource_type . '/' . $ag_resourceManager_id . '/details.xml');
                                    $ag_resourceManager_name = $ag_resourceManager_xml->name;
                                    $ag_resourceManager_creationDate = $ag_resourceManager_xml->creationDate;
                                    $ag_resourceManager_author = $ag_resourceManager_xml->author;
                                    $ag_resourceManager_version = $ag_resourceManager_xml->version;
                                    $ag_resourceManager_description = $ag_resourceManager_xml->description;
                                }
                                ?>
                                <tr>
                                    <td class="order nowrap center">
                                        <?php echo($ag_resourceManager_Key + 1); ?>
                                    </td>
                                    <td class="center">
                                        <?php echo JHtml::_('grid.id', ($ag_resourceManager_Key + 1), $ag_resourceManager_id); ?>
                                    </td>
                                    <td class="nowrap">

                                            <span class="editlinktip hasTip"
                                                  title="<?php echo $ag_resourceManager_name . '::<img border=&quot;1&quot; src=&quot;' . JURI::root() . 'plugins/content/admirorgallery/admirorgallery/' . $this->ag_resource_type . '/' . $ag_resourceManager_id . '/preview.jpg' . '&quot; name=&quot;imagelib&quot; alt=&quot;&quot; width=&quot;206&quot; height=&quot;145&quot; />' ?>">
                                                <a href="#" class="ag_title_link">
                                                    <?php echo $ag_resourceManager_name; ?>
                                                </a>

                                            </span>
                                    </td>
                                    <td class="nowrap">
                                        <?php echo $ag_resourceManager_id; ?>
                                    </td>
                                    <td class="hidden-phone">
                                        <?php echo $ag_resourceManager_description; ?>
                                    </td>
                                    <td class="center nowrap">
                                        <?php echo $ag_resourceManager_version; ?>
                                    </td>
                                    <td class="nowrap hidden-phone">
                                        <?php echo $ag_resourceManager_creationDate; ?>
                                    </td>
                                    <td class="hidden-phone">
                                        <?php echo $ag_resourceManager_author; ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        }//foreach ($ag_resourceManager_installed as $ag_resourceManager_Key => $ag_resourceManager_Value)
                    }//if(!empty($ag_resourceManager_installed))
                    ?>
                    <tfoot>
                    <tr>
                        <td align="center" colspan="9">
                            <?php echo $pageNav->getListFooter(); ?>
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </form>
        </div>
    </div>
</div>
