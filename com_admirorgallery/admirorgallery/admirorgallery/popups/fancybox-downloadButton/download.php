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
$original_file = $_GET['img'];
$mime = "";
if (preg_match("/jpg|jpeg/i", $original_file)) {
    $mime= "image/jpg";
} else if (preg_match("/png/i", $original_file)) {
    $mime= "image/png";
} else if (preg_match("/gif/i", $original_file)) {
    $mime= "image/gif";
}
header('Content-Type: '.$mime);
header('Content-Disposition: attachment; filename="'.basename($original_file).'"');
readfile($original_file);
?>