<?php

namespace Project\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

/**
 * Форма для комментариев
 *
 * Class CommentForm
 * @package Project\Form
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

//        $taskId = $this->taskId;

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

    private function addInputFilter()
    {
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);
    }
}