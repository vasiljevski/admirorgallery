<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (component)
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2021 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

PluginHelper::importPlugin( 'content' );

$app = Factory::getApplication();
$active = $app->getMenu()->getActive();
$params = $active->getParams();

$ag_params='';
foreach($params as $key => $value)
{
    $ag_inlineParams.=' '.$key .'="'.$value.'"';
}

$article = new \stdClass();

//Display page heading
if($active->getParams()->get('show_page_heading'))
{
    $article->text = '<h1>'.$active->getParams()->get('page_title').'</h1>';
}
$article->text .= '{AG '.$ag_params.' }'.$params->get('galleryName').'{/AG}';
$article->id = $active->id;

$app->triggerEvent('onContentPrepare', array ( &$context, &$article, & $params));
echo $article->text;
