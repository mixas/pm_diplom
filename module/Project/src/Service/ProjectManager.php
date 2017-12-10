<?php
namespace Project\Service;

use Project\Entity\Project;
use User\Entity\User;

/**
 * Класс для выполнения операций связанных с проектами в БД
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
     * Добавление проекта в БД
     *
     * @param $data
     * @return Project
     * @throws \Exception
     */
    public function addProject($data)
    {
        // Не позволять создавать несколько проектов с одинаковыми кодами
        if($this->checkProjectExists($data['code'])) {
            throw new \Exception("Another project with the same code " . $data['$code'] . " already exists");
        }

        // Создание новой сущности
        $project = new Project();
        $project->setCode($data['code']);
        $project->setName($data['name']);
        $project->setDescription($data['description']);

        $project->setStatus($data['status']);
        
        $currentDate = date('Y-m-d H:i:s');
        $project->setDateCreated($currentDate);

        // Добавить сущность в entity manager.
        $this->entityManager->persist($project);
        
        // Apply changes to database.
        $this->entityManager->flush();
        
        return $project;
    }

    /**
     * Обновление проекта в БД
     *
     * @param $project
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function updateProject($project, $data)
    {
        // Не позволять создавать несколько проектов с одинаковыми кодами
        if($project->getCode()!=$data['code'] && $this->checkProjectExists($data['code'])) {
            throw new \Exception("Another project with the same code " . $data['email'] . " already exists");
        }
        
        $project->setCode($data['code']);
        $project->setName($data['name']);
        $project->setStatus($data['status']);
        $project->setDescription($data['description']);

        // Применить изменения в БД
        $this->entityManager->flush();

        return true;
    }

    /**
     * Удаление проекта из БД
     *
     * @param $project
     */
    public function deleteProject($project)
    {
        // Удалние проекта, счязанных с ним тасков и комментариев
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
        // Применить изменения в БД
        $this->entityManager->flush();
    }

    /**
     * Проверяет существует ли проект с таким кодом
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
     * Обновляет проект в БД
     *
     * @param $project
     * @param $data
     * @throws \Exception
     */
    public function updateProjectUsers($project, $data)
    {
        // Удаление старых назначеных пользователей из проекта
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

        // Применить изменения в БД
        $this->entityManager->flush();
    }

}

