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

use ArrayAccess;

 /**
  * Parameters class AG uses
  *
  * @since 5.5.0
  */
class Parameters implements ArrayAccess
{
	/**
	 * @var   array
	 * @since 5.5.0
	 */
	private array $staticParams = [];

	/**
	 * @var array
	 * @since 5.5.0
	 */
	private array $params;

	/**
	 * @param   array   $globalParams    Parameters
	 */
	public function __construct($globalParams)
	{
		foreach ($globalParams as $key => $value)
		{
			$this->staticParams[$key] = $value;
		}

		$this->params = $this->staticParams;
	}

	/**
	 *  Reads inline parameter if any or sets default values
	 *
	 * @param   string $match String containtng the parameters
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function readInlineParams(string $match)
	{
		$filterParams = substr($match, strpos($match, " "), strpos($match, "}") - strpos($match, " "));
		$keyValuePair = explode(' ', $filterParams);
		$params = array();

		// If there is less than one value, no params are added inline
		if (count($keyValuePair) < 2)
		{
			return;
		}

		foreach ($keyValuePair as $value)
		{
			$split = explode("=", $value);

			if (count($split) > 1)
			{
				$params[$split[0]] = $split[1];
			}
		}

		foreach ($params as $key => $value)
		{
			$this->params[$key] = trim($value, '"');
		}
	}

	/**
	 * Returns specific inline parameter if entered or returns default value
	 *
	 * @param   string   $attrib    Key to search for
	 * @param   string   $tag       String to search in
	 * @param   string   $default   Default value
	 *
	 * @return string|$default value if no presented
	 *
	 * @since 5.5.0
	 */
	public static function getParamFromHTML(string $attrib, string $tag, string $default): string
	{
		// Get attribute from html tag
		$re = '/' . preg_quote($attrib) . '=([\'"])?((?(1).+?|[^\s>]+))(?(1)\1)/is';

		if (preg_match($re, $tag, $match))
		{
			return urldecode($match[2]);
		}

		return $default;
	}

	/**
	 * @param   mixed   $offset The offset to assign the value to.
	 * @param   mixed   $value  The value to set.
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function offsetSet(mixed $offset, mixed $value): void
	{
		if (is_null($offset))
		{
			$this->params[] = $value;
		}
		else
		{
			$this->params[$offset] = $value;
		}
	}

	/**
	 * @param   mixed   $offset  The offset to assign the value to.
	 *
	 * @return boolean
	 *
	 * @since 5.5.0
	 */
	public function offsetExists($offset): bool
	{
		return isset($this->params[$offset]);
	}

	/**
	 * @param   mixed   $offset  The offset to assign the value to.
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function offsetUnset(mixed $offset): void
	{
		unset($this->params[$offset]);
	}

	/**
	 * @param   mixed   $offset  The offset to assign the value to.
	 *
	 * @return mixed|null
	 *
	 * @since 5.5.0
	 */
	public function offsetGet($offset)
	{
		return $this->params[$offset] ?? null;
	}

}
