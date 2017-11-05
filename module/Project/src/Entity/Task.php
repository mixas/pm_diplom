<?php

namespace Project\Entity;

use Project\Service\TaskManager;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

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



    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="task")
     */
    protected $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->timeLogs = new ArrayCollection();
    }
    /**
     * Возвращает таски проекта.
     *
     * @return array
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Adds a new task to this post.
     *
     * @param $comment
     */
    public function addComment($comment)
    {
        $this->comments[] = $comment;
    }


    /**
     * @ORM\OneToMany(targetEntity="TimeLog", mappedBy="task")
     */
    protected $timeLogs;

    /**
     * Возвращает таски проекта.
     *
     * @return array
     */
    public function getTimeLogs()
    {
        return $this->timeLogs;
    }

    /**
     * Adds a new task to this post.
     *
     * @param $timeLog
     */
    public function addTimeLog($timeLog)
    {
        $this->timeLogs[] = $timeLog;
    }


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
     * @ORM\Column(name="priority")
     * @ORM\GeneratedValue
     */
    protected $priority;

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    const PRIORITY_MINOR = 1;
    const PRIORITY_CRITICAL = 2;

    /**
     * Returns possible priorities as array.
     *
     * @return array
     */
    public static function getPriorityList()
    {
        return [
            self::PRIORITY_MINOR => 'Minor',
            self::PRIORITY_CRITICAL => 'Critical'
        ];
    }



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
     * @ORM\OneToOne(targetEntity="\User\Entity\User", mappedBy="task")
     * @ORM\JoinColumn(name="assigned_user_id", referencedColumnName="id")
     */
    protected $assignedUser;

    public function getAssignedUser()
    {
        return $this->assignedUser;
    }

    public function setAssignedUser($assignedUser)
    {
        $this->assignedUser = $assignedUser;
        return $this;
    }

    /**
     * @ORM\Column(name="project_id")
     * @ORM\GeneratedValue
     */
    protected $projectId;

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



