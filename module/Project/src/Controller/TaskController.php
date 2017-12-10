<?php

namespace Project\Controller;

use Project\Entity\TimeLog;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Project\Entity\Task;
use Project\Entity\Project;
use User\Entity\User;
use User\Entity\Role;
use Project\Form\TaskForm;
use Project\Form\CommentForm;
use Project\Form\ReassignForm;
use Project\Entity\TaskStatus;

/**
 * ���������� ��� ��������� ��������, ��������� � ��������
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

    private $rbacManager;

    public function __construct($entityManager, $taskManager, $timeLogManager, $authService, $rendererInterface, $rbacManager)
    {
        $this->entityManager = $entityManager;
        $this->taskManager = $taskManager;
        $this->timeLogManager = $timeLogManager;
        $this->authService = $authService;
        $this->rendererInterface = $rendererInterface;
        $this->rbacManager = $rbacManager;
    }

    /**
     * ����������� ������ ����� (��� �������� ������������)
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $currentUser = $this->entityManager->getRepository(User::class)
            ->findOneByEmail($this->authService->getIdentity());

        // ���������� ������� �� �������
        $currentRoles = $currentUser->getRoles();
        $currentRole = $currentRoles[0];
        $defaultRoleFilterStatus = $currentRole->getDefaultStatusFilter();
        $currentFilterStatus = 0;
        $selectedFilter = $this->getRequest()->getQuery('filter_status');
        if($selectedFilter || $selectedFilter === "0"){
            $defaultRoleFilterStatus = $selectedFilter;
        }
        if($defaultRoleFilterStatus) {
            $tasks = $this->entityManager->getRepository(Task::class)
                ->findBy(['assignedUserId' => $currentUser->getId(), 'status' => $defaultRoleFilterStatus], ['id' => 'ASC']);
            $currentFilterStatus = $defaultRoleFilterStatus;
        }else{
            $tasks = $this->entityManager->getRepository(Task::class)
                ->findBy(['assignedUserId' => $currentUser->getId()], ['id' => 'ASC']);
        }

        $allTaskStatuses = $this->entityManager->getRepository(TaskStatus::class)
            ->findBy([], ['id'=>'ASC']);

        // ������ �������
        return new ViewModel([
            'tasks' => $tasks,
            'taskManager' => $this->taskManager,
            'currentUser' => $currentUser,
            'rbacManager' => $this->rbacManager,
            'currentFilterStatus' => $currentFilterStatus,
            'allStatuses' => $allTaskStatuses,
        ]);
    }

    /**
     * �������� ���� � ��
     *
     * @return void|\Zend\Http\Response|ViewModel
     */
    public function addAction()
    {
        $projectCode = $this->params()->fromRoute('project', -1);
        if ($projectCode == -1 || $projectCode == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        // ����� �������
        $project = $this->entityManager->getRepository(Project::class)
            ->findOneByCode($projectCode);

        if(!$project){
            return $this->redirect()->toRoute('projects',
                ['action' => 'view', 'code' => $projectCode]);
        }

        // �������� ����������
        if (!$this->access('projects.manage.all') &&
            !$this->access('projects.manage.own', ['project' => $project])) {
            return $this->redirect()->toRoute('not-authorized');
        }

        // �������� �����
        $form = new TaskForm('create', $this->entityManager, null, $this->taskManager);

        // ����� ���� ����� � ��
        $allRoles = $this->entityManager->getRepository(Role::class)
            ->findBy([], ['name'=>'ASC']);
        $excludedRoles = ['Administrator', 'Guest', 'Project Manager'];
        foreach ($allRoles  as $key => $role) {
            if(in_array($role->getName(), $excludedRoles)){
                unset($allRoles[$key]);
            }
        }

        // �������� ���������� �� �����
        if ($this->getRequest()->isPost()) {

            // ���������� ������ �� �������
            $data = $this->params()->fromPost();

            $form->setData($data);

            // ��������� �����
            if($form->isValid()) {

                $data = $form->getData();

                // ����� ����������� �� ���� ������������
                $assignedUser = $this->entityManager->getRepository(User::class)
                    ->findOneById($data['assigned_user_id']);

                // ���������� ������ � ��
                $task = $this->taskManager->addTask($data, $project, $assignedUser);

                $this->flashMessenger()->addMessage('Task has been successfully created', 'success');

                return $this->redirect()->toRoute('tasks',
                    ['action' => 'view', 'task' => $task->getId(), 'project' => $project->getId()]);
            }
        }

        // ������ �������
        return new ViewModel([
            'form' => $form,
            'roles' => $allRoles,
            'projectId' => $project->getId()
        ]);
    }

    /**
     * �������� ������
     *
     * @return void|ViewModel
     */
    public function viewAction()
    {
        $id = (int)$this->params()->fromRoute('task', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // �������� ����� ��� �������������
        $commentForm = new CommentForm('create', $this->entityManager, $id);

        // ����� ����� � �� �� ID
        $task = $this->entityManager->getRepository(Task::class)
            ->find($id);

        $comments = $task->getComments();

        // ����� ���� time logs ��� �����
        $timeLogs = $task->getTimeLogs();
        $spentTime = 0;
        foreach ($timeLogs as $timeLog) {
            $spentTime += $timeLog->getSpentTime();
        }

        if ($task == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // ������ �������
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
     * �������������� ������
     *
     * @return void|\Zend\Http\Response|ViewModel
     */
    public function editAction()
    {
        $id = (int)$this->params()->fromRoute('task', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // ����� ������ �� ID
        $task = $this->entityManager->getRepository(Task::class)
            ->find($id);

        $project = $task->getProject();

        // �������� ����������
        if (!$this->access('projects.manage.all') &&
            !$this->access('projects.manage.own', ['project' => $project])) {
            return $this->redirect()->toRoute('not-authorized');
        }

        if ($task == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // �������� �����
        $form = new TaskForm('update', $this->entityManager, $task, $this->taskManager);

        // ����� ���� �����
        $allRoles = $this->entityManager->getRepository(Role::class)
            ->findBy([], ['name'=>'ASC']);
        $excludedRoles = ['Administrator', 'Guest', 'Project Manager'];
        foreach ($allRoles  as $key => $role) {
            if(in_array($role->getName(), $excludedRoles)){
                unset($allRoles[$key]);
            }
        }

        // �������� ���������� �� �����
        if ($this->getRequest()->isPost()) {

            // ���������� ������ �� �������
            $data = $this->params()->fromPost();

            $form->setData($data);

            // ��������� �����
            if($form->isValid()) {

                $data = $form->getData();

                // ���������� ������ � ��
                $this->taskManager->updateTask($task, $data);

                $project = $task->getProject();

                return $this->redirect()->toRoute('tasks',
                    ['action'=>'view', 'task' => $task->getId(), 'project' => $project->getCode()]);
            }
        } else {
            // ��������� ������ ��� �����
            $form->setData(array(
                'assigned_user_id'=>$task->getAssignedUserId(),
                'estimate'=>$task->getEstimate(),
                'task_title'=>$task->getTaskTitle(),
                'status'=>$task->getStatus(),
                'description'=>$task->getDescription(),
            ));
        }

        // ������ �����
        return new ViewModel(array(
            'task' => $task,
            'form' => $form,
            'roles'=>$allRoles
        ));
    }

    /**
     * �������� ������ �� ��
     */
    public function deleteAction()
    {
        $id = (int)$this->params()->fromRoute('task', -1);
        if ($id < 1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // ����� ������ � ��
        $task = $this->entityManager->getRepository(Task::class)
            ->find($id);

        $project = $task->getProject();

        // �������� ����������
        if (!$this->access('projects.manage.all') &&
            !$this->access('projects.manage.own', ['project' => $project])) {
            return $this->redirect()->toRoute('not-authorized');
        }

        if ($task == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // ������� ������ � ��
        $this->taskManager->deleteTask($task);

        $this->flashMessenger()->addSuccessMessage('Task has been removed.');

        return $this->redirect()->toRoute('tasks', ['action'=>'index']);
    }


    /**
     * �������������� ������������ ��� ������
     *
     * @return \Zend\Http\Response|\Zend\Stdlib\ResponseInterface
     */
    public function reassignAction(){
        $response = $this->getResponse();

        // �������� ���������� �� �����
        if ($this->getRequest()->isPost()) {

            // ���������� ������ �� �������
            $data = $this->params()->fromPost();
            $taskId = $data['task_id'];

            $task = $this->entityManager->getRepository(Task::class)
                ->find($taskId);

            $project = $task->getProject();

            // �������� ����������
            if (!$this->access('projects.manage.all') &&
                !$this->access('projects.manage.own', ['project' => $project])) {
                return $this->redirect()->toRoute('not-authorized');
            }

            try {
                $userId = $data['user_id'];
                $user = $this->entityManager->getRepository(User::class)
                    ->find($userId);

                $taskData = ['assigned_user_id' => $userId];

                // ���������� ������ � ��
                $this->taskManager->updateTask($task, $taskData);

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

            // ������ �������
            $viewModel = new ViewModel(
                ['allUsers' => $allUsers]
            );
            $viewModel->setTemplate('all_users');
            $renderer = $this->rendererInterface;
            $html = $renderer->render($viewModel);

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
     * �������� time log
     *
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function addtimelogAction(){
        $response = $this->getResponse();

        // �������� ���������� �� �����
        if ($this->getRequest()->isPost()) {

            // ���������� ������ �� �������
            $data = $this->params()->fromPost();
            $taskId = $data['task_id'];

            // ����� ������
            $task = $this->entityManager->getRepository(Task::class)
                ->find($taskId);

            // ����� �������� ������������
            $currentUser = $this->entityManager->getRepository(User::class)
                ->findOneByEmail($this->authService->getIdentity());

            try {
                $timeLogData = ['spent_time' => $data['spent_time']];

                $timeLog = $this->timeLogManager->addTimeLog($timeLogData, $task, $currentUser);

                // ������ time log �����
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

    /**
     * �������������� ����������� �������
     *
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function edittimelogAction(){
        $response = $this->getResponse();

        // �������� ���������� �� �����
        if ($this->getRequest()->isPost()) {

            // ���������� ������ �� �������
            $data = $this->params()->fromPost();
            $timeLogId = $data['time_log_id'];

            try {
                // ����� time log entity � ��
                $timeLog = $this->entityManager->getRepository(TimeLog::class)
                    ->find($timeLogId);

                $task = $timeLog->getTask();

                if (!$timeLog->getId()) {
                    $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => 'Time log was not found')));
                    return $response;
                }

                // �������� ����������
                if (!$this->access('comments.manage.own', ['comment' => $timeLog])) {
                    $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => 'You are not allowed to manage this comment')));
                    return $response;
                }

                // ���������� ������ � ��
                $this->timeLogManager->updateTimeLog($timeLog, $data);

                // ������ ������� time log
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


