<?php

namespace Project\Service;

use Project\Entity\Attachment;

/**
 * ����� ��� ���������� �������� ��������� � �������������� � ��
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
     * �������� ������������ � ��
     *
     * @param $data
     * @param null $entity
     * @return Attachment
     * @throws \Exception
     */
    public function addAttachment($data, $entity = null)
    {
        // �������� ����� ��������
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

        // �������� �������� � entity manager.
        $this->entityManager->persist($attachment);
        
        // ��������� ��������� � ��
        $this->entityManager->flush();
        
        return $attachment;
    }

    /**
     * ������� ������������ �� ��
     *
     * @param $attachment
     */
    public function deleteAttachment($attachment){
        $this->entityManager->remove($attachment);
        $this->entityManager->flush();
    }
    

}

