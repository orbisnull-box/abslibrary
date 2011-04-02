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

require_once ('Abs/Model/Db/Abstract.php');

/**
 * Абстрактный класс для модели, связанной с базой данных
 *
 */
abstract class Abs_Model_Db_Abstract_Collection extends Abs_Model_Db_Abstract
{
    /**
     * @var Abs_Model_Db_Thesaurus
     */
    var $_thesaurus;

    protected $_thesaurusTables=array();

    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->_thesaurus=Abs_Model_Db_Thesaurus::getInstance();
    }

    public function getThesaurusTables()
    {
        return $this->_thesaurusTables;
    }


    function loadThesaurus($data)
    {
        foreach ($this->_thesaurusTables as $column=>$table)
        {
            $terms_ids=array_unique(array_table_column($data, $column));
            $this->_thesaurus->prepareTerms($table, $terms_ids);
        }
    }


    //Функции для работы с одним объекто

    public function prepareToSave($data)
    {
        //Подготавливает строку, удаляя лишние данны
        $cols=$this->info('cols');
        foreach ($data as $key=>$val)
        {
            if (!in_array($key, $cols)) {
                unset($data[$key]);
            }
        }
        return $data;
    }

    /**
     * Fetches rows by primary key.  The argument specifies one or more primary
     * key value(s).  To find multiple rows by primary key, the argument must
     * be an array.
     *
     * This method accepts a variable number of arguments.  If the table has a
     * multi-column primary key, the number of arguments must be the same as
     * the number of columns in the primary key.  To find multiple rows in a
     * table with a multi-column primary key, each argument must be an array
     * with the same number of elements.
     *
     * The find() method always returns a Rowset object, even if only one row
     * was found.
     *
     * @param  mixed $key The value(s) of the primary keys.
     * @return Zend_Db_Table_Rowset_Abstract Row(s) matching the criteria.
     * @throws Zend_Db_Table_Exception
     */
    public function itemGet($id)
    {
        $data=$this->find($id)->current();
        if (!is_null($data)) {
            $this->loadThesaurus(array($data->toArray()));
        }
        return $data;
    }

    public function itemSave($data)
    {
        if (!is_array($data)) {
            throw new Zend_Exception('Данные не являются массивом');
        }
        if (isset($data[$this->getIdName()]) and (string)$data[$this->getIdName()]!=='') {
            if ($this->getIdName()==='id') {
                $isInsert=false;
            }
            else {
                $count=$this->count($this->quoteInto($this->getIdName().'=?', $data[$this->getIdName()]));
                $isInsert=($count===0);
                //$isInsert=!((bool) null or (bool) $this->itemGet());
            }
        }
        else {
            $isInsert=true;
        }

        if ($isInsert){
            return $this->itemAdd($data);
        }
        else {
            $primary_val=$data[$this->getIdName()];
            if ($this->getIdName()==='id') {
                unset($data[$this->getIdName()]);
            }
            //Запись есть (мы знаем её id, поэтому сохраняем)
            return $this->itemUpdate($data, $primary_val);
        }

    }

    private function itemAdd($data)
    {
        return $this->insert($data);
    }


    private function itemUpdate($data, $id)
    {
        $where=$this->_db->quoteInto($this->getIdName().' = ? ', $id);
        return $this->update($data, $where);
    }

    public function itemDelete($id)
    {
        $where=$this->_db->quoteInto($this->getIdName().' = ? ', $id);
        $this->delete($where);
    }


    //Функции для работы со списком

    /**
     * Fetches rows
     *
     * @param string|array|Zend_Db_Table_Select $where  OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @param int                               $count  OPTIONAL An SQL LIMIT count.
     * @param int                               $offset OPTIONAL An SQL LIMIT offset.
     * @return Zend_Db_Table_Rowset_Abstract The row results per the Zend_Db_Adapter fetch mode.
     */
    public function listGet($where = null, $order = null, $count = null, $offset = null)
    {
        $data=$this->fetchAll($where, $order, $count, $offset);
        if (!is_null($data)) {
            $this->loadThesaurus($data->toArray());
        }
        return $data;
    }

    public function listSave($data, $isAdd=null)
    {
        $this->getAdapter()->beginTransaction();
        try {
            if (!is_bool($isAdd)){
                $isAdd=!isSet($data[0][$this->_primary[1]]);
            }
            if ($isAdd){
                $this->listAdd($data);
            }
            else {
                $this->listUpdate($data);
            }
            $this->getAdapter()->commit();
        } catch (Exception $e) {
            $this->getAdapter()->rollBack();
            throw new Zend_Exception('Ошибка сохранения списка: '.$e->__toString());
        }
    }

    private function listAdd($data)
    {
        foreach ($data as $row)
        {
            $sql=$this->insert($row);
        }
    }

    private function listUpdate($data)
    {
        foreach ($data as $key->$row)
        {
            $primary_val=$data[$key][$this->_primary[1]];
            $sql=$this->update($row, $primary_val);
        }
    }

    public function listDelete($keys=null)
    {
        if (!is_null($keys)) {
            $keys_str='';
            foreach ($keys as $key)
            {
                $keys_str.=$key.',';
            }
            $this->delete($this->_primary[1]." in ($keys_str)");
        }
        else {
            $this->delete('true');
        }
    }

    /**
     * Количество записей в таблице
     *
     * @param unknown_type $where
     * @param unknown_type $order
     * @param unknown_type $count
     * @param unknown_type $offset
     * @return integer
     */
    public function count($where = null, $order = null, $count = null, $offset = null)
    {
        $select=new Zend_Db_Table_Select($this);
        $select->from($this->_name, array('count'=>new Zend_Db_Expr('count(*)')));
        if (!is_null($where)) $select->where($where);
        $select->order($order);
        $select->limit($count, $offset);
        $count=$select->query()->fetchAll();
        return (int) $count[0]['count'];
    }

    public function truncate()
    {
        $sql='truncate table '.$this->_db->quoteIdentifier($this->_name);
        $result=$this->_db->query($sql);
        return -1;
    }

    /**
     * Deletes existing rows.
     *
     * @param  array|string $where SQL WHERE clause(s).
     * @return int          The number of rows deleted.
     */
    public function delete($where)
    {
        if ($where===true or $where===1 or $where==='1' or $where==='1=1' or $where==='true'){
            return $this->truncate();
        }
        else {
            return parent::delete($where);
        }
    }

    public function getOptionsFormSelect($value='id', $label='name', $where=null)
    {
        $list=$this->fetchAll($where)->toArray();
        $list_array=array();
        foreach ($list as $item)
        {
            $list_array[$item[$value]]=$item[$label];
        }
        return $list_array;
    }

    /**
     * возврашает название первичного ключа таблицы
     *
     * @return string
     */
    public function getIdName()
    {
        return array_shift($this->info('primary'));
    }
    
    public function isIdAllow($id, $where)
    {
        $select=new Zend_Db_Select($this->getAdapter());
        $select->from($this->_name, array('count' => new Zend_Db_Expr('count(*)')));
        $select->where($this->quoteInto('id=?', $id));
        $select->where($where);
        $result=$select->query()->fetchColumn();
        if ($result>0) {
            return true;               
        }
        else {
            return false;
        }
    }
}
