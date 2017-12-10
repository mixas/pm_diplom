<?php
namespace User\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use User\Validator\RoleExistsValidator;

class RoleForm extends Form
{
    private $scenario;
    
    private $entityManager;
    
    private $role;
    
    public function __construct($scenario='create', $entityManager = null, $taskManager = null, $role = null)
    {
        $this->scenario = $scenario;
        $this->entityManager = $entityManager;
        $this->taskManager = $taskManager;
        $this->role = $role;
        
        parent::__construct('role-form');
     
        $this->setAttribute('method', 'post');
        
        $this->addElements();
        $this->addInputFilter();          
    }
    
    protected function addElements()
    {
        $this->add([
            'type'  => 'text',
            'name' => 'name',
            'attributes' => [
                'id' => 'name'
            ],
            'options' => [
                'label' => 'Role Name',
            ],
        ]);
        
        $this->add([
            'type'  => 'textarea',
            'name' => 'description',
            'attributes' => [
                'id' => 'description'
            ],
            'options' => [
                'label' => 'Description',
            ],
        ]);
        
        $this->add([
            'type'  => 'select',
            'name' => 'inherit_roles[]',
            'attributes' => [
                'id' => 'inherit_roles[]',
                'multiple' => 'multiple',
            ],
            'options' => [
                'label' => 'Optionally inherit permissions from these role(s)'
            ],
        ]);

        $statuses = $this->taskManager->getStatusList();

        $this->add([
            'type' => 'select',
            'name' => 'default_status_filter',
            'options' => [
                'label' => 'Status',
                'value_options' => $statuses
            ],
        ]);
                        
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
    
    private function addInputFilter()
    {
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);
        
        $inputFilter->add([
                'name'     => 'name',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 128
                        ],
                    ],
                    [
                        'name' => RoleExistsValidator::class,
                        'options' => [
                            'entityManager' => $this->entityManager,
                            'role' => $this->role
                        ],
                    ],
                ],
            ]);                          
        
        $inputFilter->add([
                'name'     => 'description',
                'required' => true,
                'filters'  => [
                    ['name' => 'StringTrim'],                    
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => 1024
                        ],
                    ],
                ],
            ]);                  
        
        $inputFilter->add([
                'name'     => 'inherit_roles[]',
                'required' => false,
                'filters'  => [
                                    
                ],                
                'validators' => [
                    
                ],
            ]);                  
    }           
}
