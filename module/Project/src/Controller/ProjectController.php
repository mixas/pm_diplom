<?php

namespace Project\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Project\Entity\Project;
use User\Entity\User;
use Project\Entity\Task;
use Project\Form\ProjectForm;
use Project\Form\ProjectUsersAssignmentForm;
use Project\Form\TechnicalAssignmentForm;
//use User\Form\PasswordChangeForm;
//use User\Form\PasswordResetForm;

use Doctrine\Common\Collections\Criteria;

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
     * Project manager.
     * @var Project\Service\technicalAssignmentManager
     */
    private $technicalAssignmentManager;

    /**
     * Constructor.
     */
    public function __construct($entityManager, $projectManager, $technicalAssignmentManager)
    {
        $this->entityManager = $entityManager;
        $this->projectManager = $projectManager;
        $this->technicalAssignmentManager = $technicalAssignmentManager;
    }

    /**
     * This is the default "index" action of the controller. It displays the
     * list of users.
     */
    public function indexAction()
    {
        $projects = $this->entityManager->getRepository(Project::class)
            ->findBy([], ['code'=>'ASC']);

        return new ViewModel([
            'projects' => $projects
        ]);
    }

    /**
     * This action displays a page allowing to add a new user.
     */
    public function addAction()
    {
        if (!$this->access('projects.manage.all')) {
            return $this->redirect()->toRoute('not-authorized');
        }

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
                    ['action'=>'view', 'code' => $project->getCode()]);
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
        $code = $this->params()->fromRoute('code', -1);
        if ($code == -1 || $code == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Find a user with such ID.
        $project = $this->entityManager->getRepository(Project::class)
            ->findOneByCode($code);

        $tasks = $project->getTasks();
        $tasks->initialize();

        if ($project == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $participants = $project->getUsers();
        $technicalAssignment = $project->getTechnicalAssignment();
        return new ViewModel([
            'project' => $project,
            'tasks' => $tasks,
            'participants' => $participants,
            'technicalAssignment' => $technicalAssignment,
        ]);
    }

    /**
     * The "edit" action displays a page allowing to edit user.
     */
    public function editAction()
    {
        $code = $this->params()->fromRoute('code', -1);
        if ($code == -1 || $code == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $project = $this->entityManager->getRepository(Project::class)
            ->findOneByCode($code);

        if ($project == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }


        if (!$this->access('projects.manage.all') &&
            !$this->access('projects.manage.own', ['project' => $project])) {
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
                    ['action'=>'view', 'code'=>$project->getCode()]);
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

    function assignUsersAction(){
        $code = $this->params()->fromRoute('code', -1);
        if ($code == -1 || $code == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $project = $this->entityManager->getRepository(Project::class)
            ->findOneByCode($code);

        if ($project == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $allUsers = $this->entityManager->getRepository(User::class)
            ->findBy([], ['email'=>'ASC']);

//        $assignedUsers = $this->roleManager->getEffectivePermissions($project);
        $assignedUsers = $project->getUsers();
        $assignedUsers->initialize();

        // Create form
        $form = new ProjectUsersAssignmentForm($this->entityManager);
        foreach ($allUsers as $user) {
            $label = $user->getFullName();
            $checked = false;
//            $criteria = Criteria::create()->where(Criteria::expr()->in("id", [$user->getId()]));
            //TODO: get $assigned users ids array and find current user in this array instead of foreach collection
            foreach ($assignedUsers as $assignedUser) {
                if($assignedUser->getId() == $user->getId()){
                    $label .= ' (assigned)';
                    $checked = 'checked';
                    break;
                }
            }
            $form->addUsersField($user->getId(), $label, $checked);
        }

        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {

            // Fill in the form with POST data
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Validate form
            if($form->isValid()) {

                // Get filtered and validated data
                $data = $form->getData();

                // Update permissions.
                $this->projectManager->updateProjectUsers($project, $data);

                // Add a flash message.
                $this->flashMessenger()->addSuccessMessage('User were successfully assignment.');

                // Redirect to "index" page
                return $this->redirect()->toRoute('projects', ['action' => 'view', 'code' => $project->getCode()]);
            }
        } else {

            $data = [];
            foreach ($assignedUsers as $name=>$inherited) {
                $data['users'][$name] = 1;
            }

            $form->setData($data);
        }

        $errors = $form->getMessages();

        return new ViewModel([
            'form' => $form,
            'project' => $project,
            'allUsers' => $allUsers,
            'assignedUsers' => $assignedUsers
        ]);
    }


    public function viewTechnicalAssignmentAction()
    {
        $code = $this->params()->fromRoute('code', -1);
        if ($code == -1 || $code == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Find a user with such ID.
        $project = $this->entityManager->getRepository(Project::class)
            ->findOneByCode($code);

        $technicalAssignment = $project->getTechnicalAssignment();

        if(!$technicalAssignment){
            $this->getResponse()->setStatusCode(404);
            return;
        }

        if ($project == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        return new ViewModel([
            'technicalAssignment' => $technicalAssignment,
            'project' => $project,
        ]);
    }


    public function editTechnicalAssignmentAction()
    {
        $code = $this->params()->fromRoute('code', -1);
        if ($code == -1 || $code == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $project = $this->entityManager->getRepository(Project::class)
            ->findOneByCode($code);

        if ($project == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $technicalAssignment = $project->getTechnicalAssignment();

        if ($technicalAssignment == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        if (!$this->access('projects.manage.all') &&
            !$this->access('projects.manage.own', ['project' => $project])) {
            return $this->redirect()->toRoute('not-authorized');
        }

        // Create project form
        $form = new TechnicalAssignmentForm('update', $this->entityManager, $project);

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
                $this->technicalAssignmentManager->updateTechnicalAssignment($technicalAssignment, $data);

                // Redirect to "view" page
                return $this->redirect()->toRoute('projects',
                    ['action' => 'viewTechnicalAssignment', 'code' => $project->getCode()]);
            }
        }else{
            $form->setData(array(
                'deadline_date' => $technicalAssignment->getDeadlineDate(),
                'description' => $technicalAssignment->getDescription(),
            ));
        }

        return new ViewModel(array(
            'project' => $project,
            'technicalAssignment' => $technicalAssignment,
            'form' => $form
        ));
    }


    public function createTechnicalAssignmentAction()
    {
        $code = $this->params()->fromRoute('code', -1);
        if ($code == -1 || $code == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Find a user with such ID.
        $project = $this->entityManager->getRepository(Project::class)
            ->findOneByCode($code);

        if (!$this->access('projects.manage.all') &&
            !$this->access('projects.manage.own', ['project' => $project])) {
            return $this->redirect()->toRoute('not-authorized');
        }

        // Create user form
        $form = new TechnicalAssignmentForm('create', $this->entityManager);

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
                $technicalAssignment = $this->technicalAssignmentManager->addTechnicalAssignment($data, $project);

                // Redirect to "view" page
                return $this->redirect()->toRoute('projects',
                    ['action' => 'view', 'code' => $project->getCode()]);
            }
        }

        return new ViewModel([
            'form' => $form,
            'project' => $project
        ]);

    }

}


