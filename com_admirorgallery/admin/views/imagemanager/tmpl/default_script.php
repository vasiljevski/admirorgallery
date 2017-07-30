<?php
/**
 * @version     5.1.2
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2017 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();
?>
<script type="text/javascript">

    var ag_init_itemURL='<?php echo $this->ag_init_itemURL; ?>';
    var ag_init_itemType='<?php echo $ag_init_itemType; ?>';

<?php if ($this->ag_front_end == 'true') { ?>
        Joomla.submitbutton = function(pressbutton) {
            AG_jQuery('input[name="task"]').val(pressbutton);
            AG_jQuery('form[id="adminForm"]').submit();
        };
<?php } ?>
    function basename(path) {
        return path.replace(/\\/g,"/").replace( /.*\//, "" );
    }

    function dirname(path) {
        return path.replace(/\\/g,"/").replace(/\/[^\/]*$/, "")+"/";
    }

    function ag_folder_selected(itemURL){
        AG_jQuery('input[name="AG_itemURL"]').val(itemURL);
        AG_jQuery('input[name="task"]').val("AG_imgMan_renderFolder");
        AG_jQuery('form[id="adminForm"]').submit();
    }

    function ag_file_selected(itemURL){
        AG_jQuery('input[name="AG_itemURL"]').val(itemURL);
        AG_jQuery('input[name="task"]').val("AG_imgMan_renderFile");
        AG_jQuery('form[id="adminForm"]').submit();
    }

    AG_jQuery(function(){

        // Binding event to Folder Add
        AG_jQuery("#AG_folder_add a").click(function(e) {
            e.preventDefault();        
            AG_jQuery("#AG_folder_add").prepend("<input type=\'text\' class=\'AG_input\' name=\'AG_addFolders[]\'/>");
            if(!AG_jQuery("#ag_create_new_folder").length)
            {
                AG_jQuery("#AG_folder_add").append('<a href="#" id="ag_create_new_folder" class="AG_common_button"><span><span><?php echo JText::_('COM_ADMIRORGALLERY_TOOLBAR_APPLY'); ?></span></span></a>');
                AG_jQuery("#AG_folder_add").append('<a href="#" id="ag_cancel_new_folder" class="AG_common_button"><span><span><?php echo JText::_('COM_ADMIRORGALLERY_TOOLBAR_CANCEL'); ?></span></span></a>');
                // Binding event to Create New Folder
                AG_jQuery("#ag_create_new_folder").click(function(e) {
                   e.preventDefault();
                   Joomla.submitbutton('AG_apply');
                });       
                // Binding event to Cancel New Folder
                AG_jQuery("#ag_cancel_new_folder").click(function(e) {
                   e.preventDefault();
                   AG_jQuery("#ag_create_new_folder").remove();
                   AG_jQuery("#ag_cancel_new_folder").remove();
                   AG_jQuery("#AG_folder_add .AG_input").remove();
               });
            }
        });
        // Binding event to folder links
        AG_jQuery(".AG_folderLink").click(function(e) {
            e.preventDefault();        
            ag_folder_selected(AG_jQuery(this).attr("href"));
        });

        // Binding event to file links
        AG_jQuery(".AG_fileLink").click(function(e) {
            e.preventDefault();
            ag_file_selected(AG_jQuery(this).attr("href"));
        });

        // Binding event to folder links
        AG_jQuery("#ag_preview .AG_folderLink").click(function(e) {
            e.preventDefault();
            ag_folder_selected(AG_jQuery(this).attr("href"));
        });

        // Binding event to file links
        AG_jQuery("#ag_preview .AG_fileLink").click(function(e) {
            e.preventDefault();
            ag_file_selected(AG_jQuery(this).attr("href"));
        });

        AG_jQuery(".AG_cbox_selectItem").click(function(e) {
            AG_jQuery(this).closest(".AG_item_wrapper").toggleClass("AG_mark_selectItem");
        });


        AG_jQuery("#AG_bookmarks_showHide").click(function(e) {
            e.preventDefault();
            if(AG_jQuery(".AG_bookmarks_wrapper").css("display")!="none"){
                AG_jQuery(".AG_bookmarks_wrapper").css("display","none");
                AG_jQuery("#AG_main").removeClass();
                AG_jQuery("#AG_bookmarks_showHide").find("span").find("span").html("<?php echo JText::_('AG_SHOW_SIDEBAR') ?>");
            }else{
                AG_jQuery(".AG_bookmarks_wrapper").css("display","block");
                AG_jQuery("#AG_main").addClass("AG_border_color AG_border_width AG_details_wrapper");
                AG_jQuery("#AG_bookmarks_showHide").find("span").find("span").html("<?php echo JText::_('AG_HIDE_SIDEBAR') ?>");   
            }

        });
      
        AG_jQuery("#AG_btn_showFolderSettings").click(function(e) {
            e.preventDefault();
            if(AG_jQuery("#AG_folderSettings_wrapper").css("display")!="none"){     
                AG_jQuery("#AG_folderSettings_wrapper").css("display","none"); 
                AG_jQuery("#AG_btn_showFolderSettings").find("span").find("span").html("<?php echo JText::_('AG_EDIT_FOLDER_CAPTIONS') ?>");
            }else{
                AG_jQuery("#AG_folderSettings_wrapper").css("display","block"); 
                AG_jQuery("#AG_folderSettings_status").val("edit");
                AG_jQuery("#AG_btn_showFolderSettings").find("span").find("span").html("<?php echo JText::_('AG_CLOSE_FOLDER_CAPTIONS') ?>");   
            }
        });
      
        AG_jQuery(".AG_folder_thumb").change(function(){
            AG_jQuery("#AG_folderSettings_status").val("edit");
        });
<?php if ($this->ag_front_end == 'true') { ?>
            // SET SHORCUTS
            AG_jQuery(document).bind("keydown", "ctrl+return", function (){submitbutton("AG_apply");return false;});	
            AG_jQuery(document).bind("keydown", "ctrl+backspace", function (){submitbutton("AG_reset");return false;});
<?php } ?>
    });//AG_jQuery(function()

</script>