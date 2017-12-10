<?php

namespace Project\Service;

use Project\Entity\Task;
use User\Entity\Role;
use Project\Entity\Project;

/**
 * ����� ��� ������� ���������� �������� � �������������
 *
 * Class StatisticManager
 * @package Project\Service
 */
class StatisticManager
{

    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * ������� ���������� �������������
     *
     * @param $excludedRoles
     * @return array
     */
    public function getUsersStats($excludedRoles){
        // ������� ���� �����
        $roles = $this->entityManager->getRepository(Role::class)
            ->findBy([], ['id'=>'ASC']);

        $criticalPriority = Task::PRIORITY_CRITICAL;
        $minorPriority = Task::PRIORITY_MINOR;
        $priorities = [$criticalPriority, $minorPriority];

        // ������������ ������������� �������������
        $effectivityCoefficientArray = [];

        foreach ($roles as $userRole) {
            if(in_array($userRole->getName(), $excludedRoles)){
                continue;
            }

            $roleUsers = $userRole->getUsers();
            $roleUsers->initialize();

            $roleId = $userRole->getId();

            //������� ������� �/� ��� ������ ����
            $queryBuilder = $this->entityManager->createQueryBuilder();
            $queryBuilder
                ->select('avg(u.salary_rate) salary_rate')
                ->from('user', 'u')
                ->leftJoin('user_role', 'ur', 'ON', 'u.id = ur.user_id')
                ->where("ur.role_id = $roleId");
            $query = $queryBuilder->getQuery();
            $stmt = $this->entityManager->getConnection()->prepare($query->getDQL());
            $stmt->execute();
            $averageSalaryResult = $stmt->fetch();
            $averageSalary = $averageSalaryResult['salary_rate'];


            foreach ($roleUsers as $user) {

                foreach ($priorities as $priority) {

                    if($priority == $minorPriority) {
                        $salaryCoefficient = $averageSalary / (float)$user->getSalaryRate();
                    }

                    // ������� ���� ����� ������������� ���������� ��� ������������ ����
                    $queryBuilder = $this->entityManager->createQueryBuilder();
                    $userId = $user->getId();
                    $queryBuilder
                        ->select('t.id', 't.assigned_user_id', 'ur.role_id', 't.estimate', 'sum(tl.spent_time) real_spent_time')
                        ->from('task', 't')
                        ->leftJoin('user_role', 'ur', 'ON', 't.assigned_user_id = ur.user_id')
                        ->leftJoin('time_log', 'tl', 'ON', 't.id = tl.task_id')
                        ->where("ur.role_id = $roleId")
                        ->andWhere("t.priority = $priority")
                        ->andWhere("t.assigned_user_id = $userId")
                        ->groupBy("tl.task_id");
                    $query = $queryBuilder->getQuery();
                    $stmt = $this->entityManager->getConnection()->prepare($query->getDQL());
                    $stmt->execute();
                    $allTasksForParticularRole = $stmt->fetchAll();

                    // ������������ ������ ����������� � ���������� ������� ��� ������� ������������
                    $estimateSum = 0;
                    $spentTimeSum = 0;
                    foreach ($allTasksForParticularRole as $task) {
                        $estimateSum += $task['estimate'];
                        $spentTimeSum += $task['real_spent_time'];
                    }

                    // ������ ������������� ������������� � ���������� ����������
                    if($priority == $minorPriority) {
                        $estimatedExpenses = $estimateSum * $salaryCoefficient;
                        $realExpenses = $spentTimeSum * $salaryCoefficient;
                        if ($estimatedExpenses == 0 || $realExpenses == 0) {

                        } else {
                            $effectivityCoefficientArray[$userRole->getId()][$priority][$userId] = [$user->getFullName(), $estimatedExpenses, $realExpenses];
                        }
                    }else{
                        if ($spentTimeSum == 0 || $estimateSum == 0) {
                        } else {
                            $effectivityCoefficientArray[$userRole->getId()][$priority][$userId] = [$user->getFullName(), $estimateSum, $spentTimeSum];
                        }
                    }

                }
            }

        }

        return $effectivityCoefficientArray;

    }

    /**
     * ������� ���������� ��������
     *
     * @return array
     */
    public function getProjectsStats(){
        // ������� ���� ��������
        $projects = $this->entityManager->getRepository(Project::class)
            ->findBy([], ['code'=>'ASC']);

        // �������� ����������
        $projectsStats = [];

        foreach ($projects as $project) {
            // ������� ���������� �������, ����������� �������, ���������� ������� �� �������
            $projectTasks = $project->getTasks();
            $projectTasks->initialize();
            $projectTime = 0;
            $projectAmount = 0;
            $projectEstimateTime = 0;
            foreach ($projectTasks as $projectTask) {
                $taskTimeLogs = $projectTask->getTimeLogs();
                foreach ($taskTimeLogs as $taskTimeLog) {
                    $spentTime = $taskTimeLog->getSpentTime();
                    $projectTime += $spentTime;
                    $timeLogUser = $taskTimeLog->getUser();
                    $rate = $timeLogUser->getSalaryRate();
                    $amount = $rate * $spentTime / 60;//division by 60 minutes
                    $projectAmount += $amount;
                }
                $taskEstimate = $projectTask->getEstimate();
                $projectEstimateTime += $taskEstimate;
            }
            $projectsStats[$project->getId()]['time'] = round($projectTime);
            $projectsStats[$project->getId()]['salary'] = round($projectAmount);
            $projectsStats[$project->getId()]['estimate_time'] = round($projectEstimateTime);
        }

        return $projectsStats;
    }

}

