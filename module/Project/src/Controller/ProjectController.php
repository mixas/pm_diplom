<?php

namespace Project\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Project\Entity\Project;
use Project\Entity\Task;
use Project\Form\ProjectForm;
//use User\Form\PasswordChangeForm;
//use User\Form\PasswordResetForm;

/**
 * This controller is responsible for user management (adding, editing,
 * viewing users and changing user's password).
 */
class ProjectController extends AbstractActionController
{
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Project manager.
     * @var Project\Service\ProjectManager
     */
    private $projectManager;

    /**
     * Constructor.
     */
    public function __construct($entityManager, $projectManager)
    {
        $this->entityManager = $entityManager;
        $this->projectManager = $projectManager;
    }

    /**
     * This is the default "index" action of the controller. It displays the
     * list of users.
     */
    public function indexAction()
    {
        $projects = $this->entityManager->getRepository(Project::class)
            ->findBy([], ['id'=>'ASC']);

        return new ViewModel([
            'projects' => $projects
        ]);
    }

    /**
     * This action displays a page allowing to add a new user.
     */
    public function addAction()
    {
        // Create user form
        $form = new ProjectForm('create', $this->entityManager);

        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {

            // Fill in the form with POST data
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Validate form
            if($form->isValid()) {

                // Get filtered and validated data
                $data = $form->getData();

                // Add user.
                $project = $this->projectManager->addProject($data);

                // Redirect to "view" page
                return $this->redirect()->toRoute('projects',
                    ['action'=>'view', 'id'=>$project->getId()]);
            }
        }

        return new ViewModel([
            'form' => $form
        ]);
    }

    /**
     * The "view" action displays a page allowing to view user's details.
     */
    public function viewAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Find a user with such ID.
        $project = $this->entityManager->getRepository(Project::class)
            ->find($id);

        //TODO: deal with proper way to get all tasks from particular project. Like $tasks = $project->getTasks();. but it doesn't work
        //TODO: !!!!!!!!
        $tasks = $this->entityManager->getRepository(Task::class)
            ->findByProjectId($id);

        //all project tasks
//        $tasks = $project->getTasks();

        if ($project == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        return new ViewModel([
            'project' => $project,
            'tasks' => $tasks,
        ]);
    }

    /**
     * The "edit" action displays a page allowing to edit user.
     */
    public function editAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $project = $this->entityManager->getRepository(Project::class)
            ->find($id);

        if ($project == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }


        if (!$this->access('projects.manage.all') &&
            !$this->access('project.manage.own', ['project' => $project])) {
            return $this->redirect()->toRoute('not-authorized');
        }

        // Create project form
        $form = new ProjectForm('update', $this->entityManager, $project);

        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {

            // Fill in the form with POST data
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Validate form
            if($form->isValid()) {

                // Get filtered and validated data
                $data = $form->getData();

                // Update the user.
                $this->projectManager->updateProject($project, $data);

                // Redirect to "view" page
                return $this->redirect()->toRoute('projects',
                    ['action'=>'view', 'id'=>$project->getId()]);
            }
        } else {
            $form->setData(array(
                'name'=>$project->getName(),
                'code'=>$project->getCode(),
                'status'=>$project->getStatus(),
                'description'=>$project->getDescription(),
            ));
        }

        return new ViewModel(array(
            'project' => $project,
            'form' => $form
        ));
    }

}


