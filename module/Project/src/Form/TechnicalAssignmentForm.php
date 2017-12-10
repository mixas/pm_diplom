<?php
namespace Project\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

/**
 * Форма для добавления/редактирования технического задания
 *
 * Class TechnicalAssignmentForm
 * @package Project\Form
 */
class TechnicalAssignmentForm extends Form
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
     * Current Project.
     * @var Project\Entity\Project
     */
    private $project = null;

    public function __construct($scenario = 'create', $entityManager = null, $project = null)
    {
        parent::__construct('project-form');

        $this->setAttribute('method', 'post');

        $this->scenario = $scenario;
        $this->entityManager = $entityManager;
        $this->project = $project;

        $this->addElements();
        $this->addInputFilter();
    }

    protected function addElements()
    {
        $this->add([
            'type' => 'textarea',
            'name' => 'description',
            'options' => [
                'label' => 'General Description',
            ],
        ]);

        $this->add([
            'type' => 'Zend\Form\Element\DateSelect',
            'name' => 'deadline_date',
            'options' => [
                'label' => 'Deadline Date',
            ],
        ]);

        $this->add([
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => [
                'value' => 'Create'
            ],
        ]);
    }

    private function addInputFilter()
    {
        // Create main input filter
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

    }
}