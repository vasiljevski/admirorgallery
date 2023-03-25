<?php
/**
 * @version     0.3.0
 * @package     Admiror.Administrator
 * @subpackage  com_admirorgallery
 * @author      Mesut Timur <mesut@h-labs.org>
 * @copyright   Copyright (C) 2008 mesut@h-labs.org All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

/**
 * SecureImage
 *
 * @since 0.3.0
 */
class SecureImage
{
	/**
	 * file
	 *
	 * @var mixed
	 */
	private $file;

	/**
	 * image
	 *
	 * @var mixed
	 */
	private $image;

	/**
	 * extension
	 *
	 * @var mixed
	 */
	private $extension;

	/**
	 * Constructor
	 *
	 * @param   string $file File path
	 *
	 * @since 0.3.0
	 */
	public function __construct(string $file)
	{
		$this->file = $file;

		// Get extension
		$this->extension = strrchr($this->file, '.');
		$this->extension = strtolower($this->extension);
	}

	/**
	 * Check routine
	 *
	 * @return boolean
	 *
	 * @since 0.3.0
	 */
	public function checkIt(): bool
	{
		// If it can be opened
		$this->image = $this->openImage($this->file);

		if ($this->image == false)
		{
			return false;
		}

		// Removing EXIF
		$this->convert();

		return true;
	}

	/**
	 * openImage
	 *
	 * @param   mixed $file Filepath to open
	 *
	 * @return mixed
	 *
	 * @since 0.3.0
	 */
	private function openImage(string $file): mixed
	{
		switch ($this->extension)
		{
			case '.jpg':
			case '.jpeg':
				$im = @imagecreatefromjpeg($this->file);
				break;
			case '.gif':
				$im = @imagecreatefromgif($this->file);
				break;
			case '.png':
				$im = @imagecreatefrompng($this->file);
				break;

			default:
				$im = false;
				break;
		}

		return $im;
	}

	/**
	 * Converts the image and removes EXIF data
	 *
	 * @return void
	 *
	 * @since 0.3.0
	 */
	private function convert(): void
	{
		switch ($this->extension)
		{
			case '.jpg':
			case '.jpeg':
				imagegif($this->image, $this->file);
				imagejpeg($this->image, $this->file);
				$this->jpgClean("clean.tmp");
				rename("clean.tmp", $this->file);
				break;
			case '.gif':
				imagejpeg($this->image, $this->file);
				imagegif($this->image, $this->file);
				break;
			case '.png':
				imagejpeg($this->image, $this->file);
				imagepng($this->image, $this->file);
				break;
			default:
				die("Something went wrong");
		}
	}

	/**
	 * jpgClean
	 *
	 * @param   mixed $destination Destination path
	 * @param   mixed $erstellen   First time?
	 *
	 * @return mixed
	 *
	 * @since 0.3.0
	 */
	private function jpgClean(string $destination, bool $erstellen = true): mixed
	{
		// By Robert Beran
		// webmaster@robert-beran.de
		$handle = fopen($this->file, "rb");
		$segment[] = fread($handle, 2);

		if ($segment[0] === "\xFF\xD8")
		{
			$segment[] = fread($handle, 1);

			if ($segment[1] === "\xFF")
			{
				rewind($handle);

				while (!feof($handle))
				{
					$daten = fread($handle, 2);

					if ((preg_match("/FFE[1-9a-zA-Z]{1,1}/i", bin2hex($daten))) || ($daten === "\xFF\xFE"))
					{
						$position = ftell($handle);
						$size = fread($handle, 2);
						$newsize = 256 * ord($size[0]) + ord($size[1]);
						$newpos = $position + $newsize;
						fseek($handle, $newpos);
					}
					else
					{
							$newfile[] = $daten;
					}
				}

				fclose($handle);
				$newfile = implode('', $newfile);

				if ($erstellen === true)
				{
					$handle = fopen($destination, "wb");
					fwrite($handle, $newfile);
					fclose($handle);

					return true;
				}
				else
				{
					return $newfile;
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
}

