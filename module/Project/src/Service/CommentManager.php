<?php

namespace Project\Service;

use Project\Entity\Comment;
use Project\Entity\TaskStatus;
use Zend\Crypt\Password\Bcrypt;
use Zend\Math\Rand;

use Interop\Container\ContainerInterface;

/**
 * This service is responsible for adding/editing tasks
 */
class CommentManager
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
    public function addComment($data, $task = null, $user = null)
    {
        // Create new Comment entity.
        $comment = new Comment();
        $comment->setCommentText($data['comment_text']);
        if($task){
            $comment->setTask($task);
        }
        if($user){
            $comment->setUser($user);
        }
        $currentDate = date('Y-m-d H:i:s');
        $comment->setCreatedDate($currentDate);
                
        // Add the entity to the entity manager.
        $this->entityManager->persist($comment);
        
        // Apply changes to database.
        $this->entityManager->flush();
        
        return $comment;
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

