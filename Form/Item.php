<?php
/**
 * ABS Library
 *
 * @category   ABS_Library
 * @package    Form
 * @copyright  Copyright (c) 2008 Shvakin V. (http://a1p2m3.googlepages.com/)
 * @version    2.0
 */

class Abs_Form_Item extends Zend_Form {
    protected $table_name;
    protected $_Groups=array();


    public function __construct($table_name, $options = null, $groups=null, $action=null)
    {
        parent::__construct($options);

        $this->addPrefixPath('Abs_Form', 'Abs/Form');

        if (!is_null($groups)) {
            $this->_Groups=$groups;
            foreach ($this->_Groups as $name=>$fields)
            {
                $subform=new Zend_Form_SubForm();
                $subform->setIsArray(false);
                $subform->setLegend(t($name));
                $this->addSubForm($subform, $name);
            }
        }

        $this->table_name=$table_name;

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setIgnore(true);
        $submit->class = 'formsubmit';
        $submit->setLabel('Отправить');
        $submit->setDecorators(array(
        array('ViewHelper', array('helper' => 'formSubmit'))
        ));

        $this->addElement($submit);

        $this->setDecorators(array(
            'FormElements',
            'Fieldset',
            'Form',
            'Description',
        ));

        $this->setMethod('post');
        if (!is_null($action)) {
            $this->setAction($action);
        }
    }

    /**
     * Возвращаеть субформу, в которую должен быть помещен элемен
     *
     * @param string $name_element
     * @return Zend_Form_SubForm|null
     */
    private function _getElementSubform($name_element)
    {
        static $subelements=null;

        if (is_null($subelements))
        {
            $subelements=array();
            foreach ($this->_Groups as $name=>$fields)
            {
                foreach ($fields as $field)
                {
                    $subelements[$field]=$name;
                }
            }
        }

        if (array_key_exists($name_element, $subelements)) {
            return $this->getSubForm($subelements[$name_element]);
        }
        else {
            return null;
        }
    }

    /**
     * Создание элемента формы
     *
     * @param string $name
     * @param array $options
     * @return Zend_Form_Element_Xhtml
     */
    protected function createDbElement($name, $options, $sub_options=null)
    {
        $translate=Zend_Registry::get('Zend_Translate');
        //var_dump($options['DATA_TYPE']);
        switch ($options['DATA_TYPE'])
        {
            case 'int':
            case 'integer':
            case 'smallint':
            case 'mediumint':
                if ($options['PRIMARY'] and $name==='id') {
                    $control=new Zend_Form_Element_Hidden($name);
                    $control->addDecorator('ViewHelper', array('helper' => 'formHidden'));
                }
                else {
                    if (!is_null($sub_options) and isset($sub_options['multi'])) {
                        $control=new Zend_Form_Element_Select($name);
                        $control->setMultiOptions($sub_options['multi']);
                        $control->addDecorator('ViewHelper', array('helper' => 'formSelect'));
                        //$control->setAttrib('size', 1);
                    }
                    else {
                        $control=new Zend_Form_Element_Text($name);
                        $control->addDecorator('ViewHelper', array('helper' => 'formText'));
                    }
                }
                $control->addValidator('Int');
                break;
            case 'tinyint':
                if ($options['PRIMARY'] and $name==='id') {
                    $control=new Zend_Form_Element_Hidden($name);
                    $control->addDecorator('ViewHelper', array('helper' => 'formHidden'));
                }
                else {
                    if ($options['LENGTH']==1) {
                        $control=new Zend_Form_Element_Checkbox($name);
                        $control->addDecorator('ViewHelper', array('helper' => 'formCheckbox'));
                    }
                    else {
                        if (!is_null($sub_options) and isset($sub_options['multi'])) {
                            $control=new Zend_Form_Element_Select($name);
                            $control->setMultiOptions($sub_options['multi']);
                            $control->addDecorator('ViewHelper', array('helper' => 'formSelect'));
                            //$control->setAttrib('size', 1);
                        }
                        else {
                            $control=new Zend_Form_Element_Text($name);
                            $control->addDecorator('ViewHelper', array('helper' => 'formText'));
                        }
                    }
                }
                $control->addValidator('Int');
                break;
            case 'varchar':
            case 'char':
            case 'timestamp':
            case 'year(4)':
            case 'decimal':
                $control=new Zend_Form_Element_Text($name);
                $control->addDecorator('ViewHelper', array('helper' => 'formText'));
                break;
            case 'text':
            case 'mediumtext':
                $control=new Zend_Form_Element_Textarea($name);
                $control->addDecorator('ViewHelper', array('helper' => 'formTextarea'));
                $control->setAttrib('rows', 5);
                $control->setAttrib('cols', 40);
                break;
            default:
                //throw new Zend_Exception('Для данного типа: '.$options['DATA_TYPE'].', элемент формы не определен')
                break;
        }

        if (isSet($control)) {
            if (!$options['PRIMARY'] or ($options['PRIMARY'] and $name!=='id')) {
                $control->setLabel(t($this->table_name.'.'.$name));
                $control->addDecorators(array(
                array('Label', array('class' => 'label')),
                array('Errors'),
                ));
            }
            else {
                $control->setAllowEmpty(true);
            }

            if (!is_null($options['LENGTH'])) {
                $control->addValidator('stringLength', false, array(1, $options['LENGTH']));
                $control->setAttrib('maxlength', $options['LENGTH']);
                if ($options['LENGTH']>30) {
                    $length=30;
                }
                else {
                    $length=$options['LENGTH'];
                }

                if (get_class($control)==='Zend_Form_Element_Select') {
                    $size=1;
                }
                else {
                    $size=$length;
                }

                $control->setAttrib('size', $size);
            }
            if (!$options['NULLABLE'] and !$options['PRIMARY'] and get_class($control)!=='Zend_Form_Element_Checkbox') {
                $control->addValidator('NotEmpty', true);
                $control->setRequired(true);
            }

            $submit=$this->getElement('submit');
            $submit->setOrder($submit->getOrder()+1);
            return $control;
        }

        else {
            return null;
        }
    }

    public function addDbElement($name, $options, $sub_options=null)
    {
        $element=$this->createDbElement($name, $options, $sub_options);
        if (!is_null($element)) {
            if (get_class($element)=='Zend_Form_SubForm') {
                $this->addSubForm($element, $name);
            }
            else {
                $subform=$this->_getElementSubform($element->getName());
                if (!is_null($subform)) {
                    $subform->addElement($element);
                }
                else {
                    $this->addElement($element);
                }
            }
            $submit=$this->getElement('submit');
            $submit->setOrder($submit->getOrder()+1);
        }
    }

    public function getValues()
    {
        $values=array();
        $subforms=$this->getSubForms();
        $elements=$this->getElements();
        foreach ($subforms as $name=>$subform)
        {
            if (array_key_exists($name, $this->_Groups)) {
                $values=array_merge($values, $subform->getValues());
            }
            else {
                $values[$name]=$subform->getValues();
            }
        }
        foreach ($elements as $element){
            if (!$element->getIgnore()) {
                $values[$element->getName()]=$element->getValue();
            }
        }

        return $values;
    }
}