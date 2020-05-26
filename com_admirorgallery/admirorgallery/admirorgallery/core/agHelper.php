<?php
/**
 * @version     5.5.0
 * @package     Admiror Gallery (plugin)
 * @subpackage  admirorgallery
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2018 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();
class agHelper {
    /**
     * http://www.php.net/manual/en/function.natsort.php#45346
     * @param <type> $array
     * @return <type>
     */
    public static function array_sorting($array,$targetFolder,$arrange) {     
        $ag_array_data = Array();
        // READS XML DATA AND GENERATES ARRAYS
        foreach ($array as $key => $value) {
            // Set Possible Description File Apsolute Path // Instant patch for upper and lower case...
            $ag_pathWithStripExt = $targetFolder . agHelper::ag_removExtension(basename($value));                           
            $ag_XML_path = $ag_pathWithStripExt . ".xml";
            if (agHelper::ag_exists($ag_pathWithStripExt . ".XML")) {
                $ag_XML_path = $ag_pathWithStripExt . ".XML";
            }    
            $ag_xml_value = Array();
            $ag_xml_value["value"] = $value; // IMAGE NAME                
            $ag_xml_value["priority"] = "none"; // DEFAULT PRIORITY                
            $ag_xml_value["date"] = date("YmdHi", filemtime($targetFolder.$value)); // DEFAULT DATE    
            if (agHelper::ag_exists($ag_XML_path)) {
                $ag_XML_xml = simplexml_load_file($ag_XML_path);
                if(isset($ag_XML_xml)){
                    if (isset($ag_XML_xml->visible)){
                        if ((string)$ag_XML_xml->visible == 'false'){
                            continue; // SKIP HIDDEN IMAGES
                        }
                    }
                    if (isset($ag_XML_xml->priority) && is_numeric((string)$ag_XML_xml->priority) && (string)$ag_XML_xml->priority >= 0) {
                        $ag_xml_value["priority"] = (string)$ag_XML_xml->priority; // XML PRIORITY
                    }
                }
            }    
            $ag_array_data[] = $ag_xml_value;    
        }
        $sort_by= 'priority';
        switch ($arrange) {
            case "date":
                $sort_by = 'date';
            break;
            case "name":
                $sort_by = 'value';
            break;
        }
        // for all arguments without the first starting at end of list
        // clear arrays
        $new_array = array();
        $temporary_array = array();
        // walk through original array
        foreach($ag_array_data as $original_key => $original_value) {
            // and save only values
            $temporary_array[] = $original_value[$sort_by];
        }
        // sort array on values
        natcasesort($temporary_array);
        if($sort_by=="date"){
            $temporary_array=array_reverse($temporary_array,true);
        }
        // delete double values
        $temporary_array = array_unique($temporary_array);
        // walk through temporary array
        foreach($temporary_array as $temporary_value) {
            // walk through original array
            foreach($ag_array_data as $original_key => $original_value) {
                // and search for entries having the right value
                if($temporary_value == $original_value[$sort_by]) {
                    // save in new array
                    $new_array[$original_key] = $original_value;
                }
            }
        }
        // update original array
        $ag_array_data = $new_array;
        // CREATE SORTED ARRAY 
        $array = Array(); 
        foreach($ag_array_data as $original_key => $original_value) {
            // and save only values
            $array[] = $original_value['value'];
        }
        return $array;
    }  
    /**
     *  TODO:Add description
     * @param <type> $hex
     * @param <type> $adjust
     * @return <type>
     */
    public static function ag_foregroundColor($hex, $adjust) {
        $red = hexdec($hex[0] . $hex[1]);
        $green = hexdec($hex[2] . $hex[3]);
        $blue = hexdec($hex[4] . $hex[5]);
        if (($red + $green + $blue) >= 255) {
            $red -= $adjust;
            $green -= $adjust;
            $blue -= $adjust;
            if ($red < 0)
                $red = 0;
            if ($green < 0)
                $green = 0;
            if ($blue < 0)
                $blue = 0;
        }else {
            $red += $adjust;
            $green += $adjust;
            $blue += $adjust;
            if ($red > 255)
                $red = 255;
            if ($green > 255)
                $green = 255;
            if ($blue > 255)
                $blue = 255;
        }

        return str_pad(dechex($red), 2, '0', 0)
        . str_pad(dechex($green), 2, '0', 0)
        . str_pad(dechex($blue), 2, '0', 0);
    }
    /**
     * IMAGEINFO Last Update: 06.12.2008. Igor Kekeljevic, 2008.
     * @param <string> $imageURL
     * @return $imageInfo array:"width","height","type","size"
     */
    public static function ag_imageInfo($imageURL) {

        list($width, $height, $type, $attr) = getimagesize($imageURL);

        $types = Array(
            1 => 'GIF',
            2 => 'JPG',
            3 => 'PNG',
            4 => 'SWF',
            5 => 'PSD',
            6 => 'BMP',
            7 => 'TIFF(intel byte order)',
            8 => 'TIFF(motorola byte order)',
            9 => 'JPC',
            10 => 'JP2',
            11 => 'JPX',
            12 => 'JB2',
            13 => 'SWC',
            14 => 'IFF',
            15 => 'WBMP',
            16 => 'XBM'
        );

        if ($type) {

            return $imageInfo = array(
        "width" => $width,
        "height" => $height,
        "type" => $types[$type],
        "size" => filesize($imageURL)
            );
        }
    }
    /**
     * Rounds the file size for output
     * @param <type> $size
     * @return <type>
     */
    public static function ag_fileRoundSize($size) {
        $bytes = array('B', 'KB', 'MB', 'GB', 'TB');
        foreach ($bytes as $val) {
            if ($size > 1024) {
                $size = $size / 1024;
            } else {
                break;
            }
        }
        return round($size, 2) . " " . $val;
    }
    /**
     * Read's all folders in folder.
     * @param <string> $targetFolder
     * @return array or null
     */
    public static function ag_foldersArrayFromFolder($targetFolder) {
        $returnValue = null;
        unset($folders);
        if (!file_exists($targetFolder)) {
            return $returnValue;
        }
        $folders = array();
        $dh = opendir($targetFolder);
        if ($dh) {
            while (($f = readdir($dh)) !== false) {
                if (is_dir($targetFolder . $f) && $f != "." && $f != "..") {
                    $folders[] = $f;
                }
            }
            $returnValue = $folders;
        }
        closedir($dh);
        return $returnValue;
    }
    /**
     * Removes thumb folder
     * @param <type> $originalFolder
     * @param <type> $thumbFolder
     */
    public static function ag_cleanThumbsFolder($originalFolder, $thumbFolder) {
        $origin = agHelper::ag_foldersArrayFromFolder($originalFolder);
        $thumbs = agHelper::ag_foldersArrayFromFolder($thumbFolder);
        $diffArray = array_diff($thumbs, $origin);
        if ($diffArray != null) {
            foreach ($diffArray as $diffFolder) {
                agHelper::ag_sureRemoveDir($thumbFolder . $diffFolder, true);
            }
        }
    }
    /**
     * Removes old and unused thumbs
     * @param <type> $imagesFolder
     * @param <type> $thumbsFolder
     * @param <type> $albumsInUse
     * @return <type>
     */
    public static function ag_clearOldThumbs($imagesFolder, $thumbsFolder, $albumsInUse=false) {

        // Generate array of thumbs
        $targetFolder = $thumbsFolder;
        $thumbs = agHelper::ag_imageArrayFromFolder($targetFolder, 0);

        // Generate array of images
        $targetFolder = $imagesFolder;
        $images = agHelper::ag_imageArrayFromFolder($targetFolder, 0);

        if (empty($images) && !$albumsInUse) {
            agHelper::ag_sureRemoveDir($thumbsFolder, 1);
            return;
        }

        // Locate and delete old thumbs
        if (!empty($thumbs)) {
            foreach ($thumbs as $thumbsKey => $thumbsValue) {
                if ((!in_array($thumbsValue, $images)) && (!empty($thumbsValue)) && file_exists($thumbsFolder . $thumbsValue)) {
                    unlink($thumbsFolder . $thumbsValue);
                }
            }
        }
    }
    /**
     * Makes directory, returns TRUE if exists or made
     * @param string $pathname The directory path.
     * @return boolean returns TRUE if exists or made or FALSE on failure.
     */
    public static function ag_mkdir_recursive($pathname, $mode) {
        is_dir(dirname($pathname)) || agHelper::ag_mkdir_recursive(dirname($pathname), $mode);
        return is_dir($pathname) || @mkdir($pathname, $mode);
    }
    /**
     * Removes dir or file
     * @param <type> $dir
     * @param <type> $DeleteMe
     * @return <type>
     */
    public static function ag_sureRemoveDir($dir, $DeleteMe) {
        if (!$dh = @opendir($dir))
            return;
        while (false !== ($obj = readdir($dh))) {
            if ($obj == '.' || $obj == '..')
                continue;
            if (!@unlink($dir . '/' . $obj))
                agHelper::ag_sureRemoveDir($dir . '/' . $obj, true);
        }

        closedir($dh);
        if ($DeleteMe) {
            @rmdir($dir);
        }
    }
    /**
     * Read's all images from folder
     * @param <string> $targetFolder
     * @param <string> $arrange
     * @return <array> Sorted array of pictures
     */
    public static function ag_imageArrayFromFolder($targetFolder) {
        $images = Array();
        if (!agHelper::ag_exists($targetFolder)) {
            return null;
        }
        $dh = opendir($targetFolder);
        if ($dh) {
            $ag_ext_valid = array("jpg", "jpeg", "gif", "png"); // SET VALID IMAGE EXTENSION
            while (($f = readdir($dh)) !== false) {
                if (is_numeric(array_search(strtolower(agHelper::ag_getExtension(basename($f))), $ag_ext_valid))) {
                    $images[] = $f;
                }
            }
        }
        closedir($dh);
        return $images;        
    }
    /**
     * Gets the atributes value by name, else returns false
     * @param string $attrib
     * @param string $tag
     * @param value $default
     * @return $default value if no presented
     */
    public static function ag_getParams($attrib, $tag, $default) {
        //get attribute from html tag
        $re = '/' . preg_quote($attrib) . '=([\'"])?((?(1).+?|[^\s>]+))(?(1)\1)/is';
        if (preg_match($re, $tag, $match)) {
            return urldecode($match[2]);
        }
        return $default;
    }
    /**
     * Creates thumbnail from original images
     * return $errorMessage if creation fails
     * @param string $original_file
     * @param string $thumb_file
     * @param int $new_w
     * @param int $new_h
     * @param string $autoSize
     * @return type 
     */
    public static function ag_createThumb($original_file, $thumb_file, $new_w, $new_h, $autoSize) {

        //GD check
        if (!function_exists('gd_info')) {
            // ERROR - Invalid image
            return JText::_('AG_GD_SUPPORT_IS_NOT_ENABLED');
        }

        // Create src_img
        if (preg_match("/jpg|jpeg/i", $original_file)) {
            @$src_img = imagecreatefromjpeg($original_file);
        } else if (preg_match("/png/i", $original_file)) {
            @$src_img = imagecreatefrompng($original_file);
        } else if (preg_match("/gif/i", $original_file)) {
            @$src_img = imagecreatefromgif($original_file);
        } else {
            return JText::sprintf('AG_UNSUPPORTED_IMAGE_TYPE_FOR_IMAGE', $original_file);
        }

        @$src_width = imageSX($src_img); //$src_width
        @$src_height = imageSY($src_img); //$src_height
        $src_w = $src_width;
        $src_h = $src_height;
        $src_x = 0;
        $src_y = 0;
        $dst_w = $new_w;
        $dst_h = $new_h;
        $src_ratio = $src_w / $src_h;
        $dst_ratio = $new_w / $new_h;

        switch ($autoSize) {
            case "width":
                // AUTO WIDTH
                $dst_w = $dst_h * $src_ratio;
                break;
            case "height":
                // AUTO HEIGHT
                $dst_h = $dst_w / $src_ratio;
                break;
            case "none":
                // If proportion of source image is wider then proportion of thumbnail image, then use full height of source image and crop the width.
                if ($src_ratio > $dst_ratio) {
                    // KEEP HEIGHT, CROP WIDTH
                    $src_w = $src_h * $dst_ratio;
                    $src_x = floor(($src_width - $src_w) / 2);
                } else {
                    // KEEP WIDTH, CROP HEIGHT
                    $src_h = $src_w / $dst_ratio;
                    $src_y = floor(($src_height - $src_h) / 2);
                }
                break;
        }

        @$dst_img = imagecreatetruecolor($dst_w, $dst_h);

        // PNG THUMBS WITH ALPHA PATCH
        if (preg_match("/png/i", $original_file)) {
            // Turn off alpha blending and set alpha flag
            imagealphablending($dst_img, false);
            imagesavealpha($dst_img, true);
        }

        @imagecopyresampled($dst_img, $src_img, 0, 0, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

        if (preg_match("/jpg|jpeg/i", $original_file)) {
            @imagejpeg($dst_img, $thumb_file);
        } else if (preg_match("/png/i", $original_file)) {
            @imagepng($dst_img, $thumb_file);
        } else if (preg_match("/gif/i", $original_file)) {
            @imagegif($dst_img, $thumb_file);
        } else {
            return JText::sprintf('AG_COULD_NOT_CREATE_THUMBNAIL_FILE_FOR_IMAGE', $original_file);
        }
        @imagedestroy($dst_img);
        @imagedestroy($src_img);
    }
    /**
     * Creates blank HTML file
     * @param <string> $filename
     */
    public static function ag_indexWrite($filename) {
        $handle = fopen($filename, "w") or die("");
        $contents = fwrite($handle, '<html><body bgcolor="#FFFFFF"></body></html>');
        fclose($handle);
    }
    /**
     * Parses OS name from $user_agent string
     * @param string $user_agent
     * @return string $OsName 
     */
    public static function ag_get_os_($user_agent) {
        $oses = array(
            'Windows 3.11' => 'Win16',
            'Windows 95' => '(Windows 95)|(Win95)|(Windows_95)',
            'Windows 98' => '(Windows 98)|(Win98)',
            'Windows 2000' => '(Windows NT 5.0)|(Windows 2000)',
            'Windows XP' => '(Windows NT 5.1)|(Windows XP)',
            'Windows 2003' => '(Windows NT 5.2)',
            'Windows NT 4.0' => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)',
            'Windows ME' => 'Windows ME',
            'Open BSD' => 'OpenBSD',
            'Sun OS' => 'SunOS',
            'Linux' => '(Linux)|(X11)',
            'Macintosh' => '(Mac_PowerPC)|(Macintosh)',
            'QNX' => 'QNX',
            'BeOS' => 'BeOS',
            'OS\/2' => 'OS\/2',
            'Search Bot' => '(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp\/cat)|(msnbot)|(ia_archiver)'
        );
        foreach ($oses as $os => $pattern) {
            if (preg_match('/' . $pattern . '/', $user_agent))
                return $os;
        }
        return 'Unknown';
    }
    /**
     *  Removes the file extension
     * @param string $fileName
     * @return sting $fileNameNoExtension
     */
    public static function ag_removExtension($fileName) {
        $ext = strrchr($fileName, '.');
        if ($ext !== false) {
            $fileName = substr($fileName, 0, -strlen($ext));
        }
        return $fileName;
    }
    /**
     * Returns extension from filename
     * @param string $fileName
     * @return string $extension 
     */
    public static function ag_getExtension($fileName) {
        return substr(strrchr($fileName, '.'), 1);
    }
    /**
     * Wrapper for standard file_exists
     * @param string $fileName
     * @return bool 
     */
    public static function ag_exists($fileName) {
        return file_exists($fileName);
    }
    /**
     * Check for existance of the remote file
     * @param string $path
     * @return bool  
     */
    public static function ag_remote_exists($path) {
        return (@fopen($path,"r")==true);   
    }
    /**
     * Shrink string for display
     * @param type $string
     * @param type $stringLength
     * @param type $add
     * @return string
     */
    public static function ag_shrinkString($string,$stringLength,$add=''){
        if(strlen($string)>$stringLength){
              $string = substr($string,0,$stringLength);
              $string.="...";
        }
        return $string;
    }
}
?>
