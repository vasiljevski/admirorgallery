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

namespace Admiror\Plugin\Content\AdmirorGallery;

/**
 * @package     Admiror\Plugin\Content\AdmirorGallery
 *
 * @since       5.5.0
 */
class Helper
{
	/**
	 * https://www.php.net/manual/en/function.natsort.php#45346
	 *
	 * @param   array  $array         Array to sort
	 * @param   string $targetFolder  Folder to use
	 * @param   string $arrange       Arrange type (date, name)
	 *
	 * @return array
	 *
	 * @since 5.5.0
	 */
	public static function arraySorting(array $array, string $targetFolder, string $arrange): array
	{
		$arrayData = array();

		// READS XML DATA AND GENERATES ARRAYS
		foreach ($array as $key => $value)
		{
			// Set Possible Description File Absolute Path // Instant patch for upper and lower case...
			$pathWithStripExt = $targetFolder . self::removeExtension(basename($value));
			$xmlPath = $pathWithStripExt . ".xml";

			if (file_exists($pathWithStripExt . ".XML"))
			{
				$xmlPath = $pathWithStripExt . ".XML";
			}

			$xmlValue = array();

			 // IMAGE NAME
			$xmlValue["value"] = $value;

			// DEFAULT PRIORITY
			$xmlValue["priority"] = "none";

			 // DEFAULT DATE
			$xmlValue["date"] = date("YmdHi", filemtime($targetFolder . $value));

			if (file_exists($xmlPath))
			{
				$xmlObject = simplexml_load_file($xmlPath);

				if ($xmlObject)
				{
					if (isset($xmlObject->visible))
					{
						if ((string) $xmlObject->visible == 'false')
						{
							// SKIP HIDDEN IMAGES
							continue;
						}
					}

					if (isset($xmlObject->priority) && is_numeric((string) $xmlObject->priority) && (string) $xmlObject->priority >= 0)
					{
						// XML PRIORITY
						$xmlValue["priority"] = (string) $xmlObject->priority;
					}
				}
			}

			$arrayData[] = $xmlValue;
		}

		$sortBy = 'priority';

		switch ($arrange)
		{
			case "date":
				$sortBy = 'date';
					break;
			case "name":
				$sortBy = 'value';
					break;
		}

		// For all arguments without the first starting at end of list
		// clear arrays
		$newArray = array();
		$tempArray = array();

		// Walk through original array
		foreach ($arrayData as $originalKey => $originalValue)
		{
			// And save only values
			$tempArray[] = $originalValue[$sortBy];
		}

		// Sort array on values
		natcasesort($tempArray);

		if ($sortBy == "date")
		{
			$tempArray = array_reverse($tempArray, true);
		}

		// Delete double values
		$tempArray = array_unique($tempArray);

		// Walk through temporary array
		foreach ($tempArray as $temporaryValue)
		{
			// Walk through original array
			foreach ($arrayData as $originalKey => $originalValue)
			{
				// And search for entries having the right value
				if ($temporaryValue == $originalValue[$sortBy])
				{
					// Save in new array
					$newArray[$originalKey] = $originalValue;
				}
			}
		}

		// Update original array
		$arrayData = $newArray;

		// CREATE SORTED ARRAY
		$array = array();

		foreach ($arrayData as $originalKey => $originalValue)
		{
			// And save only values
			$array[] = $originalValue['value'];
		}

		return $array;
	}

	/**
	 *  Returns foreground color
	 *
	 * @param   string $hex    Hex value
	 * @param   string $adjust Adjust ratio
	 *
	 * @return string
	 *
	 * @since 5.5.0
	 */
	public function foregroundColor(string $hex, string $adjust): string
	{
		$red = hexdec($hex[0] . $hex[1]);
		$green = hexdec($hex[2] . $hex[3]);
		$blue = hexdec($hex[4] . $hex[5]);

		if (($red + $green + $blue) >= 255)
		{
			$red -= $adjust;
			$green -= $adjust;
			$blue -= $adjust;

			if ($red < 0)
			{
				$red = 0;
			}

			if ($green < 0)
			{
				$green = 0;
			}

			if ($blue < 0)
			{
						$blue = 0;
			}
		}
		else
		{
			$red += $adjust;
			$green += $adjust;
			$blue += $adjust;

			if ($red > 255)
			{
				$red = 255;
			}

			if ($green > 255)
			{
				$green = 255;
			}

			if ($blue > 255)
			{
						$blue = 255;
			}
		}

		return str_pad(dechex($red), 2, '0', 0)
		. str_pad(dechex($green), 2, '0', 0)
		. str_pad(dechex($blue), 2, '0', 0);
	}

	/**
	 * imageInfo
	 *
	 * @param   string $imageURL Image url
	 *
	 * @return array|null $imageInfo array:"width","height","type","size"
	 *
	 * @since 5.5.0
	 */
	public static function imageInfo(string $imageURL): ?array
	{
		list($width, $height, $type, $attr) = getimagesize($imageURL);

		$types = array(
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

		if ($type)
		{
			return $imageInfo = array(
			"width" => $width,
			"height" => $height,
			"type" => $types[$type],
			"size" => filesize($imageURL)
			);
		}

		return null;
	}

	/**
	 * Rounds the file size for output
	 *
	 * @param   int $size Size to be rounded
	 *
	 * @return string
	 *
	 * @since 5.5.0
	 */
	public static function roundFileSize(int $size): string
	{
		$bytes = array('B', 'KB', 'MB', 'GB', 'TB');

		foreach ($bytes as $val)
		{
			if ($size > 1024)
			{
				$size = $size / 1024;
			}
			else
			{
				break;
			}
		}

		return round($size, 2) . " " . $val;
	}

	/**
	 * Read's all folders in folder.
	 *
	 * @param   string $targetFolder Target folder path
	 *
	 * @return array or null
	 *
	 * @since 5.5.0
	 */
	public static function foldersArrayFromFolder(string $targetFolder): ?array
	{
		unset($folders);

		if (!file_exists($targetFolder))
		{
			return null;
		}

		$folders = array();
		$returnValue = null;
		$dh = opendir($targetFolder);

		if ($dh)
		{
			while (($f = readdir($dh)) !== false)
			{
				if (is_dir($targetFolder . $f) && $f != "." && $f != "..")
				{
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
	 *
	 * @param   string $originalFolder Original folder path
	 * @param   string $thumbFolder    Thumbnail folder path
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public static function cleanThumbsFolder(string $originalFolder, string $thumbFolder): void
	{
		$origin = self::foldersArrayFromFolder($originalFolder);
		$thumbs = self::foldersArrayFromFolder($thumbFolder);

		if ($thumbs === null)
		{
			return;
		}

		$diffArray = array_diff($thumbs, $origin);

		if ($diffArray != null)
		{
			foreach ($diffArray as $diffFolder)
			{
				self::sureRemoveDir($thumbFolder . $diffFolder, true);
			}
		}
	}

	/**
	 * Removes old and unused thumbs
	 *
	 * @param   string $imagesFolder Image folder path
	 * @param   string $thumbsFolder Thumbnail folder path
	 * @param   bool   $albumsInUse  Use ablums true|false, default false
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public static function clearOldThumbs(string $imagesFolder, string $thumbsFolder, bool $albumsInUse=false): void
	{

		// Generate array of thumbs
		$targetFolder = $thumbsFolder;
		$thumbs = self::imageArrayFromFolder($targetFolder);

		// Generate array of images
		$targetFolder = $imagesFolder;
		$images = self::imageArrayFromFolder($targetFolder);

		if (empty($images) && !$albumsInUse)
		{
			self::sureRemoveDir($thumbsFolder, 1);

			return;
		}

		// Locate and delete old thumbs
		if (!empty($thumbs))
		{
			foreach ($thumbs as $thumbsKey => $thumbsValue)
			{
				if ((!in_array($thumbsValue, $images)) && (!empty($thumbsValue)) && file_exists($thumbsFolder . $thumbsValue))
				{
					unlink($thumbsFolder . $thumbsValue);
				}
			}
		}
	}

	/**
	 * Makes directory, returns TRUE if exists or made
	 *
	 * @param   string  $pathname The directory path.
	 * @param   integer $mode     Permissions mode
	 *
	 * @return boolean returns TRUE if exists or made or FALSE on failure.
	 *
	 * @since 5.5.0
	 */
	public static function mkdirRecursive(string $pathname, $mode): bool
	{
		is_dir(dirname($pathname)) || self::mkdirRecursive(dirname($pathname), $mode);

		return is_dir($pathname) || @mkdir($pathname, $mode);
	}

	/**
	 * Removes dir or file
	 *
	 * @param   string   $dir      Directory path to delete
	 * @param   boolean  $deleteMe Remove dir
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public static function sureRemoveDir(string $dir, bool $deleteMe): void
	{
		if (!$dh = @opendir($dir))
		{
			return;
		}

		while (false !== ($obj = readdir($dh)))
		{
			if ($obj == '.' || $obj == '..')
			{
				continue;
			}

			if (!@unlink($dir . '/' . $obj))
			{
				self::sureRemoveDir($dir . '/' . $obj, true);
			}
		}

		closedir($dh);

		if ($deleteMe)
		{
			@rmdir($dir);
		}
	}

	/**
	 * Read's all images from folder
	 *
	 * @param   string $targetFolder Target folder path
	 *
	 * @return array|null Sorted array of pictures
	 *
	 * @since 5.5.0
	 */
	public static function imageArrayFromFolder(string $targetFolder): ?array
	{
		if (!file_exists($targetFolder))
		{
			return null;
		}

		$images = array();
		$dh = opendir($targetFolder);

		if ($dh)
		{
			// SET VALID IMAGE EXTENSION
			$validExtentions = array("jpg", "jpeg", "gif", "png");

			while (($f = readdir($dh)) !== false)
			{
				if (is_numeric(array_search(strtolower(self::getExtension(basename($f))), $validExtentions)))
				{
					$images[] = $f;
				}
			}
		}

		closedir($dh);

		return $images;
	}

	/**
	 * Creates thumbnail from original images return $errorMessage if creation fails
	 *
	 * @param   string  $originalFile  Original file path
	 * @param   string  $thumbFile     Thumb file path
	 * @param   integer $newWidth      New width in pixels
	 * @param   integer $newHeight     New height in pixels
	 * @param   string  $autoSize      Autosize type ( width|height|none )
	 *
	 * @return integer 0 if thumb was created or string_is of error message
	 *
	 * @since 5.5.0
	 */
	public static function createThumbnail(string $originalFile,string $thumbFile,int $newWidth,int $newHeight,string $autoSize)
	{
		// GD check
		if (!function_exists('gd_info'))
		{
			// ERROR - Invalid image
			return 'AG_GD_SUPPORT_IS_NOT_ENABLED';
		}

		// Create srcImg
		if (preg_match("/jpg|jpeg/i", $originalFile))
		{
			@$srcImg = imagecreatefromjpeg($originalFile);
		}
		elseif (preg_match("/png/i", $originalFile))
		{
			@$srcImg = imagecreatefrompng($originalFile);
		}
		elseif (preg_match("/gif/i", $originalFile))
		{
			@$srcImg = imagecreatefromgif($originalFile);
		}
		else
		{
			return 'AG_UNSUPPORTED_IMAGE_TYPE_FOR_IMAGE';
		}

		@$srcWidth = imageSX($srcImg);
		@$srcHeight = imageSY($srcImg);
		$srcW = $srcWidth;
		$srcH = $srcHeight;
		$srcX = 0;
		$srcY = 0;
		$dstW = $newWidth;
		$dstH = $newHeight;
		$srcRatio = $srcW / $srcH;
		$dstRatio = $newWidth / $newHeight;

		switch ($autoSize)
		{
			case "width":
				// AUTO WIDTH
				$dstW = $dstH * $srcRatio;
				break;
			case "height":
				// AUTO HEIGHT
				$dstH = $dstW / $srcRatio;
				break;
			case "none":
				// If proportion of source image is wider than proportion of thumbnail image, then use full height of source image and crop the width.
				if ($srcRatio > $dstRatio)
				{
					// KEEP HEIGHT, CROP WIDTH
					$srcW = $srcH * $dstRatio;
					$srcX = floor(($srcWidth - $srcW) / 2);
				}
				else
				{
					// KEEP WIDTH, CROP HEIGHT
					$srcH = $srcW / $dstRatio;
					$srcY = floor(($srcHeight - $srcH) / 2);
				}
				break;
		}

		@$dstImg = imagecreatetruecolor($dstW, $dstH);

		// PNG THUMBS WITH ALPHA PATCH
		if (preg_match("/png/i", $originalFile))
		{
			// Turn off alpha blending and set alpha flag
			imagealphablending($dstImg, false);
			imagesavealpha($dstImg, true);
		}

		@imagecopyresampled($dstImg, $srcImg, 0, 0, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);

		if (preg_match("/jpg|jpeg/i", $originalFile))
		{
			@imagejpeg($dstImg, $thumbFile);
		}
		elseif (preg_match("/png/i", $originalFile))
		{
			@imagepng($dstImg, $thumbFile);
		}
		elseif (preg_match("/gif/i", $originalFile))
		{
			@imagegif($dstImg, $thumbFile);
		}
		else
		{
			return 'AG_COULD_NOT_CREATE_THUMBNAIL_FILE_FOR_IMAGE';
		}

		@imagedestroy($dstImg);
		@imagedestroy($srcImg);

		return 0;
	}

	/**
	 * Creates blank HTML file
	 *
	 * @param   string $filename Filename to create
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public static function writeIndexFile(string $filename): void
	{
		if (!touch($filename))
		{
			trigger_error("index.html could not be created!");
		}
	}

	/**
	 * Parses OS names from $userAgent string
	 *
	 * @param   string $userAgent User agent string
	 *
	 * @return string $OsName
	 *
	 * @since 5.5.0
	 */
	public static function getOsName(string $userAgent): string
	{
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

		foreach ($oses as $os => $pattern)
		{
			if (preg_match('/' . $pattern . '/', $userAgent))
			{
				return $os;
			}
		}

		return 'Unknown';
	}

	/**
	 *  Removes the file extension
	 *
	 * @param   string $fileName Filename to sanitize
	 *
	 * @return false|string
	 *
	 * @since 5.5.0
	 */
	public static function removeExtension(string $fileName)
	{
		$ext = strrchr($fileName, '.');

		if ($ext !== false)
		{
			$fileName = substr($fileName, 0, -strlen($ext));
		}

		return $fileName;
	}

	/**
	 * Returns extension from filename
	 *
	 * @param   string $fileName Filename to get extension from
	 *
	 * @return string $extension
	 *
	 * @since 5.5.0
	 */
	public static function getExtension(string $fileName): string
	{
		return substr(strrchr($fileName, '.'), 1);
	}

	/**
	 * Check for existence of the remote file
	 *
	 * @param   string $path Path to remote
	 *
	 * @return boolean
	 *
	 * @since 5.5.0
	 */
	public static function remoteExists(string $path): bool
	{
		return (@fopen($path, "r") == true);
	}

	/**
	 * Shrink string for display
	 *
	 * @param   string $string       String to shrink
	 * @param   string $stringLength Desired length
	 * @param   string $add          Add to the end
	 *
	 * @return string
	 *
	 * @since 5.5.0
	 */
	public static function shrinkString(string $string, string $stringLength, string $add='...'): string
	{
		if (strlen($string) > $stringLength)
		{
			$string = substr($string, 0, $stringLength);
			$string .= $add;
		}

		return $string;
	}
}
