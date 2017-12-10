<?php

namespace Project\Service;

use Project\Entity\Attachment;

/**
 * Класс для выполнения операций связанных с прикреплениями в БД
 *
 * Class AttachmentManager
 * @package Project\Service
 */
class AttachmentManager
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
     * Добавить прикрепление в БД
     *
     * @param $data
     * @param null $entity
     * @return Attachment
     * @throws \Exception
     */
    public function addAttachment($data, $entity = null)
    {
        // Создание новой сущности
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

        // Добавить сущность в entity manager.
        $this->entityManager->persist($attachment);
        
        // Применить изменения в БД
        $this->entityManager->flush();
        
        return $attachment;
    }

    /**
     * Удавить прикрепление из БД
     *
     * @param $attachment
     */
    public function deleteAttachment($attachment){
        $this->entityManager->remove($attachment);
        $this->entityManager->flush();
    }
    

}

