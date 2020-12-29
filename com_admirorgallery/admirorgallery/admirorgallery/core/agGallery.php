<?php
/**
 * @version     5.5.0
 * @package     Admiror Gallery (plugin)
 * @subpackage  admirorgallery
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2020 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

JLoader::register('agHelper', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'agHelper.php');
JLoader::register('agPopup', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'agPopup.php');
JLoader::register('agCmsInterface', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'agCmsInterface.php');
JLoader::register('agJoomla', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'agJoomla.php');
JLoader::register('agTemplate', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'agTemplate.php');
JLoader::register('agParams', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'agParams.php');

agJoomla::SecurityCheck();

class agGallery extends agHelper
{
    public string $sitePath = '';
    public string $sitePhysicalPath = '';
    // Virtual path. Example: "http://www.mysite.com/plugin/content/admirorgallery/thumbs/"
    public string $thumbsFolderPath = '';
    // Physical path on the server. Example: "E:\php\www\joomla/plugin/content/admirorgallery/thumbs/"
    public string $thumbsFolderPhysicalPath = '';
    // Gallery name. Example: food
    public string $imagesFolderName = '';
    // Physical path on the server. Example: "E:\php\www\joomla/plugin/content/"
    public string $imagesFolderPhysicalPath = '';
    // Virtual path. Example: "http://www.mysite.com/images/stories/food/"
    public string $imagesFolderPath = '';
    public ?array $images = array();
    public ?array $imageInfo = array();
    public agParams $params;
    public int $index = -1;
    public int $articleID = 0;
    public agPopup $popupEngine;
    public string $currPopupRoot = '';
    public string $currTemplateRoot = '';
    // Virtual path. Example: "http://www.mysite.com/plugins/content/admirorgallery/"
    public string $pluginPath = '';
    public bool $squareImage = false;
    public array$paginInitPages = array();
    public array $albumInitFolders = array();
    public int $paginImgTotal = 0;
    public int $numOfGal = 0;
    public string $albumParentLink = '';
    private array $errors = array();
    private agCmsInterface $cms;
    private array $descArray = array();
    private string $match = '';
    private string $DS = DIRECTORY_SEPARATOR;
    private string $plugin_path = '/plugins/content/admirorgallery/admirorgallery/';
    public ?array $folders;
    public string $imagesFolderNameOriginal;

    //**************************************************************************
    //Template API functions                                                  //
    //**************************************************************************

    /**
     * Gets image info data, and loads it in imageInfo array. It also rounds image size.
     *
     * @param string $imageName
     *
     * @since 5.5.0
     */
    public function getImageInfo(string $imageName): void
    {
        $this->imageInfo = agHelper::ag_imageInfo($this->imagesFolderPhysicalPath . $this->DS . $imageName);
        $this->imageInfo["size"] = agHelper::ag_fileRoundSize($this->imageInfo["size"]);
    }

    /**
     * Returns gallery id formed from gallery index and article ID
     *
     * @return string
     *
     * @since 5.5.0
     */
    public function getGalleryID(): string
    {
        return $this->index . $this->articleID;
    }

    /**
     * Loads CSS file from the given path.
     *
     * @param string $path
     *
     * @since 5.5.0
     */
    public function loadCSS(string $path): void
    {
        $this->cms->AddCss($this->sitePath .  $this->plugin_path. $path);
    }

    /**
     * Loads JavaScript file from the given path.
     *
     * @param string $path
     *
     * @since 5.5.0
     */
    public function loadJS(string $path): void
    {
        $this->cms->AddJsFile($this->sitePath .  $this->plugin_path . $path);
    }

    /**
     * Loads JavaScript code block into document head.
     *
     * @param string $script
     *
     * @since 5.5.0
     */
    public function insertJSCode(string $script): void
    {
        $this->cms->AddJsDeclaration($script);
    }

    /**
     * Returns specific inline parameter if entered or returns default value
     *
     * @param string $attrib
     * @param string $default
     *
     * @return string
     *
     * @since 5.5.0
     */
    public function getParameter(string $attrib, string $default): string
    {
        return $this->ag_getParams($attrib, $this->match, $default);
    }

    /**
     * Returns full image html
     *
     * @param string $imageName
     * @param string $cssClass
     *
     * @return string
     *
     * @since 5.5.0
     */
    public function writeImage(string $imageName, string $cssClass=''): string
    {
        return '<img src="' . $this->imagesFolderPath . $imageName . '"
                alt="' . strip_tags($this->descArray[$imageName]) . '"
                class="' . $cssClass . '">';
    }

    /**
     * Returns thumb html
     *
     * @param string $imageName
     * @param string $cssClass
     *
     * @return string
     *
     * @since 5.5.0
     */
    public function writeThumb(string $imageName, string $cssClass=''): string
    {
        return '<img src="' . $this->sitePath .  $this->plugin_path . 'thumbs/' . $this->imagesFolderName . '/' . $imageName . '"
                alt="' . strip_tags($this->descArray[$imageName]) . '"
                class="' . $cssClass . '">';
    }

    /**
     * Generates HTML with new image tag
     *
     * @param string $image
     *
     * @return string
     *
     * @since 5.5.0
     */
    public function writeNewImageTag(string $image): string
    {
        $FileAge = date("YmdHi", filemtime($this->imagesFolderPhysicalPath . $image)); // DEFAULT DATE
        $dateLimit = date("YmdHi", mktime(date("H"), date("i"), date("s"), date("m"), date("d") - (int) ($this->params['newImageTag_days']), date("Y")));
        if ($FileAge > $dateLimit && $this->params['newImageTag'] == 1) {
            return '<span class="ag_newTag"><img src="' . $this->sitePath .  $this->plugin_path . 'newTag.gif" class="ag_newImageTag"  alt="New"/></span>';
        }
        return '';
    }

    /**
     * Generates HTML with Popup engine integration
     *
     * @param string $image
     *
     * @return string
     *
     * @since 5.5.0
     */
    public function writePopupThumb(string $image): string
    {
        $html = '';
        if ($this->popupEngine->customPopupThumb) {
            $html = $this->popupEngine->customPopupThumb;
            $html = str_replace("{imagePath}", $this->imagesFolderPath . $image, $html);
            $html = str_replace("{imageDescription}", htmlspecialchars($this->descArray[$image], ENT_QUOTES), $html);
            $html = str_replace("{className}", $this->popupEngine->className, $html);
            $html = str_replace("{rel}", $this->popupEngine->rel, $html);
            $html = str_replace("{customAttr}", $this->popupEngine->customTag, $html);
            $html = str_replace("{newImageTag}", $this->writeNewImageTag($image), $html);
            $html = str_replace("{thumbImagePath}", $this->sitePath .  $this->plugin_path . 'thumbs/' . $this->imagesFolderName . '/' . $image, $html);
        } else {
            $html.='<a href="' . $this->imagesFolderPath . $image . '" title="' .
                    htmlspecialchars($this->descArray[$image], ENT_QUOTES) .
                    '" class="' . $this->popupEngine->className . '" rel="' .
                    $this->popupEngine->rel . '" ' . $this->popupEngine->customAttr .
                    ' target="_blank">' . $this->writeNewImageTag($image) .
                    '<img src="' . $this->sitePath .  $this->plugin_path . 'thumbs/' .
                    $this->imagesFolderName . '/' . $image . '" alt="' .
                    strip_tags($this->descArray[$image]) .
                    '" class="ag_imageThumb"></a>';
        }
        return $html;
    }

    /**
     * Generates HTML link to album page
     *
     * @param string $default_folder_img
     * @param string $thumbHeight
     *
     * @return string
     *
     * @since 5.5.0
     */
    public function writeFolderThumb(string $default_folder_img, string $thumbHeight): string
    {
        // Album Support
        $html = "";
        if ($this->params['albumUse'] && !empty($this->folders)) {
            $html.='<div class="AG_album_wrap">' . "\n";
            foreach ($this->folders as $folderKey => $folderName) {
                $thumb_path = $this->ag_get_album_thumb_path($default_folder_img, $folderName);
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

    /**
     * Returns album thumb path
     *
     * @param $default_folder_img
     * @param $folderName
     *
     * @return string
     *
     * @since 5.5.0
     */
    public function ag_get_album_thumb_path($default_folder_img, $folderName): string
    {
        // Get Thumb URL value
        // Set Possible Description File Absolute Path // Instant patch for upper and lower case...
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
        } else {
            $thumb_file = $this->currTemplateRoot.$default_folder_img;
        }
        return $this->sitePath .  $this->plugin_path .$thumb_file;
    }

    /**
     * Pagination HTML output
     *
     * @return string
     *
     * @since 5.5.0
     */
    public function writePagination(): string
    {
        // Pagination Support
        $html = "";
        if ($this->params['paginUse']) {
            if ($this->params['paginUse']) {
                if (!empty($this->paginImgTotal) && ceil($this->paginImgTotal / $this->params['paginImagesPerGallery']) > 1) {
                    $html.= '<div class="AG_pagin_wrap">';
                    $paginPrev = ($this->paginInitPages[$this->index] - 1);
                    if ($paginPrev >= 1) {
                        $html.= '<a href="javascript:void(0);" onClick="AG_form_submit_' . $this->articleID . '(' . $this->index . ',' . $paginPrev . ',\'' . $this->imagesFolderName . '\'); return false;" class="AG_pagin_prev">' . $this->cms->Text("AG_PREV") . '</a>';
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
                        $html.= '<a href="javascript:void(0);" onClick="AG_form_submit_' . $this->articleID . '(' . $this->index . ',' . $paginNext . ',\'' . $this->imagesFolderName . '\'); return false;" class="AG_pagin_next">' . $this->cms->Text("AG_NEXT") . '</a>';
                    }
                    $html.= '<br style="clear:both"></div>';
                }
            }
        }
        return $html;
    }

    /**
     * Generates html with popup support for all the images in the gallery.
     *
     * @return string
     *
     * @since 5.5.0
     */
    public function writeAllPopupThumbs(): string
    {
        $html = '';
        if (!empty($this->images)) {
            foreach ($this->images as $imagesKey => $imagesValue) {
                $html.='<a href="' . $this->imagesFolderPath . $imagesValue . '" title="' . htmlspecialchars(strip_tags($this->descArray[$imagesValue])) . '" class="' . $this->popupEngine->className . '" rel="' . $this->popupEngine->rel . '" ' . $this->popupEngine->customTag . ' target="_blank">';
                $html.=$this->writeNewImageTag($imagesValue);
                $html.='<img src="' . $this->sitePath .  $this->plugin_path . 'thumbs/' . $this->imagesFolderName . '/' . $imagesValue . '
                        " alt="' . htmlspecialchars(strip_tags($this->descArray[$imagesValue])) . '" class="ag_imageThumb"></a>';
            }
        }
        return $html;
    }

    /**
     * Returns image description. The current localization is taken into account.
     *
     * @param <string> $imageName
     *
     * @return mixed
     *
     * @since 5.5.0
     */
    public function writeDescription($imageName)
    {
        return $this->descArray[$imageName];
    }

    /**
     * Initialises Popup engine. Loads popupEngine settings and scripts
     *
     * @return string
     *
     * @since 5.5.0
     */
    public function initPopup(): string
    {
        require(dirname(dirname(__FILE__)). $this->DS . 'popups' . $this->DS . $this->params['popupEngine'] . $this->DS . 'index.php');
        return $this->popupEngine->initCode;
    }

    /**
     * Includes JavaScript code ad the end of the gallery html
     *
     * @return string
     *
     * @since 5.5.0
     */
    public function endPopup(): string
    {
        return $this->popupEngine->endCode;
    }

    /**
     * Adds new error value to the error array
     *
     * @param $value
     *
     * @since 5.5.0
     */
    public function addError($value)
    {
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
     *
     * @param <string> $match
     *
     * @since 5.5.0
     */
    public function initGallery($match)
    {
        $this->match = $match;
        $this->readInlineParams();
        $this->imagesFolderNameOriginal = preg_replace("/{.+?}/", "", $match);
        $this->imagesFolderName = strip_tags($this->imagesFolderNameOriginal);
        // Pagination Support
        if ($this->params['paginUse'] || $this->params['albumUse']) {
            $initPages = $this->cms->GetActivePage('AG_form_paginInitPages_' . $this->articleID);
            $albumPath = $this->cms->GetAlbumPath('AG_form_albumInitFolders_' . $this->articleID);
            
            $this->paginInitPages[] = 1;
            if (!empty($_GET['AG_form_paginInitPages_' . $this->articleID])) {
                $AG_form_paginInitPages_array = explode(",", $_GET['AG_form_paginInitPages_' . $this->articleID]);
                $this->paginInitPages[$this->index] = strip_tags($AG_form_paginInitPages_array[$this->index]);
            }
            $script = 'var paginInitPages_' . intval($this->articleID) . '="' . $initPages . '";';
            
            $this->cms->AddJsDeclaration(strip_tags($script));
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

            // Breadcrumb Support
            if ($this->cms->BreadcrumbsNeeded()) {
                $this->writeBreadcrumb();
            }
            $script = 'var albumInitFolders_' . $this->articleID . '="' . $albumPath . '";';
            $this->cms->AddJsDeclaration(strip_tags($script));
        }
        $this->imagesFolderPhysicalPath = $this->sitePhysicalPath . $this->params['rootFolder'] . $this->imagesFolderName . $this->DS;
        $this->thumbsFolderPhysicalPath = $this->sitePhysicalPath .  $this->plugin_path . 'thumbs' . $this->DS . $this->imagesFolderName . $this->DS;
        $this->imagesFolderPath = $this->sitePath . $this->params["rootFolder"] . $this->imagesFolderName . '/';
        $this->readDescriptionFiles();
        $this->loadImageFiles();
        $this->loadFolders();
        $this->currPopupRoot = 'popups/' . $this->params['popupEngine'] . '/';
        $this->currTemplateRoot = 'templates/' . $this->params['template'] . '/';
        $this->pluginPath = $this->sitePath .  $this->plugin_path;
    }

    /**
     * Clears obsolete thumbnail folders
     *
     * @since 5.5.0
     */
    public function cleanThumbsFolder()
    {
        $this->ag_cleanThumbsFolder($this->imagesFolderPhysicalPath,
            $this->thumbsFolderPhysicalPath);
    }

    /**
     *  Clears obsolete thumbnails
     *
     * @since 5.5.0
     */
    public function clearOldThumbs()
    {
        $this->ag_clearOldThumbs($this->imagesFolderPhysicalPath,
            $this->thumbsFolderPhysicalPath, $this->params['albumUse']);
    }

    /**
     *  Reads description files
     *
     * @since 5.5.0
     */
    private function readDescriptionFiles()
    {
        // Create Images Array
        unset($this->descArray);

        if (file_exists($this->imagesFolderPhysicalPath)) {
            $ag_images = array();
            $ag_files = $this->cms->GetFiles($this->imagesFolderPhysicalPath);
            $ag_ext_valid = array("jpg", "jpeg", "gif", "png"); // SET VALID IMAGE EXTENSION
            foreach ($ag_files as $key => $value) {
                if (is_numeric(array_search(strtolower(agHelper::ag_getExtension(basename($value))), $ag_ext_valid))) {
                    $ag_images[] = $value;
                }
            }
            $ag_files = array_merge($ag_images, $this->cms->GetFolders($this->imagesFolderPhysicalPath));

            if (!empty($ag_files)) {
                foreach ($ag_files as $key => $f) {

                    // Set image name as imageDescription value, as predefined value
                    $this->descArray[$f] = $f;

                    // Set Possible Description File Absolute Path // Instant patch for upper and lower case...
                    $ag_pathWithStripExt = $this->imagesFolderPhysicalPath . agHelper::ag_removeExtension($f);
                    $descriptionFileAbsolutePath = $ag_pathWithStripExt . ".XML";
                    if (file_exists($ag_pathWithStripExt . ".xml")) {
                        $descriptionFileAbsolutePath = $ag_pathWithStripExt . ".xml";
                    }

                    if (file_exists($descriptionFileAbsolutePath)) {// Check is descriptions file exists
                        $ag_imgXML_xml = new SimpleXMLElement($descriptionFileAbsolutePath);
                        $ag_imgXML_captions = $ag_imgXML_xml->captions;
                        $langTag = $this->cms->GetActiveLanguageTag();

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
            }// if(file_exists($descriptionFileAbsolutePath))
        } else {
            $this->descArray = array();
        }
    }

    /**
     *  Loads images array, sorted as defined bu parameter.
     *
     * @since 5.5.0
     */
    private function loadImageFiles()
    {
        $this->images = agHelper::ag_imageArrayFromFolder($this->imagesFolderPhysicalPath);
        if (!empty($this->images)) {
            $this->images = agHelper::array_sorting($this->images, $this->imagesFolderPhysicalPath, $this->params['arrange']);
        }

        // Pagination Support
        if ($this->params['paginUse']) {
            $this->paginImgTotal = count($this->images);
            $paginImages = array();
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
     * Loads folder array, sorted as defined bu parameter.
     *
     * @since 5.5.0
     */
    private function loadFolders()
    {
        $this->folders = agHelper::ag_foldersArrayFromFolder($this->imagesFolderPhysicalPath);
        if (!empty($this->folders)) {
            $this->folders = agHelper::array_sorting($this->folders, $this->imagesFolderPhysicalPath, $this->params['arrange']);
        }
    }

    /**
     * Generates thumbs, check for settings change and recreates thumbs if it needs to
     *
     * @since 5.5.0
     */
    public function generateThumbs()
    {
        if (($this->params['thumbWidth'] == 0) || ($this->params['thumbHeight'] == 0)) {
            $this->adderror($this->cms->Text("AG_CANNOT_CREATE_THUMBNAILS_WIDTH_AND_HEIGHT_MUST_BE_GREATER_THEN_0"));
            return;
        }
        //Adds index.html to thumbs folder
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
                    $this->addError($this->cms->TextConcat("AG_CANNOT_READ_THUMBNAIL", $thumb_file));
                }
            }
        }
    }

    /**
     * Generates Album Thumbs
     *
     * @param string $AG_parent_folder
     * @param string $AG_img
     *
     * @since 5.5.0
     */
    public function Album_generateThumb(string $AG_parent_folder, string $AG_img)
    {
        if (($this->params['thumbWidth'] == 0) || ($this->params['thumbHeight'] == 0)) {
            $this->adderror($this->cms->Text("AG_CANNOT_CREATE_THUMBNAILS_WIDTH_AND_HEIGHT_MUST_BE_GREATER_THEN_0"));
            return;
        }
        $imagesFolderPhysicalPath = $this->imagesFolderPhysicalPath . $AG_parent_folder . $this->DS;
        $thumbsFolderPhysicalPath = $this->thumbsFolderPhysicalPath . $AG_parent_folder . $this->DS;
        //Create directory in thumbs for gallery
        if (!file_exists($thumbsFolderPhysicalPath)) {
            //TODO:Handle return value
            $this->cms->CreateFolder($thumbsFolderPhysicalPath);
        }
        //Adds index.html to thumbs folder
        if (!file_exists($thumbsFolderPhysicalPath . 'index.html')) {
            $this->ag_indexWrite($thumbsFolderPhysicalPath . 'index.html');
        }
        $original_file = $imagesFolderPhysicalPath . $AG_img;
        $thumb_file = $thumbsFolderPhysicalPath . $AG_img;
        //TODO:Extract into function
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
            $this->addError($this->cms->TextConcat("AG_CANNOT_READ_THUMBNAIL", $thumb_file));
        }
    }

    /**
     * Returns error html
     *
     * @return string
     *
     * @since 5.5.0
     */
    public function writeErrors(): string
    {
        $errors = "";
        $osVersion = isset($_SERVER['HTTP_USER_AGENT']) ? $this->ag_get_os_($_SERVER['HTTP_USER_AGENT']) : 'Unknown - no user agent';
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
     * Breadcrumb Support
     *
     * @author: Lee Anderson
     * @email: landerson@atlas-tech.com
     *
     * @since 5.5.0
     */
    public function writeBreadcrumb()
    {
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
                $this->cms->SetTitle($this->events['name']);
                $this->cms->AddToPathway($this->events['name'], $link);
            }
        }
    }

    /**
     *  Reads inline parameter if any or sets default values
     *
     * @since 5.5.0
     */
    public function readInlineParams()
    {
        $this->params->readInlineParams($this->match);
    }

    /**
     * Gallery constructor, sets path values, sets document reference
     *
     * @param $globalParams
     * @param string $path
     * @param string $sitePhysicalPath
     * @param $cms
     *
     * @since 5.5.0
     */
    public function __construct($globalParams, string $path, string $sitePhysicalPath, $cms)
    {
        $this->cms = $cms;
        $this->params = new agParams($globalParams);
        $this->popupEngine = new agPopup();
        if (substr($path, -1) == "/") {
            $path = substr($path, 0, -1);
        }
        $this->sitePath = $path;
        $this->sitePhysicalPath = $sitePhysicalPath;
        $this->thumbsFolderPhysicalPath = $sitePhysicalPath .  $this->plugin_path . 'thumbs' . $this->DS;
        $this->imagesFolderPhysicalPath = $sitePhysicalPath . $this->params["rootFolder"];
        $this->cleanThumbsFolder();
        $this->loadCSS('AdmirorGallery.css');
    }
    //**************************************************************************
    // END Gallery Functions                                                  //
    //**************************************************************************
}
