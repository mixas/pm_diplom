<?php

namespace Project\Service;

use Project\Entity\Comment;

/**
 * Класс для выполнения операций связанных с комментариями в БД
 *
 * Class CommentManager
 * @package Project\Service
 */
class CommentManager
{
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Добавление комментария в БД
     *
     * @param $data
     * @param null $task
     * @param null $user
     * @return Comment
     */
    public function addComment($data, $task = null, $user = null)
    {
        // Создание новой сущности
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

        // Добавить сущность в entity manager.
        $this->entityManager->persist($comment);

        // Применить изменения в БД
        $this->entityManager->flush();
        
        return $comment;
    }

    /**
     * Обновление комментария в БД
     *
     * @param $comment
     * @param $data
     * @return bool
     */
    public function updateComment($comment, $data)
    {
        // Установка данных в комментарии
        $comment->setCommentText($data['comment_text']);
        $currentDate = date('Y-m-d H:i:s');
        $comment->setUpdatedDate($currentDate);

        // Применить изменения в БД
        $this->entityManager->flush();

        return true;
    }


}

