<?php

namespace Project\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * —ущность представл€юща€ проекты
 *
 * @ORM\Entity()
 * @ORM\Table(name="project")
 */
class Project
{

    /**
     * @ORM\OneToMany(targetEntity="Task", mappedBy="project")
     */
    protected $tasks;

    /**
     * @ORM\ManyToMany(targetEntity="User\Entity\User")
     * @ORM\JoinTable(name="user_project",
     *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     *      )
     */
    private $users;


    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @param $task
     */
    public function addTask($task)
    {
        $this->tasks[] = $task;
    }


    // User status constants.
    const STATUS_ACTIVE       = 1; // Active user.
    const STATUS_RETIRED      = 2; // Retired user.
    
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
     * @ORM\Column(name="code")
     * @ORM\GeneratedValue
     */
    protected $code;

    /**
     * @ORM\Column(name="name")
     * @ORM\GeneratedValue
     */
    protected $name;

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
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

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
     * Returns status.
     * @return int     
     */
    public function getStatus() 
    {
        return $this->status;
    }

    /**
     * Returns possible statuses as array.
     * @return array
     */
    public function getStatusList()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_RETIRED => 'Retired'
        ];
    }

    /**
     * @return string
     */
    public function getStatusAsString()
    {
        $list = $this->getStatusList();
        if (isset($list[$this->status]))
            return $list[$this->status];
        
        return 'Unknown';
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
     * @return ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * One Customer has One Cart.
     * @ORM\OneToOne(targetEntity="Project\Entity\TechnicalAssignment", mappedBy="project")
     */
    protected $technicalAssignment;

    /**
     * @return mixed
     */
    public function getTechnicalAssignment()
    {
        return $this->technicalAssignment;
    }

    /**
     * @param $technicalAssignment
     * @return $this
     */
    public function setTechnicalAssignment($technicalAssignment)
    {
        $this->technicalAssignment = $technicalAssignment;
        return $this;
    }


}



