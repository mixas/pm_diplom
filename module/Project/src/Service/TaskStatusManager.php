<?php

namespace Project\Service;

use Project\Entity\TaskStatus;

/**
 * ����� ��� ���������� �������� ��������� �� ��������� �������� � ��
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
     * ���������� ������� ������ � ��
     *
     * @param $data
     * @return TaskStatus
     */
    public function addTaskStatus($data)
    {
        // �������� ����� ��������
        $taskStatus = new TaskStatus();
        $taskStatus->setLabel($data['label']);

        // �������� �������� � entity manager.
        $this->entityManager->persist($taskStatus);

        // ��������� ��������� � ��
        $this->entityManager->flush();
        
        return $taskStatus;
    }

    /**
     * ���������� ������� �����
     *
     * @param $taskStatus
     * @param $data
     * @return bool
     */
    public function updateTaskStatus($taskStatus, $data)
    {
        $taskStatus->setLabel($data['label']);

        // ��������� ��������� � ��
        $this->entityManager->flush();

        return true;
    }

    /**
     * �������� ������� ����� �� ��
     *
     * @param $taskStatus
     */
    public function deleteTaskStatus($taskStatus)
    {
        $this->entityManager->remove($taskStatus);
        // ��������� ��������� � ��
        $this->entityManager->flush();
    }

}

