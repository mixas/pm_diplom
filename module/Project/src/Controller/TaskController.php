<?php

namespace Project\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Project\Entity\Comment;
use Project\Entity\Task;
use Project\Entity\Project;
use Project\Service\TaskManager;
use Project\Form\TaskForm;
use Project\Form\CommentForm;
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
        $form = new TaskForm('create', $this->entityManager, null, $this->taskManager);

        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {

            // Fill in the form with POST data
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Validate form
            if($form->isValid()) {

                // Get filtered and validated data
                $data = $form->getData();

                $project = null;

                if($projectId = $this->getRequest()->getPost('project_id')){
                    $project = $this->entityManager->getRepository(Project::class)
                        ->find($projectId);
                }

                // Add user.
                $task = $this->taskManager->addTask($data, $project);

                $this->flashMessenger()->addMessage('Task has been successfully created', 'success');

                // Redirect to "view" page
                return $this->redirect()->toRoute('tasks',
                    ['action'=>'view', 'id'=>$task->getId()]);
            }
        }

        return new ViewModel([
            'form' => $form,
            'projectId' => $this->getRequest()->getQuery('project')
        ]);
    }

    /**
     * The "view" action displays a page allowing to view user's details.
     */
    public function viewAction()
    {
//        $this->flashMessenger()->addMessage('Task has been successfully created', 'success');

        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $commentForm = new CommentForm('create', $this->entityManager, $id);

        // Find a user with such ID.
        $task = $this->entityManager->getRepository(Task::class)
            ->find($id);

        // Find all task comments
        $comments = $this->entityManager->getRepository(Comment::class)
            ->findByTaskId($id);

        if ($task == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }


        return new ViewModel([
            'commentForm' => $commentForm,
            'task' => $task,
            'project' => $task->getProject(),
            'comments' => $comments,
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


