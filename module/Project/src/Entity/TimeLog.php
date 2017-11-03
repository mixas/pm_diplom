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
 * @ORM\Table(name="time_log")
 */
class TimeLog
{

    /**
     * @ORM\ManyToOne(targetEntity="Project\Entity\Task", inversedBy="timeLogs")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id")
     */
    protected $task;

    /**
     * @return \Project\Entity\Task
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @param Task $task
     */
    public function setTask(\Project\Entity\Task $task)
    {
        $this->task = $task;
        return $this;
    }


    /**
     * @ORM\Id
     * @ORM\Column(name="id")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @return mixed
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
     * @ORM\Column(name="task_id")
     * @ORM\GeneratedValue
     */
    protected $taskId;

    /**
     * @return mixed
     */
    public function getTaskId()
    {
        return $this->taskId;
    }

    /**
     * @param $taskId
     * @return $this
     */
    public function setTaskId($taskId)
    {
        $this->taskId = $taskId;
        return $this;
    }


    /**
     * @ORM\Column(name="user_id")
     * @ORM\GeneratedValue
     */
    protected $userId;

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }


    /**
     * @ORM\Column(name="spent_time")
     * @ORM\GeneratedValue
     */
    protected $spentTime;

    /**
     * @return mixed
     */
    public function getSpentTime()
    {
        return $this->spentTime;
    }

    /**
     * @param $spentTime
     * @return $this
     */
    public function setSpentTime($spentTime)
    {
        $this->spentTime = $spentTime;
        return $this;
    }


    /**
     * @ORM\Column(name="date_created")
     * @ORM\GeneratedValue
     */
    protected $dateCreated;


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

}



