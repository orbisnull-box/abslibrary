<?php
/**
 * ABS Library
 *
 * @category   ABS_Library
 * @package    Form
 * @copyright  Copyright (c) 2008 Shvakin V. (http://a1p2m3.googlepages.com/)
 * @version    2.0
 */

class Abs_Form_AskDelete extends Zend_Form {
    protected $table_name;


    public function __construct($table_name, $options = null)
    {
        parent::__construct($options);

        $id = new Zend_Form_Element_Hidden('id');
        $id->setAllowEmpty(true);
        $id->addValidator('Int');
        $id->setDecorators(array(
        array('ViewHelper', array('helper' => 'formHidden')),
        ));
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setIgnore(true);
        $submit->class = 'formsubmit';
        $submit->setLabel('Удалить')
        ->setDecorators(array(
        array('ViewHelper', array('helper' => 'formSubmit'))
        ));

        $this->addElements(array(
        $id,
        $submit
        ));

        $this->setDecorators(array(
            'FormElements',
            'Form',
            'Description',
        ));
        $this->setMethod('post');

        parent :: __construct($options);
    }
}