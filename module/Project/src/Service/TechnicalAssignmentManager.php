<?php

namespace Project\Service;

use Project\Entity\Task;
use Project\Entity\TechnicalAssignment;
use Project\Entity\TaskStatus;
use User\Entity\User;
use Zend\Crypt\Password\Bcrypt;
use Zend\Math\Rand;

use Interop\Container\ContainerInterface;

/**
 * This service is responsible for adding/editing tasks
 */
class TechnicalAssignmentManager
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
    public function addTechnicalAssignment($data, $project = null)
    {
        // Create new Task entity.
        $technicalAssignment = new TechnicalAssignment();
        $technicalAssignment->setDeadlineDate($data['deadline_date']);
        $technicalAssignment->setDescription($data['description']);
        if($project){
            $technicalAssignment->setProject($project);
        }
        $currentDate = date('Y-m-d H:i:s');
        $technicalAssignment->setDateCreated($currentDate);
                
        // Add the entity to the entity manager.
        $this->entityManager->persist($technicalAssignment);
        
        // Apply changes to database.
        $this->entityManager->flush();
        
        return $technicalAssignment;
    }
    
    /**
     * This method updates data of an existing user.
     */
    public function updateTechnicalAssignment($technicalAssignment, $data)
    {
        $technicalAssignment->setDeadlineDate($data['deadline_date']);
        $technicalAssignment->setDescription($data['description']);
        $currentDate = date('Y-m-d H:i:s');
        $technicalAssignment->getDateUpdated($currentDate);

        // Apply changes to database.
        $this->entityManager->flush();

        return true;
    }

}

