<?php

namespace Project\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Project\Entity\TaskStatus;
use Project\Form\TaskStatusForm;

/**
 * Контроллер для управления статусами проектов
 *
 * Class TaskStatusController
 * @package Project\Controller
 */
class TaskStatusController extends AbstractActionController
{
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Task manager.
     * @var Project\Service\TaskStatusManager
     */
    private $taskStatusManager;


    public function __construct($entityManager, $taskStatusManager)
    {
        $this->entityManager = $entityManager;
        $this->taskStatusManager = $taskStatusManager;
    }

    /**
     * Отображение все статусов
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        // Поиск всех статусов
        $taskStatuses = $this->entityManager->getRepository(TaskStatus::class)
            ->findBy([], ['id'=>'ASC']);

        // рендер шаблона
        return new ViewModel([
            'statuses' => $taskStatuses
        ]);
    }

    /**
     * Добавление нового статуса
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function addAction()
    {
        // Создание формы
        $form = new TaskStatusForm('create', $this->entityManager);

        // Проверка отправлена ли форма
        if ($this->getRequest()->isPost()) {

            // Извлечение данных из запроса
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Валидация формы
            if($form->isValid()) {

                $data = $form->getData();

                // Добавление статуса в БД
                $this->taskStatusManager->addTaskStatus($data);

                return $this->redirect()->toRoute('statuses',
                    ['action'=>'index']);
            }
        }

        // Рендер формы
        return new ViewModel([
            'form' => $form
        ]);
    }

    /**
     * Редактирование статуса
     *
     * @return void|\Zend\Http\Response|ViewModel
     */
    public function editAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Поиск статуса по ID
        $taskStatus = $this->entityManager->getRepository(TaskStatus::class)
            ->find($id);

        if ($taskStatus == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Создание формы
        $form = new TaskStatusForm('update', $this->entityManager, $taskStatus);

        // Проверка отправлена ли форма
        if ($this->getRequest()->isPost()) {

            // Извлечение данных из запроса
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Валидация формы
            if($form->isValid()) {

                $data = $form->getData();

                // Обновление статуса в БД
                $this->taskStatusManager->updateTaskStatus($taskStatus, $data);

                return $this->redirect()->toRoute('statuses',
                    ['action'=>'index']);
            }
        } else {
            $form->setData(array(
                'label'=>$taskStatus->getLabel(),
            ));
        }

        // Рендер формы
        return new ViewModel(array(
            'status' => $taskStatus,
            'form' => $form
        ));
    }


    /**
     * Удаление статуса
     *
     * @return void|\Zend\Http\Response
     */
    public function deleteAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id < 1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Поиск статуса по ID
        $taskStatus = $this->entityManager->getRepository(TaskStatus::class)
            ->find($id);

        if ($taskStatus == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Удаление статуса в БД
        $this->taskStatusManager->deleteTaskStatus($taskStatus);

        $this->flashMessenger()->addSuccessMessage('Status has been removed.');

        return $this->redirect()->toRoute('statuses', ['action'=>'index']);
    }

}


