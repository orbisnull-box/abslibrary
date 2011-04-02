<?php
/**
 * ABS Library
 *
 * @category   ABS_Library
 * @package    Model
 * @subpackage Menu
 * @copyright  Copyright (c) 2008 Shvakin V. (http://a1p2m3.googlepages.com/)
 * @version    2.0
 */

abstract class Abs_Model_Menu
{
    private $_Menu;
     
    public function clear()
    {
        unset($this->_Menu);
        $this->_Menu=array();
    }

    public function addDefault()
    {
        $this->addAction('Список', 'list');
        $this->addAction('Добавить', 'add');
    }

    public function addAction($name, $action, $controller = null, $module = null, array $params = null)
    {
        $request=Zend_Controller_Front::getInstance()->getRequest();

        if (null === $controller) {
            $controller = $request->getControllerName();
        }

        if (null === $module) {
            $module = $request->getModuleName();
        }

        $url = $controller . '/' . $action;
        if ($module != Zend_Controller_Front::getInstance()->getDispatcher()->getDefaultModule()) {
            $url = $module . '/' . $url;
        }

        if (null !== $params) {
            $paramPairs = array();
            foreach ($params as $key => $value) {
                $paramPairs[] = urlencode($key) . '/' . urlencode($value);
            }
            $paramString = implode('/', $paramPairs);
            $url .= '/' . $paramString;
        }

        $url = '/' . ltrim($url, '/');
        
        $this->_Menu[$name]=$url;
    }


    public function addItem($name, $url)
    {
        if ($url[1]!=='/' and strpos($url, 'http://')===false) {
            $url='/'.$url;
        }
        $this->_Menu[$name]=$url;
    }

    public function addItemList($items)
    {
        foreach ($items as $item)
        {
            $this->addItem($item['name'], $item['url']);
        }
    }

    public function toArray()
    {
        return $this->_Menu;
    }

    public function load($id)
    {
        $cache=Zend_Registry::get('Zend_Cache');

        if ($cache->test($id)){
            $this->_Menu=$cache->load($id);
            return true;
        }
        else {
            return false;
        }
    }

    public function save($id)
    {
        if (count($this->_Menu)>0) {
            $cache=Zend_Registry::get('Zend_Cache');
            return $cache->save($this->_Menu, $id, array(get_class($this)));
        }
        else {
            return true;
        }
    }

    public function test($idl)
    {
        $cache=Zend_Registry::get('Zend_Cache');
        return $cache->test($id);
    }

}
