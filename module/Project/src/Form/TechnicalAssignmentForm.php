<?php
namespace Project\Form;

use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilter;
use Project\Validator\ProjectExistsValidator;

/**
 * This form is used to collect user's email, full name, password and status. The form
 * can work in two scenarios - 'create' and 'update'. In 'create' scenario, user
 * enters password, in 'update' scenario he/she doesn't enter password.
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

    /**
     * Constructor.
     */
    public function __construct($scenario = 'create', $entityManager = null, $project = null)
    {
        // Define form name
        parent::__construct('project-form');

        // Set POST method for this form
        $this->setAttribute('method', 'post');

        // Save parameters for internal use.
        $this->scenario = $scenario;
        $this->entityManager = $entityManager;
        $this->project = $project;

        $this->addElements();
        $this->addInputFilter();
    }

    /**
     * This method adds elements to form (input fields and submit button).
     */
    protected function addElements()
    {
//        if ($this->scenario == 'create') {
            // Add "email" field
            $this->add([
                'type' => 'textarea',
                'name' => 'description',
                'options' => [
                    'label' => 'General Description',
                ],
            ]);
//        }

        // Add "full_name" field
        $this->add([
            'type' => 'Zend\Form\Element\DateSelect',
            'name' => 'deadline_date',
            'options' => [
                'label' => 'Deadline Date',
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
    }

    /**
     * This method creates input filter (used for form filtering/validation).
     */
    private function addInputFilter()
    {
        // Create main input filter
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

    }
}