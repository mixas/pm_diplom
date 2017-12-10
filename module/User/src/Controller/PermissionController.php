<?php
namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use User\Entity\Permission;
use User\Form\PermissionForm;

/**
 * ���������� �������� �� ������������ ������������
 *
 * Class PermissionController
 * @package User\Controller
 */
class PermissionController extends AbstractActionController 
{
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Permission manager.
     * @var User\Service\PermissionManager 
     */
    private $permissionManager;
    
    public function __construct($entityManager, $permissionManager)
    {
        $this->entityManager = $entityManager;
        $this->permissionManager = $permissionManager;
    }

    /**
     * ������ ���� ����������
     *
     * @return ViewModel
     */
    public function indexAction() 
    {
        // ������� ��� ���������� � ��
        $permissions = $this->entityManager->getRepository(Permission::class)
                ->findBy([], ['name'=>'ASC']);

        // ������ �������
        return new ViewModel([
            'permissions' => $permissions
        ]);
    }

    /**
     * �������� ����� ����������
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function addAction()
    {
        // ������� �����
        $form = new PermissionForm('create', $this->entityManager);

        // �������� ���������� �� �����
        if ($this->getRequest()->isPost()) {

            // ���������� ������ �� �������
            $data = $this->params()->fromPost();            
            
            $form->setData($data);

            // ��������� �����
            if($form->isValid()) {
                
                $data = $form->getData();
                
                // �������� ���������� � ��
                $this->permissionManager->addPermission($data);
                
                $this->flashMessenger()->addSuccessMessage('Added new permission.');
                
                return $this->redirect()->toRoute('permissions', ['action'=>'index']);
            }               
        }

        // ������ �������
        return new ViewModel([
                'form' => $form
            ]);
    }

    /**
     * ����������� ���������� ��� ����������
     *
     * @return void|ViewModel
     */
    public function viewAction() 
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // ����� ���������� � �� �� ID
        $permission = $this->entityManager->getRepository(Permission::class)
                ->find($id);
        
        if ($permission == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // ������ �������
        return new ViewModel([
            'permission' => $permission
        ]);
    }

    /**
     * �������������� ����������
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

        // ����� ���������� �� ID
        $permission = $this->entityManager->getRepository(Permission::class)
                ->find($id);
        
        if ($permission == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // ������� �����
        $form = new PermissionForm('update', $this->entityManager, $permission);

        // �������� ���������� �� �����
        if ($this->getRequest()->isPost()) {

            // ���������� ������ �� �������
            $data = $this->params()->fromPost();            
            
            $form->setData($data);

            // ��������� �����
            if($form->isValid()) {
                
                $data = $form->getData();
                
                // �������� ���������� � ��
                $this->permissionManager->updatePermission($permission, $data);
                
                $this->flashMessenger()->addSuccessMessage('Updated the permission.');
                
                return $this->redirect()->toRoute('permissions', ['action'=>'index']);
            }               
        } else {
            $form->setData(array(
                    'name'=>$permission->getName(),
                    'description'=>$permission->getDescription()     
                ));
        }

        // ������ �������
        return new ViewModel([
                'form' => $form,
                'permission' => $permission
            ]);
    }

    /**
     * ������� ����������
     *
     * @return void|\Zend\Http\Response
     */
    public function deleteAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // ����� ���������� � �� �� ID
        $permission = $this->entityManager->getRepository(Permission::class)
                ->find($id);
        
        if ($permission == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // ������� ���������� � ��
        $this->permissionManager->deletePermission($permission);
        
        $this->flashMessenger()->addSuccessMessage('Deleted the permission.');

        return $this->redirect()->toRoute('permissions', ['action'=>'index']);
    }
}






