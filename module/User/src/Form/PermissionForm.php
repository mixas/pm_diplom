<?php
namespace User\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use User\Validator\PermissionExistsValidator;

class PermissionForm extends Form
{
    private $scenario;
    
    private $entityManager;
    
    private $permission;
    
    public function __construct($scenario = 'create', $entityManager = null, $permission = null)
    {
        $this->scenario = $scenario;
        $this->entityManager = $entityManager;
        $this->permission = $permission;
        
        parent::__construct('permission-form');
     
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
                'label' => 'Permission Name',
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
                        'name' => PermissionExistsValidator::class,
                        'options' => [
                            'entityManager' => $this->entityManager,
                            'permission' => $this->permission
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
                            'min' => 1,
                            'max' => 1024
                        ],
                    ],
                ],
            ]);                                    
    }           
}

