<?php

namespace Project\Controller;

use Project\Form\AttachmentForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Project\Entity\Project;
use User\Entity\User;
use Project\Entity\Task;
use Project\Form\ProjectForm;
use Project\Form\ProjectUsersAssignmentForm;
use Project\Form\TechnicalAssignmentForm;

use Project\Entity\TaskStatus;

/**
 * Контроллер для обработки действий связанных за проект
 *
 * Class ProjectController
 * @package Project\Controller
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
     * Auth service.
     * @var Zend\Authentication\AuthenticationService
     */
    private $authService;

    private $rbacManager;

    public function __construct($entityManager, $projectManager, $technicalAssignmentManager, $authService, $rbacManager)
    {
        $this->entityManager = $entityManager;
        $this->projectManager = $projectManager;
        $this->technicalAssignmentManager = $technicalAssignmentManager;
        $this->authService = $authService;
        $this->rbacManager = $rbacManager;
    }

    /**
     * Отображает список всех проектов
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $projects = $this->entityManager->getRepository(Project::class)
            ->findBy([], ['code'=>'ASC']);

        $currentUser = $this->entityManager->getRepository(User::class)
            ->findOneByEmail($this->authService->getIdentity());

        return new ViewModel([
            'projects' => $projects,
            'currentUser' => $currentUser,
            'rbacManager' => $this->rbacManager,
        ]);
    }

    /**
     * Добавление проекта в БД
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function addAction()
    {
        if (!$this->access('projects.manage.all')) {
            return $this->redirect()->toRoute('not-authorized');
        }

        // Создание формы
        $form = new ProjectForm('create', $this->entityManager);

        // Проверка отправлена ли форма
        if ($this->getRequest()->isPost()) {

            // Извлечение данных из запроса
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Валидация формы
            if($form->isValid()) {

                $data = $form->getData();

                // Добавление проекта в БД
                $project = $this->projectManager->addProject($data);

                return $this->redirect()->toRoute('projects',
                    ['action'=>'view', 'code' => $project->getCode()]);
            }
        }

        // рендер шаблона
        return new ViewModel([
            'form' => $form
        ]);
    }


    /**
     * Просмотр проекта
     *
     * @return void|ViewModel
     */
    public function viewAction()
    {
        $code = $this->params()->fromRoute('code', -1);
        if ($code == -1 || $code == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Поиск проекта по ID
        $project = $this->entityManager->getRepository(Project::class)
            ->findOneByCode($code);

        // Фильтрование тасков проекта по статусу
        $currentUser = $this->entityManager->getRepository(User::class)
            ->findOneByEmail($this->authService->getIdentity());
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
                ->findBy(['projectId' => $project->getId(), 'status' => $defaultRoleFilterStatus], ['id' => 'ASC']);
            $currentFilterStatus = $defaultRoleFilterStatus;
        }else{
            $tasks = $this->entityManager->getRepository(Task::class)
                ->findBy(['projectId' => $project->getId()], ['id' => 'ASC']);
        }

        if ($project == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $participants = $project->getUsers();
        $technicalAssignment = $project->getTechnicalAssignment();

        $allTaskStatuses = $this->entityManager->getRepository(TaskStatus::class)
            ->findBy([], ['id'=>'ASC']);

        return new ViewModel([
            'project' => $project,
            'tasks' => $tasks,
            'participants' => $participants,
            'technicalAssignment' => $technicalAssignment,
            'allStatuses' => $allTaskStatuses,
            'currentFilterStatus' => $currentFilterStatus,
        ]);
    }

    /**
     * Редактирование проекта
     *
     * @return void|\Zend\Http\Response|ViewModel
     */
    public function editAction()
    {
        $code = $this->params()->fromRoute('code', -1);
        if ($code == -1 || $code == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Поиск проекта по ID
        $project = $this->entityManager->getRepository(Project::class)
            ->findOneByCode($code);

        if ($project == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Проверка полномочий
        if (!$this->access('projects.manage.all') &&
            !$this->access('projects.manage.own', ['project' => $project])) {
            return $this->redirect()->toRoute('not-authorized');
        }

        // Создание формы
        $form = new ProjectForm('update', $this->entityManager, $project);

        // Проверка отправлена ли форма
        if ($this->getRequest()->isPost()) {

            // Извлечение данных из запроса
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Валидация формы
            if($form->isValid()) {

                $data = $form->getData();

                // Обновление проекта в БД
                $this->projectManager->updateProject($project, $data);

                return $this->redirect()->toRoute('projects',
                    ['action'=>'view', 'code'=>$project->getCode()]);
            }
        } else {
            // Установка данных для формы редактирования
            $form->setData(array(
                'name'=>$project->getName(),
                'code'=>$project->getCode(),
                'status'=>$project->getStatus(),
                'description'=>$project->getDescription(),
            ));
        }

        // рендер шаблона
        return new ViewModel(array(
            'project' => $project,
            'form' => $form
        ));
    }

    /**
     * Удаление проекта
     *
     * @return void|\Zend\Http\Response
     */
    public function deleteAction()
    {
        $code = $this->params()->fromRoute('code', -1);
        if ($code == -1 || $code == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Поиск проекта
        $project = $this->entityManager->getRepository(Project::class)
            ->findOneByCode($code);

        // Проверка полномочий
        if (!$this->access('projects.manage.all') &&
            !$this->access('projects.manage.own', ['project' => $project])) {
            return $this->redirect()->toRoute('not-authorized');
        }

        if ($project == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Удаление проекта из БД
        $this->projectManager->deleteProject($project);

        $this->flashMessenger()->addSuccessMessage('Project has been removed.');

        return $this->redirect()->toRoute('projects', ['action'=>'index']);
    }


    /**
     * Назначение пользователей на проект
     *
     * @return void|\Zend\Http\Response|ViewModel
     */
    function assignUsersAction(){
        $code = $this->params()->fromRoute('code', -1);
        if ($code == -1 || $code == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Поиск проекта в БД
        $project = $this->entityManager->getRepository(Project::class)
            ->findOneByCode($code);

        if ($project == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Проверка полномочий
        if (!$this->access('projects.assign.users.all') &&
            !$this->access('projects.assign.users.own', ['project' => $project])) {
            return $this->redirect()->toRoute('not-authorized');
        }

        // Извлечение списка пользователей
        $allUsers = $this->entityManager->getRepository(User::class)
            ->findBy([], ['email'=>'ASC']);

        $assignedUsers = $project->getUsers();
        $assignedUsers->initialize();

        // Создание формы
        $form = new ProjectUsersAssignmentForm($this->entityManager);
        foreach ($allUsers as $user) {
            $label = $user->getFullName();
            $checked = false;
            foreach ($assignedUsers as $assignedUser) {
                if($assignedUser->getId() == $user->getId()){
                    $label .= ' (assigned)';
                    $checked = 'checked';
                    break;
                }
            }
            $form->addUsersField($user->getId(), $label, $checked);
        }

        // Проверка отправлена ли форма
        if ($this->getRequest()->isPost()) {

            // Извлечение данных из запроса
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Валидация формы
            if($form->isValid()) {

                $data = $form->getData();

                // Назначение пользователей на проект в БД
                $this->projectManager->updateProjectUsers($project, $data);

                $this->flashMessenger()->addSuccessMessage('Users were successfully assignment.');

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

        // Рендер шаблона
        return new ViewModel([
            'form' => $form,
            'project' => $project,
            'allUsers' => $allUsers,
            'assignedUsers' => $assignedUsers
        ]);
    }


    /**
     * Просмотр ТЗ
     *
     * @return void|ViewModel
     */
    public function viewTechnicalAssignmentAction()
    {
        $code = $this->params()->fromRoute('code', -1);
        if ($code == -1 || $code == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Поиск проекта по ID
        $project = $this->entityManager->getRepository(Project::class)
            ->findOneByCode($code);

        // Выбор ТЗ у проекта
        $technicalAssignment = $project->getTechnicalAssignment();

        if(!$technicalAssignment){
            $this->getResponse()->setStatusCode(404);
            return;
        }

        if ($project == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Выбор прикрепленных файлов у ТЗ
        $attachments = $technicalAssignment->getAttachments();
        $attachments->initialize();

        $form = new AttachmentForm('attachment');

        // Рендер шаблона
        return new ViewModel([
            'technicalAssignment' => $technicalAssignment,
            'attachments' => $attachments,
            'attachmentForm' => $form,
            'project' => $project,
        ]);
    }

    /**
     * Редактирование ТЗ
     *
     * @return void|\Zend\Http\Response|ViewModel
     */
    public function editTechnicalAssignmentAction()
    {
        $code = $this->params()->fromRoute('code', -1);
        if ($code == -1 || $code == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Поиск проекта по ID
        $project = $this->entityManager->getRepository(Project::class)
            ->findOneByCode($code);

        if ($project == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Выбор ТЗ из проекта
        $technicalAssignment = $project->getTechnicalAssignment();

        if ($technicalAssignment == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Проверка полномочий
        if (!$this->access('projects.manage.all') &&
            !$this->access('projects.manage.own', ['project' => $project])) {
            return $this->redirect()->toRoute('not-authorized');
        }

        // Создание формы
        $form = new TechnicalAssignmentForm('update', $this->entityManager, $project);

        // Проверка отправлена ли форма
        if ($this->getRequest()->isPost()) {

            // Извлечение данных из запроса
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Валидация формы
            if($form->isValid()) {

                $data = $form->getData();

                // Обновление данных ТЗ в БД
                $this->technicalAssignmentManager->updateTechnicalAssignment($technicalAssignment, $data);

                return $this->redirect()->toRoute('projects',
                    ['action' => 'viewTechnicalAssignment', 'code' => $project->getCode()]);
            }
        }else{
            $form->setData(array(
                'deadline_date' => $technicalAssignment->getDeadlineDate(),
                'description' => $technicalAssignment->getDescription(),
            ));
        }

        // Рендер шаблона
        return new ViewModel(array(
            'project' => $project,
            'technicalAssignment' => $technicalAssignment,
            'form' => $form
        ));
    }

    /**
     * Создание ТЗ
     *
     * @return void|\Zend\Http\Response|ViewModel
     */
    public function createTechnicalAssignmentAction()
    {
        $code = $this->params()->fromRoute('code', -1);
        if ($code == -1 || $code == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Поиск проекта в БД по ID
        $project = $this->entityManager->getRepository(Project::class)
            ->findOneByCode($code);

        // Проверка полномочий
        if (!$this->access('projects.manage.all') &&
            !$this->access('projects.manage.own', ['project' => $project])) {
            return $this->redirect()->toRoute('not-authorized');
        }

        // Создание формы
        $form = new TechnicalAssignmentForm('create', $this->entityManager);

        // Проверка отправлена ли форма
        if ($this->getRequest()->isPost()) {

            // Извлечение данных из запроса
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Валидация формы
            if($form->isValid()) {

                $data = $form->getData();

                // Создание ТЗ в БД
                $this->technicalAssignmentManager->addTechnicalAssignment($data, $project);

                return $this->redirect()->toRoute('projects',
                    ['action' => 'view', 'code' => $project->getCode()]);
            }
        }

        // Рендер шаблона
        return new ViewModel([
            'form' => $form,
            'project' => $project
        ]);

    }

}


