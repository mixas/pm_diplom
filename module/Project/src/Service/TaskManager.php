<?php

namespace Project\Service;

use Project\Entity\Task;
use Project\Entity\TaskStatus;
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
    public function addTask($data, $project = null)
    {
        // Create new Task entity.
        $task = new Task();
        $task->setTaskTitle($data['task_title']);
        $task->setDescription($data['description']);
        $task->setStatus($data['status']);
        if($project){
            $task->setProject($project);
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

