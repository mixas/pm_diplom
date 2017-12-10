<?php
namespace User\Form;

use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\ArrayInput;
use User\Validator\PermissionExistsValidator;

class RolePermissionsForm extends Form
{
    private $entityManager;
    
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
        
        parent::__construct('role-permissions-form');
     
        $this->setAttribute('method', 'post');
        
        $this->addElements();
        $this->addInputFilter();          
    }
    
    protected function addElements()
    {
        $fieldset = new Fieldset('permissions');
        $this->add($fieldset);
        
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Create',
                'id' => 'submit',
            ],
        ]);
        
        $this->add([
            'type' => 'csrf',
            'name' => 'csrf',
            'options' => [
                'csrf_options' => [
                'timeout' => 600
                ]
            ],
        ]);
    }
    
    public function addPermissionField($name, $label, $isDisabled = false)
    {
        $this->get('permissions')->add([
            'type'  => 'checkbox',
            'name' => $name,
            'attributes' => [
                'id' => $name,
                'disabled' => $isDisabled
            ],
            'options' => [
                'label' => $label
            ],
        ]);
        
        $this->getInputFilter()->get('permissions')->add([
                'name'     => $name,
                'required' => false,
                'filters'  => [                    
                ],                
                'validators' => [
                    ['name' => 'IsInt'],
                ],
            ]);  
    }
    
    private function addInputFilter()
    {
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);
        
             
    }           
}
