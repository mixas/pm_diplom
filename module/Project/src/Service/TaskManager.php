<?php

namespace Project\Service;

use Project\Entity\Task;
use Zend\Crypt\Password\Bcrypt;
use Zend\Math\Rand;

/**
 * This service is responsible for adding/editing tasks
 */
class TaskManager
{
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;  
    
    /**
     * Constructs the service.
     */
    public function __construct($entityManager) 
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * This method adds a new user.
     */
    public function addTask($data)
    {
        // Do not allow several users with the same email address.
//        if($this->checkProjectExists($data['code'])) {
//            throw new \Exception("Another task with the same code " . $data['$code'] . " already exists");
//        }
        
        // Create new User entity.
        $task = new Task();
        $task->setTaskTitle($data['task_title']);
        $task->setDescription($data['description']);
        $task->setStatus($data['status']);
        $currentDate = date('Y-m-d H:i:s');
        $task->setDateCreated($currentDate);
                
        // Add the entity to the entity manager.
        $this->entityManager->persist($task);
        
        // Apply changes to database.
        $this->entityManager->flush();
        
        return $task;
    }
    
    /**
     * This method updates data of an existing user.
     */
    public function updateTask($task, $data)
    {
        // Do not allow to change user email if another user with such email already exits.
//        if($task->getCode()!=$data['code'] && $this->checkProjectExists($data['code'])) {
//            throw new \Exception("Another project with the same code " . $data['email'] . " already exists");
//        }

        $task->setTaskTitle($data['task_title']);
        $task->setDescription($data['description']);
        $task->setStatus($data['status']);
        $currentDate = date('Y-m-d H:i:s');
        $task->setDateCreated($currentDate);

        // Apply changes to database.
        $this->entityManager->flush();

        return true;
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

