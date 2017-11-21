<?php

namespace Project\Form;

use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilter;
use Project\Validator\ProjectExistsValidator;
use Project\Entity\Task;

/**
 * This form is used to collect user's email, full name, password and status. The form
 * can work in two scenarios - 'create' and 'update'. In 'create' scenario, user
 * enters password, in 'update' scenario he/she doesn't enter password.
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

    /**
     * Constructor.
     */
    public function __construct($scenario = 'create', $entityManager = null, $task = null, $taskManager = null)
    {
        // Define form name
        parent::__construct('project-form');

        // Set POST method for this form
        $this->setAttribute('method', 'post');

        // Save parameters for internal use.
        $this->scenario = $scenario;
        $this->entityManager = $entityManager;
        $this->task = $task;
        $this->taskManager = $taskManager;

        $this->addElements();
        $this->addInputFilter();
    }

    /**
     * This method adds elements to form (input fields and submit button).
     */
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
        // Add "priorities" field
        $this->add([
            'type' => 'select',
            'name' => 'priority',
            'options' => [
                'label' => 'Priority',
                'value_options' => $priorities
            ],
        ]);


        $statuses = $this->taskManager->getStatusList();

        // Add "status" field
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

        // Add the Submit button
        $this->add([
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => [
                'value' => 'Create'
            ],
        ]);

        // Add the Choose user button
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

    /**
     * This method creates input filter (used for form filtering/validation).
     */
    private function addInputFilter()
    {
        // Create main input filter
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        // Add input for "full_name" field
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


        // Add input for "password" field
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

        // Add input for "status" field
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