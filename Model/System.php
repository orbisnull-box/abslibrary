<?php
/**
 * ABS Library
 *
 * @category   ABS_Library
 * @package    Model
 * @copyright  Copyright (c) 2008 Shvakin V. (http://a1p2m3.googlepages.com/)
 * @version    2.0
 */

class Abs_Model_System
{
    /**
     * static property to hold singleton instance
     */
    private static $_Instance = false;

    /**
     *
     * @var Abs_Model_Menu
     */
    private $_Menu;

    /**
     * constructor
     * private so only getInstance() method can instantiate
     * @return void
     */
    private function __construct()
    {        
        require_once 'Abs/Model/Menu/Main.php';
        $this->_Menu=Abs_Model_Menu_Main::getInstance();
    }
    
    public function __destruct()
    {
        $this->_Menu->save('Menu_Main');
    }

    /**
     * factory method to return the singleton instance
     * @return Abs_Model_System
     */
    public function getInstance() {
        if (!Abs_Model_System::$_Instance) {
            Abs_Model_System::$_Instance = new Abs_Model_System;
        }
        return Abs_Model_System::$_Instance;
    }


    public function addMenuItem($name, $url)
    {        
        $this->_Menu->addItem($name, $url);
    }

    public function addMenu($menu)
    {
        $this->_Menu->addItemList($menu);
    }

    /**
     * Получение объекта меню
     *
     * @return Abs_Model_Menu
     */
    public function getMenu()
    {
        return $this->_Menu;
    }
    
    public function loadMenu()
    {
        return $this->_Menu->load('Menu_Main');
    }
}