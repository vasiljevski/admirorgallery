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
 * @since       4.5.0
 */
class Gallery
{
	/**
	 * @var CmsInterface
	 * @since 4.5.0
	 */
	public CmsInterface $cms;

	/**
	 * @var Parameters
	 * @since 4.5.0
	 */
	public Parameters $params;

	/**
	 * @var Popup
	 * @since 4.5.0
	 */
	public Popup $popupEngine;

	/**
	 * @var ErrorHandler
	 * @since 4.5.0
	 */
	public ErrorHandler $errorHandle;

	/**
	 * @var string|false
	 * @since 4.5.0
	 */
	public string $sitePath = '';

	/**
	 * @var string
	 * @since 4.5.0
	 */
	public string $sitePhysicalPath = '';

	/**
	 * Virtual path. Example: "https://www.mysite.com/plugin/content/admirorgallery/thumbs/"
	 * @var string
	 * @since 4.5.0
	 */
	public string $thumbsFolderPath = '';

	/**
	 * Physical path on the server. Example: "E:\php\www\joomla/plugin/content/admirorgallery/thumbs/"
	 * @var string
	 * @since 4.5.0
	 */
	public string $thumbsFolderPhysicalPath = '';

	/**
	 * Gallery name. Example: food
	 * @var string
	 * @since 4.5.0
	 */
	public string $imagesFolderName = '';

	/**
	 * Physical path on the server. Example: "E:\php\www\joomla/plugin/content/"
	 * @var string
	 * @since 4.5.0
	 */
	public string $imagesFolderPhysicalPath = '';

	/**
	 * Virtual path. Example: "https://www.mysite.com/images/stories/food/"
	 * @var string
	 * @since 4.5.0
	 */
	public string $imagesFolderPath = '';

	/**
	 * @var array|null
	 * @since 4.5.0
	 */
	public ?array $images = array();

	/**
	 * Array:"width","height","type","size"
	 * @var array|null
	 * @since 4.5.0
	 */
	public ?array $imageInfo = array();

	/**
	 * @var integer
	 * @since 4.5.0
	 */
	public int $index = -1;

	/**
	 * @var integer
	 * @since 4.5.0
	 */
	public int $articleID = 0;

	/**
	 * @var string
	 * @since 4.5.0
	 */
	public string $currPopupRoot = '';

	/**
	 * @var string
	 * @since 4.5.0
	 */
	public string $currTemplateRoot = '';

	/**
	 * Virtual path. Example: "https://www.mysite.com/plugins/content/admirorgallery/"
	 * @var string
	 * @since 4.5.0
	 */
	public string $domainPluginPath = '';

	/**
	 * @var boolean
	 * @since 4.5.0
	 */
	public bool $squareImage = false;

	/**
	 * @var array
	 * @since 4.5.0
	 */
	public array$paginInitPages = array();

	/**
	 * @var array
	 * @since 4.5.0
	 */
	public array $albumInitFolders = array();

	/**
	 * @var integer
	 * @since 4.5.0
	 */
	public int $paginImgTotal = 0;

	/**
	 * @var integer
	 * @since 4.5.0
	 */
	public int $numOfGal = 0;

	/**
	 * @var string
	 * @since 4.5.0
	 */
	public string $albumParentLink = '';

	/**
	 * @var array|null
	 * @since 4.5.0
	 */
	public ?array $folders;

	/**
	 * @var string
	 * @since 4.5.0
	 */
	public string $imagesFolderNameOriginal;

	/**
	 * @var array
	 * @since 4.5.0
	 */
	private array $descArray = array();

	/**
	 * @var string
	 * @since 4.5.0
	 */
	private string $match = '';

	/**
	 * @var string
	 * @since 4.5.0
	 */
	private string $DS = DIRECTORY_SEPARATOR;

	/**
	 * @var string
	 * @since 4.5.0
	 */
	private string $absolutePluginPath = '/plugins/content/admirorgallery/admirorgallery/';

	/*
	 * Template API functions
	 */

	/**
	 * Gets image info data, and loads it in imageInfo array. It also rounds image size.
	 *
	 * @param   string $imageName Image name
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function getImageInfo(string $imageName): void
	{
		$this->imageInfo = Helper::imageInfo($this->imagesFolderPhysicalPath . $this->DS . $imageName);
		$this->imageInfo["size"] = Helper::roundFileSize($this->imageInfo["size"]);
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
	 * Loads CSS files from the given path.
	 *
	 * @param   string $path File path to CSS file
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function loadCSS(string $path): void
	{
		$this->cms->addCss($this->sitePath . $this->absolutePluginPath . $path);
	}

	/**
	 * Loads JavaScript files from the given path.
	 *
	 * @param   string $path Path to JavaScript file
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function loadJS(string $path): void
	{
		$this->cms->addJsFile($this->sitePath . $this->absolutePluginPath . $path);
	}

	/**
	 * Loads JavaScript code block into document head.
	 *
	 * @param   string $script JavaScript to be added
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function insertJSCode(string $script): void
	{
		$this->cms->addJsDeclaration($script);
	}

	/**
	 * Returns specific inline parameter if entered or returns default value
	 *
	 * @param   string $attrib  Parameter name
	 * @param   string $default Default value
	 *
	 * @return string
	 *
	 * @since 5.5.0
	 */
	public function getParameter(string $attrib, string $default): string
	{
		return $this->params->getParamFromHTML($attrib, $this->match, $default);
	}

	/**
	 * Returns full image html
	 *
	 * @param   string $imageName Image name
	 * @param   string $cssClass  CSS class to be used
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
	 * @param   string $imageName Image name
	 * @param   string $cssClass  CSS class to be used
	 *
	 * @return string
	 *
	 * @since 5.5.0
	 */
	public function writeThumb(string $imageName, string $cssClass=''): string
	{
		return '<img src="' . $this->sitePath . $this->absolutePluginPath . 'thumbs/' . $this->imagesFolderName . '/' . $imageName . '"
                alt="' . strip_tags($this->descArray[$imageName]) . '"
                class="' . $cssClass . '">';
	}

	/**
	 * Generates HTML with new image tag
	 *
	 * @param   string $image Image name
	 *
	 * @return string
	 *
	 * @since 5.5.0
	 */
	public function writeNewImageTag(string $image): string
	{
		// DEFAULT DATE
		$fileAge = date("YmdHi", filemtime($this->imagesFolderPhysicalPath . $image));
		$dateLimit = date("YmdHi",
			mktime(date("H"),
				date("i"),
				date("s"),
				date("m"),
				date("d") - (int) ($this->params['newImageTag_days']),
				date("Y")
			)
		);

		if ($fileAge > $dateLimit && $this->params['newImageTag'] == 1)
		{
			return '<span class="ag_newTag"><img src="' .
				$this->sitePath . $this->absolutePluginPath .
				'newTag.gif" class="ag_newImageTag"  alt="New"/></span>';
		}

		return '';
	}

	/**
	 * Generates HTML with Popup engine integration
	 *
	 * @param   string $image Image name
	 *
	 * @return string
	 *
	 * @since 5.5.0
	 */
	public function writePopupThumb(string $image): string
	{
		$html = '';

		if ($this->popupEngine->customPopupThumb)
		{
			$html = $this->popupEngine->customPopupThumb;
			$html = str_replace("{imagePath}", $this->imagesFolderPath . $image, $html);
			$html = str_replace("{imageDescription}", htmlspecialchars($this->descArray[$image], ENT_QUOTES), $html);
			$html = str_replace("{className}", $this->popupEngine->className, $html);
			$html = str_replace("{rel}", $this->popupEngine->rel, $html);
			$html = str_replace("{customAttr}", $this->popupEngine->customAttr, $html);
			$html = str_replace("{newImageTag}", $this->writeNewImageTag($image), $html);
			$html = str_replace("{thumbImagePath}",
				$this->sitePath . $this->absolutePluginPath . 'thumbs/' . $this->imagesFolderName . '/' . $image,
				$html
			);
		}
		else
		{
			$html .= '<a href="' . $this->imagesFolderPath . $image . '" title="' .
				htmlspecialchars($this->descArray[$image], ENT_QUOTES) .
				'" class="' . $this->popupEngine->className . '" rel="' .
				$this->popupEngine->rel . '" ' . $this->popupEngine->customAttr .
				' target="_blank">' . $this->writeNewImageTag($image) .
				'<img src="' . $this->sitePath . $this->absolutePluginPath . 'thumbs/' .
				$this->imagesFolderName . '/' . $image . '" alt="' .
				strip_tags($this->descArray[$image]) .
				'" class="ag_imageThumb"></a>';
		}

		return $html;
	}

	/**
	 * Generates HTML link to album page
	 *
	 * @param   string $defaultFolderImg  Folder default image
	 * @param   string $thumbHeight       Thumbnail height
	 *
	 * @return string
	 *
	 * @since 5.5.0
	 */
	public function writeFolderThumb(string $defaultFolderImg, string $thumbHeight): string
	{
		// Album Support
		$html = "";

		if ($this->params['albumUse'] && !empty($this->folders))
		{
			$html .= '<div class="AG_album_wrap">' . "\n";

			foreach ($this->folders as $folderKey => $folderName)
			{
				$thumbPath = $this->getAlbumThumbPath($defaultFolderImg, $folderName);
				$html .= '<a href="javascript:void(0);" onClick="AG_form_submit_' .
							$this->articleID . '(' . $this->index . ',1,\'' . $this->imagesFolderName . '/' . $folderName .
							'\'); return false;" class="AG_album_thumb">';
				$html .= '<span class="AG_album_thumb_img" >';
				$html .= '<img style="height: ' . $thumbHeight . 'px;" src="' . $thumbPath . '" />' . "\n";
				$html .= '</span>';
				$html .= '<span class="AG_album_thumb_label">';
				$html .= $this->descArray[$folderName];
				$html .= '</span>';
				$html .= '</a>';
			}

			$html .= '<br style="clear:both;" /></div>' . "\n";
		}

		return $html;
	}

	/**
	 * Returns album thumb path
	 *
	 * @param   string $defaultFolderImg  Default folder image
	 * @param   string $folderName        Folder name
	 *
	 * @return string
	 *
	 * @since 5.5.0
	 */
	public function getAlbumThumbPath(string $defaultFolderImg, string $folderName): string
	{
		// Get Thumb URL value
		// Set Possible Description File Absolute Path // Instant patch for upper and lower case...
		$pathWithStripExt = $this->imagesFolderPhysicalPath . $folderName;
		$xmlPath = $pathWithStripExt . ".XML";

		if (file_exists($pathWithStripExt . ".xml"))
		{
			$xmlPath = $pathWithStripExt . ".xml";
		}

		if (file_exists($xmlPath))
		{
			// Check is descriptions file exists
			$xmlObject = simplexml_load_file($xmlPath);

			if (isset($xmlObject->thumb))
			{
				$thumbFile = (string) $xmlObject->thumb;
			}
		}

		if (empty($thumbFile))
		{
			$images = Helper::imageArrayFromFolder($this->imagesFolderPhysicalPath . $folderName);

			if (!empty($images))
			{
				$images = Helper::arraySorting($images, $this->imagesFolderPhysicalPath . $folderName . $this->DS, $this->params['arrange']);

				// Get First image in folder as thumb
				$thumbFile = $images[0];
			}
		}

		if (!empty($thumbFile))
		{
			$this->createAlbumThumb($folderName, $thumbFile);
			$thumbFile = 'thumbs/' . $this->imagesFolderName . '/' . $folderName . '/' . basename($thumbFile);
		}
		else
		{
			$thumbFile = $this->currTemplateRoot . $defaultFolderImg;
		}

		return $this->sitePath . $this->absolutePluginPath . $thumbFile;
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

		if ($this->params['paginUse'])
		{
			if ($this->params['paginUse'])
			{
				if (!empty($this->paginImgTotal) && ceil($this->paginImgTotal / $this->params['paginImagesPerGallery']) > 1)
				{
					$html .= '<div class="AG_pagin_wrap">';
					$paginPrev = ($this->paginInitPages[$this->index] - 1);

					if ($paginPrev >= 1)
					{
						$html .= '<a href="javascript:void(0);" onClick="AG_form_submit_' .
									$this->articleID . '(' . $this->index . ',' . $paginPrev . ',\'' . $this->imagesFolderName .
									'\'); return false;" class="AG_pagin_prev">' . $this->cms->text("AG_PREV") . '</a>';
					}

					for ($i = 1; $i <= ceil($this->paginImgTotal / $this->params['paginImagesPerGallery']); $i++)
					{
						if ($i == $this->paginInitPages[$this->index])
						{
							$html .= '<span class="AG_pagin_current">' . $i . '</span>';
						}
						else
						{
							$html .= '<a href="javascript:void(0);" onClick="AG_form_submit_' .
										$this->articleID . '(' . $this->index . ',' . $i . ',\'' . $this->imagesFolderName .
										'\',this);return false;" class="AG_pagin_link">' . $i . '</a>';
						}
					}

					$paginNext = ($this->paginInitPages[$this->index] + 1);

					if ($paginNext <= ceil($this->paginImgTotal / $this->params['paginImagesPerGallery']))
					{
						$html .= '<a href="javascript:void(0);" onClick="AG_form_submit_' .
									$this->articleID . '(' . $this->index . ',' . $paginNext . ',\'' . $this->imagesFolderName .
									'\'); return false;" class="AG_pagin_next">' . $this->cms->text("AG_NEXT") . '</a>';
					}

					$html .= '<br style="clear:both"></div>';
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

		if (!empty($this->images))
		{
			foreach ($this->images as $imagesKey => $imagesValue)
			{
				$html .= '<a href="' . $this->imagesFolderPath . $imagesValue .
							'" title="' . htmlspecialchars(strip_tags($this->descArray[$imagesValue])) .
							'" class="' . $this->popupEngine->className .
							'" rel="' . $this->popupEngine->rel . '" ' . $this->popupEngine->customAttr .
							' target="_blank">';
				$html .= $this->writeNewImageTag($imagesValue);
				$html .= '<img src="' . $this->sitePath . $this->absolutePluginPath . 'thumbs/' . $this->imagesFolderName . '/' . $imagesValue . '
                        " alt="' . htmlspecialchars(strip_tags($this->descArray[$imagesValue])) . '" class="ag_imageThumb"></a>';
			}
		}

		return $html;
	}

	/**
	 * Returns image description. The current localization is taken into account.
	 *
	 * @param   string $imageName Image name
	 *
	 * @return mixed
	 *
	 * @since 5.5.0
	 */
	public function writeDescription(string $imageName): mixed
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
		require dirname(__FILE__, 2) . $this->DS . 'popups' . $this->DS . $this->params['popupEngine'] . $this->DS . 'index.php';

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

	/*
	 * END Template API functions
	 */
	/*
	 * Gallery Functions
	 */

	/**
	 * Gallery initialization
	 *
	 * @param   string $match Content to look for the trigger code
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function initGallery(string $match): void
	{
		$this->match = $match;
		$this->readInlineParams();
		$this->imagesFolderNameOriginal = (string) preg_replace("/{.+?}/", "", $match);
		$this->imagesFolderName = strip_tags($this->imagesFolderNameOriginal);

		// Pagination Support
		if ($this->params['paginUse'] || $this->params['albumUse'])
		{
			$initPages = $this->cms->getActivePage('AG_form_paginInitPages_' . $this->articleID);
			$albumPath = $this->cms->getAlbumPath('AG_form_albumInitFolders_' . $this->articleID);

			$this->paginInitPages[] = 1;

			if (!empty($_GET['AG_form_paginInitPages_' . $this->articleID]))
			{
				$formPaginInitPages = explode(",", $_GET['AG_form_paginInitPages_' . $this->articleID]);
				$this->paginInitPages[$this->index] = strip_tags($formPaginInitPages[$this->index]);
			}

			$script = 'var paginInitPages_' . $this->articleID . '="' . $initPages . '";';

			$this->cms->addJsDeclaration(strip_tags($script));

			// Album Support
			$this->albumParentLink = '';
			$this->albumInitFolders[] = "";

			// Set init folders
			$this->albumInitFolders[$this->index] = strip_tags($this->imagesFolderName);

			if (!empty($_GET['AG_form_albumInitFolders_' . $this->articleID]))
			{
				$formAlbumInitFolders = explode(",", $_GET['AG_form_albumInitFolders_' . $this->articleID]);
				$this->albumInitFolders[$this->index] = strip_tags($formAlbumInitFolders[$this->index]);
				$this->imagesFolderName = strip_tags($formAlbumInitFolders[$this->index]);

				// Support for Album Parent Link
				if ($this->imagesFolderName != $this->imagesFolderNameOriginal)
				{
					$this->albumParentLink = '
                        <a href="javascript:void(0);" onClick="AG_form_submit_' .
							strip_tags($this->articleID) . '(' . strip_tags($this->index) . ',1,\'' .
							strip_tags(dirname($this->imagesFolderName)) .
							'\'); return false;" class="AG_album_parent">
                            <span>
                                ' . strip_tags(basename(dirname($this->imagesFolderName))) . '
                            </span>
                        </a>
                        <br style="clear:both;" />
                        ';
				}
			}

			// Breadcrumb Support
			if ($this->cms->isBreadcrumbsNeeded())
			{
				$this->writeBreadcrumb();
			}

			$script = 'var albumInitFolders_' . $this->articleID . '="' . $albumPath . '";';
			$this->cms->addJsDeclaration(strip_tags($script));
		}

		$this->imagesFolderPhysicalPath = $this->sitePhysicalPath . $this->params['rootFolder'] . $this->imagesFolderName . $this->DS;
		$this->thumbsFolderPhysicalPath = $this->sitePhysicalPath .
											$this->absolutePluginPath . 'thumbs' .
											$this->DS . $this->imagesFolderName . $this->DS;
		$this->imagesFolderPath = $this->sitePath . $this->params["rootFolder"] . $this->imagesFolderName . '/';
		$this->readDescriptionFiles();
		$this->loadImageFiles();
		$this->loadFolders();
		$this->currPopupRoot = 'popups/' . $this->params['popupEngine'] . '/';
		$this->currTemplateRoot = 'templates/' . $this->params['template'] . '/';
		$this->domainPluginPath = $this->sitePath . $this->absolutePluginPath;
	}

	/**
	 * Clears obsolete thumbnail folders
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function cleanThumbsFolder(): void
	{
		Helper::cleanThumbsFolder($this->imagesFolderPhysicalPath,
			$this->thumbsFolderPhysicalPath
		);
	}

	/**
	 *  Clears obsolete thumbnails
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function clearOldThumbs(): void
	{
		Helper::clearOldThumbs($this->imagesFolderPhysicalPath,
			$this->thumbsFolderPhysicalPath, $this->params['albumUse']
		);
	}

	/**
	 *  Reads description files
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	private function readDescriptionFiles(): void
	{
		// Create Images Array
		unset($this->descArray);

		if (file_exists($this->imagesFolderPhysicalPath))
		{
			$images = array();
			$files = $this->cms->getFiles($this->imagesFolderPhysicalPath);

			// SET VALID IMAGE EXTENSION
			$validExtentions = array("jpg", "jpeg", "gif", "png");

			foreach ($files as $key => $value)
			{
				if (is_numeric(array_search(strtolower(Helper::getExtension(basename($value))), $validExtentions)))
				{
					$images[] = $value;
				}
			}

			$files = array_merge($images, $this->cms->getFolders($this->imagesFolderPhysicalPath));

			if (!empty($files))
			{
				foreach ($files as $key => $f)
				{
					// Set image name as imageDescription value, as predefined value
					$this->descArray[$f] = $f;

					// Set Possible Description File Absolute Path // Instant patch for upper and lower case...
					$pathWithStripExt = $this->imagesFolderPhysicalPath . Helper::removeExtension($f);
					$descriptionFileAbsolutePath = $pathWithStripExt . ".XML";

					if (file_exists($pathWithStripExt . ".xml"))
					{
						$descriptionFileAbsolutePath = $pathWithStripExt . ".xml";
					}

					if (file_exists($descriptionFileAbsolutePath))
					{
						// Check is descriptions file exists
						$imgXmlObject = simplexml_load_file($descriptionFileAbsolutePath);
						$imgXmlCaptions = $imgXmlObject->captions;
						$langTag = $this->cms->getActiveLanguageTag();

						// GET DEFAULT LABEL
						if (!empty($imgXmlCaptions->caption))
						{
							foreach ($imgXmlCaptions->caption as $imgXmlCaptions)
							{
								if (strtolower($imgXmlCaptions->attributes()->lang) == "default")
								{
									$this->descArray[$f] = $imgXmlCaptions;
								}
							}
						}

						// GET CURRENT LANG LABEL
						if (!empty($imgXmlCaptions->caption))
						{
							foreach ($imgXmlCaptions->caption as $imgXmlCaptions)
							{
								if (strtolower($imgXmlCaptions->attributes()->lang) == strtolower($langTag))
								{
									$this->descArray[$f] = $imgXmlCaptions;
								}
							}
						}

						// RICH TEXT SUPPORT
						if ($this->params['plainTextCaptions'])
						{
							$this->descArray[$f] = strip_tags($this->descArray[$f]);
						}
					}
				}
			}// If(file_exists($descriptionFileAbsolutePath))
		}
		else
		{
			$this->descArray = array();
		}
	}

	/**
	 *  Loads images array, sorted as defined by parameter.
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	private function loadImageFiles(): void
	{
		$this->images = Helper::imageArrayFromFolder($this->imagesFolderPhysicalPath);

		if (!empty($this->images))
		{
			$this->images = Helper::arraySorting($this->images, $this->imagesFolderPhysicalPath, $this->params['arrange']);
		}

		// Pagination Support
		if ($this->params['paginUse'])
		{
			$this->paginImgTotal = count($this->images);
			$paginImages = array();
			$paginStart = ($this->paginInitPages[$this->index] - 1) * $this->params['paginImagesPerGallery'];
			$paginEnd = ($this->paginInitPages[$this->index] * $this->params['paginImagesPerGallery']) - 1;

			if (!empty($this->images))
			{
				for ($i = $paginStart; $i <= $paginEnd; $i++)
				{
					if ($i < $this->paginImgTotal)
					{
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
	 * @return void
	 *
	 * @since 5.5.0
	 */
	private function loadFolders(): void
	{
		$this->folders = Helper::foldersArrayFromFolder($this->imagesFolderPhysicalPath);

		if (!empty($this->folders))
		{
			$this->folders = Helper::arraySorting($this->folders, $this->imagesFolderPhysicalPath, $this->params['arrange']);
		}
	}

	/**
	 * Check if thumbnail parameters are set
	 *
	 * @return void
	 *
	 * @since 6.0.0
	 */
	private function validateParams(): void
	{
		if (($this->params['thumbWidth'] == 0) || ($this->params['thumbHeight'] == 0))
		{
			$this->errorHandle->addError($this->cms->text("AG_CANNOT_CREATE_THUMBNAILS_WIDTH_AND_HEIGHT_MUST_BE_GREATER_THEN_0"));
		}
	}
	/**
	 * Generates image thumbs
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function createGalleryThumbs(): void
	{
		$this->validateParams();

		// Adds index.html to thumbs folder
		Helper::writeIndexFile($this->thumbsFolderPhysicalPath . $this->DS . 'index.html');

		// Check for Changes
		if (!empty($this->images))
		{
			foreach ($this->images as $imagesKey => $image)
			{
				$originalFile = $this->imagesFolderPhysicalPath . $image;
				$thumbFile = $this->thumbsFolderPhysicalPath . $image;
				$this->generateThumb($originalFile, $thumbFile);
			}
		}
	}

	/**
	 * Generates album thumbs
	 *
	 * @param   string $parentFolder Folder path
	 * @param   string $img          Image
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function createAlbumThumb(string $parentFolder, string $img): void
	{
		$this->validateParams();

		$imagesFolderPhysicalPath = $this->imagesFolderPhysicalPath . $parentFolder . $this->DS;
		$thumbsFolderPhysicalPath = $this->thumbsFolderPhysicalPath . $parentFolder . $this->DS;

		// Create directory in thumbs for gallery
		if (!file_exists($thumbsFolderPhysicalPath))
		{
			// TODO:Handle return value
			$this->cms->createFolder($thumbsFolderPhysicalPath);
		}

		// Adds index.html to thumbs folder
		Helper::writeIndexFile($thumbsFolderPhysicalPath . 'index.html');

		$originalFile = $imagesFolderPhysicalPath . $img;
		$thumbFile = $thumbsFolderPhysicalPath . $img;
		$this->generateThumb($originalFile, $thumbFile);
	}
	/**
	 * Generates and updates thumbnails according to settings
	 *
	 * @param   string $originalFile Original file path
	 * @param   string $thumbFile    Thumb file path
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	private function generateThumb(string $originalFile, string $thumbFile): void
	{
		$createThumb = false;

		if (!file_exists($thumbFile))
		{
			$createThumb = true;
		}
		else
		{
			list($width, $height) = getimagesize($thumbFile);

			switch ($this->params['thumbAutoSize'])
			{
				case "none":
					if ($height != $this->params['thumbHeight'] || $width != $this->params['thumbWidth'])
					{
						$createThumb = true;
					}
					break;
				case "height":
					if ($width != $this->params['thumbWidth'])
					{
						$createThumb = true;
					}
					break;
				case "width":
					if ($height != $this->params['thumbHeight'])
					{
						$createThumb = true;
					}
					break;
			}
		}

		if ($createThumb)
		{
			$result = Helper::createThumbnail(
				$originalFile,
				$thumbFile,
				$this->params['thumbWidth'],
				$this->params['thumbHeight'],
				$this->params['thumbAutoSize']
			);

			if ($result)
			{
				$this->errorHandle->addError($this->cms->textConcat($result, $originalFile));
			}
		}

		// ERROR - Invalid image
		if (!file_exists($thumbFile))
		{
			$this->errorHandle->addError($this->cms->textConcat("AG_CANNOT_READ_THUMBNAIL", $thumbFile));
		}
	}

	/**
	 * Breadcrumb Support
	 *
	 * @author: Lee Anderson
	 * @email: landerson@atlas-tech.com
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function writeBreadcrumb(): void
	{
		$folderNames = str_replace('//', '/', $this->imagesFolderName);
		$albumName = explode("/", $folderNames);
		$folderNumber = count($albumName) - 1;
		$linkFolderName = '';

		for ($i = 0; $i <= $folderNumber; $i++)
		{
			$linkFolderName .= $albumName[$i] . '/';
			$linkFolderName = str_replace('//', '/', $linkFolderName);

			if ($albumName[$i] != '' && $i != 0)
			{
				$link = 'Javascript: AG_form_submit_' . $this->articleID . '(' . $this->index . ',1,\'' . $linkFolderName . '\');';
				$this->cms->setTitle($albumName[$i]);
				$this->cms->addToPathway($albumName[$i], $link);
			}
		}
	}

	/**
	 *  Reads inline parameter if any or sets default values
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function readInlineParams(): void
	{
		$this->params->readInlineParams($this->match);
	}

	/**
	 * @param   string $id String ID
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	public function getText($id): mixed
	{
		return $this->cms->text($id);
	}

	/**
	 * @param   integer  $id    String ID
	 * @param   string   $value Text to concatenate
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	public function getConcatText(int $id, $value): mixed
	{
		return $this->cms->textConcat($id, $value);
	}

	/**
	 * Gallery constructor, sets path values, sets document reference
	 *
	 * @param   mixed        $globalParams     Plugin parameters
	 * @param   string       $path             Site path
	 * @param   string       $sitePhysicalPath Absolute path
	 * @param   CmsInterface $cms              CMS implementation
	 *
	 * @since 5.5.0
	 */
	public function __construct($globalParams, string $path, string $sitePhysicalPath, CmsInterface $cms)
	{
		$this->cms = $cms;
		$this->params = new Parameters($globalParams);
		$this->popupEngine = new Popup;
		$this->errorHandle = new ErrorHandler;

		if (substr($path, -1) == "/")
		{
			$path = substr($path, 0, -1);
		}

		$this->sitePath = $path;
		$this->sitePhysicalPath = $sitePhysicalPath;
		$this->thumbsFolderPhysicalPath = $sitePhysicalPath . $this->absolutePluginPath . 'thumbs' . $this->DS;
		$this->imagesFolderPhysicalPath = $sitePhysicalPath . $this->params["rootFolder"];
		$this->cleanThumbsFolder();
		$this->loadCSS('AdmirorGallery.css');
	}
	/*
	 * END Gallery Functions
	 */
}
