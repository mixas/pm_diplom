<?php

namespace Project\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Project\Entity\Comment;
use Project\Entity\Task;
use Project\Entity\Project;
use User\Entity\User;
use Project\Service\TaskManager;
use Project\Form\TaskForm;
use Project\Form\CommentForm;
//use User\Form\PasswordChangeForm;
//use User\Form\PasswordResetForm;

/**
 * This controller is responsible for user management (adding, editing,
 * viewing users and changing user's password).
 */
class SystemController extends AbstractActionController
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

    private $serviceManager;

    /**
     * Constructor.
     */
    public function __construct($entityManager, $taskManager, $authService, $solutionProcessor)
    {
        $this->entityManager = $entityManager;
        $this->taskManager = $taskManager;
        $this->authService = $authService;
        $this->solutionProcessor = $solutionProcessor;
    }


    public function chooseUserAutomaticallyAction(){
        $response = $this->getResponse();

        $entityIdentifier = (int)$this->params()->fromRoute('id', -1);
        if ($entityIdentifier < 1) {
            $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => 'Task was not found')));
            return $response;
        }

        if ($this->getRequest()->isPost()) {

            try {

                $data = $this->params()->fromPost();

                if($data['is_new']) {
                    $task = $this->entityManager->getRepository(Task::class)
                        ->find($entityIdentifier);
                    if (!$task->getId()) {
                        $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => 'Task was not found')));
                        return $response;
                    }

                    $project = $task->getProject();
                }else{
                    $project = $this->entityManager->getRepository(Project::class)
                        ->find($entityIdentifier);
                }

                if (!$project->getId()) {
                    $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => 'Project was not found')));
                    return $response;
                }

                if (!$this->access('projects.manage.all') &&
                    !$this->access('projects.manage.own', ['project' => $project])) {
                    $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => 'You are not allowed to assign users')));
                    return $response;

                }

                $result = $this->solutionProcessor->fetchTheBestUserSolution($data);

                $response->setContent(\Zend\Json\Json::encode(
                    array(
                        'response' => true,
                        'result' => $result
                    )
                ));
            }catch (\Exception $e){
                $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => $e->getMessage())));
            }
        } else {
            $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => 'Invalid Request')));
        }

        return $response;
    }

}


