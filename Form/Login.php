<?php
/**
 * ABS Library
 *
 * @category   ABS_Library
 * @package    Form
 * @copyright  Copyright (c) 2008 Shvakin V. (http://a1p2m3.googlepages.com/)
 * @version    2.0
 */

class Abs_Form_Login extends Zend_Form {
    public function __construct($options = null)
    {
        $hash=new Zend_Form_Element_Hash('hash');
        $hash->setSalt('STOatS24effort');
        $hash->setRequired(true);
        $hash->setDecorators(array(
                     array('ViewHelper', array('helper' => 'formHidden'))
                 ));


        $return = new Zend_Form_Element_Hidden('return');
        $return->setAllowEmpty(true);
        //$return->addValidator('Alnum');
        $return->setDecorators(array(
                     array('ViewHelper', array('helper' => 'formHidden')),
                     array('Label', array('class' => 'label'))
                 ));

        $login = new Zend_Form_Element_Text('login');
        $login->setRequired(true);
        $login->setAttribs(array('maxlength'=>20));
        $login->addValidators(array(
            array('NotEmpty', true),
            array('alnum'),
            array('stringLength', false, array(3, 20)),
        ));
        $login->class = 'formtext';
        $login->setLabel('Логин:')
                 ->setDecorators(array(
                     array('ViewHelper', array('helper' => 'formText')),
                     array('Label', array('class' => 'label')),
                     array('Errors'),
                 ));

        $pass = new Zend_Form_Element_Password('pass');
        $pass->setRequired(true);
        $pass->setAttribs(array('maxlength'=>20));
        $pass->addValidators(array(
            array('NotEmpty', true),
            array('alnum'),
            array('stringLength', false, array(3, 20)),
        ));
        $pass->class = 'formtext';
        $pass->setLabel('Пароль:')
                 ->setDecorators(array(
                     array('ViewHelper', array('helper' => 'formPassword')),
                     array('Label', array('class' => 'label')),
                     array('Errors'),
                 ));

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setIgnore(true);
        $submit->class = 'formsubmit';
        $submit->setLabel('Вход')
               ->setDecorators(array(
                   array('ViewHelper', array('helper' => 'formSubmit'))
               ));

        $this->addElements(array(
            $hash,
            $return,
            $login,
            $pass,
            $submit
        ));

        $this->setDecorators(array(
            'FormElements',
            'Fieldset',
            'Form',
            'Description',
        ));

        $this->setMethod('post');

        parent :: __construct($options);
    }
}