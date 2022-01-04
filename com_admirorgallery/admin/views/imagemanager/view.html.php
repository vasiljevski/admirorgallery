<?php
/**
 * @version     6.0.0
 * @package     Admiror.Administrator
 * @subpackage  com_admirorgallery
 * @author      Igor Kekeljevic <igor@admiror.com>
 * @author      Nikola Vasiljevski <nikola83@gmail.com>
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use Joomla\CMS\Uri\Uri as JURI;
use Joomla\Registry\Registry as JRegistry;
use Joomla\CMS\MVC\View\HtmlView as JViewLegacy;
use Joomla\CMS\Toolbar\ToolbarHelper as JToolBarHelper;
use Admiror\Plugin\Content\AdmirorGallery\Helper;

/**
 * AdmirorgalleryViewImagemanager
 *
 * @since 1.0.0
 */
class AdmirorgalleryViewImagemanager extends JViewLegacy
{
	/**
	 * @var string
	 *
	 * @since 1.0.0
	 */
	public string $templateName = 'default';

	/**
	 * @var string
	 *
	 * @since 1.0.0
	 */
	public string $initItemURL = '';

	/**
	 * @var string
	 *
	 * @since 1.0.0
	 */
	public string $startingFolder = '';

	/**
	 * @var string
	 *
	 * @since 1.0.0
	 */
	public string $rootFolder = '';

	/**
	 * @var null
	 *
	 * @since 1.0.0
	 */
	public $app = null;

	/**
	 * @var string
	 *
	 * @since 1.0.0
	 */
	public string $thumbsPath = JPATH_SITE . DIRECTORY_SEPARATOR .
	'administrator' . DIRECTORY_SEPARATOR .
	'components' . DIRECTORY_SEPARATOR .
	'com_admirorgallery' . DIRECTORY_SEPARATOR .
	'assets' . DIRECTORY_SEPARATOR .
	'thumbs';

	/**
	 * display
	 *
	 * @param   string $tpl Templalate to load
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function display($tpl = null): void
	{
		// Check if plugin is installed, otherwise don't show view
		if (!is_dir(JPATH_SITE . '/plugins/content/admirorgallery/'))
		{
			return;
		}

		/**
		 * Make sure you are logged in and have the necessary access
		 * 5 - Publisher
		 * 6 - Manager
		 * 7 - Administrator
		 * 8 - Super Users
		 */
		$validUsers = array(5 ,6 ,7 ,8);
		$user = JFactory::getUser();
		$this->app = JFactory::getApplication();
		$post = JFactory::getApplication()->input->post;
		$grantAccess = false;
		$userGroups = $user->getAuthorisedGroups();

		foreach ($userGroups as $group)
		{
			if (in_array($group, $validUsers))
			{
				$grantAccess = true;
				break;
			}
		}

		if (!$grantAccess)
		{
			$this->app->setHeader('status', 403, true);
			$this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');

			return;
		}

		/**
		 * Backward compatibility with Joomla!3
		 * Load namespace
		*/
		JLoader::registerNamespace('Admiror\\Plugin\\Content\\AdmirorGallery',
			JPATH_PLUGINS . "/content/admirorgallery/admirorgallery/core/",
			false,
			false,
			'psr4'
		);

		 // Current template for AG Component
		$this->templateName = $post->get('template', 'default');
		$itemUrl = $post->getVar('itemURL');

		// GET ROOT FOLDER
		$plugin = JPluginHelper::getPlugin('content', 'admirorgallery');
		$pluginParams = new JRegistry($plugin->params);

		$rootFolder = $pluginParams->get('rootFolder', '/images/sampledata/');
		$galleryPath = $rootFolder;

		if ($this->app->isClient('site'))
		{
			$galleryPath .= ($this->app->getParams()->get('galleryName') != "-1") ? $this->app->getParams()->get('galleryName') . "/" : "";
		}

		$this->rootFolder = $pluginParams->get('rootFolder', '/images/sampledata/');
		$this->startingFolder = $this->rootFolder;
		$this->initItemURL = $this->rootFolder;

		// Check if we are in site
		if ($this->app->isClient('site'))
		{
			$this->startingFolder = $galleryPath;
			$this->initItemURL = $galleryPath;
		}

		if (isset($itemUrl))
		{
			$this->initItemURL = $itemUrl;
		}

		JToolBarHelper::title(JText::_('COM_ADMIRORGALLERY_IMAGE_MANAGER'), 'imagemanager');

		parent::display($tpl);
	}

	/**
	 * getVersionInfoHTML
	 *
	 * @return  string
	 */
	public function getVersionInfoHTML(): string
	{
		$xmlObject = simplexml_load_file(JPATH_COMPONENT_ADMINISTRATOR . '/com_admirorgallery.xml');

		$versionInfo = "";

		if ($xmlObject)
		{
			$versionInfo .= '<li>' . JText::_('COM_ADMIRORGALLERY_COMPONENT_VERSION') . '&nbsp;' . $xmlObject->version . "</li>";
			$versionInfo .= '<li>' . JText::_('COM_ADMIRORGALLERY_PLUGIN_VERSION') . '&nbsp;' . $xmlObject->pluginVersion . "</li>";
			$versionInfo .= '<li>' . JText::_('COM_ADMIRORGALLERY_BUTTON_VERSION') . '&nbsp;' . $xmlObject->buttonVersion . "</li>";
		}

		return $versionInfo;
	}

	/**
	 * renderBreadcrumb
	 *
	 * @param   string $itemURL     Item URL
	 * @param   string $rootFolder  Root folder
	 * @param   string $folderName  Folder name
	 * @param   string $fileName    File name
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function renderBreadcrumb(string $itemURL, string $rootFolder, string $folderName, string $fileName): string
	{
		$breadcrumb = '';
		$breadcrumbLink = '';

		if ($rootFolder != $itemURL && !empty($itemURL))
		{
			$breadcrumb .= '<a href="' . $rootFolder .
			'" class="AG_folderLink AG_common_button"><span><span>' .
			substr($rootFolder, 0, -1) . '</span></span></a>/';

			$breadcrumbLink .= $rootFolder;
			$breadcrumbCut = substr($folderName, strlen($rootFolder));
			$breadcrumbCutArray = explode("/", $breadcrumbCut);

			foreach ($breadcrumbCutArray as $cutValue)
			{
				if (empty($cutValue))
				{
					continue;
				}

				$breadcrumbLink .= $cutValue . '/';
				$breadcrumb .= '<a href="' .
									$breadcrumbLink .
									'" class="AG_folderLink AG_common_button"><span><span>' .
									$cutValue .
									'</span></span></a>/';
			}

			$breadcrumb .= $fileName;
		}
		else
		{
			$breadcrumb .= $rootFolder;
		}

		return $breadcrumb;
	}

	/**
	 * renderImageInfo
	 *
	 * @param   string $itemURL  Item URL
	 * @param   array  $imgInfo  Image info
	 * @param   string $hasXML   Has XML description
	 * @param   string $hasThumb Has Thumbnail
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function renderImageInfo(string $itemURL, array $imgInfo, string $hasXML, string $hasThumb): string
	{
		return '<div class="AG_margin_bottom AG_thumbAndInfo_wrapper">
	            <table>
	                <tbody>
	                    <tr>
	                        <td>
	                            <a class="AG_item_link" href="' .
										substr(JURI::root(), 0, -1) .
										$itemURL . '" title="' .
										$itemURL . '" rel="lightbox[\'AG\']" target="_blank">
										<img src="' . JURI::root() . 'administrator/components/com_admirorgallery/assets/thumbs/' .
										basename($itemURL) .
										'" alt="' . $itemURL . '">
	                            </a>
	                        </td>
	                        <td class="AG_border_color AG_border_width" style="border-left-style:solid;">
	                            <div>' . JText::_("AG_IMG_WIDTH") . ': ' . $imgInfo["width"] . 'px</div>
	                            <div>' . JText::_("AG_IMG_HEIGHT") . ': ' . $imgInfo["height"] . 'px</div>
	                            <div>' . JText::_("AG_IMG_TYPE") . ': ' . $imgInfo["type"] . '</div>
	                            <div>' . JText::_("AG_IMG_SIZE") . ': ' . $imgInfo["size"] . '</div>
	                            <div>' . $hasXML . $hasThumb . '</div>
	                        </td>
	                    </tr>
	                </tbody>
	            </table>   
	            </div>
	            ';
	}

	/**
	 * renderCaption
	 *
	 * @param   mixed $langName    Language name
	 * @param   mixed $langTag     Language tag
	 * @param   mixed $langContent Language content
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	private function renderCaption($langName, $langTag, $langContent): string
	{
		return '
	<div class="AG_border_color AG_border_width AG_margin_bottom">
	    ' . $langName . ' / ' . $langTag . '
	    <textarea class="AG_textarea" name="descContent[]">' .
				$langContent . '</textarea><input type="hidden" name="descTags[]" value="' . $langTag . '" />
	</div>
    ';
	}

	/**
	 * renderCaptions
	 *
	 * @param   mixed $imgCaptions Array of caption objects
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function renderCaptions($imgCaptions): string
	{
		$siteLanguages = "";
		$matchCheck = Array("default");

		// GET DEFAULT LABEL
		$captionContent = "";

		if (!empty($imgCaptions->caption))
		{
			foreach ($imgCaptions->caption as $caption)
			{
				if (strtolower($caption->attributes()->lang) == "default")
				{
					$captionContent = $caption;
				}
			}
		}

		$siteLanguages .= $this->renderCaption("Default", "default", $captionContent);

		$langAvailable = LanguageHelper::getKnownLanguages(JPATH_SITE);

		if (!empty($langAvailable))
		{
			foreach ($langAvailable as $lang)
			{
				$captionContent = "";

				if (!empty($imgCaptions->caption))
				{
					foreach ($imgCaptions->caption as $caption)
					{
						if (strtolower($caption->attributes()->lang) == strtolower($lang["tag"]))
						{
							$captionContent = $caption;
							$matchCheck[] = strtolower($lang["tag"]);
						}
					}
				}

				$siteLanguages .= $this->renderCaption($lang["name"], $lang["tag"], $captionContent);
			}
		}

		if (!empty($imgCaptions->caption))
		{
			foreach ($imgCaptions->caption as $caption)
			{
				$captionAttr = $caption->attributes()->lang;

				if (!is_numeric(array_search(strtolower($captionAttr), $matchCheck)))
				{
					$siteLanguages .= $this->renderCaption($captionAttr, $captionAttr, $caption);
				}
			}
		}

		return $siteLanguages;
	}

	/**
	 * renderFileFooter
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function renderFileFooter(): string
	{
		return '<div style="clear:both" class="AG_margin_bottom"></div>
        <hr />
        <div  class="AG_legend">
        <h2>' . JText::_('AG_LEGEND') . '</h2>
        <table><tbody>
        <tr>
            <td><img src="' . JURI::root() . 'administrator/components/com_admirorgallery/templates/' .
				$this->templateName . '/images/icon-hasThumb.png" style="float:left;" /></td>
            <td>' . JText::_('AG_IMAGE_HAS_THUMBNAIL_CREATED') . '</td>
        </tr>
        <tr>
            <td><img src="' . JURI::root() . 'administrator/components/com_admirorgallery/templates/' .
				$this->templateName . '/images/icon-hasXML.png" style="float:left;" /></td>
            <td>' . JText::_('AG_IMAGE_HAS_ADDITIONAL_DETAILS_SAVED') . '</td>
        </tr>
        </tbody></table>
        <div>
        ';
	}

	/**
	 * getBookmarkPath
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function getBookmarkPath(): string
	{
		return $this->getModel('imagemanager')->bookmarkPath;
	}

	public function renderItems(string $path, string $type, string $thumb): string
	{
		$juriRoot = JURI::root(true);
		$hasXML = "<img src=\"{$juriRoot}/administrator/components/com_admirorgallery/templates/{
			$this->templateName}/images/icon-hasXML.png'  class='hasXML' />";
		$hasThumb = "<img src=\"{$juriRoot}/administrator/components/com_admirorgallery/templates/{
			$this->templateName}/images/icon-hasThumb.png\"  class=\"hasThumb\" />";
		$previewContent = JText::_('AG_NO_FOLDERS_OR_IMAGES_FOUND_IN_CURRENT_FOLDER');
		$items = array();
		$priority = array();
		$noPriority = array();
		$objects = array();
		$validExtensions = array("jpg", "jpeg", "gif", "png"); // SET VALID IMAGE EXTENSION

		if ($type == 'folder')
		{
			$items = JFolder::folders(JPATH_SITE . $path);
		}
		else
		{
			$items = JFolder::files(JPATH_SITE . $path);
		}

		foreach ($items as $value)
		{
			if (($type == 'file') && !is_numeric(array_search(strtolower(JFile::getExt(basename($value))), $validExtensions)))
			{
				continue;
			}

			// Set Possible Description File Absolute Path // Instant patch for upper and lower case...
			$descriptionPath = JPATH_SITE . $path . JFile::stripExt(basename($value)) . ".XML";

			if (file_exists($pathWithStripExt . ".xml"))
			{
				$descriptionPath = $pathWithStripExt . ".xml";
			}

			$description = simplexml_load_file($descriptionPath);

			if (!$description)
			{
				if (!empty($description->priority))
				{
					$priority[$value] = $description->priority;
				}
				else
				{
					$noPriority[] = $value;
				}
			}
			else
			{
				$noPriority[] = $value;
			}

			$previewContent = "";
		}

		asort($priority);

		foreach ($priority as $key => $value)
		{
			$objects[] = $key;
		}

		natcasesort($noPriority);

		foreach ($noPriority as $key => $value)
		{
			$objects[] = $value;
		}

		foreach ($objects as $key => $value)
		{
			$hasThumb = "";

			// Set Possible Description File Absolute Path // Instant patch for upper and lower case...
			$descriptionPath = JPATH_SITE . $path . JFile::stripExt(basename($value)) . ".XML";

			if (file_exists($pathWithStripExt . ".xml"))
			{
				$descriptionPath = $pathWithStripExt . ".xml";
			}

			$description = simplexml_load_file($descriptionPath);

			$visible = "AG_VISIBLE";
			$priority = "";

			if (!$description)
			{
				if (isset($description->priority))
				{
					$priority = $description->priority;
				}

				if (isset($description->visible))
				{
					if ((string) $description->visible == "false")
					{
						$visible = "AG_HIDDEN";
					}
				}
			}

			$valueStripped = $value;
			$iconImage = "<img src=\"{$juriRoot}/administrator/components/com_admirorgallery/templates/
						{$this->templateName}/images/folder.png\" />";

			if ($type != 'folder')
			{
				if (!file_exists(JPATH_SITE . "/plugins/content/admirorgallery/admirorgallery/thumbs/" .
					basename($folderName) . "/" . basename($value)
				)
				)
				{
					$hasThumb = '';
				}

				Helper::createThumbnail(JPATH_SITE . $path . "/" . $value, $this->thumbsPath . DIRECTORY_SEPARATOR . $value, 145, 80, "none");

				$thumbChecked = "";

				if ($thumb == $value)
				{
					$thumbChecked = " CHECKED";
				}

				$iconImage = "<img src=\"{$juriRoot}/administrator/components/com_admirorgallery/assets/thumbs/{$value}
					\" class=\"ag_imgThumb\" />";
				$valueStripped = JFile::stripExt(basename($value));
				$folderThumb = JText::_('AG_FOLDER_THUMB');
				$thumbnailInput = "
					<hr /> 
					<input type=\"radio\" value=\"{$value}\" name=\"folderThumb\" class=\"folderThumb\" class=\"AG_input {$thumbChecked}\"/>&nbsp;
					{$folderThumb}";
			}

			$outputPath = "{$path}/{$value}";
			$visibleText = JText::_($visible);
			$priorityText = JText::_('priority');
			$previewContent .= "
			<div class=\"AG_border_color AG_border_width AG_item_wrapper\">
				<a href=\"{$outputPath}\" class=\"AG_folderLink AG_item_link\" title=\"{$value}\">
					<div style=\"display:block; text-align:center;\" class=\"AG_item_img_wrapper\">
					{$iconImage}					
					</div>
				</a>
				<div class=\"AG_border_color AG_border_width AG_item_controls_wrapper\">
					<input type=\"text\" value=\"{$valueStripped}\" name=\"rename[{$outputPath}]\" class=\"AG_input\" style=\"width:95%\" />
					<hr />
					{$visibleText}
					<hr />
					<img src=\"{$juriRoot}/administrator/components/com_admirorgallery/templates/{$this->templateName}/images/operations.png\" 
						style=\"float:left;\" />
					<input type=\"checkbox\" value=\"{$outputPath}/\" name=\"selectItem[]\" class=\"selectItem\">
					<hr />
					{$priorityText}:&nbsp;
					<input type=\"text\" size=\"3\" value=\"{$priority}\" name=\"cbPriority[{$outputPath}]\" class=\"AG_input\" />
					{$thumbnailInput}
				</div>
			</div>
			";
		}

		return $previewContent;
	}
}
