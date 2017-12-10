<?php

namespace Project\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Project\Entity\Project;
use User\Entity\User;
use User\Entity\Role;

/**
 * Контроллер, обрабатывающий запросы статистики
 *
 * Class StatisticController
 * @package Project\Controller
 */
class StatisticController extends AbstractActionController
{
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Task manager.
     * @var Project\Service\TaskManager
     */
    private $taskManager;

    /**
     * Auth service.
     * @var Zend\Authentication\AuthenticationService
     */
    private $authService;

    /**
     * @var Project\Service\SolutionProcessor
     */
    private $solutionProcessor;

    private $statisticManager;

    public function __construct($entityManager, $taskManager, $authService, $solutionProcessor, $statisticManager)
    {
        $this->entityManager = $entityManager;
        $this->taskManager = $taskManager;
        $this->authService = $authService;
        $this->solutionProcessor = $solutionProcessor;
        $this->statisticManager = $statisticManager;
    }


    /**
     * Статистика пользователей
     *
     * @return ViewModel
     */
    public function usersAction(){
        // Не принимать во внимание следующие роли
        $excludedRoles = ['Administrator', 'Guest', 'Project Manager'];

        $roles = $this->entityManager->getRepository(Role::class)
            ->findBy([], ['id'=>'ASC']);

        $users = $this->entityManager->getRepository(User::class)
            ->findBy([], ['id'=>'ASC']);

        // Расчет статистики
        $statistic = $this->statisticManager->getUsersStats($excludedRoles);

        // Рендер шаблона
        return new ViewModel([
            'users' => $users,
            'roles' => $roles,
            'statistic' => $statistic,
            'excludedRoles' => $excludedRoles
        ]);
    }

    /**
     * Статистика проектов
     *
     * @return ViewModel
     */
    public function projectsAction(){

        $projects = $this->entityManager->getRepository(Project::class)
            ->findBy([], ['code'=>'ASC']);

        // Извлечение статистики проектов из модели
        $projectsStats = $this->statisticManager->getProjectsStats();

        // Рендер шаблона
        return new ViewModel([
            'projects' => $projects,
            'projectsStats' => $projectsStats,
        ]);
    }

}


