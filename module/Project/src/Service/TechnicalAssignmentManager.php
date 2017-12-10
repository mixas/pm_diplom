<?php

namespace Project\Service;

use Project\Entity\TechnicalAssignment;

/**
 * ����� ��� ���������� �������� ��������� � ����������� �������� � ��
 *
 * Class TechnicalAssignmentManager
 * @package Project\Service
 */
class TechnicalAssignmentManager
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
     * ���������� �� � ��
     *
     * @param $data
     * @param null $project
     * @return TechnicalAssignment
     */
    public function addTechnicalAssignment($data, $project = null)
    {
        // �������� ����� ��������
        $technicalAssignment = new TechnicalAssignment();
        $technicalAssignment->setDeadlineDate($data['deadline_date']);
        $technicalAssignment->setDescription($data['description']);
        if($project){
            $technicalAssignment->setProject($project);
        }
        $currentDate = date('Y-m-d H:i:s');
        $technicalAssignment->setDateCreated($currentDate);
                
        // Add the entity to the entity manager.
        $this->entityManager->persist($technicalAssignment);

        // ��������� ��������� � ��
        $this->entityManager->flush();
        
        return $technicalAssignment;
    }

    /**
     * ����������� �� � ��
     *
     * @param $technicalAssignment
     * @param $data
     * @return bool
     */
    public function updateTechnicalAssignment($technicalAssignment, $data)
    {
        // ��������� ����� ������
        $technicalAssignment->setDeadlineDate($data['deadline_date']);
        $technicalAssignment->setDescription($data['description']);
        $currentDate = date('Y-m-d H:i:s');
        $technicalAssignment->getDateUpdated($currentDate);

        // ��������� ��������� � ��
        $this->entityManager->flush();

        return true;
    }

}

