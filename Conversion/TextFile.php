<?php
/**
 * ABS Library
 *
 * @category   ABS_Library
 * @package    Conversion
 * @copyright  Copyright (c) 2008 Shvakin V. (http://a1p2m3.googlepages.com/)
 * @version    2.0
 */

class Abs_Conversion_TextFile extends  Abs_Controller_Conversion_Abstract
{
    private $_File;

    private $_Delimiter;

    public function __construct($options)
    {
        $this->_File=fopen($options['file'], $options['mode']);
    }

    public function  __destruct()
    {
        fclose($this->_File);
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
            $str=implode($this->_Delimiter, $row);
            fwrite($this->_File, $str);
        }
    }

    public function ListRead()
    {
        //Обработать названия полей
        $data=array();
        while (!feof($this->_File))
        {
            $str=fgets($this->_File);
            $row=explode($this->_Delimiter, $str);
            $data[]=$row;
        }
        return $data;
    }
}
