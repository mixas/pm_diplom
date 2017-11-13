<?php

namespace Project\Service;

use Project\Entity\Task;
use Project\Entity\TimeLog;
use Project\Entity\TaskStatus;
use User\Entity\User;
use Zend\Crypt\Password\Bcrypt;
use Zend\Math\Rand;

use Interop\Container\ContainerInterface;

/**
 * This service is responsible for adding/editing tasks
 */
class TimeLogManager
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
    public function addTimeLog($data, $task = null, $user = null)
    {
        // Create new Task entity.
        $timeLog = new TimeLog();
        $timeLog->setSpentTime($data['spent_time']);
        if($task){
            $timeLog->setTask($task);
        }
        if($user){
            $timeLog->setUser($user);
        }
        $currentDate = date('Y-m-d H:i:s');
        $timeLog->setDateCreated($currentDate);
                
        // Add the entity to the entity manager.
        $this->entityManager->persist($timeLog);
        
        // Apply changes to database.
        $this->entityManager->flush();
        
        return $timeLog;
    }
    
    /**
     * This method updates data of an existing user.
     */
    public function updateTimeLog($timeLog, $data)
    {
        if(isset($data['spent_time']))
            $timeLog->setSpentTime($data['spent_time']);

        // Apply changes to database.
        $this->entityManager->flush();

        return true;
    }

}

