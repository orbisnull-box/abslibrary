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
 * Абстрактый контроллер, для действий не связанных с использованием базы данных
 *
 */
abstract class Abs_Controller_Abstract_Activity extends Abs_Controller_Abstract
{
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        parent::__construct($request, $response, $invokeArgs);
    }
}
