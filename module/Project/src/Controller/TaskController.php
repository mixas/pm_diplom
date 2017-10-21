<?php

namespace Project\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Project\Entity\Task;
use Project\Service\TaskManager;
use Project\Form\TaskForm;
//use User\Form\PasswordChangeForm;
//use User\Form\PasswordResetForm;

/**
 * This controller is responsible for user management (adding, editing,
 * viewing users and changing user's password).
 */
class TaskController extends AbstractActionController
{
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Task manager.
     * @var Project\Service\TaskManager
     */
    private $taskManager;

    /**
     * Constructor.
     */
    public function __construct($entityManager, $taskManager)
    {
        $this->entityManager = $entityManager;
        $this->taskManager = $taskManager;
    }

    /**
     * This is the default "index" action of the controller. It displays the
     * list of users.
     */
    public function indexAction()
    {
        $tasks = $this->entityManager->getRepository(Task::class)
            ->findBy([], ['id'=>'ASC']);

        return new ViewModel([
            'tasks' => $tasks
        ]);
    }

    /**
     * This action displays a page allowing to add a new user.
     */
    public function addAction()
    {
        // Create user form
        $form = new TaskForm('create', $this->entityManager);

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
                $task = $this->taskManager->addTask($data);

                // Redirect to "view" page
                return $this->redirect()->toRoute('tasks',
                    ['action'=>'view', 'id'=>$task->getId()]);
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
        $task = $this->entityManager->getRepository(Task::class)
            ->find($id);

        if ($task == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        return new ViewModel([
            'task' => $task,
            'status' => $this->taskManager->getStatusAsString($task->getStatus())
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

        $task = $this->entityManager->getRepository(Task::class)
            ->find($id);

        if ($task == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Create task form
        $form = new TaskForm('update', $this->entityManager, $task, $this->taskManager);

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
                $this->taskManager->updateTask($task, $data);

                // Redirect to "view" page
                return $this->redirect()->toRoute('tasks',
                    ['action'=>'view', 'id'=>$task->getId()]);
            }
        } else {
            $form->setData(array(
                'assigned_user_id'=>$task->getAssignedUserId(),
                'estimate'=>$task->getEstimate(),
                'task_title'=>$task->getTaskTitle(),
                'status'=>$task->getStatus(),
                'description'=>$task->getDescription(),
            ));
        }

        return new ViewModel(array(
            'task' => $task,
            'form' => $form
        ));
    }

}


