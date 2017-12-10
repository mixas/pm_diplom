<?php

namespace Project\Service;

use Project\Entity\TechnicalAssignment;

/**
 * Класс для выполнения операций связанных с техническим заданием в БД
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
     * Добавление ТЗ в БД
     *
     * @param $data
     * @param null $project
     * @return TechnicalAssignment
     */
    public function addTechnicalAssignment($data, $project = null)
    {
        // Создание новой сущности
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

        // Применить изменения в БД
        $this->entityManager->flush();
        
        return $technicalAssignment;
    }

    /**
     * Обвновление ТЗ в БД
     *
     * @param $technicalAssignment
     * @param $data
     * @return bool
     */
    public function updateTechnicalAssignment($technicalAssignment, $data)
    {
        // Установка новых данных
        $technicalAssignment->setDeadlineDate($data['deadline_date']);
        $technicalAssignment->setDescription($data['description']);
        $currentDate = date('Y-m-d H:i:s');
        $technicalAssignment->getDateUpdated($currentDate);

        // Применить изменения в БД
        $this->entityManager->flush();

        return true;
    }

}

