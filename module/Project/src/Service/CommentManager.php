<?php

namespace Project\Service;

use Project\Entity\Comment;

/**
 * ����� ��� ���������� �������� ��������� � ������������� � ��
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
     * ���������� ����������� � ��
     *
     * @param $data
     * @param null $task
     * @param null $user
     * @return Comment
     */
    public function addComment($data, $task = null, $user = null)
    {
        // �������� ����� ��������
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

        // �������� �������� � entity manager.
        $this->entityManager->persist($comment);

        // ��������� ��������� � ��
        $this->entityManager->flush();
        
        return $comment;
    }

    /**
     * ���������� ����������� � ��
     *
     * @param $comment
     * @param $data
     * @return bool
     */
    public function updateComment($comment, $data)
    {
        // ��������� ������ � �����������
        $comment->setCommentText($data['comment_text']);
        $currentDate = date('Y-m-d H:i:s');
        $comment->setUpdatedDate($currentDate);

        // ��������� ��������� � ��
        $this->entityManager->flush();

        return true;
    }


}

