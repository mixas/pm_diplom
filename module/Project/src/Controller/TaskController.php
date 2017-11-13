<?php

namespace Project\Controller;

use Project\Entity\TimeLog;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Project\Entity\Comment;
use Project\Entity\Task;
use Project\Entity\Project;
use User\Entity\User;
use User\Entity\Role;
use Project\Service\TaskManager;
use Project\Form\TaskForm;
use Project\Form\CommentForm;
use Project\Form\ReassignForm;
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
     * Auth service.
     * @var Zend\Authentication\AuthenticationService
     */
    private $authService;

    /**
     * Constructor.
     */
    public function __construct($entityManager, $taskManager, $timeLogManager, $authService, $rendererInterface)
    {
        $this->entityManager = $entityManager;
        $this->taskManager = $taskManager;
        $this->timeLogManager = $timeLogManager;
        $this->authService = $authService;
        $this->rendererInterface = $rendererInterface;
    }

    /**
     * renders list of assigned to current user tasks.
     */
    public function indexAction()
    {
        $currentUser = $this->entityManager->getRepository(User::class)
            ->findOneByEmail($this->authService->getIdentity());

        $tasks = $this->entityManager->getRepository(Task::class)
            ->findByAssignedUserId($currentUser->getId(), ['id'=>'ASC']);

        return new ViewModel([
            'tasks' => $tasks
        ]);
    }

    /**
     * This action displays a page allowing to add a new task.
     */
    public function addAction()
    {
        $projectCode = $this->params()->fromRoute('project', -1);
        if ($projectCode == -1 || $projectCode == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        $project = $this->entityManager->getRepository(Project::class)
            ->findOneByCode($projectCode);

        if(!$project){
            return $this->redirect()->toRoute('projects',
                ['action' => 'view', 'code' => $projectCode]);
        }

        if (!$this->access('projects.manage.all') &&
            !$this->access('projects.manage.own', ['project' => $project])) {
            return $this->redirect()->toRoute('not-authorized');
        }

        // Create user form
        $form = new TaskForm('create', $this->entityManager, null, $this->taskManager);

        $allRoles = $this->entityManager->getRepository(Role::class)
            ->findBy([], ['name'=>'ASC']);

        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {

            // Fill in the form with POST data
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Validate form
            if($form->isValid()) {

                // Get filtered and validated data
                $data = $form->getData();

                $assignedUser = $this->entityManager->getRepository(User::class)
                    ->findOneById($data['assigned_user_id']);

                // Add task.
                $task = $this->taskManager->addTask($data, $project, $assignedUser);

                $this->flashMessenger()->addMessage('Task has been successfully created', 'success');

                // Redirect to "view" page
                return $this->redirect()->toRoute('tasks',
                    ['action' => 'view', 'task' => $task->getId(), 'project' => $project->getId()]);
            }
        }

        return new ViewModel([
            'form' => $form,
            'roles' => $allRoles,
            'projectId' => $project->getId()
        ]);
    }

    /**
     * The "view" action displays a page allowing to view user's details.
     */
    public function viewAction()
    {
//        $this->flashMessenger()->addMessage('Task has been successfully created', 'success');

        $id = (int)$this->params()->fromRoute('task', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $commentForm = new CommentForm('create', $this->entityManager, $id);

        // Find a user with such ID.
        $task = $this->entityManager->getRepository(Task::class)
            ->find($id);

        $comments = $task->getComments();

        $timeLogs = $task->getTimeLogs();
        $spentTime = 0;
        foreach ($timeLogs as $timeLog) {
            $spentTime += $timeLog->getSpentTime();
        }


        if ($task == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }


        return new ViewModel([
            'commentForm' => $commentForm,
            'task' => $task,
            'project' => $task->getProject(),
            'comments' => $comments,
            'timeLogs' => $timeLogs,
            'spentTime' => $spentTime,
            'status' => $this->taskManager->getStatusAsString($task->getStatus())
        ]);
    }

    /**
     * The "edit" action displays a page allowing to edit user.
     */
    public function editAction()
    {
        $id = (int)$this->params()->fromRoute('task', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $task = $this->entityManager->getRepository(Task::class)
            ->find($id);

        $project = $task->getProject();
        if (!$this->access('projects.manage.all') &&
            !$this->access('projects.manage.own', ['project' => $project])) {
            return $this->redirect()->toRoute('not-authorized');
        }

        if ($task == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Create task form
        $form = new TaskForm('update', $this->entityManager, $task, $this->taskManager);

        $allRoles = $this->entityManager->getRepository(Role::class)
            ->findBy([], ['name'=>'ASC']);

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

                $project = $task->getProject();

                // Redirect to "view" page
                return $this->redirect()->toRoute('tasks',
                    ['action'=>'view', 'task' => $task->getId(), 'project' => $project->getCode()]);
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
            'form' => $form,
            'roles'=>$allRoles
        ));
    }


    /**
     * Reassign user for particular task action
     *
     * @return \Zend\Http\Response|\Zend\Stdlib\ResponseInterface
     */
    public function reassignAction(){
        $response = $this->getResponse();

        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {

            $data = $this->params()->fromPost();
            $taskId = $data['task_id'];

            $task = $this->entityManager->getRepository(Task::class)
                ->find($taskId);

            $project = $task->getProject();

            if (!$this->access('projects.manage.all') &&
                !$this->access('projects.manage.own', ['project' => $project])) {
                return $this->redirect()->toRoute('not-authorized');
            }

            try {
                $userId = $data['user_id'];
                $user = $this->entityManager->getRepository(User::class)
                    ->find($userId);

                $taskData = ['assigned_user_id' => $userId];

                $this->taskManager->updateTask($task, $taskData);

                //todo: russian translation should be here but json can't encode russian characters.
                $response->setContent(\Zend\Json\Json::encode(
                    array(
                        'response' => true,
                        'message' => 'User was successfully assigned to the task',
                        'user_name' => $user->getFullName()
                    )
                ));
            }catch (\Exception $e){
                $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => $e->getMessage())));
            }

        }else{
            $allUsers = $this->entityManager->getRepository(User::class)
                ->findBy([], ['id'=>'ASC']);

            $viewModel = new ViewModel(
                ['allUsers' => $allUsers]
            );
            $viewModel->setTemplate('all_users');
            $renderer = $this->rendererInterface;
            $html = $renderer->render($viewModel);

            //todo: russian translation should be here but json can't encode russian characters.
            $response->setContent(\Zend\Json\Json::encode(
                array(
                    'response' => true,
                    'html' => $html
                )
            ));
        }


        return $response;

    }


    /**
     * Add time log ajax action
     *
     * @return \Zend\Http\Response|\Zend\Stdlib\ResponseInterface
     */
    public function addtimelogAction(){
        $response = $this->getResponse();

        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {

            $data = $this->params()->fromPost();
            $taskId = $data['task_id'];

            $task = $this->entityManager->getRepository(Task::class)
                ->find($taskId);

            $currentUser = $this->entityManager->getRepository(User::class)
                ->findOneByEmail($this->authService->getIdentity());

            try {

                $timeLogData = ['spent_time' => $data['spent_time']];

                $timeLog = $this->timeLogManager->addTimeLog($timeLogData, $task, $currentUser);

                $viewModel = new ViewModel(
                    ['timeLog' => $timeLog]
                );
                $viewModel->setTemplate('time_log');
                $renderer = $this->rendererInterface;
                $html = $renderer->render($viewModel);


                $timeLogs = $task->getTimeLogs();
                $spentTime = 0;
                foreach ($timeLogs as $timeLog) {
                    $spentTime += $timeLog->getSpentTime();
                }
                $spentTimeViewModel = new ViewModel(
                    ['task' => $task,
                    'spentTime' => $spentTime]
                );
                $spentTimeViewModel->setTemplate('spent_time');
                $spentTimeHtml = $renderer->render($spentTimeViewModel);

                //todo: russian translation should be here but json can't encode russian characters.
                $response->setContent(\Zend\Json\Json::encode(
                    array(
                        'response' => true,
                        'message' => 'User was successfully assigned to the task',
                        'html' => $html,
                        'spent_time_html' => $spentTimeHtml
                    )
                ));
            }catch (\Exception $e){
                $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => $e->getMessage())));
            }

        }else{
            $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => 'Invalid request')));
        }

        return $response;

    }

    public function edittimelogAction(){
        $response = $this->getResponse();

        if ($this->getRequest()->isPost()) {

            // Fill in the form with POST data
            $data = $this->params()->fromPost();
            $timeLogId = $data['time_log_id'];

            try {
                $timeLog = $this->entityManager->getRepository(TimeLog::class)
                    ->find($timeLogId);

                $task = $timeLog->getTask();

                if (!$timeLog->getId()) {
                    $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => 'Time log was not found')));
                    return $response;
                }

                if (!$this->access('comments.manage.own', ['comment' => $timeLog])) {
                    $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => 'You are not allowed to manage this comment')));
                    return $response;
                }
                $this->timeLogManager->updateTimeLog($timeLog, $data);

                $viewModel = new ViewModel(
                    ['timeLog' => $timeLog]
                );
                $viewModel->setTemplate('time_log');
                $renderer = $this->rendererInterface;
                $html = $renderer->render($viewModel);


                $allTimeLogs = $task->getTimeLogs();
                $spentTime = 0;
                foreach ($allTimeLogs as $timeLogGeneral) {
                    $spentTime += $timeLogGeneral->getSpentTime();
                }
                $spentTimeViewModel = new ViewModel(
                    ['task' => $task,
                    'spentTime' => $spentTime]
                );
                $spentTimeViewModel->setTemplate('spent_time');
                $spentTimeHtml = $renderer->render($spentTimeViewModel);

                //todo: russian translation should be here but json can't encode russian characters.
                $response->setContent(\Zend\Json\Json::encode(
                    array(
                        'response' => true,
                        'message' => 'Your comment has been successfully updated',
                        'html' => $html,
                        'spent_time_html' => $spentTimeHtml
                    )
                ));
            }catch (\Exception $e){
                $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => $e->getMessage())));
            }

        } else {
            $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => 'Invalid Request')));
        }

        return $response;
    }

}


