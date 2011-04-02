<?php
/**
 * ABS Library
 *
 * @category   ABS_Library
 * @package    Views
 * @copyright  Copyright (c) 2008 Shvakin V. (http://a1p2m3.googlepages.com/)
 * @version    2.0
 */

class Zend_View_Helper_ItemAction {
    /**
     * The view object that created this helper object.
     * @var Zend_View
     */
    public $view;

    public function itemAction($action, $id)
    {
        switch ($action)
        {
            case 'show':
                $html='<a href="'.$this->view->url(array('action'=>'show', $this->view->essense_id_name=>$id)).'">
				<img border="0" class="button-img" src="/tools/html/images/buttons/show_small.png" / alt="Просмотреть">
				</a>';
                break;
            case 'edit':
                $html='<a href="'.$this->view->url(array('action'=>'edit', $this->view->essense_id_name=>$id)).'">
				<img border="0" class="button-img" src="/tools/html/images/buttons/edit_small.png" / alt="Редактировать">
				</a>';
                break;
            case 'delete':
                $html='<a href="'.$this->view->url(array('action'=>'delete', $this->view->essense_id_name=>$id)).'">
				<img border="0" class="button-img" src="/tools/html/images/buttons/del_small.png" / alt="Удалить">
				</a>';
                break;
            default:
                $html='';
        }
        return $html;
    }


    /**
     * Set view object
     *
     * @param  Zend_View_Interface $view
     * @return Zend_View_Helper_DeclareVars
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
        return $this;
    }
}
