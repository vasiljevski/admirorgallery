<?php
/**
 * @version     5.1.2
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2017 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */


/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

JLoader::register('agHelper', JPATH_SITE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'admirorgallery' . DIRECTORY_SEPARATOR . 'admirorgallery' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'agHelper.php');

$ag_itemURL = $this->ag_init_itemURL;

$ag_folderName = dirname($ag_itemURL);
$ag_fileName = basename($ag_itemURL);
$AG_imgInfo = agHelper::ag_imageInfo(JPATH_SITE.$ag_itemURL);

$thumbsFolderPhysicalPath = JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_admirorgallery'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'thumbs';

agHelper::ag_sureRemoveDir($thumbsFolderPhysicalPath,true);
if(!JFolder::create($thumbsFolderPhysicalPath,0755)){
    JFactory::getApplication()->enqueueMessage( JText::_( "AG_CANNOT_CREATE_FOLDER" )."&nbsp;".$newFolderName, 'error' );
}

$ag_hasXML="";
$ag_hasThumb="";

// Set Possible Description File Apsolute Path // Instant patch for upper and lower case...
$ag_pathWithStripExt=JPATH_SITE.$ag_folderName.'/'.JFile::stripExt(basename($ag_itemURL));
$ag_imgXML_path=$ag_pathWithStripExt.".XML";
if(JFIle::exists($ag_pathWithStripExt.".xml")){
    $ag_imgXML_path=$ag_pathWithStripExt.".xml";
}

if(file_exists(JPATH_SITE."/plugins/content/admirorgallery/admirorgallery/thumbs/".basename($ag_folderName)."/".basename($ag_fileName))){
     $ag_hasThumb='<img src="'.JURI::root().'administrator/components/com_admirorgallery/templates/'.$this->ag_template_id.'/images/icon-hasThumb.png" class="ag_hasThumb" />';
}

if(file_exists($ag_imgXML_path)){
     $ag_hasXML='<img src="'.JURI::root().'administrator/components/com_admirorgallery/templates/'.$this->ag_template_id.'/images/icon-hasXML.png" class="ag_hasXML" />';
     $ag_imgXML_xml = simplexml_load_file( $ag_imgXML_path );
     $ag_imgXML_captions = $ag_imgXML_xml->captions;
}
else
{
    $ag_imgXML_captions = null;
}

$ag_preview_content='';

// GET IMAGES FOR NEXT AND PREV IMAGES FUNCTIONS
$ag_files=JFolder::files(JPATH_SITE.$ag_folderName);

if(!empty($ag_files)){
    $ag_ext_valid = array ("jpg","jpeg","gif","png");// SET VALID IMAGE EXTENSION
    $ag_images=Array();
    foreach($ag_files as $key => $value){
        if(is_numeric(array_search(strtolower(JFile::getExt(basename($value))),$ag_ext_valid))){
            $ag_images[]=$value;
        }
    }
 if(array_search($ag_fileName, $ag_images)!=0){    
        $ag_fileName_prev=$ag_images[array_search($ag_fileName, $ag_images)-1];
    }
    if(array_search($ag_fileName, $ag_images)<count($ag_images)-1){
        $ag_fileName_next=$ag_images[array_search($ag_fileName, $ag_images)+1];
    }
    if(!empty($ag_fileName_prev)){
        $ag_preview_content.='<a class="AG_common_button" href="" onclick="AG_jQuery(\'#AG_input_itemURL\').val(\''.$ag_folderName.'/'.$ag_fileName_prev.'\');submitbutton(\'AG_reset\');return false;"><span><span>'.JText::_( "AG_PREVIOUS_IMAGE").'</span></span></a>'."\n";
    }
    if(!empty($ag_fileName_next)){
        $ag_preview_content.='<a class="AG_common_button" href="" onclick="AG_jQuery(\'#AG_input_itemURL\').val(\''.$ag_folderName.'/'.$ag_fileName_next.'\');submitbutton(\'AG_reset\');return false;"><span><span>'.JText::_( "AG_NEXT_IMAGE").'</span></span></a>'."\n";
    }
}

$ag_preview_content.='<hr />';

$ag_preview_content.='
<h1>'.JText::_( 'AG_IMAGE_DETAILS_FOR_FILE' ).'</h1>
<div class="AG_border_color AG_border_width AG_margin_bottom AG_breadcrumbs_wrapper">
'.$this->ag_render_breadcrumb($ag_itemURL, $this->ag_starting_folder, $ag_folderName, $ag_fileName).'
</div>
';

agHelper::ag_createThumb(JPATH_SITE.$ag_itemURL, $thumbsFolderPhysicalPath.DIRECTORY_SEPARATOR.basename($ag_itemURL), 145, 80, "none");

//Image and image details
$ag_preview_content.=$this->ag_render_image_info($ag_itemURL,  $AG_imgInfo, $ag_hasXML, $ag_hasThumb);

require_once (JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_admirorgallery'.DIRECTORY_SEPARATOR.'slimbox'.DIRECTORY_SEPARATOR.'index.php');

$ag_preview_content.= $this->ag_render_captions($ag_imgXML_captions);

$ag_preview_content.= $this->ag_render_file_footer();

?>
