<?php
/**
 * @version     6.0.0
 * @package     Admiror.Plugin
 * @subpackage  Content.AdmirorGallery
 * @author      Igor Kekeljevic <igor@admiror.com>
 * @author      Nikola Vasiljevski <nikola83@gmail.com>
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();
$original_file = $_GET['img'];
$mime = "";

if (preg_match("/jpg|jpeg/i", $original_file))
{
	$mime = "image/jpg";
}
elseif (preg_match("/png/i", $original_file))
{
	$mime = "image/png";
}
elseif (preg_match("/gif/i", $original_file))
{
	$mime = "image/gif";
}

header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . basename($original_file) . '"');
readfile($original_file);
