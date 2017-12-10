<?php

namespace Project\Service;

use Project\Entity\TaskStatus;

/**
 * Класс для выполнения операций связанных со статусами проектов в БД
 *
 * Class TaskStatusManager
 * @package Project\Service
 */
class TaskStatusManager
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
     * Добавление статуса задачи в БД
     *
     * @param $data
     * @return TaskStatus
     */
    public function addTaskStatus($data)
    {
        // Создание новой сущности
        $taskStatus = new TaskStatus();
        $taskStatus->setLabel($data['label']);

        // Добавить сущность в entity manager.
        $this->entityManager->persist($taskStatus);

        // Применить изменения в БД
        $this->entityManager->flush();
        
        return $taskStatus;
    }

    /**
     * Обновление статуса задач
     *
     * @param $taskStatus
     * @param $data
     * @return bool
     */
    public function updateTaskStatus($taskStatus, $data)
    {
        $taskStatus->setLabel($data['label']);

        // Применить изменения в БД
        $this->entityManager->flush();

        return true;
    }

    /**
     * Удаление статуса задач из БД
     *
     * @param $taskStatus
     */
    public function deleteTaskStatus($taskStatus)
    {
        $this->entityManager->remove($taskStatus);
        // Применить изменения в БД
        $this->entityManager->flush();
    }

}

