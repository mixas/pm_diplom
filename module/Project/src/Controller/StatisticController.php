<?php

namespace Project\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Project\Entity\Project;
use User\Entity\User;
use User\Entity\Role;

/**
 * ����������, �������������� ������� ����������
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
     * ���������� �������������
     *
     * @return ViewModel
     */
    public function usersAction(){
        // �� ��������� �� �������� ��������� ����
        $excludedRoles = ['Administrator', 'Guest', 'Project Manager'];

        $roles = $this->entityManager->getRepository(Role::class)
            ->findBy([], ['id'=>'ASC']);

        $users = $this->entityManager->getRepository(User::class)
            ->findBy([], ['id'=>'ASC']);

        // ������ ����������
        $statistic = $this->statisticManager->getUsersStats($excludedRoles);

        // ������ �������
        return new ViewModel([
            'users' => $users,
            'roles' => $roles,
            'statistic' => $statistic,
            'excludedRoles' => $excludedRoles
        ]);
    }

    /**
     * ���������� ��������
     *
     * @return ViewModel
     */
    public function projectsAction(){

        $projects = $this->entityManager->getRepository(Project::class)
            ->findBy([], ['code'=>'ASC']);

        // ���������� ���������� �������� �� ������
        $projectsStats = $this->statisticManager->getProjectsStats();

        // ������ �������
        return new ViewModel([
            'projects' => $projects,
            'projectsStats' => $projectsStats,
        ]);
    }

}


