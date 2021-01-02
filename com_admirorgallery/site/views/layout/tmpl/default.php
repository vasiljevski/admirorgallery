<?php
/**
 * @version     5.5.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2020 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

$ag_inlineParams='';
$ag_inlineParams.=' template="'.$this->template.'"';
$ag_inlineParams.=' thumbWidth="'.$this->thumbWidth.'"';
$ag_inlineParams.=' thumbHeight="'.$this->thumbHeight.'"';
$ag_inlineParams.=' thumbAutoSize="'.$this->thumbAutoSize.'"';
$ag_inlineParams.=' arrange="'.$this->arrange.'"';
$ag_inlineParams.=' newImageTag="'.$this->newImageTag.'"';
$ag_inlineParams.=' newImageDays="'.$this->newImageDays.'"';
$ag_inlineParams.=' frameWidth="'.$this->frameWidth.'"';
$ag_inlineParams.=' frameHeight="'.$this->frameHeight.'"';
$ag_inlineParams.=' showSignature="'.$this->showSignature.'"';
$ag_inlineParams.=' plainTextCaptions="'.$this->plainTextCaptions.'"';
$ag_inlineParams.=' popupEngine="'.$this->popupEngine.'"';
$ag_inlineParams.=' backgroundColor="'.$this->backgroundColor.'"';
$ag_inlineParams.=' foregroundColor="'.$this->foregroundColor.'"';
$ag_inlineParams.=' highlightColor="'.$this->highlightColor.'"';

// Albums Support
$ag_inlineParams.=' albumUse="'.$this->albumUse.'"';
// Pagination Support
$ag_inlineParams.=' paginUse="'.$this->paginUse.'"';
$ag_inlineParams.=' paginImagesPerGallery="'.$this->paginImagesPerGallery.'"';

JPluginHelper::importPlugin( 'content' );

$article = new JObject();

$app = JFactory::getApplication();
$active = $app->getMenu()->getActive();

$article->text = null;
//Display page heading
if($active->params->get('show_page_heading'))
{
    $article->text = '<h1>'.$active->params->get('page_title').'</h1>';
}
$article->text .= '{AG '.$ag_inlineParams.' }'.$this->galleryName.'{/AG}';
$article->id = 0;
$limitstart = 0;
$dispatcher = JDispatcher::getInstance();
$results = $dispatcher->trigger('onContentPrepare', array ( &$context, &$article, & $params, $limitstart));
echo $article->text;

?>