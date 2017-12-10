<?php
namespace Project\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Project\Validator\ProjectExistsValidator;

/**
 * ‘орма дл€ добавлени€/редактировани€ проектов
 *
 * Class ProjectForm
 * @package Project\Form
 */
class ProjectForm extends Form
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
            'type' => 'text',
            'name' => 'code',
            'options' => [
                'label' => 'Project code',
            ],
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'name',
            'options' => [
                'label' => 'Project Name',
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
            'type' => 'select',
            'name' => 'status',
            'options' => [
                'label' => 'Status',
                'value_options' => [
                    1 => 'Active',
                    2 => 'Retired',
                ]
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
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        if ($this->scenario == 'create') {

            $inputFilter->add([
                'name' => 'code',
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 1,
                            'max' => 10
                        ],
                    ],
                    [
                        'name' => ProjectExistsValidator::class,
                        'options' => [
                            'entityManager' => $this->entityManager,
                            'project' => $this->project
                        ],
                    ],
                ],
            ]);

        }

        $inputFilter->add([
            'name' => 'name',
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
            'name' => 'status',
            'required' => true,
            'filters' => [
                ['name' => 'ToInt'],
            ],
            'validators' => [
                ['name' => 'InArray', 'options' => ['haystack' => [1, 2]]]
            ],
        ]);
    }
}