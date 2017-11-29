<?php

namespace Project\Service;

use Project\Entity\Attachment;
use Project\Entity\TaskStatus;
use Zend\Crypt\Password\Bcrypt;
use Zend\Math\Rand;

use Interop\Container\ContainerInterface;

/**
 * This service is responsible for adding/editing tasks
 */
class AttachmentManager
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
     * This method adds a new comment to DB.
     */
    public function addAttachment($data, $entity = null)
    {
        // Create new Comment entity.
        $attachment = new Attachment();
        $attachment->setFileLink($data['file_link']);
        $attachment->setFileName($data['file_name']);
        if($entity){
            if ($entity instanceof \Project\Entity\TechnicalAssignment) {
                $attachment->setAttachmentType((int)Attachment::TYPE_TECHNICAL_ASSIGNMENT);
                $attachment->setTechnicalAssignment($entity);
            }elseif($entity instanceof \Project\Entity\Task){
                $attachment->setAttachmentType((int)Attachment::TYPE_TASK);
                $attachment->setTask($entity);
            }else{
                throw new \Exception ('Unknown entity type');
            }
            $attachment->setAssignedTaskId((int)$entity->getId());
        }else{
            throw new \Exception ('Attachment entity can\'t be define properly');
        }
        $currentDate = date('Y-m-d H:i:s');
        $attachment->setDateCreated($currentDate);

        // Add the entity to the entity manager.
        $this->entityManager->persist($attachment);
        
        // Apply changes to database.
        $this->entityManager->flush();
        
        return $attachment;
    }

    public function deleteAttachment($attachment){
        $this->entityManager->remove($attachment);
        $this->entityManager->flush();
    }
    
    /**
     * This method updates data of an existing comment.
     */
    public function updateComment($comment, $data)
    {
        $comment->setCommentText($data['comment_text']);
        $currentDate = date('Y-m-d H:i:s');
        $comment->setUpdatedDate($currentDate);

        // Apply changes to database.
        $this->entityManager->flush();

        return true;
    }


}

