<?php
namespace Project\Service;

use Project\Entity\Project;
use Zend\Crypt\Password\Bcrypt;
use Zend\Math\Rand;

/**
 * This service is responsible for adding/editing projects
 */
class ProjectManager
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
    public function addProject($data)
    {
        // Do not allow several users with the same email address.
        if($this->checkProjectExists($data['code'])) {
            throw new \Exception("Another project with the same code " . $data['$code'] . " already exists");
        }
        
        // Create new User entity.
        $project = new Project();
        $project->setCode($data['code']);
        $project->setName($data['name']);
        $project->setDescription($data['description']);

        $project->setStatus($data['status']);
        
        $currentDate = date('Y-m-d H:i:s');
        $project->setDateCreated($currentDate);
                
        // Add the entity to the entity manager.
        $this->entityManager->persist($project);
        
        // Apply changes to database.
        $this->entityManager->flush();
        
        return $project;
    }
    
    /**
     * This method updates data of an existing user.
     */
    public function updateProject($project, $data)
    {
        // Do not allow to change user email if another user with such email already exits.
        if($project->getCode()!=$data['code'] && $this->checkProjectExists($data['code'])) {
            throw new \Exception("Another project with the same code " . $data['email'] . " already exists");
        }
        
        $project->setCode($data['code']);
        $project->setName($data['name']);
        $project->setStatus($data['status']);
        $project->setDescription($data['description']);

        // Apply changes to database.
        $this->entityManager->flush();

        return true;
    }

    /**
     * Checks whether an active user with given email address already exists in the database.     
     */
    public function checkProjectExists($code) {
        
        $project = $this->entityManager->getRepository(Project::class)
                ->findOneByCode($code);
        
        return $project !== null;
    }

}

