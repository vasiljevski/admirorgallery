<?php
/**
 * @version     6.0.0
 * @package     Admiror Gallery (plugin)
 * @subpackage  admirorgallery
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2021 https://www.admiror-design-studio.com All Rights Reserved.
 * @since       5.5.0
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

class agErrorHandler
{
    private array $errors = [];

    /**
     * Adds new error value to the error array
     *
     * @param $value
     */
    public function addError($value): void
    {
        if ($value !== '') {
            array_push($this->errors, $value);
        }
    }

    /**
     * Returns error html
     *
     * @param array $params An array of parameters including the values of AG_VERSION, SERVER_SOFTWARE, HTTP_USER_AGENT, and PHP_VERSION.
     *
     * @return string
     *
     * @since 5.5.0
     */
    public function writeErrors(array $params): string
    {
        $errors = '';
        $osVersion = agHelper::ag_get_os_($params['HTTP_USER_AGENT'] ?? '');
        $phpVersion = $params['PHP_VERSION'] ?? phpversion();

        if (isset($this->errors)) {
            foreach ($this->errors as $key => $value) {
                $errors .= '<div class="error">' . $value . ' <br/>
                        Admiror Gallery: ' . $params['AG_VERSION'] . '<br/>
                        Server OS:' . $params['SERVER_SOFTWARE'] . '<br/>
                        Client OS:' . $osVersion . '<br/>
                        PHP:' . $phpVersion . '
                        </div>';
            }
            unset($this->errors);
        }
        return $errors;
    }
}