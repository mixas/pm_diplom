<?php

namespace Project\Service\PriorityProcessor;

use Project\Entity\Task;
use User\Entity\User;
use User\Entity\Role;

class Critical extends PriorityAbstract
{

    protected $type;

    public function __construct($entityManager)
    {
        parent::__construct($entityManager);
        $this->type = Task::PRIORITY_CRITICAL;
    }

    /**
     * Основная логика для автоматического назначения пользователей для задач с приоритетом Critical
     * За основу берется коэффициент эффективности труда
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

        // Коэффициент эффективности труда
        // Будет расчитываться на основе сравнения реально затраченого времени и оценочного времени(estimate)
        $effectivityCoefficientArray = [];

        foreach ($roleUsers as $user) {
            // Выборка всех задач для пользователя для дальнейшего подсчета коэффициента эффективности труда
            $queryBuilder = $this->entityManager->createQueryBuilder();
            $userId = $user->getId();
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

            // Расчет общего коэффициента эффективности труда
            $effectivityCoefficientArray[$userId] = $estimateSum / $spentTimeSum;

        }

        // Формирование данных для ответа
        if(!empty($effectivityCoefficientArray)) {
            $maxCoefficient = max($effectivityCoefficientArray);
            $theMostEffectiveUserId = array_keys($effectivityCoefficientArray, $maxCoefficient);
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

