<?php
/**
 * @version     5.5.0
 * @package     Admiror Gallery (plugin)
 * @subpackage  admirorgallery
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2018 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/**
 * Description of agParams
 *
 */
class agParams implements ArrayAccess {

    private $staticParams = [];
    private $params = [];

    public function __construct($globalParams) {
        foreach ($globalParams as $key => $value) {
            $this->staticParams[$key] = $value;
        }
        $this->params = $this->staticParams;
    }

    /**
     *  Reads inline parameter if any or sets default values
     *
     * @param string $match
     *
     * @since 5.0.0
     */
    public function readInlineParams(string $match) {
        $filter_params = substr($match, strpos($match, " "), strpos($match, "}") - strpos($match, " "));
        $key_value_pair = explode(' ', $filter_params);
        $params = array();
        
        //if there is less than one value, no params are added inline
        if (count($key_value_pair) < 2) {
            return;
        }

        foreach ($key_value_pair as $value) {
            $split = explode("=", $value);
            if(count($split) > 1) {
                $params[$split[0]] = $split[1];
            }
        }
        foreach ($params as $key => $value) {
            $this->params[$key] = trim($value, '"');
        }
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->params[] = $value;
        } else {
            $this->params[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->params[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->params[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->params[$offset]) ? $this->params[$offset] : null;
    }

}
