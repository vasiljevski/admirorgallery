<?php
/**
 * @version     5.5.0
 * @package     Admiror Gallery (plugin)
 * @subpackage  admirorgallery
 * @author      Igor Kekeljevic & Nikola Vasiljevski
 * @copyright   Copyright (C) 2010 - 2020 http://www.admiror-design-studio.com All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/**
 * Parameters class AG uses
 *
 * @since 5.5.0
 */
class agParams implements ArrayAccess {

    private array $staticParams = [];
    private array $params;

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
     * @since 5.5.0
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

    /**
     * Returns specific inline parameter if entered or returns default value
     *
     * @param string $attrib
     * @param string $tag
     * @param $default
     *
     * @return string|$default value if no presented
     *
     * @since 5.5.0
     */
    public static function getParamFromHTML(string $attrib, string $tag, $default): string
    {
        //get attribute from html tag
        $re = '/' . preg_quote($attrib) . '=([\'"])?((?(1).+?|[^\s>]+))(?(1)\1)/is';
        if (preg_match($re, $tag, $match)) {
            return urldecode($match[2]);
        }
        return $default;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     *
     * @since 5.5.0
     */
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->params[] = $value;
        } else {
            $this->params[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     *
     * @since 5.5.0
     */
    public function offsetExists($offset): bool
    {
        return isset($this->params[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @since 5.5.0
     */
    public function offsetUnset($offset) {
        unset($this->params[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed|null
     *
     * @since 5.5.0
     */
    public function offsetGet($offset) {
        return isset($this->params[$offset]) ? $this->params[$offset] : null;
    }

}
