<?php

namespace Project\Service\PriorityProcessor;

use Project\Entity\Task;
use Project\Entity\TaskStatus;
use User\Entity\User;
use User\Entity\Role;
use Zend\Crypt\Password\Bcrypt;
use Zend\Math\Rand;

use Interop\Container\ContainerInterface;

class Critical extends PriorityAbstract
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
     * The main auto assign logic for Critical tasks
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

//        $queryBuilder
//            ->select('u.id', 'u.full_name', 'ur.role_id')
//            ->from('user', 'u')
//            ->innerJoin('user_role', 'ur', 'ON', 'u.id = ur.user_id')
//            ->where("role_id = $roleId");

        $type = $this->type;

        $effectivityCoefficientArray = [];

        foreach ($roleUsers as $user) {

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


            $estimateSum = 0;
            $spentTimeSum = 0;
            foreach ($allTasksForParticularRole as $task) {
                $estimateSum += $task['estimate'];
                $spentTimeSum += $task['real_spent_time'];
            }

            if($spentTimeSum == 0 || $estimateSum == 0){
                throw new \Exception("There are few data to define the most effective user");
            }

            $effectivityCoefficientArray[$userId] = $estimateSum / $spentTimeSum;

        }

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

