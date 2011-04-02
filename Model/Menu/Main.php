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

require_once 'Abs/Model/Menu.php';

class Abs_Model_Menu_Main extends Abs_Model_Menu 
{
 /**
     * static property to hold singleton instance
     */
    private static $_Instance = false;
    
    
    /**
     * constructor
     * private so only getInstance() method can instantiate
     * @return void
     */
    private function __construct()
    {
        $this->clear();
    }

    /**
     * factory method to return the singleton instance
     * @return Abs_Model_System
     */
    public function getInstance() {
        if (!Abs_Model_Menu_Main::$_Instance) {
            Abs_Model_Menu_Main::$_Instance = new Abs_Model_Menu_Main();
        }
        return Abs_Model_Menu_Main::$_Instance;
    }    
}