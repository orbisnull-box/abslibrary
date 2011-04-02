<?php
/**
 * ABS Library
 *
 * @category   ABS_Library
 * @package    Conversion
 * @copyright  Copyright (c) 2008 Shvakin V. (http://a1p2m3.googlepages.com/)
 * @version    2.0
 */

class Abs_Conversion_Dbf extends Abs_Conversion_Abstract implements Abs_Conversion
{
    private $_dBase;

    public function __construct($options)
    {
        $this->_dBase=dbase_open($options['file'], 0);
        if ($this->_dBase===false) {
            throw new Zend_Exception('Ошибка открытия файла импорта');
        }
    }

    public function  __destruct()
    {
        dbase_close($this->_dBase);
    }

    public function Write($data)
    {

    }

    public function Read()
    {

    }

    public function ListWrite($data)
    {
        foreach ($data as $row)
        {

        }
    }

    public function ListRead()
    {
        $count=dbase_numrecords($this->_dBase);
        var_dump($count);
        for ($i=1; $i<=$count; $i++)
        {
            $row=dbase_get_record_with_names($this->_dBase, $i);
            var_dump($row);
        }

    }
}