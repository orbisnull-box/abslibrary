<?php
/**
 * ABS Library
 *
 * @category   ABS_Library
 * @package    Form
 * @copyright  Copyright (c) 2008 Shvakin V. (http://a1p2m3.googlepages.com/)
 * @version    2.0
 */

class Abs_Form_Select extends Zend_Form {

    public function __construct($optionsSelect, $label=null, $id=null, $options = null)
    {
        parent::__construct($options);

        $this->addPrefixPath('Abs_Form', 'Abs/Form');

        $this->setName('select'.$id);

        $sub_action = new Zend_Form_Element_Hidden('sub_action');
        $sub_action->setRequired(true);
        $sub_action->setValue('answer');
        $sub_action->addValidator('Alnum');
        $sub_action->setDecorators(array(
        array('ViewHelper', array('helper' => 'formHidden')),
        ));
         
        if (!is_null($id)) {
            $idf = new Zend_Form_Element_Hidden('idf'.$id);
            $idf->setRequired(true);
            $idf->setValue($id);
            //$idf->addValidator('Alnum');
            $idf->setDecorators(array(
            array('ViewHelper', array('helper' => 'formHidden')),
            ));
            $this->addElement($idf);
        }

        $select=new Zend_Form_Element_Select('select');
        $select->setRequired(true);
        $select->addValidators(array(
        array('int'),
        ));
        $select->class = 'formselect';
        $select->setLabel($label)
        ->setDecorators(array(
        array('ViewHelper', array('helper' => 'formSelect')),
        array('Label', array('class' => 'label')),
        array('Errors'),
        ));
        $select->addMultiOptions($optionsSelect);

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setIgnore(true);
        $submit->setValue(true);
        $submit->class = 'formsubmit';
        $submit->setLabel('Выбрать')
        ->setDecorators(array(
        array('ViewHelper', array('helper' => 'formSubmit'))
        ));


        $this->addElements(array(
        $sub_action,
        $select,
        $submit
        ));
         
        $this->setDecorators(array(
            'FormElements',
            'Fieldset',
            'Form',
            'Description',
        ));

        $this->setMethod('get');
    }
}