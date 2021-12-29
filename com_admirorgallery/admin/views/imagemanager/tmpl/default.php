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

use Joomla\CMS\Language\Text as JText;

//Check if plugin is installed, otherwise don't show view
if (!is_dir(JPATH_SITE . '/plugins/content/admirorgallery/')) {
    return;
}
?>
<div class="row-fluid">
    <?php
    if (!$this->app->isClient('site')) {
    ?>
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
        <?php
        } else {
        ?>
        <div class="span12">
            <?php
            }
            ?>
            <div class="well well-small">
                <form action="<?php echo JRoute::_('index.php?option=com_admirorgallery&view=imagemanager'); ?>"
                      method="post"
                      name="adminForm"
                      id="adminForm"
                      enctype="multipart/form-data">

                    <input type="hidden" name="option" value="com_admirorgallery"/>
                    <input type="hidden" name="task" value=""/>
                    <input type="hidden" name="boxchecked" value="0"/>
                    <input type="hidden" name="view" value="imagemanager"/>
                    <input type="hidden" name="controller" value="imagemanager"/>
                    <input type="hidden" name="AG_itemURL" value="<?php echo $this->ag_init_itemURL; ?>"
                           id="AG_input_itemURL"/>

                    <?php
                    if (file_exists(JPATH_SITE . $this->ag_init_itemURL)) {
                        if (is_dir(JPATH_SITE . $this->ag_init_itemURL)) {
                            $ag_init_itemType = "folder";
                        } else {
                            $ag_init_itemType = "file";
                        }
                        require_once(JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_admirorgallery' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'imagemanager' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . 'default_' . $ag_init_itemType . '.php');
                    } else {
                        $ag_error[] = array(JText::_('AG_FOLDER_OR_IMAGE_NOT_FOUND'), $this->ag_init_itemURL);
                        $this->app->enqueueMessage( JText::_('AG_FOLDER_OR_IMAGE_NOT_FOUND') . '<br>' . $this->ag_init_itemURL, 'warning');
                        $ag_preview_content = '
                                    <div class="ag_screenSection_title">
                                         ' . $this->ag_init_itemURL . '
                                    </div>';
                        return;
                    }
                    ?>
                    <!--Include JavaScript-->
                    <?php require_once(JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_admirorgallery' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'imagemanager' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . 'default_script.php'); ?>
                    <!--In front end add toolbar-->
                    <?php
                    if ($this->app->isClient('site')) {
                        require_once(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'toolbar.php');
                        ?>
                        <div class="AG_border_color AG_border_width AG_toolbar">
                            <?php echo AdmirorgalleryHelperToolbar::getToolbar(); ?>
                        </div>
                    <?php } ?>
                    <!--Include panel HTML-->
                    <?php require_once(JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_admirorgallery' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'imagemanager' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . 'default_panel.php'); ?>
                </form>
            </div>
        </div>
    </div>
