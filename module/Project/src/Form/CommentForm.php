<?php

namespace Project\Form;

use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilter;
use Project\Entity\Comment;

/**
 * This form is used to collect user's email, full name, password and status. The form
 * can work in two scenarios - 'create' and 'update'. In 'create' scenario, user
 * enters password, in 'update' scenario he/she doesn't enter password.
 */
class CommentForm extends Form
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

    private $taskId = null;

    /**
     * Constructor.
     */
    public function __construct($scenario = 'create', $entityManager = null, $taskId = null)
    {
        // Define form name
        parent::__construct('project-form');

        // Set POST method for this form
        $this->setAttribute('method', 'post');

        // Save parameters for internal use.
        $this->scenario = $scenario;
        $this->entityManager = $entityManager;
        $this->taskId = $taskId;

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
            'name' => 'task_id',
            'options' => [
                'label' => 'Related Task ID',
            ],
        ]);

        $this->add([
            'type' => 'textarea',
            'name' => 'comment_text',
            'options' => [
                'label' => 'Text',
            ],
            'attributes' => [
                'id' => 'comment-text',
            ]
        ]);

        $taskId = $this->taskId;

        // Add the Submit button
        $this->add([
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => [
                'value' => 'Add Comment',
                'class' => 'btn btn-default',
                'onclick' => "addComment($taskId)"
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
//        $inputFilter->add([
//            'name' => 'comment_text',
//            'required' => true,
//            'filters' => [
//                ['name' => 'StringTrim'],
//            ],
//            'validators' => [
//                [
//                    'name' => 'StringLength',
//                    'options' => [
//                        'min' => 1,
////                        'max' => 255
//                    ],
//                ],
//            ],
//        ]);

    }
}