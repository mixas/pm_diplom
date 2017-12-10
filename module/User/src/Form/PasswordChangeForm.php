<?php
namespace User\Form;

use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilter;

class PasswordChangeForm extends Form
{   
    private $scenario;

    /**
     * @param int|null|string $scenario
     */
    public function __construct($scenario)
    {
        parent::__construct('password-change-form');
     
        $this->scenario = $scenario;
        
        $this->setAttribute('method', 'post');
        
        $this->addElements();
        $this->addInputFilter();          
    }

    protected function addElements()
    {
        if ($this->scenario == 'change') {
        
            $this->add([
                'type'  => 'password',
                'name' => 'old_password',
                'options' => [
                    'label' => 'Old Password',
                ],
            ]);       
        }
        
        $this->add([
            'type'  => 'password',
            'name' => 'new_password',
            'options' => [
                'label' => 'New Password',
            ],
        ]);
        
        $this->add([
            'type'  => 'password',
            'name' => 'confirm_new_password',
            'options' => [
                'label' => 'Confirm new password',
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
        
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [                
                'value' => 'Change Password'
            ],
        ]);
    }
    
    private function addInputFilter()
    {
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);
        
        if ($this->scenario == 'change') {
            
            $inputFilter->add([
                    'name'     => 'old_password',
                    'required' => true,
                    'filters'  => [                    
                    ],                
                    'validators' => [
                        [
                            'name'    => 'StringLength',
                            'options' => [
                                'min' => 6,
                                'max' => 64
                            ],
                        ],
                    ],
                ]);      
        }
        
        $inputFilter->add([
                'name'     => 'new_password',
                'required' => true,
                'filters'  => [                    
                ],                
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 6,
                            'max' => 64
                        ],
                    ],
                ],
            ]);
        
        $inputFilter->add([
                'name'     => 'confirm_new_password',
                'required' => true,
                'filters'  => [                    
                ],                
                'validators' => [
                    [
                        'name'    => 'Identical',
                        'options' => [
                            'token' => 'new_password',                            
                        ],
                    ],
                ],
            ]);
    }
}

