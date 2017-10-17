<?php

namespace Project\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Project\Entity\TaskStatus;
use Project\Form\TaskStatusForm;
//use User\Form\PasswordChangeForm;
//use User\Form\PasswordResetForm;

/**
 * This controller is responsible for user management (adding, editing,
 * viewing users and changing user's password).
 */
class TaskStatusController extends AbstractActionController
{
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Task manager.
     * @var Project\Service\TaskStatusManager
     */
    private $taskStatusManager;

    /**
     * Constructor.
     */
    public function __construct($entityManager, $taskStatusManager)
    {
        $this->entityManager = $entityManager;
        $this->taskStatusManager = $taskStatusManager;
    }

    /**
     * This is the default "index" action of the controller. It displays the
     * list of users.
     */
    public function indexAction()
    {
        $taskStatuses = $this->entityManager->getRepository(TaskStatus::class)
            ->findBy([], ['id'=>'ASC']);

        return new ViewModel([
            'statuses' => $taskStatuses
        ]);
    }

    /**
     * This action displays a page allowing to add a new user.
     */
    public function addAction()
    {
        // Create user form
        $form = new TaskStatusForm('create', $this->entityManager);

        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {

            // Fill in the form with POST data
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Validate form
            if($form->isValid()) {

                // Get filtered and validated data
                $data = $form->getData();

                // Add status.
                $taskStatus = $this->taskStatusManager->addTaskStatus($data);

                // Redirect to "view" page
                return $this->redirect()->toRoute('statuses',
                    ['action'=>'view', 'id'=>$taskStatus->getId()]);
            }
        }

        return new ViewModel([
            'form' => $form
        ]);
    }

    /**
     * The "view" action displays a page allowing to view user's details.
     */
    public function viewAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Find a user with such ID.
        $taskStatus = $this->entityManager->getRepository(TaskStatus::class)
            ->find($id);

        if ($taskStatus == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        return new ViewModel([
            'status' => $taskStatus
        ]);
    }

    /**
     * The "edit" action displays a page allowing to edit user.
     */
    public function editAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $taskStatus = $this->entityManager->getRepository(TaskStatus::class)
            ->find($id);

        if ($taskStatus == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Create task form
        $form = new TaskStatusForm('update', $this->entityManager, $taskStatus);

        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {

            // Fill in the form with POST data
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Validate form
            if($form->isValid()) {

                // Get filtered and validated data
                $data = $form->getData();

                // Update the user.
                $this->taskStatusManager->updateTaskStatus($taskStatus, $data);

                // Redirect to "view" page
                return $this->redirect()->toRoute('statuses',
                    ['action'=>'view', 'id'=>$taskStatus->getId()]);
            }
        } else {
            $form->setData(array(
                'label'=>$taskStatus->getLabel(),
            ));
        }

        return new ViewModel(array(
            'status' => $taskStatus,
            'form' => $form
        ));
    }

}


