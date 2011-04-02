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
 * Системный контроллер
 *
 */
abstract class Abs_Controller_System extends Abs_Controller_Abstract_Activity
{
    /**
     *
     * @var Abs_Model_System
     */
    private $_System;

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        parent::__construct($request, $response, $invokeArgs);
        $this->_System=Abs_Model_System::getInstance();
    }


    function menumainAction()
    {
        $this->view->menu_main=$this->_System->getMenu();
    }

    function menucontrollerAction()
    {
        $menu=Abs_Model_Menu_Controller::getInstance();
        $this->view->menu_controller=$menu;
    }

    function messagesAction()
    {
        $this->view->messages = $this->_FlashMessenger->getMessages();
        if ($this->_FlashMessenger->count()<=0) {
            $this->_helper->viewRenderer->setNoRender();
        }
    }

    function profilerAction()
    {
        $profiler = Zend_Registry::get('Zend_Db')->getProfiler();
        if ($profiler->getEnabled()){
            $totalTime    = $profiler->getTotalElapsedSecs();
            $queryCount   = $profiler->getTotalNumQueries();
            $query_str  = '';

            foreach ($profiler->getQueryProfiles() as $query) {
                $query_str.= "<li>".$query->getQuery() . ' : ' . $query->getElapsedSecs()."</li>\n";
            }

            $info='';

            $info.='Executed ' . $queryCount . ' queries in ' . $totalTime . ' seconds' . "<br />\n";
            $info.='Average query length: ' . $totalTime / $queryCount . ' seconds' . "<br />\n";
            $info.='Queries per second: ' . $queryCount / $totalTime . "<br />\n";
            //$info.='Longest query length: ' . $longestTime . "<br />\n";
            $info.="Query list: <br />\n<ol>" . $query_str . "</ol><br />\n";

            $this->view->info=$info;
        }
    }
}