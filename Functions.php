<?php
/**
 * ABS Library
 *
 * @category   ABS_Library
 * @package    Kernel
 * @copyright  Copyright (c) 2008 Shvakin V. (http://a1p2m3.googlepages.com/)
 * @version    2.0
 */

/**
 * Получение типа переменной в виде строки
 *
 * @param mixed $var
 * @return string
 */
function get_type($var)
{
    /*if (is_scalar($var)) {
     return 'scalar';
     }*/
    if (is_null($var)) {
        return 'null';
    }
    elseif (is_int($var)) {
        return 'int';
    }
    elseif (is_string($var)) {
        return 'string';
    }
    elseif (is_array($var)) {
        return 'array';
    }
    elseif (is_object($var)) {
        return 'object';
    }
    else {
        throw new Zend_Exception('Неопределенный тип');
    }
}

/**
 * Перевод строкового ресурса
 *
 * @param string $string строка для перевода
 * @param mixed $args аргументы
 * @param  string|Zend_Locale $locale    (optional) Locale/Language to use, identical with
 *                                       locale identifier, @see Zend_Locale for more information
 * @return string
 */
function t($string, $args = null, $locale = null)
{
    if (Zend_Registry::isRegistered('Zend_Translate')) {
        $translate=Zend_Registry::get('Zend_Translate');
        $trans_string=$translate->_($string, $locale);
    }
    else {
        throw new Zend_Exception('Отсутствует зарегестрированный объект  Zend_Translate');
    }
    $type_args=get_type($args);
    switch ($type_args)
    {
        case 'string':
            $sp_args=array($args);
            break;
        case 'array':
            $sp_args=$args;
            break;
        default:
            $sp_args=null;
            break;
    }
    if (!is_null($sp_args))
    {
        $full_string=call_user_func_array('sprintf', $args);
    }
    else {
        $full_string=$trans_string;
    }
    return $full_string;
}

/**
 * Базовый путь URL
 *
 * @return string
 */
function baseUrl()
{
    $url=Zend_Controller_Front::getInstance()->getBaseUrl();
    if ($url==='' or is_null($url)) {
        $url='http://'.$_SERVER['SERVER_NAME'];
        if ($_SERVER['SERVER_PORT']!=='80')
        {
            $url.=':'.'SERVER_PORT';
        }
    }
    return ($url);
}

/**
 * Возвращает столбец массива в виде одномерного массива (строки)
 *
 * @param array $array_table
 * @param string $column
 * @return array
 */
function array_table_column($array_table, $column)
{
    $result_col=array();
    foreach ($array_table as $row)
    {
        $result_col[]=$row[$column];
    }
    return $result_col;
}

/**
 * Возвращает ассоциативный в виде, пригодном для вывода в html
 *
 * @param unknown_type $array
 * @return unknown
 */
function _array_to_string($array)
{
    $result='';
    foreach ($array as $key=>$value)
    {
        if(get_type($value)==='array') {
            $result.='-'._array_to_string($value);
        }
        $result.=$key.'='.$value."<br \>\n";
    }
    return $result;
}

/**
 * Приводит переменную любого типа в строку html
 *
 * @param mixed $data
 * @return stringt
 */
function toString($data)
{
    $type=get_type($data);

    switch ($type) {
        case 'array':
            return _array_to_string($data);
            break;
    }
}