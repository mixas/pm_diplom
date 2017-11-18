<?php

namespace Project\Service;

use Zend\Permissions\Rbac\Rbac;
use Project\Entity\TaskStatus;
use Zend\Crypt\Password\Bcrypt;
use Zend\Math\Rand;

/**
 * This service is responsible for adding/editing tasks
 */
class TaskStatusManager
{
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;  
    
    /**
     * Constructs the service.
     */
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
     * This method adds a new user.
     */
    public function addTaskStatus($data)
    {
        // Do not allow several users with the same email address.
//        if($this->checkProjectExists($data['code'])) {
//            throw new \Exception("Another task with the same code " . $data['$code'] . " already exists");
//        }
        
        // Create new User entity.
        $taskStatus = new TaskStatus();
        $taskStatus->setLabel($data['label']);

        // Add the entity to the entity manager.
        $this->entityManager->persist($taskStatus);
        
        // Apply changes to database.
        $this->entityManager->flush();
        
        return $taskStatus;
    }
    
    /**
     * This method updates data of an existing user.
     */
    public function updateTaskStatus($taskStatus, $data)
    {
        // Do not allow to change user email if another user with such email already exits.
//        if($task->getCode()!=$data['code'] && $this->checkProjectExists($data['code'])) {
//            throw new \Exception("Another project with the same code " . $data['email'] . " already exists");
//        }

        $taskStatus->setLabel($data['label']);

        // Apply changes to database.
        $this->entityManager->flush();

        return true;
    }

    /**
     * Deletes the given task status.
     */
    public function deleteTaskStatus($taskStatus)
    {
        $this->entityManager->remove($taskStatus);
        $this->entityManager->flush();
    }

    /**
     * Checks whether an active user with given email address already exists in the database.     
     */
//    public function checkProjectExists($code) {
//
//        $project = $this->entityManager->getRepository(Project::class)
//                ->findOneByCode($code);
//
//        return $project !== null;
//    }

}

