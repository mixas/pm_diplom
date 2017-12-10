<?php

namespace Project\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Project\Entity\TaskStatus;
use Project\Form\TaskStatusForm;

/**
 * ���������� ��� ���������� ��������� ��������
 *
 * Class TaskStatusController
 * @package Project\Controller
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


    public function __construct($entityManager, $taskStatusManager)
    {
        $this->entityManager = $entityManager;
        $this->taskStatusManager = $taskStatusManager;
    }

    /**
     * ����������� ��� ��������
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        // ����� ���� ��������
        $taskStatuses = $this->entityManager->getRepository(TaskStatus::class)
            ->findBy([], ['id'=>'ASC']);

        // ������ �������
        return new ViewModel([
            'statuses' => $taskStatuses
        ]);
    }

    /**
     * ���������� ������ �������
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function addAction()
    {
        // �������� �����
        $form = new TaskStatusForm('create', $this->entityManager);

        // �������� ���������� �� �����
        if ($this->getRequest()->isPost()) {

            // ���������� ������ �� �������
            $data = $this->params()->fromPost();

            $form->setData($data);

            // ��������� �����
            if($form->isValid()) {

                $data = $form->getData();

                // ���������� ������� � ��
                $this->taskStatusManager->addTaskStatus($data);

                return $this->redirect()->toRoute('statuses',
                    ['action'=>'index']);
            }
        }

        // ������ �����
        return new ViewModel([
            'form' => $form
        ]);
    }

    /**
     * �������������� �������
     *
     * @return void|\Zend\Http\Response|ViewModel
     */
    public function editAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // ����� ������� �� ID
        $taskStatus = $this->entityManager->getRepository(TaskStatus::class)
            ->find($id);

        if ($taskStatus == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // �������� �����
        $form = new TaskStatusForm('update', $this->entityManager, $taskStatus);

        // �������� ���������� �� �����
        if ($this->getRequest()->isPost()) {

            // ���������� ������ �� �������
            $data = $this->params()->fromPost();

            $form->setData($data);

            // ��������� �����
            if($form->isValid()) {

                $data = $form->getData();

                // ���������� ������� � ��
                $this->taskStatusManager->updateTaskStatus($taskStatus, $data);

                return $this->redirect()->toRoute('statuses',
                    ['action'=>'index']);
            }
        } else {
            $form->setData(array(
                'label'=>$taskStatus->getLabel(),
            ));
        }

        // ������ �����
        return new ViewModel(array(
            'status' => $taskStatus,
            'form' => $form
        ));
    }


    /**
     * �������� �������
     *
     * @return void|\Zend\Http\Response
     */
    public function deleteAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id < 1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // ����� ������� �� ID
        $taskStatus = $this->entityManager->getRepository(TaskStatus::class)
            ->find($id);

        if ($taskStatus == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // �������� ������� � ��
        $this->taskStatusManager->deleteTaskStatus($taskStatus);

        $this->flashMessenger()->addSuccessMessage('Status has been removed.');

        return $this->redirect()->toRoute('statuses', ['action'=>'index']);
    }

}


