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

class Abs_Controller_Plugin_LoopShutdown extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopShutdown()
    {
        //Устанавливаем заголовок ответа - правильную кодировку
        $this->getResponse()->setHeader('Content-Type', 'text/html; charset=utf-8');

        
        $request=$this->getRequest();

        $controller_name='';
        if ($request->getModuleName() != 'default') {
            $controller_name .= ucfirst($request->getModuleName()).'_';
        }
        $controller_name.=ucfirst($request->getControllerName());
        $class_name=$controller_name.'Controller';

        $menu=Abs_Model_Menu_Controller::getInstance();
        $menu->save('Menu_'.$class_name);
    }
}
