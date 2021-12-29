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
class Popup
{
	/**
	 * @var string
	 * @since 5.5.0
	 */
	public string $customAttr = '';

	/**
	 * @var string
	 * @since 5.5.0
	 */
	public string $rel = '';

	/**
	 * @var string
	 * @since 5.5.0
	 */
	public string $className = '';

	/**
	 * @var string
	 * @since 5.5.0
	 */
	public string $jsInclude = '';

	/**
	 * @var string
	 * @since 5.5.0
	 */
	public string $initCode = '';

	/**
	 * @var string
	 * @since 5.5.0
	 */
	public string $customPopupThumb = '';

	/**
	 * @var string
	 * @since 5.5.0
	 */
	public string $endCode = '';
}
