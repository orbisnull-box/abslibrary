<?php
/**
 * ABS Library
 *
 * @category   ABS_Library
 * @package    Controller
 * @copyright  Copyright (c) 2008 Shvakin V. (http://a1p2m3.googlepages.com/)
 * @version    2.0
 */

require_once 'Abs/Controller/Abstract.php';

/**
 * Абстрактный класс для действий, связанных с б.д.
 *
 */
abstract class Abs_Controller_Abstract_Collection extends Abs_Controller_Abstract
{
    /**
     * Дефолтный адаптер базы данных
     *
     * @var Zend_Db_Adapter_Abstract
     */
    private $_Db;

    /**
     * Объект модели - коллекции
     *
     * @var Abs_Model_Db_Abstract_Collection
     */
    protected $_Essense;

    /**
     * Массив с колонками для отображения в списке
     *
     * @var array
     */
    protected $_ListCols=array();

    /**
     * массив с колонками поиска
     * Строковые значения ищутся с помошью like, числовые задаются двумя полями с диапазоном
     *
     * @var array
     */
    protected $_SearchCols=array();

    protected $_GroupCols=array();

    function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        parent::__construct($request, $response, $invokeArgs);
        $this->_Db=Zend_Registry::get('Zend_Db');
        $this->view->essense_id_name=$this->_Essense->getIdName();
    }

    private function setViewMeta()
    {
        $this->view->cols=$this->_Essense->info('metadata');
        $this->view->table=$this->_Essense->info('name');
        $this->view->thesaurusTables=$this->_Essense->getThesaurusTables();
    }

    protected function _getId()
    {
        $id_name=$this->_Essense->getIdName();
        if ($id_name==='id') {
            $filter='int';
        }
        else {
            $filter='alnum';
        }
        return $id=$this->_filter($this->_request->getParam($id_name), $filter);
    }

    protected function _getIdList()
    {
        $idl=$this->_request->getParam('id');
        return ($this->_request->getParam('id'));
        if (is_array($idl)) {
            $idl_new=array();
            foreach ($idl as $id) {
                $id=$this->_filter($id, 'int');
                if (!is_null($id)) {
                    $idl_new[]=$id;
                }
            }
            return $idl_new;
        }
        else {
            return null;
        }
    }


    /**
     * Форма редактирования элемента
     *
     * @return Abs_Form_Item
     */
    private function _getFormItem($sub_options=null)
    {
        $cols=$this->_Essense->info(metadata);
        $action=$this->_getActionUrl();

        $form=new Abs_Form_Item($this->_Essense->info('name'), null, $this->_GroupCols, $action);
        foreach($cols as $name=>$item_options)
        {
            //Zend_Debug::dump($sub_options[$name]);
            $form->addDbElement($name, $item_options, $sub_options[$name]);
        }
        $form->setDecorators(array(
        array('ViewScript', array('viewScript' => '_item_form.phtml', 'class'=> 'form'))
        ));
        return $form;
    }

    /**
     * Форма редактирования списка элемента
     *
     * @return Abs_Form_List
     */
    private function _getFormList($countItems)
    {
        $cols=$this->_Essense->info(metadata);
        $form=new Abs_Form_List($this->_Essense->info('name'));
        for ($i=1; $i<=$countItems; $i++)
        {
            $row=$form->addItem();
            foreach($cols as $name=>$options)
            {
                $form->addDbElement($row, $name, $options);
            }
        }
        return $form;
    }

    private function _getFormSearch()
    {
        $cols=$this->_Essense->info(metadata);
        $form=new Abs_Form_Search($this->_Essense->info('name'), null, $this->_GroupCols);
        foreach($cols as $name=>$options)
        {
            if (in_array($name, $this->_SearchCols)) {
                $form->addDbElement($name, $options);
            }
        }
        $form->setDecorators(array(
        array('ViewScript', array('viewScript' => '_item_form.phtml', 'class'=> 'form'))
        ));
        return $form;
    }

    protected function getColsNames()
    {
        return array_keys($this->_Essense->info('metadata'));
    }

    protected function count($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->_Essense->count($where, $order, $count, $offset);
    }

    /**
     * Фабрика форм
     *
     * @param string $form_class
     * @param array $params
     * @return Zend_Form
     */
    protected function _getForm($form_class, $params=null)
    {
        switch ($form_class) {
            case 'item':
                return $this->_getFormItem($params['sub_options']);
                break;
            case 'list':
                if (isset($params['countItems'])) {
                    $countItems=$params['countItems'];
                }
                else {
                    $countItems=3;
                }
                return $this->_getFormList($countItems);
                break;
            case 'askDelete':
                return new Abs_Form_AskDelete($this->_Essense->info('name'));
                break;
            case 'search':
                return $this->_getFormSearch();
                break;
            default:
                throw new Zend_Exception('Неверно указан тип формы');
                break;
        }
    }

    /**
     * Экспорт списка
     *
     * @param string $backend_name
     * @param array $options
     * @param string|array|Zend_Db_Table_Select $where  OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @param int                               $count  OPTIONAL An SQL LIMIT count.
     * @param int                               $offset OPTIONAL An SQL LIMIT offset.
     * @return Zend_Db_Table_Rowset_Abstract The row results per the Zend_Db_Adapter fetch mode.
     */
    protected function _export($backend_name, $options, $where = null, $order = null, $count = null, $offset = null, $translate=null)
    {
        $data=$this->_Essense->listGet($where, $order, $count, $offset)->toArray();
        $backend= new $backend_name($options);
        $backend->listWrite($data, $translate);
        $this->view->result='Экспорт завершен!!';
        $this->_renderTry('conversion', $null, true);
    }

    protected function _import($backend_name, $options, $clear=false)
    {
        if ($clear) {
            $this->_Essense->ListDelete();
        }
        $backend= new $backend_name($options);
        $data=$backend->listRead();
        $this->_Essense->listSave($data);
        $this->view->result='Импорт завершен!!';
        //$this->_renderTry('conversion', $null, true);
    }

    protected function _itemShow($action='item', $name=null, $noController=true)
    {
        $data=$this->_Essense->ItemGet($this->_getId())->toArray();
        if (isset($data['pass'])) {
            unset($data['pass']);
        }
        $this->view->item=$data;
        $this->setViewMeta();
        $this->_renderTry($action, $name, $noController);
    }

    protected function _itemAdd($redirect=null, $action='form', $name=null, $noController=true, $sub_options=null, Zend_Session_Namespace $session=null)
    {
        $this->_checkToCansel($redirect, $session);

        $form=$this->_getForm('item', array('sub_options'=>$sub_options));
        $this->view->form=$form;

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getParams())){
                try {
                    $form_data=$form->getValues();
                    if (isSet($form_data['pass'])) {
                        $form_data['pass']=md5($form_data['pass']);
                    }
                    $this->_Essense->itemSave($form_data);

                    if (!is_null($session)) {
                        $session->unsetAll();
                    }
                    $this->_finishAction($redirect, 'Добавление выполнено');
                }catch (Exception $e) {
                    $this->_finishAction($redirect, '<b>Ошибка добавления </b>'.$e->__toString());
                }
            }
        }

        $this->_renderTry($action, $name, $noController);
        return $result;
    }

    protected function _itemEdit($redirect=null, $action='form', $name=null, $noController=true, $sub_options=null)
    {
        $form=$this->_getForm('item', array('sub_options'=>$sub_options));
        $this->view->form=$form;
        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getParams())){
                $form_data=$form->getValues();
                if (isSet($form_data['pass'])) {
                    $form_data['pass']=md5($form_data['pass']);
                }
                $this->_Essense->itemSave($form_data);
                $this->_finishAction($redirect, 'Изменение выполнено');
            }
        }
        else {
            $id=$this->_getId();
            if (!is_null($id)) {
                $item=$this->_Essense->itemGet($id);
                $item_data=$item->toArray();
                if (isSet($item_data['pass'])) {
                    unset ($item_data['pass']);
                }
                if (!is_null($item)) {
                    $form->populate($item_data);
                }
            }
        }
        $this->_renderTry($action, $name, $noController);
    }

    protected function _itemDelete($redirect=null, $action='delete', $name=null, $noController=true)
    {
        $form=$this->_getForm('askDelete');
        $this->view->form=$form;
        $id=$this->_getId();
        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getParams())) {
                $this->_Essense->itemDelete($id);
                $this->_finishAction($redirect, 'Удаление выполнено');
            }
        }
        else {
            $this->view->item=$this->_Essense->itemGet($id)->toArray();
            $this->view->cansel=$this->_createUrl($redirect);
        }
        $this->_renderTry($action, $name, $noController);
    }

    /** @todo настроить пейджинг */
    /** @todo настроить колонки */
    protected function _listShow($sqlParams=null, $action='list', $name=null, $noController=true)
    {
        if (isset($sqlParams['data'])) {
            $list=$sqlParams['data'];

            $cols=array_keys($sqlParams['data'][0]);
            $cols_array=array();
            foreach ($cols as $col_name) {
                $cols_array[]=array('COLUMN_NAME'=>$col_name);
            }
            $cols_array[0]['PRIMARY']=true;

            $this->view->cols=$cols_array;
        }
        else {
            $pagerParams=$this->_pagerParams('all', 40, $this->count($sqlParams['where']));

            $sqlParamsDefault=array('where'=>null, 'order'=>null, 'count'=>null, 'offset'=>null);
            if (is_array($sqlParams)) {
                $sqlParams=array_merge($sqlParamsDefault, $sqlParams);
            }
            else {
                $sqlParams=$sqlParamsDefault;
            }
            $this->setViewMeta();
            $list=$this->_Essense->listGet($sqlParams['where'],
            $sqlParams['order'],
            $pagerParams['count'],
            $pagerParams['offset'])->toArray();
        }
        $this->view->list=$list;
        $this->view->allowCols=$this->_ListCols;
        $this->view->pagerParams=$pagerParams;
        $this->_viewSortTableJs();
        $this->_renderTry($action, $name, $noController);
    }

    protected function _listAdd($redirect=null, $countItems=3, $action='form', $name=null, $noController=true)
    {
        $form=$this->_getForm('list', array('countItems'=>$countItems));
        $this->view->form=$form;
        if ($this->_request->isPost()) {
            $items=$form->getSubForms();
            $data=array();
            foreach ($items as $name=>$item)
            {
                if ($item->isValid($this->_request->getParams($name, false))) {
                    $values=$item->getValues();
                    $data[$name]=$values[$name];
                }
            }
            $this->_Essense->listSave($data, true);
            $this->_finishAction($redirect, 'Добавление выполнено');
        }
        $this->_renderTry($action, $name, $noController);
    }

    protected function _listEdit($redirect=null, $countItems=3, $action='form', $name=null, $noController=true)
    {
        $form=$this->_getForm('list', array('countItems'=>$countItems));
        $this->view->form=$form;
        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getParams())){
                $this->_Essense->itemSave($form->getValues());
                $this->_finishAction($redirect, 'Изменение выполнено');
            }
        }
        else {
            $idl=$this->_getIdList();
            if (!is_null($idl)) {
                $items=$this->_Essense->listGet($idl);
                if (!is_null($items)) {
                    $form->populate($items->toArray());
                }
            }
        }
        $this->_renderTry($action, $name, $noController);
    }

    protected function _listDelete($redirect=null, $action='form', $name=null, $noController=true)
    {
        $form=$this->_getForm('askDelete');
        $this->view->form=$form;
        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getParams())) {
                $ids=$this->_getIdList();
                $this->_Essense->ListDelete($ids);
                $this->_finishAction($redirect, 'Удаление выполнено');
            }
        }
        else {
            $this->view->items=$this->_Essense->listGet($ids)->toArray();
        }
        $this->_renderTry($action, $name, $noController);
    }

    protected function _searchAction($action='search', $name=null, $noController=true)
    {

        $this->setViewMeta();
        $this->view->allowCols=$this->_ListCols;
        $list=array();

        $db=$this->_Essense->getAdapter();

        $form=$this->_getForm('search');
        $this->view->form=$form;
        $this->view->first_request=true;

        if ($form->isValid($this->_request->getParams())) {
            //Преобразовать массив с формой в условие where
            $where=$form->getWhere($this->_Essense->info('metadata'));

            if ($this->_request->getParam('pager', 'off')==='off') {
                $this->_request->setParam('page', 1);
            }

            $pagerParams=$this->_pagerParams('all', 10, $this->count($where));
            $this->view->pagerParams=$pagerParams;
            $this->view->queryString=$_SERVER['QUERY_STRING'];

            if (!is_null($where)) {
                $this->view->first_request=false;
            }

            $list=$this->_Essense->listGet($where, null, $pagerParams['count'], $pagerParams['offset'])->toArray();
        }
        $this->view->list=$list;
        $this->_renderTry($action, $name, $noController);
    }
    
    public function denyAccess()
    {   
        $pacl=Zend_Controller_Front::getInstance()->getPlugin('Abs_Controller_Plugin_Acl');
        $this->_redirectTry(array('controller'=>'error', 'action'=>'denied'));       
    }
}
