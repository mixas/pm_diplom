<?php

namespace Project\Entity;

use Project\Service\TaskManager;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\ServiceManager\ServiceManager;

use Project\Entity\Project;

use stdClass;



use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * This class represents a registered task.
 * @ORM\Entity()
 * @ORM\Table(name="technical_assignment")
 */
class TechnicalAssignment
{

    public function __construct()
    {
        $this->attachments = new ArrayCollection();
    }

    /**
     * One Cart has One Customer.
     * @ORM\OneToOne(targetEntity="Project\Entity\Project", inversedBy="technicalAssignment")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     */
    protected $project;

    /**
     * @return \Project\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param \Project\Entity\Project $project
     * @return $this
     */
    public function setProject(\Project\Entity\Project $project)
    {
        $this->project = $project;
        return $this;
    }

    /**
     * @ORM\OneToMany(targetEntity="Attachment", mappedBy="technicalAssignment")
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
     * @ORM\Column(name="project_id")
     * @ORM\GeneratedValue
     */
    protected $projectId;

    /**
     * @return mixed
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * @param $taskId
     * @return $this
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;
        return $this;
    }

    /**
     * @ORM\Column(name="description")
     * @ORM\GeneratedValue
     */
    protected $description;

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


    /**
     * @ORM\Column(name="date_updated")
     * @ORM\GeneratedValue
     */
    protected $dateUpdated;


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

    /**
     * @ORM\Column(name="deadline_date")
     * @ORM\GeneratedValue
     */
    protected $deadlineDate;


    /**
     * @return mixed
     */
    public function getDeadlineDate()
    {
        return $this->deadlineDate;
    }

    /**
     * @param $deadlineDate
     * @return $this
     */
    public function setDeadlineDate($deadlineDate)
    {
        $this->deadlineDate = $deadlineDate;
        return $this;
    }

}



