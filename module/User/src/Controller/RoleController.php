<?php
namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use User\Entity\Role;
use User\Entity\Permission;
use User\Form\RoleForm;
use User\Form\RolePermissionsForm;

/**
 * ���������� ������
 *
 * Class RoleController
 * @package User\Controller
 */
class RoleController extends AbstractActionController 
{
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    private $taskManager;

    /**
     * Role manager.
     * @var User\Service\RoleManager 
     */
    private $roleManager;
    

    public function __construct($entityManager, $roleManager, $taskManager)
    {
        $this->entityManager = $entityManager;
        $this->roleManager = $roleManager;
        $this->taskManager = $taskManager;
    }

    /**
     * ����������� ���� �����
     *
     * @return ViewModel
     */
    public function indexAction() 
    {
        // ������� ��� ����
        $roles = $this->entityManager->getRepository(Role::class)
                ->findBy([], ['id'=>'ASC']);

        // ������ �����
        return new ViewModel([
            'roles' => $roles
        ]);
    }

    /**
     * ���������� ����� ����
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function addAction()
    {
        // ������� �����
        $form = new RoleForm('create', $this->entityManager, $this->taskManager);

        $roleList = [];
        $roles = $this->entityManager->getRepository(Role::class)
                ->findBy([], ['name'=>'ASC']);
        foreach ($roles as $role) {
            $roleList[$role->getId()] = $role->getName();
        }
        $form->get('inherit_roles[]')->setValueOptions($roleList);
        
        // �������� ���������� �� �����
        if ($this->getRequest()->isPost()) {
            
            // ���������� ������ �� �������
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // ��������� �����
            if($form->isValid()) {

                $data = $form->getData();
                
                // �������� ���� � ��
                $this->roleManager->addRole($data);
                
                $this->flashMessenger()->addSuccessMessage('Added new role.');
                
                return $this->redirect()->toRoute('roles', ['action'=>'index']);
            }               
        }

        // ������ �����
        return new ViewModel([
                'form' => $form
            ]);
    }

    /**
     * �������� ������ ����
     *
     * @return void|ViewModel
     */
    public function viewAction() 
    {
        // ���������� ������ �� �������
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // ����� ���� �� ID
        $role = $this->entityManager->getRepository(Role::class)
                ->find($id);
        
        if ($role == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $allPermissions = $this->entityManager->getRepository(Permission::class)
                ->findBy([], ['name'=>'ASC']);
        
        $effectivePermissions = $this->roleManager->getEffectivePermissions($role);

        // ������ �����
        return new ViewModel([
            'role' => $role,
            'allPermissions' => $allPermissions,
            'effectivePermissions' => $effectivePermissions
        ]);
    }

    /**
     * ������������� ����
     *
     * @return void|\Zend\Http\Response|ViewModel
     */
    public function editAction()
    {
        // ���������� ������ �� �������
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // ����� ���� �� ID
        $role = $this->entityManager->getRepository(Role::class)
                ->find($id);
        
        if ($role == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // �������� �����
        $form = new RoleForm('update', $this->entityManager, $this->taskManager, $role);
        
        $roleList = [];
        $roles = $this->entityManager->getRepository(Role::class)
                ->findBy([], ['name'=>'ASC']);
        foreach ($roles as $role2) {
            $roleList[$role2->getId()] = $role2->getName();
        }
        $form->get('inherit_roles[]')->setValueOptions($roleList);
        
        // �������� ���������� �� �����
        if ($this->getRequest()->isPost()) {

            // ���������� ������ �� �������
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // ��������� �����
            if($form->isValid()) {
                
                $data = $form->getData();
                
                // ���������� ���� � ��
                $this->roleManager->updateRole($role, $data);
                
                $this->flashMessenger()->addSuccessMessage('Updated the role.');
                
                return $this->redirect()->toRoute('roles', ['action'=>'index']);
            }               
        } else {
            $form->setData(array(
                    'name'=>$role->getName(),
                    'description'=>$role->getDescription()     
                ));
        }

        // ������ �����
        return new ViewModel([
                'form' => $form,
                'role' => $role
            ]);
    }

    /**
     * ������������� ����������
     *
     * @return void|\Zend\Http\Response|ViewModel
     */
    public function editPermissionsAction()
    {
        // ���������� ������ �� �������
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // ���������� ���� �� ID
        $role = $this->entityManager->getRepository(Role::class)
                ->find($id);
        
        if ($role == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
            
        $allPermissions = $this->entityManager->getRepository(Permission::class)
                ->findBy([], ['name'=>'ASC']);
        
        $effectivePermissions = $this->roleManager->getEffectivePermissions($role);
            
        // �������� �����
        $form = new RolePermissionsForm($this->entityManager);
        foreach ($allPermissions as $permission) {
            $label = $permission->getName();
            $isDisabled = false;
            if (isset($effectivePermissions[$permission->getName()]) && $effectivePermissions[$permission->getName()]=='inherited') {
                $label .= ' (inherited)';
                $isDisabled = true;
            }
            $form->addPermissionField($permission->getName(), $label, $isDisabled);
        }
        
        // �������� ���������� �� �����
        if ($this->getRequest()->isPost()) {

            // ���������� ������ �� �������
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // ��������� �����
            if($form->isValid()) {
                
                $data = $form->getData();
                
                // �������� ���������� � ��
                $this->roleManager->updateRolePermissions($role, $data);
                
                $this->flashMessenger()->addSuccessMessage('Updated permissions for the role.');
                
                return $this->redirect()->toRoute('roles', ['action'=>'view', 'id'=>$role->getId()]);
            }
        } else {
        
            $data = [];
            foreach ($effectivePermissions as $name=>$inherited) {
                $data['permissions'][$name] = 1;
            }
            
            $form->setData($data);
        }
        
        $errors = $form->getMessages();

        // ������ �����
        return new ViewModel([
                'form' => $form,
                'role' => $role,
                'allPermissions' => $allPermissions,
                'effectivePermissions' => $effectivePermissions
            ]);
    }

    /**
     * ������� ���������� (permission)
     *
     * @return void|\Zend\Http\Response
     */
    public function deleteAction()
    {
        // ���������� ������ �� �������
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // ����� ���� �� ID
        $role = $this->entityManager->getRepository(Role::class)
                ->find($id);
        
        if ($role == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // ������� ���� � ��
        $this->roleManager->deleteRole($role);

        $this->flashMessenger()->addSuccessMessage('Deleted the role.');

        return $this->redirect()->toRoute('roles', ['action'=>'index']);
    }
}




