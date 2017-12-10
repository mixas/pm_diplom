<?php

namespace Project\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Project\Entity\Task;

/**
 * Форма для добавления/редактирования задач
 *
 * Class TaskForm
 * @package Project\Form
 */
class TaskForm extends Form
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
     * Current Task.
     * @var Project\Entity\Task
     */
    private $task = null;

    public function __construct($scenario = 'create', $entityManager = null, $task = null, $taskManager = null)
    {
        parent::__construct('project-form');

        $this->setAttribute('method', 'post');

        $this->scenario = $scenario;
        $this->entityManager = $entityManager;
        $this->task = $task;
        $this->taskManager = $taskManager;

        $this->addElements();
        $this->addInputFilter();
    }

    protected function addElements()
    {
        $this->add([
            'type' => 'hidden',
            'name' => 'project_id',
            'options' => [
                'label' => 'Project ID',
            ],
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'task_title',
            'options' => [
                'label' => 'Task title',
            ],
        ]);

        $this->add([
            'type' => 'textarea',
            'name' => 'description',
            'options' => [
                'label' => 'Description',
            ],
        ]);

        $this->add([
            'type' => 'hidden',
            'name' => 'estimate',
            'options' => [
                'label' => 'Estimate',
            ],
        ]);
        $this->add([
            'type' => 'text',
            'name' => 'estimate_hours',
            'options' => [
                'label' => 'Estimate hours',
            ],
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'estimate_minutes',
            'options' => [
                'label' => 'Estimate minutes',
            ],
        ]);

        $priorities = Task::getPriorityList();
        $this->add([
            'type' => 'select',
            'name' => 'priority',
            'options' => [
                'label' => 'Priority',
                'value_options' => $priorities
            ],
        ]);


        $statuses = $this->taskManager->getStatusList();

        $this->add([
            'type' => 'select',
            'name' => 'status',
            'options' => [
                'label' => 'Status',
                'value_options' => $statuses
            ],
        ]);


        $users = $this->taskManager->getAllUsersList();

        $this->add([
            'type' => 'select',
            'name' => 'assigned_user_id',
            'options' => [
                'label' => 'Assigned user',
                'value_options' => $users
            ],
        ]);

        $this->add([
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => [
                'value' => 'Create'
            ],
        ]);

        $this->add([
            'type' => 'button',
            'name' => 'choose_user',
            'label' => 'blabla',
            'attributes' => [
                'value' => 'Choose user automatically',
                'label' => 'Choose user automatically'
            ],
        ]);
    }

    private function addInputFilter()
    {
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        $inputFilter->add([
            'name' => 'task_title',
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


        $inputFilter->add([
            'name' => 'description',
            'required' => true,
            'filters' => [
            ],
            'validators' => [
                [
                    'name' => 'StringLength',
                    'options' => [
                        'min' => 6,
                        'max' => 5000
                    ],
                ],
            ],
        ]);


        $statuses = $this->taskManager->getStatusList();
        $statusesKeys = array_keys($statuses);

        $inputFilter->add([
            'name' => 'status',
            'required' => true,
            'filters' => [
                ['name' => 'ToInt'],
            ],
            'validators' => [
                ['name' => 'InArray', 'options' => ['haystack' => $statusesKeys]]
            ],
        ]);
    }
}