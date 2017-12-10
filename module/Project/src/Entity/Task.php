<?php

namespace Project\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Сущность представляющая задачи
 *
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
     * @return mixed
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param Project $project
     */
    public function setProject(\Project\Entity\Project $project)
    {
        $this->project = $project;
    }



    /**
     * @ORM\OneToMany(targetEntity="Attachment", mappedBy="task")
     */
    protected $attachments;

    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param $attachment
     */
    public function addAttachment($attachment)
    {
        $this->attachments[] = $attachment;
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
     * Returns priorities as array.
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
     * @return array
     */
    public static function getPriorityClassesConformity()
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
     * @ORM\Column(name="date_updated")
     * @ORM\GeneratedValue
     */
    protected $dateUpdated;

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
     * @param $estimate
     * @return $this
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
     * @param $description
     * @return $this
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
     * @param $taskTitle
     * @return $this
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
     * @param $id
     * @return $this
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
     * @return mixed
     */
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }

    /**
     * @param $dateCreated
     * @return $this
     */
    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateUpdated()
    {
        return $this->dateUpdated;
    }

    /**
     * @param $dateUpdated
     * @return $this
     */
    public function setDateUpdated($dateUpdated)
    {
        $this->dateUpdated = $dateUpdated;
        return $this;
    }

}



