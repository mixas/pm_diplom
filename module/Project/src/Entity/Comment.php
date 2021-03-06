<?php

namespace Project\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * �������� �������������� �����������
 *
 * @ORM\Entity()
 * @ORM\Table(name="comment")
 */
class Comment
{

    /**
     * @ORM\ManyToOne(targetEntity="Project\Entity\Task", inversedBy="comments")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id")
     */
    protected $task;

    /**
     * @return mixed
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
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="comments")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \User\Entity\User $user
     * @return $this
     */
    public function setUser(\User\Entity\User $user)
    {
        $this->user = $user;
        return $this;
    }


    /**
     * @ORM\Id
     * @ORM\Column(name="id")
     * @ORM\GeneratedValue
     */
    protected $id;

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
     * @param $commentText
     * @return $this
     */
    public function setCommentText($commentText)
    {
        $this->commentText = $commentText;
        return $this;
    }

    /**
     * @param $createdDate
     * @return $this
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;
        return $this;
    }

    /**
     * @param $updatedDate
     * @return $this
     */
    public function setUpdatedDate($updatedDate)
    {
        $this->updatedDate = $updatedDate;
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
     * @return mixed
     */
    public function getCommentText()
    {
        return $this->commentText;
    }

    /**
     * @return mixed
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
    }

    /**
     * @return mixed
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * @ORM\Column(name="comment_text")
     * @ORM\GeneratedValue
     */
    protected $commentText;

    /**
     * @ORM\Column(name="created_date")
     * @ORM\GeneratedValue
     */
    protected $createdDate;

    /**
     * @ORM\Column(name="updated_date")
     * @ORM\GeneratedValue
     */
    protected $updatedDate;

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

}



