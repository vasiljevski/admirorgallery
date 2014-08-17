<?php
 /*------------------------------------------------------------------------
# admirorgallery - Admiror Gallery Plugin
# ------------------------------------------------------------------------
# author   Igor Kekeljevic & Nikola Vasiljevski
# copyright Copyright (C) 2014 admiror-design-studio.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.admiror-design-studio.com/joomla-extensions
# Technical Support:  Forum - http://www.vasiljevski.com/forum/index.php
# Version: 5.0.0
-------------------------------------------------------------------------*/
defined('_JEXEC') or die();

define('PLUGIN_BASE_PATH', '/plugins/content/admirorgallery/admirorgallery/');

require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'agHelper.php');
require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'agPopup.php');

class agGallery extends agHelper {

    var $sitePath = '';
    var $sitePhysicalPath = '';
    // Virtual path. Example: "http://www.mysite.com/plugin/content/admirorgallery/thumbs/"
    var $thumbsFolderPath = '';
    // Physical path on the server. Example: "E:\php\www\joomla/plugin/content/admirorgallery/thumbs/"
    var $thumbsFolderPhysicalPath = '';
    // Gallery name. Example: food
    var $imagesFolderName = '';
    // Physical path on the server. Example: "E:\php\www\joomla/plugin/content/"
    var $imagesFolderPhysicalPath = '';
    // Virtual path. Example: "http://www.mysite.com/images/stories/food/"
    var $imagesFolderPath = '';
    var $images = array();
    var $imageInfo = array();
    var $params = array();
    var $staticParams = array();
    var $index = -1;
    var $articleID = 0;
    var $popupEngine;
    var $currPopupRoot = '';
    var $currTemplateRoot = '';
    // Virtual path. Example: "http://www.mysite.com/plugins/content/admirorgallery/"
    var $pluginPath = '';
    var $squareImage = false;
    var $paginInitPages = array();
    var $albumInitFolders = array();
    var $paginImgTotal = 0;
    var $numOfGal = 0;
    var $albumParentLink = '';
    private $errors = array();
    private $doc = null;
    private $descArray = array();
    private $match = '';
    private $DS = DIRECTORY_SEPARATOR;

    //**************************************************************************
    //Template API functions                                                  //
    //**************************************************************************
    /**
     * Gets image info data, and loads it in imageInfo array. It also rounds image size.
     * @param <string> $imageName
     */
    function getImageInfo($imageName) {
        $this->imageInfo = agHelper::ag_imageInfo($this->imagesFolderPhysicalPath . $this->DS . $imageName);
        $this->imageInfo["size"] = agHelper::ag_fileRoundSize($this->imageInfo["size"]);
    }

    /**
     * Returns gallery id formed from gallery index and article ID
     * @return <string>
     */
    function getGalleryID() {
        return $this->index . $this->articleID;
    }

    /**
     * Loads CSS file from the given path.
     * @param <string> $path
     */
    function loadCSS($path) {
        $this->doc->addStyleSheet($this->sitePath . PLUGIN_BASE_PATH . $path);
    }

    /**
     * Loads JavaScript file from the given path.
     * @param <string> $path
     */
    function loadJS($path) {
        $this->doc->addScript($this->sitePath . PLUGIN_BASE_PATH . $path);
    }

    /**
     * Loads JavaScript code block into document head.
     * @param <string> $script
     */
    function insertJSCode($script) {
        $this->doc->addScriptDeclaration($script);
    }

    /**
     * Returns specific inline parametar if entered or returns default value
     * @param <string> $atrib
     * @param <string> $default
     * @return <value> 
     */
    function getParameter($atrib, $default) {
        return $this->ag_getParams($atrib, $this->match, $default);
    }

    /**
     * Returns full image html
     * @param <string> $imageName
     * @param <string> $cssClass
     * @return <html> 
     */
    function writeImage($imageName, $cssClass='') {
        return '<img src="' . $this->imagesFolderPath . $imageName . '"
                alt="' . strip_tags($this->descArray[$imageName]) . '"
                class="' . $cssClass . '">';
    }

    /**
     * Returns thumb html
     * @param <string> $imageName
     * @param <string> $cssClass
     * @return <html> 
     */
    function writeThumb($imageName, $cssClass='') {
        return '<img src="' . $this->sitePath . PLUGIN_BASE_PATH . 'thumbs/' . $this->imagesFolderName . '/' . $imageName . '"
                alt="' . strip_tags($this->descArray[$imageName]) . '"
                class="' . $cssClass . '">';
    }

    /**
     * Generates HTML with new image tag
     * @param <string> $image
     * @return <html>
     */
    function writeNewImageTag($image) {
        $FileAge = date("YmdHi", filemtime($this->imagesFolderPhysicalPath . $image)); // DEFAULT DATE
        $dateLimit = date("YmdHi", mktime(date("H"), date("i"), date("s"), date("m"), date("d") - (int) ($this->params['newImageTag_days']), date("Y")));
        if ($FileAge > $dateLimit && $this->params['newImageTag'] == 1) {
            return '<span class="ag_newTag"><img src="' . $this->sitePath . PLUGIN_BASE_PATH . 'newTag.gif" class="ag_newImageTag" /></span>';
        }
    }

    /**
     * Generates HTML with Popup engine integration
     * @param <string> $image
     * @return <html>
     */
    function writePopupThumb($image) {
        $html = '';
        if ($this->popupEngine->customPopupThumb) {
            $html = $this->popupEngine->customPopupThumb;
            $html = str_replace("{imagePath}", $this->imagesFolderPath . $image, $html);
            $html = str_replace("{imageDescription}", htmlspecialchars($this->descArray[$image], ENT_QUOTES, "UTF-8"), $html);
            $html = str_replace("{className}", $this->popupEngine->className, $html);
            $html = str_replace("{rel}", $this->popupEngine->rel, $html);
            $html = str_replace("{customAttr}", $this->popupEngine->customTag, $html);
            $html = str_replace("{newImageTag}", $this->writeNewImageTag($image), $html);
            $html = str_replace("{thumbImagePath}", $this->sitePath . PLUGIN_BASE_PATH . 'thumbs/' . $this->imagesFolderName . '/' . $image, $html);
        } else {
            $html.='<a href="' . $this->imagesFolderPath . $image . '" title="' . htmlspecialchars($this->descArray[$image], ENT_QUOTES, "UTF-8") . '" class="' . $this->popupEngine->className . '" rel="' . $this->popupEngine->rel . '" ' . $this->popupEngine->customAttr . ' target="_blank">' . $this->writeNewImageTag($image) . '<img src="' . $this->sitePath . PLUGIN_BASE_PATH . 'thumbs/' . $this->imagesFolderName . '/' . $image . '" alt="' . strip_tags($this->descArray[$image]) . '" class="ag_imageThumb"></a>';
        }
        return $html;
    }

    /**
     * Generates HTML link to album page
     * @param <type> $default_folder_img
     * @param <type> $thumbHeight
     * @return string
     */
    function writeFolderThumb($default_folder_img, $thumbHeight) {

        // Album Support
        $html = "";
        if ($this->params['albumUse'] && !empty($this->folders)) {
            $html.='<div class="AG_album_wrap">' . "\n";
            foreach ($this->folders as $folderKey => $folderName) {
                $thumb_path = $this->ag_get_album_thumb_path($default_folder_img,$folderName);
                $html.='<a href="javascript:void(0);" onClick="AG_form_submit_' . $this->articleID . '(' . $this->index . ',1,\'' . $this->imagesFolderName . '/' . $folderName . '\'); return false;" class="AG_album_thumb">';    
                $html.='<span class="AG_album_thumb_img" >';
                $html.='<img style="height: '.$thumbHeight.'px;" src="'.$thumb_path.'" />' . "\n";
                $html.='</span>';
                $html.='<span class="AG_album_thumb_label">';
                $html.=$this->descArray[$folderName];
                $html.='</span>';
                $html.='</a>';
            }
            $html.= '<br style="clear:both;" /></div>' . "\n";
        }
        return $html;
    }
    
    function ag_get_album_thumb_path($default_folder_img,$folderName)
    {
        // Get Thumb URL value                
        // Set Possible Description File Apsolute Path // Instant patch for upper and lower case...
        $ag_pathWithStripExt = $this->imagesFolderPhysicalPath . $folderName;
        $ag_XML_path = $ag_pathWithStripExt . ".XML";
        if (file_exists($ag_pathWithStripExt . ".xml")) {
            $ag_XML_path = $ag_pathWithStripExt . ".xml";
        }
        if (file_exists($ag_XML_path)) {// Check is descriptions file exists
            $ag_XML_xml = simplexml_load_file($ag_XML_path);
            if (isset($ag_XML_xml->thumb)) {
                $thumb_file = (string) $ag_XML_xml->thumb;
            }
        }
        if (empty($thumb_file)) {
            $images = agHelper::ag_imageArrayFromFolder($this->imagesFolderPhysicalPath . $folderName);
            if (!empty($images)) {
                $images = agHelper::array_sorting($images, $this->imagesFolderPhysicalPath . $folderName . $this->DS, $this->params['arrange']);
                $thumb_file = $images[0]; // Get First image in folder as thumb 
            }
        }
        if (!empty($thumb_file)) {
            $this->Album_generateThumb($folderName, $thumb_file);
            $thumb_file = 'thumbs/' . $this->imagesFolderName . '/' . $folderName . '/' . basename($thumb_file);
        }
        else
        {
            $thumb_file = $this->currTemplateRoot.$default_folder_img;
        }
        return $this->sitePath . PLUGIN_BASE_PATH .$thumb_file;
    }

    /**
     * Pagination HTML output
     * @return string
     */
    function writePagination() {
        // Paggination Support
        $html = "";
        if ($this->params['paginUse']) {
            if ($this->params['paginUse']) {
                if (!empty($this->paginImgTotal) && ceil($this->paginImgTotal / $this->params['paginImagesPerGallery']) > 1) {
                    $html.= '<div class="AG_pagin_wrap">';
                    $paginPrev = ($this->paginInitPages[$this->index] - 1);
                    if ($paginPrev >= 1) {
                        $html.= '<a href="javascript:void(0);" onClick="AG_form_submit_' . $this->articleID . '(' . $this->index . ',' . $paginPrev . ',\'' . $this->imagesFolderName . '\'); return false;" class="AG_pagin_prev">' . JText::_("AG_PREV") . '</a>';
                    }
                    for ($i = 1; $i <= ceil($this->paginImgTotal / $this->params['paginImagesPerGallery']); $i++) {
                        if ($i == $this->paginInitPages[$this->index]) {
                            $html.= '<span class="AG_pagin_current">' . $i . '</span>';
                        } else {
                            $html.= '<a href="javascript:void(0);" onClick="AG_form_submit_' . $this->articleID . '(' . $this->index . ',' . $i . ',\'' . $this->imagesFolderName . '\',this);return false;" class="AG_pagin_link">' . $i . '</a>';
                        }
                    }
                    $paginNext = ($this->paginInitPages[$this->index] + 1);
                    if ($paginNext <= ceil($this->paginImgTotal / $this->params['paginImagesPerGallery'])) {
                        $html.= '<a href="javascript:void(0);" onClick="AG_form_submit_' . $this->articleID . '(' . $this->index . ',' . $paginNext . ',\'' . $this->imagesFolderName . '\'); return false;" class="AG_pagin_next">' . JText::_("AG_NEXT") . '</a>';
                    }
                    $html.= '<br style="clear:both"></div>';
                }
            }
        }
        return $html;
    }

    /**
     * Generates html with popup support for all the images in the gallery.
     * @return <html>
     */
    function writeAllPopupThumbs() {
        $html = '';
        if (!empty($this->images)) {
            foreach ($this->images as $imagesKey => $imagesValue) {
                $html.='<a href="' . $this->imagesFolderPath . $imagesValue . '" title="' . htmlspecialchars(strip_tags($this->descArray[$imagesValue])) . '" class="' . $this->popupEngine->cssClass . '" rel="' . $this->popupEngine->rel . '" ' . $this->popupEngine->customTag . ' target="_blank">';
                $html.=$this->writeNewImageTag($imagesValue);
                $html.='<img src="' . $this->sitePath . PLUGIN_BASE_PATH . 'thumbs/' . $this->imagesFolderName . '/' . $imagesValue . '
                        " alt="' . htmlspecialchars(strip_tags($this->descArray[$imagesValue])) . '" class="ag_imageThumb"></a>';
            }
        }
        return $html;
    }

    /**
     * Returns image description. The current localization is taken into account.
     * @param <string> $imageName
     * @return <string> 
     */
    function writeDescription($imageName) {
        return $this->descArray[$imageName];
    }

    /*
     * Initialises Popup engine. Loads popupEngine settings and scripts
     */

    function initPopup() {
        require (dirname(dirname(__FILE__)). $this->DS . 'popups' . $this->DS . $this->params['popupEngine'] . $this->DS . 'index.php');
        return $this->popupEngine->initCode;
    }

    /*
     * Includes JavaScript code ad the end of the gallery html
     */

    function endPopup() {
        return $this->popupEngine->endCode;
    }

    /*
     * adds new error value to the error array
     */

    function addError($value) {
        if ($value != '') {
            $this->errors[] = $value;
        }
    }

    //**************************************************************************
    // END Template API functions                                             //
    //**************************************************************************
    //**************************************************************************
    // Gallery Functions                                                      //
    //**************************************************************************
    /**
     * Gallery initialization
     * @param <string> $match
     */
    function initGallery($match) {
        $this->match = $match;
        $this->readInlineParams();
        $this->imagesFolderNameOriginal = preg_replace("/{.+?}/", "", $match);
        $this->imagesFolderName = strip_tags($this->imagesFolderNameOriginal);
        // Pagination Support
        if ($this->params['paginUse'] || $this->params['albumUse']) {
            $this->paginInitPages[] = 1;
            if (!empty($_GET['AG_form_paginInitPages_' . $this->articleID])) {
                $AG_form_paginInitPages_array = explode(",", $_GET['AG_form_paginInitPages_' . $this->articleID]);
                $this->paginInitPages[$this->index] = strip_tags($AG_form_paginInitPages_array[$this->index]);
            }
            $script = 'var paginInitPages_' . $this->articleID . '="' . implode(",", $this->paginInitPages) . '";';
            
            $this->doc->addScriptDeclaration(strip_tags($script));

            // Album Support
            $this->albumParentLink = '';
            $this->albumInitFolders[] = "";
            $this->albumInitFolders[$this->index] = strip_tags($this->imagesFolderName); // Set init folders
            if (!empty($_GET['AG_form_albumInitFolders_' . $this->articleID])) {
                $AG_form_albumInitFolders_array = explode(",", $_GET['AG_form_albumInitFolders_' . $this->articleID]);
                $this->albumInitFolders[$this->index] = strip_tags($AG_form_albumInitFolders_array[$this->index]);
                $this->imagesFolderName = strip_tags($AG_form_albumInitFolders_array[$this->index]);
                // Support for Album Parent Link
                if ($this->imagesFolderName != $this->imagesFolderNameOriginal) {
                    $this->albumParentLink = '
                        <a href="javascript:void(0);" onClick="AG_form_submit_' . strip_tags($this->articleID) . '(' . strip_tags($this->index) . ',1,\'' . strip_tags(dirname($this->imagesFolderName)) . '\'); return false;" class="AG_album_parent">
                            <span>
                                ' . strip_tags(basename(dirname($this->imagesFolderName))) . '
                            </span>
                        </a>
                        <br style="clear:both;" />
                        ';
                }
            }

            // Breadcrump Support           
            if (JFactory::getApplication()->getMenu()->getActive()->query['view'] == "layout") {
                $this->writeBreadcrum();
            }

            $script = 'var albumInitFolders_' . $this->articleID . '="' . implode(",", $this->albumInitFolders) . '";';
            $this->doc->addScriptDeclaration(strip_tags($script));
        }
        $this->imagesFolderPhysicalPath = $this->sitePhysicalPath . $this->params['rootFolder'] . $this->imagesFolderName . $this->DS;
        $this->thumbsFolderPhysicalPath = $this->sitePhysicalPath . PLUGIN_BASE_PATH . 'thumbs' . $this->DS . $this->imagesFolderName . $this->DS;
        $this->imagesFolderPath = $this->sitePath . $this->params["rootFolder"] . $this->imagesFolderName . '/';
        $this->readDescriptionFiles();
        $this->loadImageFiles();
        $this->loadFolders();
        $this->currPopupRoot = 'popups/' . $this->params['popupEngine'] . '/';
        $this->currTemplateRoot = 'templates/' . $this->params['template'] . '/';
        $this->pluginPath = $this->sitePath . PLUGIN_BASE_PATH;
    }

    /**
     *  Clears obsolete thumbnail folders
     */
    function cleanThumbsFolder() {
        $this->ag_cleanThumbsFolder($this->imagesFolderPhysicalPath, $this->thumbsFolderPhysicalPath);
    }

    /**
     *  Clears obsolete thumbnails
     */
    function clearOldThumbs() {
        $this->ag_clearOldThumbs($this->imagesFolderPhysicalPath, $this->thumbsFolderPhysicalPath, $this->params['albumUse']);
    }

    /**
     *  Reads description files
     */
    private function readDescriptionFiles() {
        // Create Images Array
        unset($this->descArray);

        if (file_exists($this->imagesFolderPhysicalPath)) {

            $ag_images = Array();
            $ag_files = JFolder::files($this->imagesFolderPhysicalPath);
            $ag_ext_valid = array("jpg", "jpeg", "gif", "png"); // SET VALID IMAGE EXTENSION
            foreach ($ag_files as $key => $value) {
                if (is_numeric(array_search(strtolower(agHelper::ag_getExtension(basename($value))), $ag_ext_valid))) {
                    $ag_images[] = $value;
                }
            }
            $ag_files = array_merge($ag_images, JFolder::folders($this->imagesFolderPhysicalPath));

            if (!empty($ag_files)) {
                foreach ($ag_files as $key => $f) {

                    // Set image name as imageDescription value, as predifined value
                    $this->descArray[$f] = $f;

                    // Set Possible Description File Apsolute Path // Instant patch for upper and lower case...
                    $ag_pathWithStripExt = $this->imagesFolderPhysicalPath . agHelper::ag_removExtension($f);
                    $descriptionFileApsolutePath = $ag_pathWithStripExt . ".XML";
                    if (file_exists($ag_pathWithStripExt . ".xml")) {
                        $descriptionFileApsolutePath = $ag_pathWithStripExt . ".xml";
                    }

                    if (file_exists($descriptionFileApsolutePath)) {// Check is descriptions file exists
                        $ag_imgXML_xml = JFactory::getXML($descriptionFileApsolutePath);
                        $ag_imgXML_captions = $ag_imgXML_xml->captions;
                        $lang = JFactory::getLanguage();
                        $langTag = strtolower($lang->getTag());

                        // GET DEFAULT LABEL
                        if (!empty($ag_imgXML_captions->caption)) {
                            foreach ($ag_imgXML_captions->caption as $ag_imgXML_caption) {
                                if (strtolower($ag_imgXML_caption->attributes()->lang) == "default") {
                                    $this->descArray[$f] = $ag_imgXML_caption;
                                }
                            }
                        }

                        // GET CURRENT LANG LABEL
                        if (!empty($ag_imgXML_captions->caption)) {
                            foreach ($ag_imgXML_captions->caption as $ag_imgXML_caption) {
                                if (strtolower($ag_imgXML_caption->attributes()->lang) == strtolower($langTag)) {
                                    $this->descArray[$f] = $ag_imgXML_caption;
                                }
                            }
                        }



                        // RICH TEXT SUPPORT
                        if ($this->params['plainTextCaptions']) {
                            $this->descArray[$f] = strip_tags($this->descArray[$f]);
                        }
                    }
                }
            }// if(file_exists($descriptionFileApsolutePath))
        }
        else
            $this->descArray = null;
    }

    /**
     *  Loads images array, sorted as defined bu parametar.
     */
    private function loadImageFiles() {
        $this->images = agHelper::ag_imageArrayFromFolder($this->imagesFolderPhysicalPath);
        if (!empty($this->images)) {
            $this->images = agHelper::array_sorting($this->images, $this->imagesFolderPhysicalPath,$this->params['arrange']);
        }

        // Paginations Support
        if ($this->params['paginUse']) {
            $this->paginImgTotal = count($this->images);
            $paginImages = Array();
            $ag_pagin_start = ($this->paginInitPages[$this->index] - 1) * $this->params['paginImagesPerGallery'];
            $ag_pagin_end = ($this->paginInitPages[$this->index] * $this->params['paginImagesPerGallery']) - 1;

            if (!empty($this->images)) {
                for ($i = $ag_pagin_start; $i <= $ag_pagin_end; $i++) {
                    if ($i < $this->paginImgTotal) {
                        $paginImages[] = $this->images[$i];
                    }
                }
            }

            $this->images = $paginImages;
        }
    }

    /**
     * Loads folder array, sorted as defined bu parametar.
     */
    private function loadFolders() {
        $this->folders = agHelper::ag_foldersArrayFromFolder($this->imagesFolderPhysicalPath);
        if (!empty($this->folders)) {
            $this->folders = agHelper::array_sorting($this->folders, $this->imagesFolderPhysicalPath,$this->params['arrange']);
        }
    }

    /**
     * Generates thumbs, check for settings change and recreates thumbs if it needs to
     */
    function generateThumbs() {
        if (($this->params['thumbWidth'] == 0) || ($this->params['thumbHeight'] == 0)) {
            $this->adderror(JText::_("AG_CANNOT_CREATE_THUMBNAILS_WIDTH_AND_HEIGHT_MUST_BE_GREATER_THEN_0"));
            return;
        }
        //Add's index.html to thumbs folder
        if (!file_exists($this->thumbsFolderPhysicalPath . $this->DS . 'index.html')) {
            $this->ag_indexWrite($this->thumbsFolderPhysicalPath . $this->DS . 'index.html');
        }
        // Check for Changes
        if (!empty($this->images)) {
            foreach ($this->images as $imagesKey => $imagesValue) {
                $original_file = $this->imagesFolderPhysicalPath . $imagesValue;
                $thumb_file = $this->thumbsFolderPhysicalPath . $imagesValue;
                if (!file_exists($thumb_file)) {
                    $this->addError(agHelper::ag_createThumb($this->imagesFolderPhysicalPath . $imagesValue, $thumb_file, $this->params['thumbWidth'], $this->params['thumbHeight'], $this->params['thumbAutoSize']));
                } else {

                    list($imagewidth, $imageheight) = getimagesize($thumb_file);
                    switch ($this->params['thumbAutoSize']) {
                        case "none":
                            if ($imageheight != $this->params['thumbHeight'] || $imagewidth != $this->params['thumbWidth']) {
                                $this->addError(agHelper::ag_createThumb($this->imagesFolderPhysicalPath . $imagesValue, $thumb_file, $this->params['thumbWidth'], $this->params['thumbHeight'], $this->params['thumbAutoSize']));
                            }
                            break;
                        case "height":
                            if ($imagewidth != $this->params['thumbWidth']) {
                                $this->addError(agHelper::ag_createThumb($this->imagesFolderPhysicalPath . $imagesValue, $thumb_file, $this->params['thumbWidth'], $this->params['thumbHeight'], $this->params['thumbAutoSize']));
                            }
                            break;
                        case "width":
                            if ($imageheight != $this->params['thumbHeight']) {
                                $this->addError(agHelper::ag_createThumb($this->imagesFolderPhysicalPath . $imagesValue, $thumb_file, $this->params['thumbWidth'], $this->params['thumbHeight'], $this->params['thumbAutoSize']));
                            }
                            break;
                    }
                }
                // ERROR - Invalid image
                if (!file_exists($thumb_file)) {
                    //$this->addError("Cannot read thumbnail");
                    $this->addError(JText::sprintf("AG_CANNOT_READ_THUMBNAIL", $thumb_file));
                }
            }
        }
    }

    /**
     * Generates Album Thumbs
     */
    function Album_generateThumb($AG_parent_folder, $AG_img) {
        if (($this->params['thumbWidth'] == 0) || ($this->params['thumbHeight'] == 0)) {
            $this->adderror(JText::_("AG_CANNOT_CREATE_THUMBNAILS_WIDTH_AND_HEIGHT_MUST_BE_GREATER_THEN_0"));
            return;
        }
        $imagesFolderPhysicalPath = $this->imagesFolderPhysicalPath . $AG_parent_folder . $this->DS;
        $thumbsFolderPhysicalPath = $this->thumbsFolderPhysicalPath . $AG_parent_folder . $this->DS;
        //Create directory in thumbs for gallery
        if (!file_exists($thumbsFolderPhysicalPath)) {
            JFolder::create($thumbsFolderPhysicalPath, 0755);
        }
        //Add's index.html to thumbs folder
        if (!file_exists($thumbsFolderPhysicalPath . 'index.html')) {
            $this->ag_indexWrite($thumbsFolderPhysicalPath . 'index.html');
        }
        $original_file = $imagesFolderPhysicalPath . $AG_img;
        $thumb_file = $thumbsFolderPhysicalPath . $AG_img;
        if (!file_exists($thumb_file)) {
            $this->addError(agHelper::ag_createThumb($original_file, $thumb_file, $this->params['thumbWidth'], $this->params['thumbHeight'], $this->params['thumbAutoSize']));
        } else {
            list($imagewidth, $imageheight) = getimagesize($thumb_file);
            switch ($this->params['thumbAutoSize']) {
                case "none":
                    if ($imageheight != $this->params['thumbHeight'] || $imagewidth != $this->params['thumbWidth']) {
                        $this->addError(agHelper::ag_createThumb($original_file, $thumb_file, $this->params['thumbWidth'], $this->params['thumbHeight'], $this->params['thumbAutoSize']));
                    }
                    break;
                case "height":
                    if ($imagewidth != $this->params['thumbWidth']) {
                        $this->addError(agHelper::ag_createThumb($original_file, $thumb_file, $this->params['thumbWidth'], $this->params['thumbHeight'], $this->params['thumbAutoSize']));
                    }
                    break;
                case "width":
                    if ($imageheight != $this->params['thumbHeight']) {
                        $this->addError(agHelper::ag_createThumb($original_file, $thumb_file, $this->params['thumbWidth'], $this->params['thumbHeight'], $this->params['thumbAutoSize']));
                    }
                    break;
            }
        }
        // ERROR - Invalid image
        if (!file_exists($thumb_file)) {
            //$this->addError("Cannot read thumbnail");
            $this->addError(JText::sprintf("AG_CANNOT_READ_THUMBNAIL", $thumb_file));
        }
    }

    /**
     * Returns error html
     */
    function writeErrors() {
        $errors = "";
        $osVersion = $this->ag_get_os_($_SERVER['HTTP_USER_AGENT']);
        $phpVersion = phpversion();
        if (isset($this->errors)) {
            foreach ($this->errors as $key => $value) {
                $errors.='<div class="error">' . $value . ' <br/>
                        Admiror Gallery: ' . AG_VERSION . '<br/>
                        Server OS:' . $_SERVER['SERVER_SOFTWARE'] . '<br/>
                        Client OS:' . $osVersion . '<br/>
                        PHP:' . $phpVersion . '
                        </div>' . "\n";
            }
            unset($this->errors);
        }
        return $errors;
    }

    /**
     * Breadcrump Support
     * Author: Lee Anderson
     * Authors e-mail: landerson@atlas-tech.com
     */
    function writeBreadcrum() {
        $folderNames = str_replace('//', '/', $this->imagesFolderName);
        $albumName = explode("/", $folderNames);
        $folderNumber = count($albumName) - 1;
        $linkFolderName = '';
        for ($i = 0; $i <= $folderNumber; $i++) {
            $linkFolderName .= $albumName[$i] . '/';
            $linkFolderName = str_replace('//', '/', $linkFolderName);
            if ($albumName[$i] != '' && $i != 0) {
                $this->events['name'] = $albumName[$i];
                $link = 'Javascript: AG_form_submit_' . $this->articleID . '(' . $this->index . ',1,\'' . $linkFolderName . '\');';
                $mainframe = JFactory::getApplication();
                $document = JFactory::getDocument();
                $pathway = $mainframe->getPathway();
                $document->setTitle($this->events['name']);
                $pathway->addItem($this->events['name'], $link);
            }
        }
    }

    /**
     *  Reads inline parametar if any or sets default values
     */
    function readInlineParams() {
        ////setting parametars for current gallery, if there is no inline params default params are set
        $this->params['template'] = $this->ag_getParams("template", $this->match, $this->staticParams['template']);
        $this->params['thumbWidth'] = $this->ag_getParams("thumbWidth", $this->match, $this->staticParams['thumbWidth']);
        $this->params['thumbHeight'] = $this->ag_getParams("thumbHeight", $this->match, $this->staticParams['thumbHeight']);
        $this->params['thumbAutoSize'] = $this->ag_getParams("thumbAutoSize", $this->match, $this->staticParams['thumbAutoSize']);
        $this->params['arrange'] = $this->ag_getParams("arrange", $this->match, $this->staticParams['arrange']);
        $this->params['newImageTag'] = $this->ag_getParams("newImageTag", $this->match, $this->staticParams['newImageTag']);
        $this->params['newImageTag_days'] = $this->ag_getParams("newImageDays", $this->match, $this->staticParams['newImageTag_days']);
        $this->params['frameWidth'] = $this->ag_getParams("frameWidth", $this->match, $this->staticParams['frame_width']);
        $this->params['frameHeight'] = $this->ag_getParams("frameHeight", $this->match, $this->staticParams['frame_height']);
        $this->params['showSignature'] = $this->ag_getParams("showSignature", $this->match, $this->staticParams['showSignature']);
        $this->params['plainTextCaptions'] = $this->ag_getParams("plainTextCaptions", $this->match, $this->staticParams['plainTextCaptions']);
        $this->params['popupEngine'] = $this->ag_getParams("popupEngine", $this->match, $this->staticParams['popupEngine']);
        $this->params['backgroundColor'] = $this->ag_getParams("backgroundColor", $this->match, $this->staticParams['backgroundColor']);
        $this->params['foregroundColor'] = $this->ag_getParams("foregroundColor", $this->match, $this->staticParams['foregroundColor']);
        $this->params['highliteColor'] = $this->ag_getParams("highliteColor", $this->match, $this->staticParams['highliteColor']);

        // Albums Support
        $this->params['albumUse'] = $this->ag_getParams("albumUse", $this->match, $this->staticParams['albumUse']);
        // Paginations Support
        $this->params['paginUse'] = $this->ag_getParams("paginUse", $this->match, $this->staticParams['paginUse']);
        $this->params['paginImagesPerGallery'] = $this->ag_getParams("paginImagesPerGallery", $this->match, $this->staticParams['paginImagesPerGallery']);
    }

    /**
     * Gallery constructor, sets path values, sets document reference
     * @param <JParameter> $globalParams
     * @param <string> $path
     * @param <string> $sitePhysicalPath
     * @param <pointer> $document
     */
    function __construct($globalParams, $path, $sitePhysicalPath, $document) {
        $this->staticParams['thumbWidth'] = $globalParams->get('thumbWidth', 200);
        $this->staticParams['thumbHeight'] = $globalParams->get('thumbHeight', 120);
        $this->staticParams['thumbAutoSize'] = $globalParams->get('thumbAutoSize', "none");
        $this->staticParams['template'] = $globalParams->get('template', 'classic');
        $this->staticParams['arrange'] = $globalParams->get('arrange', 'priority');
        $this->staticParams['newImageTag'] = $globalParams->get('newImageTag', true);
        $this->staticParams['newImageTag_days'] = $globalParams->get('newImageTag_days', '7');
        $this->staticParams['frame_width'] = $globalParams->get('frame_width', false);
        $this->staticParams['frame_height'] = $globalParams->get('frame_height', false);
        $this->staticParams['showSignature'] = $globalParams->get('showSignature', true);
        $this->staticParams['plainTextCaptions'] = $globalParams->get('plainTextCaptions', true);
        $this->staticParams['popupEngine'] = $globalParams->get('popupEngine', 'slimbox');
        $this->staticParams['usePopuEngine'] = $globalParams->get('usePopuEngine', true);
        $this->staticParams['ignoreError'] = $globalParams->get('ignoreError', true);
        $this->staticParams['ignoreAllError'] = $globalParams->get('ignoreAllError', false);
        $this->staticParams['rootFolder'] = $globalParams->get('rootFolder', '/images/sampledata/');
        $this->staticParams['backgroundColor'] = $globalParams->get('backgroundColor', 'ffffff');
        $this->staticParams['foregroundColor'] = $globalParams->get('foregroundColor', '808080');
        $this->staticParams['highliteColor'] = $globalParams->get('highliteColor', 'fea804');
        $this->popupEngine = new agPopup();
        $this->params = $this->staticParams;
        if (substr($path, -1) == "/")
            $path = substr($path, 0, -1);
        $this->sitePath = $path;
        $this->sitePhysicalPath = $sitePhysicalPath;
        $this->thumbsFolderPhysicalPath = $sitePhysicalPath . PLUGIN_BASE_PATH . 'thumbs' . $this->DS;
        $this->imagesFolderPhysicalPath = $sitePhysicalPath . $this->params["rootFolder"];
        $this->cleanThumbsFolder();
        $this->doc = $document;
        $this->loadCSS('AdmirorGallery.css');
        //$this->errors = new agErrors();
        // Album Support
        $this->staticParams['albumUse'] = $globalParams->get('albumUse', true);
        // Paginations Support
        $this->staticParams['paginUse'] = $globalParams->get('paginUse', true);
        $this->staticParams['paginImagesPerGallery'] = $globalParams->get('paginImagesPerGallery', 10);
    }
    //Constructor backward compatibility with PHP4
    function agGallery($globalParams, $path, $sitePhysicalPath, $document)
    {
        $this->__construct($globalParams, $path, $sitePhysicalPath, $document);
    }
    //**************************************************************************
    // END Gallery Functions                                                  //
    //**************************************************************************
}

?>
