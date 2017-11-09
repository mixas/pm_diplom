<?php

namespace Project\Service;

use Project\Entity\Task;
use Project\Entity\TaskStatus;
use User\Entity\User;
use Zend\Crypt\Password\Bcrypt;
use Zend\Math\Rand;

use Interop\Container\ContainerInterface;

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
     * This method adds a new task to DB.
     */
    public function addTask($data, $project = null, $user = null)
    {
        // Create new Task entity.
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

        // Apply changes to database.
        $this->entityManager->flush();

        return true;
    }

    /**
     * Returns possible statuses from DB as array.
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
     * Returns possible statuses from DB as array.
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

