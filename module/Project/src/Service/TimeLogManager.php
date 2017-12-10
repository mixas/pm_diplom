<?php

namespace Project\Service;

use Project\Entity\TimeLog;

/**
 * ����� ��� ���������� �������� ��������� � ������ ������� � ��
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
     * ���������� ����� ������� � ��
     *
     * @param $data
     * @param null $task
     * @param null $user
     * @return TimeLog
     */
    public function addTimeLog($data, $task = null, $user = null)
    {
        // �������� ����� ��������
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

        // �������� �������� � entity manager.
        $this->entityManager->persist($timeLog);

        // ��������� ��������� � ��
        $this->entityManager->flush();
        
        return $timeLog;
    }


    /**
     * ���������� ����� ������� � ��
     *
     * @param $timeLog
     * @param $data
     * @return bool
     */
    public function updateTimeLog($timeLog, $data)
    {
        if(isset($data['spent_time']))
            $timeLog->setSpentTime($data['spent_time']);

        // ��������� ��������� � ��
        $this->entityManager->flush();

        return true;
    }

}

