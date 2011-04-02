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
 * Front Controller Plugin ControllerMenu
 *
 */
class Abs_Controller_Plugin_ControllerMenu extends Zend_Controller_Plugin_Abstract
{

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $controller_name='';
        if ($request->getModuleName() != 'default') {
            $controller_name .= ucfirst($request->getModuleName()).'_';
        }
        $controller_name.=ucfirst($request->getControllerName());
        $class_name=$controller_name.'Controller';

        $menu=Abs_Model_Menu_Controller::getInstance();

        if (in_array('addMenu', get_class_methods($class_name))) {
            call_user_func(array($class_name, 'addMenu'));
        }
    }
}