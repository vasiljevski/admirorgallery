<?php

/**
 * @version     6.0.0
 * @package     Admiror Gallery (plugin)
 * @subpackage  admirorgallery
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri as JUri;
use Joomla\CMS\Version;
use Joomla\Filesystem\Folder;
use Admiror\Plugin\Content\AdmirorGallery\agGallery;
use Admiror\Plugin\Content\AdmirorGallery\agJoomla;

const AG_VERSION = '6.0.0';

/**
 * Backward compatibility with Joomla!3
 * Load namespace
*/
JLoader::registerNamespace('Admiror\\Plugin\\Content\\AdmirorGallery', JPATH_PLUGINS."/content/admirorgallery/admirorgallery/core/", false, false, 'psr4');

class plgContentAdmirorGallery extends CMSPlugin
{
    //Constructor
    function __construct(&$subject, $params)
    {
        parent::__construct($subject, $params);
        // load current language
        $this->loadLanguage();
    }

    function onContentBeforeDisplay($context, &$row, &$params, $limitstart = 0)
    {
        $this->onContentPrepare($context, $row, $params, $limitstart);
    }

    function onContentPrepare($context, $row, &$params, $limitstart = 0)
    {
        if ($context === 'com_finder.indexer') {
            // skip plug-in activation when the content is being indexed
            return false;
        }

        $gd_exists = true;
        if (!isset($row->text)) {
            return false;
        }
        if (!preg_match("#{AdmirorGallery[^}]*}(.*?){/AdmirorGallery}|{AG[^}]*}(.*?){/AG}|{ag[^}]*}(.*?){/ag}#s", $row->text)) {
            return false;
        }
        $doc = Factory::getDocument();
        //check for PHP version, 5.0.0 and above are accepted
        if (strnatcmp(phpversion(), '5.0.0') <= 0) {
            $doc->addStyleSheet('plugins/content/admirorgallery/admirorgallery/AdmirorGallery.css');
            $php_version_error_html = '<div class="error">' . Text::_('AG_PHP_VERSION_MUST_BE_ABOVE_PHP5') . '</div>' . "\n";
            if ((preg_match_all("#{AdmirorGallery[^}]*}(.*?){/AdmirorGallery}#s", $row->text, $matches) > 0)
                || (preg_match_all("#{AG[^}]*}(.*?){/AG}#s", $row->text, $matches) > 0)
            ) {
                foreach ($matches[0] as $match) {
                    $galleryname = preg_replace("/{.+?}/", "", $match);
                    $row->text = preg_replace("#{AdmirorGallery[^}]*}" . $galleryname . "{/AdmirorGallery}|{AG[^}]*}" . $galleryname . "{/AG}#s", "<div style='clear:both'></div>" . $php_version_error_html, $row->text, 1);
                }
            }
            return false;
        }

        //Create galleries
        if (preg_match_all("#{AdmirorGallery[^}]*}(.*?){/AdmirorGallery}|{AG[^}]*}(.*?){/AG}#s", $row->text, $matches) > 0) {
            $AG = new agGallery($this->params, JUri::base(), JPATH_SITE, new agJoomla());
            //Load current language
            CMSPlugin::loadLanguage('plg_content_admirorgallery', JPATH_ADMINISTRATOR);
            // Version check
            if (Version::PRODUCT == "Joomla!" && (Version::MAJOR_VERSION == "1.5")) {
                $AG->error_handle->addError(Text::_('AG_ADMIROR_GALLERY_PLUGIN_FUNCTIONS_ONLY_UNDER'));
            }
            //if any image is corrupted suppresses recoverable error
            ini_set('gd.jpeg_ignore_warning', $AG->params['ignoreError']);
            if ($AG->params['ignoreAllError'])
                error_reporting('E_NOTICE');
            //Joomla specific variables is passed as parameters for agGallery independent from specific CMS
            $AG->loadJS('AG_jQuery.js');
            $AG->articleID = $row->id;

            $queryArray = null;
            $queryString = strip_tags($_SERVER['QUERY_STRING']);
            parse_str($queryString, $queryArray);

            $basepath = $AG->sitePhysicalPath . $AG->params['rootFolder'];
            $realBase = realpath($basepath);

            $i = 0;
            while (array_key_exists('AG_form_albumInitFolders_' . $i, $queryArray)) {
                $userpath = $basepath . $queryArray['AG_form_albumInitFolders_' . $i];
                $realUserPath = realpath($userpath);

                if ($realUserPath === false || strpos($realUserPath, $realBase) !== 0) {
                    header('Location: ' . strip_tags($_SERVER['PHP_SELF']));
                }
                $i++;
            }
            //generate gallery html
            foreach ($matches[0] as $match) {
                $AG->index++;
                $AG->initGallery($match); // match = ;
                // ERROR - Cannot find folder with images
                if (!file_exists($AG->imagesFolderPhysicalPath)) {
                    $AG->error_handle->addError(Text::sprintf('AG_CANNOT_FIND_FOLDER_INSIDE_FOLDER', $AG->imagesFolderName, $AG->imagesFolderPhysicalPath));
                }
                //Create directory in thumbs for gallery
                Folder::create($AG->thumbsFolderPhysicalPath);
                if (is_writable($AG->thumbsFolderPhysicalPath))
                    $AG->create_gallery_thumbs();
                else
                    $AG->error_handle->addError(Text::sprintf('AG_CANNOT_CREATE_THUMBS_PERMISSIONS_ERROR', $AG->thumbsFolderPhysicalPath));
                include(dirname(__FILE__) . '/admirorgallery/templates/' . $AG->params['template'] . '/index.php');

                $AG->clearOldThumbs();
                $row->text = $AG->error_handle->writeErrors() . preg_replace("#{AdmirorGallery[^}]*}" . $AG->imagesFolderNameOriginal . "{/AdmirorGallery}|{AG[^}]*}" . $AG->imagesFolderNameOriginal . "{/AG}#s", "<div style='clear:both'></div>" . $html, $row->text, 1);
            } // foreach($matches[0] as $match)

            $row->text .= '<script type="text/javascript">';

            if (strpos($_SERVER['REQUEST_URI'], 'AG_form_paginInitPages_') !== false) {
                $row->text .= '
                    AG_jQuery(document).ready(function() {
                        AG_jQuery(document).scrollTop(AG_jQuery("#AG_0' . $AG->articleID . '").offset().top);
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
            $row->text .= '<br />'
                . '<a href="https://www.admiror-design-studio.com" target="_blank">AdmirorGallery ' . AG_VERSION . '</a>,'
                . ' ' . Text::_("AG_AUTHORS")
                . ' <a href="https://www.vasiljevski.com/" target="_blank">Vasiljevski</a> '
                . '& '
                . '<a href="https://www.admiror-design-studio.com" target="_blank">Kekeljevic</a>.</div>';
        } //if (preg_match_all("#{AdmirorGallery}(.*?){/AdmirorGallery}#s", $row->text, $matches, PREG_PATTERN_ORDER)>0)
    } //onPrepareContent(&$row, &$params, $limitstart)
}//class plgContentAdmirorGallery extends CMSPlugin
