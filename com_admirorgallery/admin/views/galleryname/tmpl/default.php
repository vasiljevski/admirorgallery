<?php
/*------------------------------------------------------------------------
# com_admirorgallery - Admiror Gallery Component
# ------------------------------------------------------------------------
# author   Igor Kekeljevic & Nikola Vasiljevski
# copyright Copyright (C) 2014 admiror-design-studio.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.admiror-design-studio.com/joomla-extensions
# Technical Support:  Forum - http://www.vasiljevski.com/forum/index.php
# Version: 5.0.0
-------------------------------------------------------------------------*/
defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.filesystem.folder' );
$AG_template = JRequest::getVar('AG_template'); // Current template for AG Component
// GET ROOT FOLDER
$plugin = JPluginHelper::getPlugin('content', 'admirorgallery');
$pluginParams = new JRegistry($plugin->params);
$ag_rootFolder = $pluginParams->get('rootFolder', '/images/sampledata/');
$ag_init_itemURL = $ag_rootFolder;
?>
<script type="text/javascript" src="<?php echo JURI::root() . 'plugins/content/admirorgallery/admirorgallery/AG_jQuery.js'; ?>"></script>
<link rel="stylesheet" href="<?php echo JURI::root() . 'administrator/components/com_admirorgallery/templates/' . $AG_template . '/css/add-trigger.css'; ?>" type="text/css" />

<form action="index.php" id="AG_form" method="post" enctype="multipart/form-data">
    <div style="float: right">
        <button class="btn" type="button" onclick="AG_getGalleryName();window.parent.SqueezeBox.close();"><?php echo JText::_('Insert') ?></button>
        <button class="btn" type="button" onclick="window.parent.SqueezeBox.close();"><?php echo JText::_('Cancel') ?></button>
    </div>
    <br style="clear:both;"/>
    <hr />
    <h2><?php echo JText::_("AG_FOLDERS"); ?></h2>
<?php
$ag_folders = JFolder::listFolderTree(JPATH_SITE . $ag_init_itemURL, ".");

$ag_init_itemURL_strlen = strlen($ag_init_itemURL);

if (!empty($ag_folders)) {
    foreach ($ag_folders as $ag_folders_key => $ag_folders_value) {
        $ag_folderName = substr($ag_folders_value['relname'], $ag_init_itemURL_strlen);
        $ag_folderName = str_replace(array('/', '\\'), '/', $ag_folderName);
        echo '<input type="radio" name="AG_form_folderName" value="' . $ag_folderName . '" /> ' . $ag_folderName . '<br />';
    }
}
?>
    <p> </p>
    <script type="text/javascript">
        AG_jQuery(document).ready(function() {
            AG_jQuery(".ag_button_folderName").click(function(event) {
                event.preventDefault();
            });
            AG_jQuery('input[name="AG_form_folderName"]')[0].checked = true;
        });
        function AG_getGalleryName(){

            window.parent.document.getElementById('jform_params_galleryName').value = AG_jQuery('input[name="AG_form_folderName"]:checked').val();
        }
    </script>
</form>



