<?php
/**
 * ABS Library
 *
 * @category   ABS_Library
 * @package    Model
 * @subpackage Db
 * @copyright  Copyright (c) 2008 Shvakin V. (http://a1p2m3.googlepages.com/)
 * @version    2.0
 */

class Abs_Model_Db_Thesaurus
{
    private static $instance = null;

    var $_dictionary;
    /**
     * @var Zend_Db
     */
    var $_db;

    /**
     * @var Zend_Cache_Core
     */
    var $_Cache;
     

    /**
     * @return Abs_Model_Db_Thesaurus
     */
    public static function getInstance ( )
    {
        if ( is_null ( self::$instance ) )
        {
            self::$instance = new Abs_Model_Db_Thesaurus( );
        }

        return self::$instance;
    }

    private function __construct( )
    {
        $this->_db=Zend_Registry::get('Zend_Db');
        $this->_Cache=Zend_Registry::get('Zend_Cache');
        if ($this->_Cache->test('Thesaurus')) {
            $this->_dictionary=$this->_Cache->load('Thesaurus');
        }
    }

    public function __destruct()
    {
        $this->_Cache->save($this->_dictionary, 'Thesaurus', array ('Thesaurus'));
    }

    private function __clone ( )
    {
        // Конструктор копирования нам не нужен
    }

    public function quoteInto($text, $value, $type = null, $count = null)
    {
        return $this->_db->quoteInto($text, $value, $type, $count);
    }


    function prepareTerms($table, $list)
    {
        $list=array_unique($list);
        foreach ($list as $key=>$value)
        {
            if($this->issetTerm($table, $value)) {
                unset($list[$key]);
            }
        }

        if (count($list)>0) {
            $select=new Zend_Db_Select($this->_db);
            $select->from($table, array('id', 'name'));
            $select->where($this->quoteInto('id in (?)', $list));

            $term_array=$this->_db->query($select)->fetchAll();


            foreach ($term_array as $term)
            {
                $this->setTerm($table, $term['id'], $term['name']);
            }
        }
         
    }

    function issetTerm($table, $id)
    {
        return isset($this->_dictionary[$table][$id]);
    }


    //Обработка справочников
    public function getTerm($table, $id)
    {
        if ($this->issetTerm($table, $id)) {
            return $this->_dictionary[$table][$id];
        }
        else {
            return $id;
        }
    }

    public function  setTerm($table, $id, $value)
    {
        $this->_dictionary[$table][$id]=$value;
    }

}