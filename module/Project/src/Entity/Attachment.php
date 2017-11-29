<?php

namespace Project\Entity;

use Project\Service\TaskManager;

use Doctrine\ORM\Mapping as ORM;

/**
 * This class represents a registered task.
 * @ORM\Entity()
 * @ORM\Table(name="attachment")
 */
class Attachment
{

    const TYPE_TASK = 1;
    const TYPE_TECHNICAL_ASSIGNMENT = 2;
    const FILES_LOCATION = "public/files/";

    /**
     * @ORM\ManyToOne(targetEntity="Project\Entity\TechnicalAssignment", inversedBy="attachments")
     * @ORM\JoinColumn(name="assigned_technical_assignment_id", referencedColumnName="id")
     */
    protected $technicalAssignment;

    /**
     * @return \Project\Entity\TechnicalAssignment
     */
    public function getTechnicalAssignment()
    {
        return $this->technicalAssignment;
    }

    /**
     * @param TechnicalAssignment $technicalAssignment
     * @return $this
     */
    public function setTechnicalAssignment(\Project\Entity\TechnicalAssignment $technicalAssignment)
    {
        $this->technicalAssignment = $technicalAssignment;
        return $this;
    }

    /**
     * @ORM\ManyToOne(targetEntity="Project\Entity\Task", inversedBy="attachments")
     * @ORM\JoinColumn(name="assigned_task_id", referencedColumnName="id")
     */
    protected $task;

    /**
     * Returns associated task
     *
     * @return \Project\Entity\Task
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @param Task $task
     * @return $this
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
     * @ORM\Column(name="assigned_task_id")
     * @ORM\GeneratedValue
     */
    protected $assignedTaskId;

    /**
     * @ORM\Column(name="assigned_technical_assignment_id")
     * @ORM\GeneratedValue
     */
    protected $assignedTechnicalAssignmentId;

    /**
     * @ORM\Column(name="attachment_type")
     * @ORM\GeneratedValue
     */
    protected $attachmentType;

    /**
     * @ORM\Column(name="date_created")
     * @ORM\GeneratedValue
     */
    protected $dateCreated;

    /**
     * @ORM\Column(name="file_link")
     * @ORM\GeneratedValue
     */
    protected $fileLink;

    /**
     * @ORM\Column(name="file_name")
     * @ORM\GeneratedValue
     */
    protected $fileName;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getAssignedTaskId()
    {
        return $this->assignedTaskId;
    }

    /**
     * @param mixed $assignedTaskId
     */
    public function setAssignedTaskId($assignedTaskId)
    {
        $this->assignedTaskId = $assignedTaskId;
    }

    /**
     * @return mixed
     */
    public function getAssignedTechnicalAssignmentId()
    {
        return $this->assignedTechnicalAssignmentId;
    }

    /**
     * @param mixed $assignedTechnicalAssignmentId
     */
    public function setAssignedTechnicalAssignmentId($assignedTechnicalAssignmentId)
    {
        $this->assignedTechnicalAssignmentId = $assignedTechnicalAssignmentId;
    }

    /**
     * @return mixed
     */
    public function getAttachmentType()
    {
        return $this->attachmentType;
    }

    /**
     * @param mixed $type
     */
    public function setAttachmentType($type)
    {
        $this->attachmentType = $type;
    }

    /**
     * @return mixed
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param mixed $dateCreated
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
    }

    /**
     * @return mixed
     */
    public function getFileLink()
    {
        return $this->fileLink;
    }

    /**
     * @param mixed $fileLink
     */
    public function setFileLink($fileLink)
    {
        $this->fileLink = $fileLink;
    }

    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param mixed $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }


}



