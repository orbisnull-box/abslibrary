<?php
/**
 * ABS Library
 *
 * @category   ABS_Library
 * @package    Conversion
 * @copyright  Copyright (c) 2008 Shvakin V. (http://a1p2m3.googlepages.com/)
 * @version    2.0
 */

interface Abs_Conversion {
    function Open($options);
    function Close();
    function Write($data);
    function Read();
    function ListWrite($data, $tranlate=null);
    function ListRead();
}