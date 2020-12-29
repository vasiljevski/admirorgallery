<?php
/**
 * @version     5.5.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2020 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.language.language');
jimport('joomla.filesystem.archive');
class AdmirorgalleryViewImagemanager extends JViewLegacy {
   
    var $ag_template_id = 'default';
    var $ag_init_itemURL = '';
    var $ag_starting_folder = '';
    var $ag_rootFolder = '';
    var $ag_front_end = '';
    
    function display($tpl = null) {
        // Make sure you are logged in and have the necessary access
        $validUsers = array(5 /*Publisher*/,6/*Manager*/,7/*Administrator*/,8/*Super Users*/);
        $user = JFactory::getUser();
        $grantAccess = false;
        $userGroups = $user->getAuthorisedGroups();
        foreach ($userGroups as $group) {
            if(in_array($group, $validUsers))
            {
                $grantAccess = true;
                break;
            }
        }
        if(!$grantAccess)
        {
            JResponse::setHeader('HTTP/1.0 403',true);
            JError::raiseWarning( 403, JText::_('JERROR_ALERTNOAUTHOR') );
            return;
        }
        $mainframe = JFactory::getApplication();
        $params = $mainframe->getParams();

        $this->assign('galleryName', $params->get('galleryName'));

        
        $this->ag_template_id = JRequest::getString('AG_template', 'default'); // Current template for AG Component

        $ag_item_url = JRequest::getVar('AG_itemURL');
        
        $this->ag_front_end = JRequest::getVar('ag_front_end'); // Current template for AG Component
        // GET ROOT FOLDER
        $plugin = JPluginHelper::getPlugin('content', 'admirorgallery');
        $pluginParams = new JRegistry($plugin->params);
        $this->ag_rootFolder = $pluginParams->get('rootFolder', '/images/sampledata/');
        if ($this->ag_front_end == 'true') {
            $this->ag_starting_folder = $pluginParams->get('rootFolder', '/images/sampledata/') . $this->galleryName . '/';
        } else {
            $this->ag_starting_folder = $this->ag_rootFolder;
        }

        if (!empty($ag_item_url)) {
            $this->ag_init_itemURL = $ag_item_url;
        } else {
            if ($this->ag_front_end == 'true') {
                $this->ag_init_itemURL = $pluginParams->get('rootFolder', '/images/sampledata/') . $this->galleryName . '/';
            } else {
                $this->ag_init_itemURL = $this->ag_rootFolder;
            }
        }

        
        $this->sidebar = JHtmlSidebar::render();
        parent::display($tpl);
    }

    function ag_render_breadcrumb($AG_itemURL, $ag_rootFolder, $ag_folderName, $ag_fileName) {
        $ag_breadcrumb = '';
        $ag_breadcrumb_link = '';
        if ($ag_rootFolder != $AG_itemURL && !empty($AG_itemURL)) {
            $ag_breadcrumb.='<a href="' . $ag_rootFolder . '" class="AG_folderLink AG_common_button"><span><span>' . substr($ag_rootFolder, 0, -1) . '</span></span></a>/';
            $ag_breadcrumb_link.=$ag_rootFolder;
            $ag_breadcrumb_cut = substr($ag_folderName, strlen($ag_rootFolder));
            $ag_breadcrumb_cut_array = explode("/", $ag_breadcrumb_cut);
            if (!empty($ag_breadcrumb_cut_array[0])) {
                foreach ($ag_breadcrumb_cut_array as $cut_key => $cut_value) {
                    $ag_breadcrumb_link.=$cut_value . '/';
                    $ag_breadcrumb.='<a href="' . $ag_breadcrumb_link . '" class="AG_folderLink AG_common_button"><span><span>' . $cut_value . '</span></span></a>/';
                }
            }
            $ag_breadcrumb.=$ag_fileName;
        } else {
            $ag_breadcrumb.=$ag_rootFolder;
        }
        return $ag_breadcrumb;
    }
function ag_render_image_info($ag_itemURL, $AG_imgInfo, $ag_hasXML, $ag_hasThumb) {
        $return_value = '<div class="AG_margin_bottom AG_thumbAndInfo_wrapper">
                <table cellspacing="0" cellpadding="0" border="0">
                    <tbody>
                        <tr>
                            <td>
                                <a class="AG_item_link" href="' . substr(JURI::root(), 0, -1) . $ag_itemURL . '" title="' . $ag_itemURL . '" rel="lightbox[\'AG\']" target="_blank">
                                    <img src="' . JURI::root() . 'administrator/components/com_admirorgallery/assets/thumbs/' . basename($ag_itemURL) . '" alt="' . $ag_itemURL . '">
                                </a>
                            </td>
                            <td class="AG_border_color AG_border_width" style="border-left-style:solid;">
                                <div>' . JText::_("AG_IMG_WIDTH") . ': ' . $AG_imgInfo["width"] . 'px</div>
                                <div>' . JText::_("AG_IMG_HEIGHT") . ': ' . $AG_imgInfo["height"] . 'px</div>
                                <div>' . JText::_("AG_IMG_TYPE") . ': ' . $AG_imgInfo["type"] . '</div>
                                <div>' . JText::_("AG_IMG_SIZE") . ': ' . $AG_imgInfo["size"] . '</div>
                                <div>' . $ag_hasXML . $ag_hasThumb . '</div>
                            </td>
                        </tr>
                    </tbody>
                </table>   
                </div>
                ';
        return $return_value;
    }

    function ag_render_caption($ag_lang_name, $ag_lang_tag, $ag_lang_content) {
        return '
	<div class="AG_border_color AG_border_width AG_margin_bottom">
	    ' . $ag_lang_name . ' / ' . $ag_lang_tag . '
	    <textarea class="AG_textarea" name="AG_desc_content[]">' . $ag_lang_content . '</textarea><input type="hidden" name="AG_desc_tags[]" value="' . $ag_lang_tag . '" />
	</div>
    ';
    }

    function ag_render_captions($ag_imgXML_captions) {
        $ag_site_languages = "";
        $ag_matchCheck = Array("default");

        // GET DEFAULT LABEL
        $ag_imgXML_caption_content = "";
        if (!empty($ag_imgXML_captions->caption)) {
            foreach ($ag_imgXML_captions->caption as $ag_imgXML_caption) {
                if (strtolower($ag_imgXML_caption->attributes()->lang) == "default") {
                    $ag_imgXML_caption_content = $ag_imgXML_caption;
                }
            }
        }
        $ag_site_languages.=$this->ag_render_caption("Default", "default", $ag_imgXML_caption_content);

        $ag_lang_available = JLanguage::getKnownLanguages(JPATH_SITE);
        if (!empty($ag_lang_available)) {
            foreach ($ag_lang_available as $ag_lang) {
                $ag_imgXML_caption_content = "";
                if (!empty($ag_imgXML_captions->caption)) {
                    foreach ($ag_imgXML_captions->caption as $ag_imgXML_caption) {
                        if (strtolower($ag_imgXML_caption->attributes()->lang) == strtolower($ag_lang["tag"])) {
                            $ag_imgXML_caption_content = $ag_imgXML_caption;
                            $ag_matchCheck[] = strtolower($ag_lang["tag"]);
                        }
                    }
                }
                $ag_site_languages.= $this->ag_render_caption($ag_lang["name"], $ag_lang["tag"], $ag_imgXML_caption_content);
            }
        }

        if (!empty($ag_imgXML_captions->caption)) {
            foreach ($ag_imgXML_captions->caption as $ag_imgXML_caption) {
                $ag_imgXML_caption_attr = $ag_imgXML_caption->attributes()->lang;
                if (!is_numeric(array_search(strtolower($ag_imgXML_caption_attr), $ag_matchCheck))) {
                    $ag_site_languages.= $this->ag_render_caption($ag_imgXML_caption_attr, $ag_imgXML_caption_attr, $ag_imgXML_caption);
                }
            }
        }
        return $ag_site_languages;
    }

    function ag_render_file_footer() {
        return '<div style="clear:both" class="AG_margin_bottom"></div>
        <hr />
        <div  class="AG_legend">
        <h2>' . JText::_('AG_LEGEND') . '</h2>
        <table><tbody>
        <tr>
            <td><img src="' . JURI::root() . 'administrator/components/com_admirorgallery/templates/' . $this->ag_template_id . '/images/icon-hasThumb.png" style="float:left;" /></td>
            <td>' . JText::_('AG_IMAGE_HAS_THUMBNAIL_CREATED') . '</td>
        </tr>
        <tr>
            <td><img src="' . JURI::root() . 'administrator/components/com_admirorgallery/templates/' . $this->ag_template_id . '/images/icon-hasXML.png" style="float:left;" /></td>
            <td>' . JText::_('AG_IMAGE_HAS_ADDITIONAL_DETAILS_SAVED') . '</td>
        </tr>
        </tbody></table>
        <div>
        ';
    }
    
    function ag_get_bookmark_path()
    {
        return $this->getModel('imagemanager')->ag_bookmark_path;
    }
}
