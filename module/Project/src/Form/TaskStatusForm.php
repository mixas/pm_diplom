<?php

namespace Project\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

/**
 * Форма для редактирования/добавления статусов
 *
 * Class TaskStatusForm
 * @package Project\Form
 */
class TaskStatusForm extends Form
{
    /**
     * Scenario ('create' or 'update').
     * @var string
     */
    private $scenario;

    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager = null;

    /**
     * Current TaskЫефегы.
     * @var Project\Entity\TaskStatus
     */
    private $taskStatus = null;

    public function __construct($scenario = 'create', $entityManager = null, $taskStatus = null)
    {
        parent::__construct('task-status-form');

        $this->setAttribute('method', 'post');

        $this->scenario = $scenario;
        $this->entityManager = $entityManager;
        $this->taskStatus = $taskStatus;

        $this->addElements();
        $this->addInputFilter();
    }

    protected function addElements()
    {
        $this->add([
            'type' => 'text',
            'name' => 'label',
            'options' => [
                'label' => 'Status label',
            ],
        ]);

        $this->add([
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => [
                'value' => 'Create Status'
            ],
        ]);
    }

    private function addInputFilter()
    {
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        $inputFilter->add([
            'name' => 'label',
            'required' => true,
            'filters' => [
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name' => 'StringLength',
                    'options' => [
                        'min' => 1,
                        'max' => 255
                    ],
                ],
            ],
        ]);
    }
}