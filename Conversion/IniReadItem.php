<?php
/**
 * ABS Library
 *
 * @category   ABS_Library
 * @package    Conversion
 * @copyright  Copyright (c) 2008 Shvakin V. (http://a1p2m3.googlepages.com/)
 * @version    2.0
 */

require_once 'Abs/Conversion/Abstract.php';

class Abs_Conversion_IniReadItem
{    
    /**
     * @var Zend_Config
     */
    private $_IniReader;

    public function __construct($options=null)
    {
        if (!is_null($options)) {
            $this->Open($options);
        }
    }

    public function Open($options)
    {
        $this->_IniReader=new Zend_Config_Ini ($options['file'], null);
    }

    public function Close()
    {
        unset ($this->_IniReader);
    }

    public function Write($data)
    {
        throw new Zend_Exception('Этот адаптер не поддерживает запись');
    }

    public function Read()
    {
        return $this->_IniReader->toArray();
    }

    public function ListWrite($data)
    {
        throw new Zend_Exception('Этот адаптер не поддерживает запись');
    }

    public function ListRead()
    {
       throw new Zend_Exception('Этот адаптер не поддерживает чтение набора записей');
    }
}