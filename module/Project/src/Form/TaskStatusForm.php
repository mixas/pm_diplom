<?php

namespace Project\Form;

use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilter;
use Project\Validator\ProjectExistsValidator;
use Project\Entity\TaskStatus;

/**
 * This form is used to collect user's email, full name, password and status. The form
 * can work in two scenarios - 'create' and 'update'. In 'create' scenario, user
 * enters password, in 'update' scenario he/she doesn't enter password.
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
     * Current Task.
     * @var Project\Entity\TaskStatus
     */
    private $taskStatus = null;

    /**
     * Constructor.
     */
    public function __construct($scenario = 'create', $entityManager = null, $taskStatus = null)
    {
        // Define form name
        parent::__construct('task-status-form');

        // Set POST method for this form
        $this->setAttribute('method', 'post');

        // Save parameters for internal use.
        $this->scenario = $scenario;
        $this->entityManager = $entityManager;
        $this->taskStatus = $taskStatus;

        $this->addElements();
        $this->addInputFilter();
    }

    /**
     * This method adds elements to form (input fields and submit button).
     */
    protected function addElements()
    {
        // Add "task_title" field
        $this->add([
            'type' => 'text',
            'name' => 'label',
            'options' => [
                'label' => 'Status label',
            ],
        ]);

        /**
         * TODO: GET VALUES FROM DB
         */
        // Add "status" field
//        $this->add([
//            'type' => 'select',
//            'name' => 'status',
//            'options' => [
//                'label' => 'Status',
//                'value_options' => [
//                    TaskStatus::STATUS_TO_DO => 'To do',
//                    TaskStatus::STATUS_IN_PROCESS => 'In progress',
//                    TaskStatus::STATUS_TO_BE_CHECKED => 'To be checked',
//                    TaskStatus::STATUS_IN_QA => 'In QA',
//                    TaskStatus::STATUS_CHECKED => 'Checked',
//                    TaskStatus::STATUS_DONE => 'Done',
//                ]
//            ],
//        ]);

        // Add the Submit button
        $this->add([
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => [
                'value' => 'Create Status'
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