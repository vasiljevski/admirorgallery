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
 * @since        5.5.0
 */
class ErrorHandler
{
	/**
	 * @var array
	 * @since 5.5.0
	 */
	private array $errors = array();
	/**
	 * Adds new error value to the error array
	 *
	 * @param   string $value Error message to be added
	 *
	 * @return void
	 *
	 * @since 5.5.0
	 */
	public function addError(string $value): void
	{
		if ($value != '')
		{
			$this->errors[] = $value;
		}
	}
	/**
	 * Returns error html
	 *
	 * @return string
	 *
	 * @since 5.5.0
	 */
	public function writeErrors(): string
	{
		$errors = "";
		$osVersion = isset($_SERVER['HTTP_USER_AGENT']) ? agHelper::ag_get_os_($_SERVER['HTTP_USER_AGENT']) : 'Unknown - no user agent';
		$phpVersion = phpversion();

		if (isset($this->errors))
		{
			foreach ($this->errors as $key => $value)
			{
				$errors .= '<div class="error">' . $value . ' <br/>
                        Admiror Gallery: ' . AG_VERSION . '<br/>
                        Server OS:' . $_SERVER['SERVER_SOFTWARE'] . '<br/>
                        Client OS:' . $osVersion . '<br/>
                        PHP:' . $phpVersion . '
                        </div>' . "\n";
			}

			unset($this->errors);
		}

		return $errors;
	}
}
