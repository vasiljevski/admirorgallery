<?php
/**
 * @version     5.5.0
 * @package     Admiror Gallery (plugin)
 * @subpackage  admirorgallery
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2020 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

class agErrorHandler
{
    private array $errors = array();
    /**
     * Adds new error value to the error array
     *
     * @param $value
     *
     * @since 5.5.0
     */
    public function addError($value)
    {
        if ($value != '') {
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
        if (isset($this->errors)) {
            foreach ($this->errors as $key => $value) {
                $errors.='<div class="error">' . $value . ' <br/>
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