<?php

namespace Project\Entity;

use Project\Service\TaskManager;

use Doctrine\ORM\Mapping as ORM;

use Project\Entity\Project;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\ServiceManager\ServiceManager;

use stdClass;

/**
 * This class represents a registered task.
 * @ORM\Entity()
 * @ORM\Table(name="task")
 */
class Task
{

    /**
     * @ORM\ManyToOne(targetEntity="Project\Entity\Project", inversedBy="tasks")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     */
    protected $project;

    /**
     * Returns associated project
     *
     * @return \Project\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Sets associated project.
     *
     * @param Project $project
     */
    public function setProject(\Project\Entity\Project $project)
    {
        $this->project = $project;
//        $project->addTask($this);
    }

//    /**
//     * @OneToOne(targetEntity="TaskStatus", inversedBy="task")
//     * @JoinColumn(name="status_id", referencedColumnName="id")
//     */
//    protected $statusEntity;

    /**
     * Returns associated status
     *
     * @return \Project\Entity\TaskStatus
     */
//    public function getStatusEntity()
//    {
//        return $this->statusEntity;
//    }

    /**
     * Sets status entity
     *
     * @param $statusEntity \Project\Entity\TaskStatus
     * @return $this
     */
//    public function setStatusEntity($statusEntity)
//    {
//        $this->statusEntity = $statusEntity;
//        return $this;
//    }


    // User status constants.
//    const STATUS_TO_DO       = 1; // To do.
//    const STATUS_IN_PROCESS      = 2; // In process.
//    const STATUS_TO_BE_CHECKED      = 3; // To be checked.
//    const STATUS_IN_QA      = 4; // In QA.
//    const STATUS_CHECKED      = 5; // Checked by QA.
//    const STATUS_DONE      = 6; // Done.

    /**
     * @ORM\Id
     * @ORM\Column(name="id")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(name="status")
     * @ORM\GeneratedValue
     */
    protected $status;

    /**
     * @ORM\Column(name="task_title")
     * @ORM\GeneratedValue
     */
    protected $taskTitle;

    /**
     * @ORM\Column(name="description")
     * @ORM\GeneratedValue
     */
    protected $description;

    /**
     * @ORM\Column(name="date_created")
     * @ORM\GeneratedValue
     */
    protected $dateCreated;

    /**
     * @ORM\Column(name="assigned_user_id")
     * @ORM\GeneratedValue
     */
    protected $assignedUserId;

    /**
     * @ORM\Column(name="project_id")
     * @ORM\GeneratedValue
     */
    protected $projectId;

    /**
     * @return mixed
     */
    public function getAssignedUserId()
    {
        return $this->assignedUserId;
    }

    /**
     * @param $assignedUserId
     * @return $this
     */
    public function setAssignedUserId($assignedUserId)
    {
        $this->assignedUserId = $assignedUserId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * @param $projectId
     * @return $this
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEstimate()
    {
        return $this->estimate;
    }

    /**
     * @param mixed $estimate
     */
    public function setEstimate($estimate)
    {
        $this->estimate = $estimate;
        return $this;
    }

    /**
     * @ORM\Column(name="estimate")
     * @ORM\GeneratedValue
     */
    protected $estimate;

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTaskTitle()
    {
        return $this->taskTitle;
    }

    /**
     * @param mixed $taskTitle
     */
    public function setTaskTitle($taskTitle)
    {
        $this->taskTitle = $taskTitle;
        return $this;
    }

    /**
     * Returns user ID.
     * @return integer
     */
    public function getId() 
    {
        return $this->id;
    }

    /**
     * Sets user ID. 
     * @param int $id    
     */
    public function setId($id) 
    {
        $this->id = $id;
        return $this;
    }


    /**
     * Retrieve task status
     *
     * @return mixed
     */
    public function getStatus() 
    {
        return $this->status;
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Returns possible statuses as array.
     * @return array
     */
//    public function getStatusList()
//    {
//        $taskStatuses = $this->getStatusEntity();
//        return [
//            self::STATUS_TO_DO => 'To do',
//            self::STATUS_IN_PROCESS => 'In process',
//            self::STATUS_TO_BE_CHECKED => 'To be checked',
//            self::STATUS_IN_QA => 'In QA',
//            self::STATUS_CHECKED => 'Checked',
//            self::STATUS_DONE => 'Done'
//        ];
//    }

    /**
     * Returns the date of user creation.
     * @return string     
     */
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }
    
    /**
     * Sets the date when this user was created.
     * @param string $dateCreated     
     */
    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
        return $this;
    }

}



