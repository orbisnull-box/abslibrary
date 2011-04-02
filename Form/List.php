<?php
/**
 * ABS Library
 *
 * @category   ABS_Library
 * @package    Form
 * @copyright  Copyright (c) 2008 Shvakin V. (http://a1p2m3.googlepages.com/)
 * @version    2.0
 */


class Abs_Form_List extends Abs_Form_Item {


    public function __construct($table_name, $options = null)
    {
        parent::__construct($table_name, $options);
    }

    /**
     * Retrieve a form subForm/subform
     *
     * @param  string $name
     * @return Zend_Form|null
     */
    public function addItem()
    {
        $subform=new Zend_Form_SubForm();
        $num=count($this->getSubForms())+1;
        $name='row'.$num;
        $this->addSubForm($subform, $name);
        return $this->getSubForm($name);
    }

    public function addDbElement($subform, $name, $options)
    {
        $element=$this->createDbElement($name, $options);
        if (!is_null($element)) {
            $subform->addElement($element);
        }
    }
}