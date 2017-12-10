<?php
namespace Project\Service;

use Project\Entity\Project;
use User\Entity\User;

/**
 * ����� ��� ���������� �������� ��������� � ��������� � ��
 *
 * Class ProjectManager
 * @package Project\Service
 */
class ProjectManager
{
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * RBAC manager.
     * @var User\Service\RbacManager
     */
    private $rbacManager;

    public function __construct($entityManager, $rbacManager)
    {
        $this->entityManager = $entityManager;
        $this->rbacManager = $rbacManager;
    }

    /**
     * ���������� ������� � ��
     *
     * @param $data
     * @return Project
     * @throws \Exception
     */
    public function addProject($data)
    {
        // �� ��������� ��������� ��������� �������� � ����������� ������
        if($this->checkProjectExists($data['code'])) {
            throw new \Exception("Another project with the same code " . $data['$code'] . " already exists");
        }

        // �������� ����� ��������
        $project = new Project();
        $project->setCode($data['code']);
        $project->setName($data['name']);
        $project->setDescription($data['description']);

        $project->setStatus($data['status']);
        
        $currentDate = date('Y-m-d H:i:s');
        $project->setDateCreated($currentDate);

        // �������� �������� � entity manager.
        $this->entityManager->persist($project);
        
        // Apply changes to database.
        $this->entityManager->flush();
        
        return $project;
    }

    /**
     * ���������� ������� � ��
     *
     * @param $project
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function updateProject($project, $data)
    {
        // �� ��������� ��������� ��������� �������� � ����������� ������
        if($project->getCode()!=$data['code'] && $this->checkProjectExists($data['code'])) {
            throw new \Exception("Another project with the same code " . $data['email'] . " already exists");
        }
        
        $project->setCode($data['code']);
        $project->setName($data['name']);
        $project->setStatus($data['status']);
        $project->setDescription($data['description']);

        // ��������� ��������� � ��
        $this->entityManager->flush();

        return true;
    }

    /**
     * �������� ������� �� ��
     *
     * @param $project
     */
    public function deleteProject($project)
    {
        // ������� �������, ��������� � ��� ������ � ������������
        $this->entityManager->remove($project);
        $tasks = $project->getTasks();
        $tasks->initialize();
        foreach ($tasks as $task) {
            $comments = $task->getComments();
            $comments->initialize();
            foreach ($comments as $comment) {
                $this->entityManager->remove($comment);
            }
            $this->entityManager->remove($task);
        }
        // ��������� ��������� � ��
        $this->entityManager->flush();
    }

    /**
     * ��������� ���������� �� ������ � ����� �����
     *
     * @param $code
     * @return bool
     */
    public function checkProjectExists($code) {
        
        $project = $this->entityManager->getRepository(Project::class)
                ->findOneByCode($code);
        
        return $project !== null;
    }

    /**
     * ��������� ������ � ��
     *
     * @param $project
     * @param $data
     * @throws \Exception
     */
    public function updateProjectUsers($project, $data)
    {
        // �������� ������ ���������� ������������� �� �������
        $project->getUsers()->clear();

        // Assign new permissions to role
        foreach ($data['users'] as $userId => $isChecked) {
            if (!$isChecked)
                continue;

            $user = $this->entityManager->getRepository(User::class)->find($userId);
            if ($user == null) {
                throw new \Exception('User with such id doesn\'t exist');
            }

            $project->getUsers()->add($user);
        }

        // ��������� ��������� � ��
        $this->entityManager->flush();
    }

}

