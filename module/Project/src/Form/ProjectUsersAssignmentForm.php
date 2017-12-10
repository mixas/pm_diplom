<?php

namespace Project\Form;

use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilter;

/**
 * ‘орма дл€ назначени€ пользователей на проект
 *
 * Class ProjectUsersAssignmentForm
 * @package Project\Form
 */
class ProjectUsersAssignmentForm extends Form
{
    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct('project-users-form');

        $this->setAttribute('method', 'post');

        $this->addElements();
        $this->addInputFilter();
    }

    protected function addElements()
    {
        $fieldset = new Fieldset('users');
        $this->add($fieldset);

        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [
                'value' => 'Assign',
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

    public function addUsersField($name, $label, $checked = false)
    {
        $this->get('users')->add([
            'type'  => 'checkbox',
            'name' => $name,
            'attributes' => [
                'id' => $name,
                'checked' => $checked
            ],
            'options' => [
                'label' => $label
            ],
        ]);

        $this->getInputFilter()->get('users')->add([
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
        // Create input filter
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);


    }
}
