<?php
/**
 * ABS Library
 *
 * @category   ABS_Library
 * @package    Controller
 * @copyright  Copyright (c) 2008 Shvakin V. (http://a1p2m3.googlepages.com/)
 * @version    2.0
 */

require_once ('Zend/Controller/Action.php');
require_once ('Zend/Auth.php');

/**
 * Абстрактный контроллер, служит предком для всех остальных
 *
 */
abstract class Abs_Controller_Abstract extends Zend_Controller_Action
{
    /**
     * FlashMessenger
     *
     * @var Zend_Controller_Action_Helper_FlashMessenger
     */
    protected  $_FlashMessenger = null;
    /**
     * Url
     *
     * @var Zend_Controller_Action_Helper_Url
     */
    protected $_Url = null;

    /**
     * viewRender
     *
     * @var Zend_Controller_Action_Helper_ViewRenderer
     */
    protected $_viewRender;

    /**
     * Текущий зарегистрированный пользователь
     *
     * @var Mixed
     */
    protected $_CurrentUser = null;

    /**
     * Конфигурация
     *
     * @var array
     */
    protected $_Config=array();

    /**
     * Фильтры
     *
     * @var array
     */
    protected $_Filters;

    /**
     * Инициализация ACL
     *
     * @return mixed|bool
     */
    public static function initAcl()
    {
        //говорим акл что доступ неопределен
        return false;
    }

    /**
     * инициализация ACL по умолчанию
     *
     * @param string $class название класса
     * @return bool
     */
    public static function initAclDefault($class=null)
    {
        if (!is_null($class)) {
            $controller_name=self::controllerName($class);
        }
        else {
            $controller_name=self::controllerRequestName();
        }

        $acl=Zend_Registry::get('Zend_Acl');
        $acl->allow($controller_name.'_View', $controller_name, array('index', 'list', 'show'));
        $acl->allow($controller_name.'_Change', $controller_name, array('add', 'edit', 'delete'));
        return true;
    }
    
     /**
     * Конструктор
     *
     * The request and response objects should be registered with the
     * controller, as should be any additional optional arguments; these will be
     * available via {@link getRequest()}, {@link getResponse()}, and
     * {@link getInvokeArgs()}, respectively.
     *
     * When overriding the constructor, please consider this usage as a best
     * practice and ensure that each is registered appropriately; the easiest
     * way to do so is to simply call parent::__construct($request, $response,
     * $invokeArgs).
     *
     * After the request, response, and invokeArgs are set, the
     * {@link $_helper helper broker} is initialized.
     *
     * Finally, {@link init()} is called as the final action of
     * instantiation, and may be safely overridden to perform initialization
     * tasks; as a general rule, override {@link init()} instead of the
     * constructor to customize an action controller's instantiation.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Zend_Controller_Response_Abstract $response
     * @param array $invokeArgs Any additional invocation arguments
     * @return void
     */
    function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        parent::__construct($request, $response, $invokeArgs);

        $this->_Config=Zend_Registry::get('config');

        $this->initView();
        $this->view->baseUrl = $this->_request->getBaseUrl();

        $this->_FlashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->_Url = $this->_helper->getHelper('Url');
        $this->_viewRender=Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');

        if (Zend_Auth::getInstance()->hasIdentity()) {
            $this->_CurrentUser=Zend_Auth::getInstance()->getIdentity();
        }

        $this->_Filters['int']=new Zend_Filter_Int();
        $this->_Filters['alnum']=new Zend_Filter_Alnum();

        $this->view->module=$this->_request->getModuleName();
        $this->view->controller=$this->_request->getControllerName();
        $this->view->action=$this->_request->getActionName();
    }

    /**
     * Название контроллера из имени класса
     *
     * @param string $class
     * @return string
     */
    public static function controllerName($class)
    {
        return substr($class, 0, -10);
    }

    public static function controllerRequestName()
    {
        $controller_name='';
        $request=Zend_Controller_Front::getInstance()->getRequest();
        if ($request->getModuleName() != 'default') {
            $controller_name .= ucfirst($request->getModuleName()).'_';
        }
        $controller_name.=ucfirst($request->getControllerName());
        return $controller_name;
    }

    /**
     * Фильтрация данных указанным фильтром
     *
     * @param mixed $data данные для фильтрации
     * @param string $filter название фильтра
     * @return mixed
     */
    protected function _filter($data, $filter)
    {
        if (isSet($this->_Filters[$filter])) {
            return $this->_Filters[$filter]->filter($data);
        }
        else {
            throw new Zend_Exception('Данного фильтра "'.$filter.'" не зарегестрировано');
        }
    }

    /**
     * Создание url
     *
     * @param mixed $url
     * @return string
     */
    protected function _createUrl($url)
    {
        if (is_null($this->_Url)) {
            $this->_Url = $this->_helper->getHelper('Url');
        }
        $type=get_type($url);
        switch ($type) {
            case 'string' :
                $new_url=$this->_Url->simple($url);
                break;
            case 'array' :
                $new_url=$this->_Url->url($url, null, true);
                break;
            default:
                $new_url=null;
                break;
        }
        return $new_url;
    }

    protected function _pagerParams($param='all', $itemsOnPage, $countItems)
    {
        $page=(int) $this->_filter($this->_request->getParam('page', 1), 'int');
        if ($page===0) {
            $page=1;
        }

        $offset=($page-1)*$itemsOnPage;
        if ($offset>=$countItems) {
            $offset=$countItems-$itemsOnPage;
        }
        if ($offset<0) {
            $offset=0;
        }

        $count=$itemsOnPage;
        if ($itemsOnPage>$countItems-$offset) {
            $count=$countItems-$offset;
        }

        $count_pages=(int)ceil($countItems / $itemsOnPage);

        switch ($param)
        {
            case 'offset':
                return $offset;
                break;
            case 'count':
                return $count;
                break;
            case 'page':
                return $page;
                break;
            case 'count_pages':
                return $count_pages;
                break;
            case 'all':
                return array('page'=>$page, 'count_pages'=>$count_pages, 'offset'=>$offset, 'count'=>$count,);
        }
    }

    /**
     * Добавление системного сообщения (поддерживается локализация)
     *
     * @param string $message сообщение
     * @param array $args аргуументы для подстановки
     */
    protected function _addMessage($message, $args=null)
    {
        $this->_FlashMessenger->addMessage(t($message, $args));
    }

    protected function _renderTry($action=null, $name=null, $noController=false)
    {
        if (is_null($action)) {
            $this->_helper->viewRenderer->setNoRender();
        }
        else {
            $this->render($action, $name, $noController);
        }
    }

    protected function _redirectTry($redirect)
    {
        $url=$this->_createUrl($redirect);
        if (!is_null($url)) {
            $this->_redirect($url);
        }
    }

    protected function _finishAction($redirect=null, $message=null, $args=null)
    {
        if (!is_null($message)) {
            $this->_addMessage($message, $args);
        }
        if (!is_null($redirect)) {
            $this->_redirectTry($redirect);
        }
    }

    public function quoteInto($text, $value, $type = null, $count = null)
    {
        $db=Zend_Registry::get('Zend_Db');
        return $db->quoteInto($text, $value, $type, $count);
    }

    protected function _askSelect($optionsSelect, $label='', $id=null)
    {
        require_once 'Abs/Form/Select.php';
        $form=new Abs_Form_Select($optionsSelect, $label, $id);
        $this->view->form=$form;
        if ($this->_request->isGet()) {
            if ($this->_request->sub_action==='answer') {
                if ($form->isValid($this->_request->getParams())){
                    if (is_null($id) or $form->getValue('idf'.$id)===$id) {
                        $select=$form->getValue('select');
                        return $select;
                    }
                }
            }
            $this->_renderTry('form', null, true);
        }
    }

    protected  function _checkToCansel($redirect=null, Zend_Session_Namespace $session=null) {
        if ($this->_request->getParam('cansel_abs_action')==='true') {
            if (!is_null($session)) {
                $session->unsetAll();
            }
            $this->_finishAction($redirect, 'Действие отменено');
        }
    }

    protected function _getActionUrl()
    {
        return '/'.$this->_request->getModuleName().'/'.$this->_request->getControllerName().'/'.$this->_request->getActionName();
    }

    protected function _createMenuItem($action)
    {
        return $this->_createUrl(array(
        'module'=>$this->_request->getModuleName(),
        'controller'=>$this->_request->getControllerName(),
        'action'=>$action,
        ));
    }

    protected function _viewSortTableJs($tablename='listAction', $sortList=array(0,0))
    {
        $script='
        $(document).ready(function(){
  			$("#'.$tablename.'").tablesorter({sortList:[['.$sortList[0].','.$sortList[1].']]});
		});
		';
        $this->view->headScript()->appendScript($script);
    }

    public function getTableColumn($table, $column, $where)
    {
        $select=new Zend_Db_Select(Zend_Registry::get('Zend_Db'));
        $select->from($table, $column);
        $select->where($where);
        $list=array_table_column($select->query()->fetchAll(), $column);
        return $list;
    }
}
