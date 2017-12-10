<?php

namespace Project\Service;

use Project\Entity\TimeLog;

/**
 * Класс для выполнения операций связанных с логами времени в БД
 *
 * Class TimeLogManager
 * @package Project\Service
 */
class TimeLogManager
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
     * Добавление логов времени в БД
     *
     * @param $data
     * @param null $task
     * @param null $user
     * @return TimeLog
     */
    public function addTimeLog($data, $task = null, $user = null)
    {
        // Создание новой сущности
        $timeLog = new TimeLog();
        $timeLog->setSpentTime($data['spent_time']);
        if($task){
            $timeLog->setTask($task);
        }
        if($user){
            $timeLog->setUser($user);
        }
        $currentDate = date('Y-m-d H:i:s');
        $timeLog->setDateCreated($currentDate);

        // Добавить сущность в entity manager.
        $this->entityManager->persist($timeLog);

        // Применить изменения в БД
        $this->entityManager->flush();
        
        return $timeLog;
    }


    /**
     * Обновление логов времени в БД
     *
     * @param $timeLog
     * @param $data
     * @return bool
     */
    public function updateTimeLog($timeLog, $data)
    {
        if(isset($data['spent_time']))
            $timeLog->setSpentTime($data['spent_time']);

        // Применить изменения в БД
        $this->entityManager->flush();

        return true;
    }

}

