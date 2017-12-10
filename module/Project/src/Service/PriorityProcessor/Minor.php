<?php

namespace Project\Service\PriorityProcessor;

use Project\Entity\Task;
use User\Entity\User;
use User\Entity\Role;

class Minor extends PriorityAbstract
{

    protected $type;

    public function __construct($entityManager)
    {
        parent::__construct($entityManager);
        $this->type = Task::PRIORITY_MINOR;
    }

    /**
     * Основная логика для автоматического назначения пользователей для задач с приоритетом Minor
     * За основу берется коэффициент экономической эффективности
     *
     * @param $userType
     * @return array
     * @throws \Exception
     */
    public function process($userType){
        // Нахождение роли в БД на основе переданного типа
        $userRole = $this->entityManager->getRepository(Role::class)
            ->findOneById($userType, ['name'=>'ASC']);
        if(!$userRole){
            throw new \Exception("User type (role) is undefined");
        }

        // Нахождение всех пользователей роли
        $roleUsers = $userRole->getUsers();
        $roleUsers->initialize();

        $roleId = $userRole->getId();
        $type = $this->type;

        // Расчет средней з/п для пользователей определенной роли
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

        // Финальный коэффициент экономической эффективности (основной показатель для сравнения)
        // Будет расчитываться на основе коэффициента з/п и коэффициента эффективности труда
        $economicEffectivityCoefficientArray = [];

        foreach ($roleUsers as $user) {
            if(!$user->getSalaryRate()){
                continue;
            }
            $userId = $user->getId();

            // Коэффициент з/п для пользователя
            $salaryCoefficient = $averageSalary / (float)$user->getSalaryRate();

            // Выборка всех задач для пользователя для дальнейшего подсчета коэффициента эффективности труда
            $queryBuilder = $this->entityManager->createQueryBuilder();
            $queryBuilder
                ->select('t.id', 't.assigned_user_id', 'ur.role_id', 't.estimate', 'sum(tl.spent_time) real_spent_time')
                ->from('task', 't')
                ->leftJoin('user_role', 'ur', 'ON', 't.assigned_user_id = ur.user_id')
                ->leftJoin('time_log', 'tl', 'ON', 't.id = tl.task_id')
                ->where("ur.role_id = $roleId")
                ->andWhere("t.priority = $type")
                ->andWhere("t.assigned_user_id = $userId")
                ->groupBy("tl.task_id");
            $query = $queryBuilder->getQuery();
            $stmt = $this->entityManager->getConnection()->prepare($query->getDQL());
            $stmt->execute();
            $allTasksForParticularRole = $stmt->fetchAll();

            //Расчет общего затраченого времени + общего оценочного времени(estimate)
            $estimateSum = 0;
            $spentTimeSum = 0;
            foreach ($allTasksForParticularRole as $task) {
                $estimateSum += $task['estimate'];
                $spentTimeSum += $task['real_spent_time'];
            }

            // Если данных недостаточно - возвращаем ошибку
            if($spentTimeSum == 0 || $estimateSum == 0){
                throw new \Exception("There are few data to define the most effective user");
            }

            // Расчет общего коэффициента экономической эффективности
            $economicEffectivityCoefficientArray[$userId] = $estimateSum / $spentTimeSum * $salaryCoefficient;

        }

        // Формирование данных для ответа
        if(!empty($economicEffectivityCoefficientArray)) {
            $maxCoefficient = max($economicEffectivityCoefficientArray);
            $theMostEffectiveUserId = array_keys($economicEffectivityCoefficientArray, $maxCoefficient);
            $user = $this->entityManager->getRepository(User::class)
                ->findOneById($theMostEffectiveUserId);
            $userData = [
                'id' => $user->getId(),
                'full_name' => $user->getFullName(),
                'coefficient' => $maxCoefficient,
            ];
            return $userData;
        }else{
            throw new \Exception("User was not found");
        }
    }


}

