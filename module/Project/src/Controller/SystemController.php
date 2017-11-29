<?php

namespace Project\Controller;

use DoctrineORMModule\Proxy\__CG__\Project\Entity\TechnicalAssignment;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Project\Entity\Comment;
use Project\Entity\Task;
use Project\Entity\Project;
use Project\Entity\Attachment;
use Project\Form\AttachmentForm;
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
     * Attachment manager.
     * @var Project\Service\AttachmentManager
     */
    private $attachmentManager;

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
    public function __construct($entityManager, $taskManager, $attachmentManager, $authService, $solutionProcessor, $rendererInterface)
    {
        $this->entityManager = $entityManager;
        $this->taskManager = $taskManager;
        $this->attachmentManager = $attachmentManager;
        $this->authService = $authService;
        $this->solutionProcessor = $solutionProcessor;
        $this->rendererInterface = $rendererInterface;
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

                if(isset($data['is_new']) && $data['is_new']) {
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

    public function uploadFileAction(){
        $response = $this->getResponse();

        $data = $this->params()->fromPost();

        $project = $this->entityManager->getRepository(Project::class)
            ->findOneById($data['project_id']);

        if(!$project->getId()){
            $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => 'Project was not found')));
            return $response;
        }

        $form = new AttachmentForm('attachment');

        if (!$this->access('projects.manage.all') &&
            !$this->access('projects.manage.own', ['project' => $project])) {
            return $this->redirect()->toRoute('not-authorized');
        }

        if ($this->getRequest()->isPost()) {

            // Merge data thus
            $data = array_merge_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );

            $form->setData($data);

            if ($form->isValid()) {

                $data = $form->getData();

                // Upload path
                $location = Attachment::FILES_LOCATION;

                // A bit validation of uploaded file
                $allowedExtension = array('jpg', 'jpeg', 'png', 'txt', 'doc', 'pdf');

                $extension = explode('.', $data['attachment']['name']);
                $extension = end($extension);
                $fileName = $data['attachment']['name'];
                $uniqueName = uniqid();

                // Check if everything is OK!
                if (0 === $data['attachment']['error'] && in_array($extension, $allowedExtension)) {
                    move_uploaded_file($data['attachment']['tmp_name'], $location . $uniqueName . '.' .$extension);
                } else {
                    $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => 'Error during file upload. Please try again')));
                }

                $entity = $project->getTechnicalAssignment();
                if(!$entity->getId()){
                    $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => 'Technical assignment was not found')));
                    return;
                }

                $technicalAssignmentData = [
                    'file_name' => $fileName,
                    'file_link' => $location . $uniqueName . '.' . $extension,
                ];

                try {
                    $attachment = $this->attachmentManager->addAttachment($technicalAssignmentData, $entity);
                }catch (\Exception $e){
                    $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => $e->getMessage())));
                    return;
                }

                $viewModel = new ViewModel(
                    ['attachment' => $attachment]
                );
                $viewModel->setTemplate('technical_assignment_attachments');
                $renderer = $this->rendererInterface;
                $html = $renderer->render($viewModel);

                $response->setContent(\Zend\Json\Json::encode(array('response' => true, 'html' => $html, 'message' => 'File has been successfully uploaded')));
            }else{
                $response->setContent(\Zend\Json\Json::encode(array('response' => false, 'message' => 'File is invalid')));
            }
        }
        return $response;
    }

    public function downloadFileAction(){
        $entityIdentifier = (int)$this->params()->fromRoute('id', -1);
        if ($entityIdentifier < 1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        $attachment = $this->entityManager->getRepository(Attachment::class)
            ->find($entityIdentifier);

        if(!$attachment->getId()){
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $file = $attachment->getFileLink();
        $response = new \Zend\Http\Response\Stream();
        $response->setStream(fopen($file, 'r'));
        $response->setStatusCode(200);
        $response->setStreamName(basename($file));
        $headers = new \Zend\Http\Headers();
        $headers->addHeaders(array(
            'Content-Disposition' => 'attachment; filename="' . basename($file) .'"',
            'Content-Type' => 'application/octet-stream',
            'Content-Length' => filesize($file),
            'Expires' => '@0', // @0, because zf2 parses date as string to \DateTime() object
            'Cache-Control' => 'must-revalidate',
            'Pragma' => 'public'
        ));
        $response->setHeaders($headers);
        return $response;
    }

    public function removeFileAction(){
        $entityIdentifier = (int)$this->params()->fromRoute('id', -1);
        if ($entityIdentifier < 1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $attachment = $this->entityManager->getRepository(Attachment::class)
            ->find($entityIdentifier);

        if ($attachment == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Delete role.
        $this->attachmentManager->deleteAttachment($attachment);

        // Add a flash message.
        $this->flashMessenger()->addSuccessMessage('Attachment has been removed.');

        if($attachment->getAttachmentType() == Attachment::TYPE_TECHNICAL_ASSIGNMENT){
            $technicalAssignment = $attachment->getTechnicalAssignment();
            $project = $technicalAssignment->getProject();
            return $this->redirect()->toRoute('projects', ['action'=>'viewTechnicalAssignment', 'code' => $project->getCode()]);
        }elseif($attachment->getAttachmentType() == Attachment::TYPE_TASK){
            $task = $attachment->getTask();
            $project = $task->getProject();
            return $this->redirect()->toRoute('projects', ['action'=>'viewTechnicalAssignment', 'project' => $project->getCode(), 'task' => $task->getId()]);
        }





    }

}


