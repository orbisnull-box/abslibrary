<?php
/**
 * ABS Library
 *
 * @category   ABS_Library
 * @package    Function
 * @copyright  Copyright (c) 2008 Shvakin V. (http://a1p2m3.googlepages.com/)
 * @version    2.0
 */

class Aps_Function_FileIni
{
    var $mData;
    var $mFile;
    
    function __construct()
    {
        $this->mData=array();
    }
    
    function SetFile($fileName)
    {
        $this->mFile=$fileName;
        unset($this->mData);
        $this->mData=array();
    }
    
    function ReadFile()
    { 
        $fd=fopen($this->mFile, 'r');
        $section='main';
        while (!feof($fd))
        {
            $buf=trim(fgets($fd, filesize($this->mFile)));
            $match=array();           
            $is_section=preg_match('~^\[(.*)\]$~', $buf, $matches);            
            if (strlen($buf)>0)
            {
                if (true==$is_section)
                {         
                    $section=trim($matches[1]);
                }
                else 
                {
                    $str_arr=explode('=',$buf);
                    $key=trim($str_arr[0]);
                    $val=trim($str_arr[1]);
                    $this->mData[$section][$key]= $val;
                }            
            }
        }
        return true;
    }
    
    function GetValue($section, $key)
    {
        if (true===isset($this->mData[$section][$key]))
        {
            return $this->mData[$section][$key];
        }
        else 
        {
            return null;
        }
    }    
}