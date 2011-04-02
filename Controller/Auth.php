<?php
/**
 * ABS Library
 *
 * @category   ABS_Library
 * @package    Controller
 * @copyright  Copyright (c) 2008 Shvakin V. (http://a1p2m3.googlepages.com/)
 * @version    2.0
 */
 
/**
 * Контроллер аутентификации
 *
 */ 
class Abs_Controller_Auth extends Abs_Controller_Abstract_Activity
{
    function init()
    {       
        $this->view->baseUrl = $this->_request->getBaseUrl();
    }

    function indexAction()
    {
        $this->_redirect('/');
    }

    protected function _login($redirect=null, $action='form', $name=null, $noController=true)
    {
        $this->view->message = '';
        
        $form = new Abs_Form_Login();
        $this->view->form=$form;
        $form->getElement('return')->setValue($this->_request->getParam('return','/'));

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getParams())) {
                $user=$form->getValues();
                // setup Zend_Auth adapter for a database table
                Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');

                $authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Registry::get('Zend_Db'));
                $authAdapter->setTableName('users');
                $authAdapter->setIdentityColumn('login');
                $authAdapter->setCredentialColumn('pass');
                $authAdapter->setCredentialTreatment('md5(?)');

                // Set the input credential values
                // to authenticate against
                $authAdapter-> setIdentity($user['login']);
                $authAdapter->setCredential($user['pass']);

                // do the authentication
                $auth = Zend_Auth::getInstance();
                $result = $auth->authenticate($authAdapter);
                if ($result->isValid()) {
                    // success: store database row to auth's storage
                    // system. (Not the password though!)
                    $data = $authAdapter->getResultRowObject(null, 'pass');
                    $auth->getStorage()->write($data);
                    //Сохраняем объекты в сессии
                    /*
                    $namespaseCurrentUser= new Zend_Session_Namespace('CurrentUser');
                    if (!isset($namespaseCurrentUser->user)) {
                    $users= new Users();
                    $user=$users->find($data->id)->current();
                    $group=$user->findParentRow('Devgroups');
                    $namespaseCurrentUser->User=$user;
                    $namespaseCurrentUser->Devgroup=$group;
                    }
                    */
                    $this->_redirect($form->getValue('return'));
                } else {
                    // failure: clear database row from session
                    $this->view->message = 'Неправильные имя пользователя или пароль.';
                }
            }
            $this->view->message = 'Введены некорректные данные.';
        }
        $this->_renderTry($action, $name, $noController);
    }

    protected function _logout()
    {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_redirect('/');
    }
}