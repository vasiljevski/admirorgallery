<?php
/* ------------------------------------------------------------------------
  # admirorgallery - Admiror Gallery Plugin
  # ------------------------------------------------------------------------
  # author   Igor Kekeljevic & Nikola Vasiljevski
  # copyright Copyright (C) 2014 admiror-design-studio.com. All Rights Reserved.
  # @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.admiror-design-studio.com/joomla-extensions
  # Technical Support:  Forum - http://www.vasiljevski.com/forum/index.php
  # Version: 5.0.0
  ------------------------------------------------------------------------- */
defined('_JEXEC') or die();
// Import library dependencies
jimport('joomla.event.plugin');
jimport('joomla.plugin.plugin');
jimport( 'joomla.filesystem.folder' );
define('AG_VERSION', '5.0.0');

class plgContentAdmirorGallery extends JPlugin {

    //Constructor
    function plgContentadmirorGallery(&$subject, $params) {
        parent::__construct($subject, $params);
        // load current language
        $this->loadLanguage();
    }
    
    function onContentBeforeDisplay($context, &$row, &$params, $limitstart = 0) {
        $this->onContentPrepare($context, $row, $params, $limitstart);
    }

    function onContentPrepare($context, &$row, &$params, $limitstart = 0) {
        $gd_exists = true;
        if (!preg_match("#{AdmirorGallery[^}]*}(.*?){/AdmirorGallery}|{AG[^}]*}(.*?){/AG}|{ag[^}]*}(.*?){/ag}#s", $row->text)) {
            return;
        }
        $doc = JFactory::getDocument();
        //check for PHP version, 5.0.0 and above are accepted
        if (strnatcmp(phpversion(), '5.0.0') <= 0) {
            $doc->addStyleSheet('plugins/content/admirorgallery/admirorgallery/AdmirorGallery.css');
            $html = '<div class="error">Admiror Gallery requires PHP version 5.0.0 or greater!</div>' . "\n";
            if ((preg_match_all("#{AdmirorGallery[^}]*}(.*?){/AdmirorGallery}#s", $row->text, $matches, PREG_PATTERN_ORDER) > 0) || (preg_match_all("#{AG[^}]*}(.*?){/AG}#s", $row->text, $matches, PREG_PATTERN_ORDER) > 0)) {
                foreach ($matches[0] as $match) {
                    $galleryname = preg_replace("/{.+?}/", "", $match);
                    $row->text = preg_replace("#{AdmirorGallery[^}]*}" . $galleryname . "{/AdmirorGallery}|{AG[^}]*}" . $galleryname . "{/AG}#s", "<div style='clear:both'></div>" . $html, $row->text, 1);
                }
            }
            return;
        }
        // Load gallery class php script
        require_once (dirname(__FILE__) . '/admirorgallery/classes/agGallery.php');
        //CreateGallerys
        if (preg_match_all("#{AdmirorGallery[^}]*}(.*?){/AdmirorGallery}|{AG[^}]*}(.*?){/AG}#s", $row->text, $matches, PREG_PATTERN_ORDER) > 0) {
            $AG = new agGallery($this->params, JURI::base(), JPATH_SITE, $doc);
            //Load current language
            JPlugin::loadLanguage('plg_content_admirorgallery', JPATH_ADMINISTRATOR);
            // Version check
            $version = new JVersion();
            if ($version->PRODUCT == "Joomla!" && ($version->RELEASE == "1.5")) {
                $AG->addError(JText::_('AG_ADMIROR_GALLERY_PLUGIN_FUNCTIONS_ONLY_UNDER'));
            }
            //if any image is corrupted supresses recoverable error
            ini_set('gd.jpeg_ignore_warning', $AG->params['ignoreError']);
            if ($AG->params['ignoreAllError'])
                error_reporting('E_NOTICE');
            //Joomla specific variables is passed as parametars for agGallery independce from specific CMS
            $AG->loadJS('AG_jQuery.js');
            $AG->articleID = $row->id;

            $queryArray = null;
            $queryString = strip_tags($_SERVER['QUERY_STRING']);
            parse_str($queryString, $queryArray);
            
            $basepath = $AG->sitePhysicalPath.$AG->staticParams['rootFolder'];
            $realBase = realpath($basepath);

            $i=0;
            while(array_key_exists('AG_form_albumInitFolders_'.$i,$queryArray))
            {
                $userpath = $basepath . $queryArray['AG_form_albumInitFolders_'.$i];
                $realUserPath = realpath($userpath);

                if ($realUserPath === false || strpos($realUserPath, $realBase) !== 0) {
                    header('Location: '.strip_tags($_SERVER['PHP_SELF']));
                }
                $i++;
            }
            //generate gallery html
            foreach ($matches[0] as $match) {
                $AG->index++;
                $AG->initGallery($match); // match = ;
                // ERROR - Cannot find folder with images
                if (!file_exists($AG->imagesFolderPhysicalPath)) {
                    $AG->addError(JText::sprintf('AG_CANNOT_FIND_FOLDER_INSIDE_FOLDER', $AG->imagesFolderName, $AG->imagesFolderPhysicalPath));
                }
                //Create directory in thumbs for gallery
                JFolder::create($AG->thumbsFolderPhysicalPath, 0755);
                if (is_writable($AG->thumbsFolderPhysicalPath))
                    $AG->generateThumbs();
                else
                    $AG->addError(JText::sprintf('AG_CANNOT_CREATE_THUMBS_PERMMISIONS_ERROR', $AG->thumbsFolderPhysicalPath));
                include (dirname(__FILE__) . '/admirorgallery/templates/' . $AG->params['template'] . '/index.php');

                $AG->clearOldThumbs();
                $row->text = $AG->writeErrors() . preg_replace("#{AdmirorGallery[^}]*}" . $AG->imagesFolderNameOriginal . "{/AdmirorGallery}|{AG[^}]*}" . $AG->imagesFolderNameOriginal . "{/AG}#s", "<div style='clear:both'></div>" . $html, $row->text, 1);
            }// foreach($matches[0] as $match)

            $row->text .= '<script type="text/javascript">';
            
            if(strpos($_SERVER['REQUEST_URI'],'AG_form_paginInitPages_') !== false)
            {
                $row->text .= '
                    AG_jQuery(document).ready(function() {
                        AG_jQuery(document).scrollTop(AG_jQuery("#AG_0'.$AG->articleID.'").offset().top);
                    });';
            }

            $row->text .= '  
            function AG_form_submit_' . $AG->articleID . '(galleryIndex,paginPage,albumFolder,linkID) {

            var AG_URL="' . strip_tags($_SERVER['REQUEST_URI']) . '";
            var split = AG_URL.split("AG_MK=0");
            if(split.length==3){
                AG_URL = split[0]+split[2];
            }
            var char = AG_URL.charAt((AG_URL.length)-1);
            if ((char != "?") && (char != "&"))
            {
                AG_URL += (AG_URL.indexOf("?")<0) ? "?" : "&";
            }
            AG_URL+="AG_MK=0&";

            AG_jQuery(".ag_hidden_ID").each(function(index) {

                var paginInitPages=eval("paginInitPages_"+AG_jQuery(this).attr(\'id\'));
                var albumInitFolders=eval("albumInitFolders_"+AG_jQuery(this).attr(\'id\'));

                if(AG_jQuery(this).attr(\'id\') == ' . $AG->articleID . '){
                    var paginInitPages_array = paginInitPages.split(",");
                    paginInitPages_array[galleryIndex] = paginPage;
                    paginInitPages = paginInitPages_array.toString();
                    var albumInitFolders_array = albumInitFolders.split(",");
                    albumInitFolders_array[galleryIndex] = albumFolder;
                    albumInitFolders = albumInitFolders_array.toString();
                }
                AG_URL+="AG_form_paginInitPages_"+AG_jQuery(this).attr(\'id\')+"="+paginInitPages+"&";
                AG_URL+="AG_form_albumInitFolders_"+AG_jQuery(this).attr(\'id\')+"="+albumInitFolders+"&";
            });

            AG_URL+="AG_MK=0";

            window.open(AG_URL,"_self");

            }
            </script>

            <span class="ag_hidden_ID" id="' . $AG->articleID . '"></span>

            ' . "\n";

            /* ========================= SIGNATURE ====================== */
            if ($AG->params['showSignature']) {
                $row->text .= '<div style="display:block; font-size:10px;">';
            } else {
                $row->text .= '<div style="display:block; font-size:10px; overflow:hidden; height:1px; padding-top:1px;">';
            }
            $row->text .= '<br /><a href="http://www.admiror-design-studio.com" target="_blank">AdmirorGallery ' . AG_VERSION . '</a>, ' . JText::_("AG_AUTHORS") . ' <a href="http://www.vasiljevski.com/" target="_blank">Vasiljevski</a> & <a href="http://www.admiror-design-studio.com" target="_blank">Kekeljevic</a>.<br /></div>';
        }//if (preg_match_all("#{AdmirorGallery}(.*?){/AdmirorGallery}#s", $row->text, $matches, PREG_PATTERN_ORDER)>0)
    }

//onPrepareContent(&$row, &$params, $limitstart)
}

//class plgContentadmirorGallery extends JPlugin
?>
