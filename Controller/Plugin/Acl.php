<?php
/**
 * ABS Library
 *
 * @category   ABS_Library
 * @package    Controller
 * @subpackage Plugin
 * @copyright  Copyright (c) 2008 Shvakin V. (http://a1p2m3.googlepages.com/)
 * @version    2.0
 */

/** Zend_Acl */
require_once 'Zend/Acl.php';

/** Zend_Controller_Plugin_Abstract */
require_once 'Zend/Controller/Plugin/Abstract.php';

/**
 * Front Controller Plugin ACL
 *
 * @uses       Zend_Controller_Plugin_Abstract
 */
class Abs_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{
    /**
     * @var Zend_Acl
     **/
    protected $_acl;

    /**
     * @var string
     **/
    protected $_roleName;

    /**
     * @var array
     **/
    protected $_errorPage;

    /**
     * Constructor
     *
     * @param mixed $aclData
     * @param $roleName
     * @return void
     **/
    public function __construct()
    {

        $this->_errorPage = array('module' => 'default',
                                  'controller' => 'error', 
                                  'action' => 'denied');

        //$this->_roleName = $roleName;

        /*if (null !== $aclData) {
         $this->setAcl($aclData);
         }*/

        $this->_acl=Zend_Registry::get('Zend_Acl');
    }

    /**
     * Sets the ACL role to use
     *
     * @param string $roleName
     * @return void
     **/
    public function setRoleName($roleName)
    {
        $this->_roleName = $roleName;
    }

    /**
     * Returns the ACL role used
     *
     * @return string
     * @author
     **/
    public function getRoleName()
    {
        return $this->_roleName;
    }

    /**
     * Sets the error page
     *
     * @param string $action
     * @param string $controller
     * @param string $module
     * @return void
     **/
    public function setErrorPage($action, $controller = 'error', $module = null)
    {
        $this->_errorPage = array('module' => $module,
                                  'controller' => $controller,
                                  'action' => $action);
    }

    /**
     * Returns the error page
     *
     * @return array
     **/
    public function getErrorPage()
    {
        return $this->_errorPage;
    }

    /**
     * Predispatch
     * Checks if the current user identified by roleName has rights to the requested url (module/controller/action)
     * If not, it will call denyAccess to be redirected to errorPage
     *
     * @return void
     **/
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {

        $controller_name='';
        if ($request->getModuleName() != 'default') {
            $controller_name .= ucfirst($request->getModuleName()).'_';
        }
        $controller_name.=ucfirst($request->getControllerName());

        if ($controller_name!=='Error') {
            //Добавляем роли

            $this->_acl->addRole(new Zend_Acl_Role('Admin'));

            $this->_acl->addRole(new Zend_Acl_Role('Guest'));

            $this->_acl->addRole(new Zend_Acl_Role('Authorized'));

            $this->_acl->allow('Admin', null, null);

            $this->_acl->addRole(new Zend_Acl_Role($controller_name.'_View'), 'Authorized');
            $this->_acl->addRole(new Zend_Acl_Role($controller_name.'_Change'), $controller_name.'_View');

            //юзер создает новую роль, наследующую определенные бдля него
            //класс юзверя возвращает данные о правах для текущего ресурса и о глобальных правах

            $this->_acl->add(new Zend_Acl_Resource($controller_name));

            $class_name=$controller_name.'Controller';

            require_once $class_name.'.php';
            $isInitSet=call_user_func(array($class_name, 'initAcl'));

            if(!is_null($isInitSet) and $isInitSet==false) {
                $this->_acl->allow(null, $controller_name, null);
            }

            //получаем роль пользователя из объекта модели user, если такого класса нет - то делаем его админом, если класс есть, но нет роли - то госте
            $user_role='Guest';
            try {
                if (Zend_Loader::isReadable('Users.php')) {
                    Zend_Loader::loadFile('Users.php');

                    if (($controller_name!=='Auth') and class_exists('Users', false) and in_array('getCurrentUserRole', get_class_methods('Users'))) {
                        $user_role=call_user_func(array('Users', 'getCurrentUserRole'), $controller_name);
                        if (is_null($user_role) or $user_role===false) {
                            $user_role='Guest';
                        }
                    }
                }
            } catch (Exception $e) {
                $user_role='Guest';
            }

            if ($user_role==='Guest') {
                $this->_acl->addRole(new Zend_Acl_Role('Current_User'), 'Guest');
            }
            else {
                $this->_acl->addRole(new Zend_Acl_Role('Current_User'), array('Authorized', $user_role));
            }

            /** Check if the controller/action can be accessed by the current user */
            if (!$this->_acl->isAllowed('Current_User', $controller_name,  $request->getActionName())) {
                //** Redirect to access denied page *//*
                $this->denyAccess();
            }
        }
    }

    /**
     * Deny Access Function
     * Redirects to errorPage, this can be called from an action using the action helper
     *
     * @return void
     **/
    public function denyAccess()
    {
        $this->_request->setModuleName($this->_errorPage['module']);
        $this->_request->setControllerName($this->_errorPage['controller']);
        $this->_request->setActionName($this->_errorPage['action']);
    }
}