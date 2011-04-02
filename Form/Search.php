<?php
/**
 * ABS Library
 *
 * @category   ABS_Library
 * @package    Form
 * @copyright  Copyright (c) 2008 Shvakin V. (http://a1p2m3.googlepages.com/)
 * @version    2.0
 */


class Abs_Form_Search extends Abs_Form_Item {


    public function __construct($table_name, $options = null, $groups=null)
    {
        parent::__construct($table_name, $options, $groups);
        $this->setMethod('get');
    }

    /**
     * Enter description here...
     *
     * @param Zend_Form_Element $control
     * @param unknown_type $label
     * @param unknown_type $options
     */
    private function setDbElementAttribs($control, $label, $options)
    {
        $control->setLabel($label);
        $control->addDecorators(array(
        //array('Label', array('class' => 'label')),
        array('Errors'),
        ));

        //$control->setAllowEmpty(false);
        $control->setRequired(false);

        if (!is_null($options['LENGTH'])) {
            $control->addValidator('stringLength', false, array(1, $options['LENGTH']));
            $control->setAttrib('maxlength', $options['LENGTH']);
            if ($options['LENGTH']>20) {
                $length=20;
            }
            else {
                $length=$options['LENGTH'];
            }
            $control->setAttrib('size', $length);
        }
    }

    /**
     * Создание элемента формы
     *
     * @param string $name
     * @param array $options
     * @return Zend_Form_Element_Xhtml
     */
    protected function createDbElement($name, $options)
    {
        $translate=Zend_Registry::get('Zend_Translate');
        switch ($options['DATA_TYPE'])
        {
            case 'int':
            case 'integer':
            case 'smallint':
            case 'year(4)':
                $control=new Zend_Form_SubForm();
                $control->addDecorator('Description');
                $control1=new Zend_Form_Element_Text('min');
                $control1->addDecorator('ViewHelper', array('helper' => 'formText'));
                $control1->addValidator('Int');

                $control2=new Zend_Form_Element_Text('max');
                $control2->addDecorator('ViewHelper', array('helper' => 'formText'));
                $control2->addValidator('Int');
                $control->addElement($control1);
                $control->addElement($control2);
                break;
            case 'tinyint':
                if ($options['LENGTH']==1) {
                    $control=new Zend_Form_Element_Checkbox($name);
                    $control->addDecorator('ViewHelper', array('helper' => 'formCheckbox'));
                    $control->addValidator('Int');
                }
                else {
                    $control=new Zend_Form_SubForm();
                    $control->addDecorator('Description');
                    $control1=new Zend_Form_Element_Text('min');
                    $control1->addDecorator('ViewHelper', array('helper' => 'formText'));
                    $control1->addValidator('Int');

                    $control2=new Zend_Form_Element_Text('max');
                    $control2->addDecorator('ViewHelper', array('helper' => 'formText'));
                    $control2->addValidator('Int');
                    $control->addElement($control1);
                    $control->addElement($control2);
                }
                break;
            case 'varchar':
            case 'char':
                $control=new Zend_Form_Element_Text($name);
                $control->addDecorator('ViewHelper', array('helper' => 'formText'));
                break;
            default:
                throw new Zend_Exception('Для данного типа: '.$options['DATA_TYPE'].', элемент формы не определен');
                break;
        }
        if (isSet($control)) {
            $label=t($this->table_name.'.'.$name);
            if (get_class($control)==='Zend_Form_SubForm') {
                $control->setLegend($label);
                $elemets=$control->getElements();
                foreach ($elemets as $element)
                {
                    $this->setDbElementAttribs($element, t($label).' - '.t($element->getName()), $options);
                }
            }
            else {
                $this->setDbElementAttribs($control, t($label), $options);
            }
            return $control;
        }
        else {
            return null;
        }
    }

    private function getElementWhere($element, $column)
    {
        $db=Zend_Registry::get('Zend_Db');
        $type=$column['DATA_TYPE'];
        $colname=$column['COLUMN_NAME'];
        switch ($type)
        {
            case 'int':
            case 'smallint':
            case 'year(4)':
                $min=(string) $element['min']->getValue();
                $max=(string) $element['max']->getValue();
                if ($min!=='' and $max!=='') {
                    $where='('.$colname.' BETWEEN '.$db->quote($min).' and '.$db->quote($max).')';
                }
                elseif ($min!=='') {
                    $where='('.$db->quoteInto($colname.' >= ?', $min).')';
                }
                elseif ($max!=='') {
                    $where='('.$db->quoteInto($colname.' <= ?', $max).')';
                }
                else {
                    $where=null;
                }
                break;
            case 'varchar':
            case 'char':
                if ((string) $element->getValue()!=='') {
                    $where='('.$db->quoteInto($colname.' like ?', $element->getValue()).')';
                }
                else {
                    $where=null;
                }
                break;
            case 'tinyint':
                if ($column['LENGTH']==1) {
                    $where='('.$db->quoteInto($colname.' = ?', (int)$element->getValue()).')';
                }
                else {
                    $min=(string)$element['min']->getValue();
                    $max=(string)$element['max']->getValue();
                    if ($min!=='' and $max!=='') {
                        $where='('.$colname.' BETWEEN '.$db->quote($min).' and '.$db->quote($max).')';
                    }
                    elseif ($min!=='') {
                        $where='('.$db->quoteInto($colname.' >= ?', $min).')';
                    }
                    elseif ($max!=='') {
                        $where='('.$db->quoteInto($colname.' <= ?', $max).')';
                    }
                    else {
                        $where=null;
                    }
                }
                break;


            default:
                $where=null;
        }
        return $where;
    }

    public function getWhere($cols)
    {
        $where_items=array();
        $elemets=$this->getElements();
        foreach ($elemets as $element)
        {
            $name=$element->getName();
            if (isSet($cols[$name])) {
                $where_item=$this->getElementWhere($element, $cols[$name]);
                if (!is_null($where_item)) {
                    $where_items[$name]=$where_item;
                }
            }
        }

        $subforms=$this->getSubForms();
        foreach ($subforms as $subform)
        {
            $name=$subform->getName();
            if (isSet($cols[$name])) {
                $where_item=$this->getElementWhere($subform->getElements(), $cols[$name]);
                if (!is_null($where_item)) {
                    $where_items[$name]=$where_item;
                }
            }
            else {
                $elemets=$subform->getElements();
                foreach ($elemets as $element)
                {
                    $name=$element->getName();
                    if (isSet($cols[$name])) {
                        $where_item=$this->getElementWhere($element, $cols[$name]);
                        if (!is_null($where_item)) {
                            $where_items[$name]=$where_item;
                        }
                    }
                }
            }
        }

        if (count($where_items)) {
            $where='';
            foreach ($where_items as $key=>$value)
            {
                if ($where!=='') $where.=' and ';
                $where.=$value;
            }
            return $where;
        }
        else {
            return null;
        }
    }
}