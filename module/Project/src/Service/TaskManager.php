<?php

namespace Project\Service;

use Project\Entity\Task;
use Project\Entity\TaskStatus;
use User\Entity\User;

/**
 * Класс для выполнения операций связанных с задачами в БД
 *
 * Class TaskManager
 * @package Project\Service
 */
class TaskManager
{
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    public function __construct($entityManager, $rbacManager)
    {
        $this->entityManager = $entityManager;
        $this->rbacManager = $rbacManager;
    }

    /**
     * RBAC manager.
     * @var User\Service\RbacManager
     */
    private $rbacManager;

    /**
     * Добавление задач в БД
     *
     * @param $data
     * @param null $project
     * @param null $user
     * @return Task
     */
    public function addTask($data, $project = null, $user = null)
    {
        // Создание новой сущности
        $task = new Task();
        $task->setTaskTitle($data['task_title']);
        $task->setEstimate($data['estimate']);
        $task->setPriority($data['priority']);
        $task->setDescription($data['description']);
        $task->setStatus($data['status']);
        if($project){
            $task->setProject($project);
        }
        if($user){
            $task->setAssignedUser($user);
        }
        $currentDate = date('Y-m-d H:i:s');
        $task->setDateCreated($currentDate);

        // Добавить сущность в entity manager.
        $this->entityManager->persist($task);

        // Применить изменения в БД
        $this->entityManager->flush();
        
        return $task;
    }

    /**
     * Обновление задач в БД
     *
     * @param $task
     * @param $data
     * @return bool
     */
    public function updateTask($task, $data)
    {
        // Установка новых данных
        if(isset($data['task_title']))
            $task->setTaskTitle($data['task_title']);
        if(isset($data['description']))
            $task->setDescription($data['description']);
        if(isset($data['estimate']))
            $task->setEstimate($data['estimate']);
        if(isset($data['priority']))
            $task->setPriority($data['priority']);
        if(isset($data['assigned_user_id']))
            $task->setAssignedUserId($data['assigned_user_id']);
        if(isset($data['status']))
            $task->setStatus($data['status']);

        $currentDate = date('Y-m-d H:i:s');
        $task->setDateUpdated($currentDate);

        // Применить изменения в БД
        $this->entityManager->flush();

        return true;
    }

    /**
     * Удаление задачи из БД
     *
     * @param $task
     */
    public function deleteTask($task)
    {
        // Удаление задачи и связанных комментариев из БД
        $this->entityManager->remove($task);
        $comments = $task->getComments();
        $comments->initialize();
        foreach ($comments as $comment) {
            $this->entityManager->remove($comment);
        }
        $this->entityManager->remove($task);

        // Применить изменения в БД
        $this->entityManager->flush();
    }

    /**
     * Выборка всех возможных статусов для задач из БД
     *
     * @return array
     */
    public function getStatusList()
    {
        $taskStatuses = array();
        $statuses = $this->entityManager->getRepository(TaskStatus::class)->findAll();
        foreach($statuses as $status){
            $taskStatuses[$status->getId()] = $status->getLabel();
        }
        return $taskStatuses;
    }

    /**
     * Выборка всех пользователей из БД (необходимо для вывода списка пользователей при переназначении пользователя)
     *
     * @return array
     */
    public function getAllUsersList()
    {
        $allUsers = array();
        $users = $this->entityManager->getRepository(User::class)->findAll();
        foreach($users as $user){
            $allUsers[$user->getId()] = $user->getFullName();
        }
        return $allUsers;
    }

    /**
     * Вывод читаемого статуса
     *
     * @param $status
     * @return string
     */
    public function getStatusAsString($status)
    {
        $list = $this->getStatusList();
        if (isset($list[$status]))
            return $list[$status];

        return 'Undefined';
    }

}

