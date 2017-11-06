<?php

namespace Project\Service\PriorityProcessor;

use Project\Entity\Task;
use Project\Entity\TaskStatus;
use User\Entity\User;
use User\Entity\Role;
use Zend\Crypt\Password\Bcrypt;
use Zend\Math\Rand;


use Interop\Container\ContainerInterface;

class Minor extends PriorityAbstract
{

    protected $type;

    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * Constructs the service.
     */
    public function __construct($entityManager)
    {
        parent::__construct($entityManager);
        $this->type = Task::PRIORITY_CRITICAL;
    }

    /**
     * The main auto assign logic for Minor tasks
     *
     * @param $userType
     * @return array
     * @throws \Exception
     */
    public function process($userType){
        $userRole = $this->entityManager->getRepository(Role::class)
            ->findOneById($userType, ['name'=>'ASC']);
        if(!$userRole){
            throw new \Exception("User type (role) is undefined");
        }

        $roleUsers = $userRole->getUsers();
        $roleUsers->initialize();

        $roleId = $userRole->getId();

        $type = $this->type;

        $economicEffectivityCoefficientArray = [];

        foreach ($roleUsers as $user) {
            if(!$user->getSalaryRate()){
                continue;
            }
            $userId = $user->getId();


            //calculate average salary
            $queryBuilder = $this->entityManager->createQueryBuilder();
            $queryBuilder
                ->select('avg(u.salary_rate) salary_rate')
                ->from('user', 'u');

            $query = $queryBuilder->getQuery();
            $stmt = $this->entityManager->getConnection()->prepare($query->getDQL());
            $stmt->execute();
            $averageSalaryResult = $stmt->fetch();
            $averageSalary = $averageSalaryResult['salary_rate'];

            $salaryCoefficient = $averageSalary / (float)$user->getSalaryRate();

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

            $estimateSum = 0;
            $spentTimeSum = 0;
            foreach ($allTasksForParticularRole as $task) {
                $estimateSum += $task['estimate'];
                $spentTimeSum += $task['real_spent_time'];
            }

            if($spentTimeSum == 0 || $estimateSum == 0){
                throw new \Exception("There are few data to define the most effective user");
            }

            $economicEffectivityCoefficientArray[$userId] = $estimateSum / $spentTimeSum * $salaryCoefficient;

        }

        if(!empty($economicEffectivityCoefficientArray)) {
            $theMostEffectiveUserId = array_keys($economicEffectivityCoefficientArray, max($economicEffectivityCoefficientArray));
            $user = $this->entityManager->getRepository(User::class)
                ->findOneById($theMostEffectiveUserId);
            $userData = [
                'id' => $user->getId(),
                'full_name' => $user->getFullName(),
            ];
            return $userData;
        }else{
            throw new \Exception("User was not found");
        }
    }


}

