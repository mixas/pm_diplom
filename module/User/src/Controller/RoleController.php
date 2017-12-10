<?php
namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use User\Entity\Role;
use User\Entity\Permission;
use User\Form\RoleForm;
use User\Form\RolePermissionsForm;

/**
 * Управление ролями
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
     * Отображение всех ролей
     *
     * @return ViewModel
     */
    public function indexAction() 
    {
        // Извлечь все роли
        $roles = $this->entityManager->getRepository(Role::class)
                ->findBy([], ['id'=>'ASC']);

        // Рендер формы
        return new ViewModel([
            'roles' => $roles
        ]);
    }

    /**
     * Добавление новой роли
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function addAction()
    {
        // Создать форму
        $form = new RoleForm('create', $this->entityManager, $this->taskManager);

        $roleList = [];
        $roles = $this->entityManager->getRepository(Role::class)
                ->findBy([], ['name'=>'ASC']);
        foreach ($roles as $role) {
            $roleList[$role->getId()] = $role->getName();
        }
        $form->get('inherit_roles[]')->setValueOptions($roleList);
        
        // Проверка отправлена ли форма
        if ($this->getRequest()->isPost()) {
            
            // Извлечение данных из запроса
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Валидация формы
            if($form->isValid()) {

                $data = $form->getData();
                
                // Добавить роль в БД
                $this->roleManager->addRole($data);
                
                $this->flashMessenger()->addSuccessMessage('Added new role.');
                
                return $this->redirect()->toRoute('roles', ['action'=>'index']);
            }               
        }

        // Рендер формы
        return new ViewModel([
                'form' => $form
            ]);
    }

    /**
     * Просмотр данныъ роли
     *
     * @return void|ViewModel
     */
    public function viewAction() 
    {
        // Извлечение данных из запроса
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Поиск роли по ID
        $role = $this->entityManager->getRepository(Role::class)
                ->find($id);
        
        if ($role == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $allPermissions = $this->entityManager->getRepository(Permission::class)
                ->findBy([], ['name'=>'ASC']);
        
        $effectivePermissions = $this->roleManager->getEffectivePermissions($role);

        // Рендер формы
        return new ViewModel([
            'role' => $role,
            'allPermissions' => $allPermissions,
            'effectivePermissions' => $effectivePermissions
        ]);
    }

    /**
     * Редактировать роль
     *
     * @return void|\Zend\Http\Response|ViewModel
     */
    public function editAction()
    {
        // Извлечение данных из запроса
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Поиск роли по ID
        $role = $this->entityManager->getRepository(Role::class)
                ->find($id);
        
        if ($role == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Создание формы
        $form = new RoleForm('update', $this->entityManager, $this->taskManager, $role);
        
        $roleList = [];
        $roles = $this->entityManager->getRepository(Role::class)
                ->findBy([], ['name'=>'ASC']);
        foreach ($roles as $role2) {
            $roleList[$role2->getId()] = $role2->getName();
        }
        $form->get('inherit_roles[]')->setValueOptions($roleList);
        
        // Проверка отправлена ли форма
        if ($this->getRequest()->isPost()) {

            // Извлечение данных из запроса
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Валидация формы
            if($form->isValid()) {
                
                $data = $form->getData();
                
                // Обновление роли в БД
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

        // Рендер формы
        return new ViewModel([
                'form' => $form,
                'role' => $role
            ]);
    }

    /**
     * Редактировать полномочие
     *
     * @return void|\Zend\Http\Response|ViewModel
     */
    public function editPermissionsAction()
    {
        // Извлечение данных из запроса
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Нахождение роли по ID
        $role = $this->entityManager->getRepository(Role::class)
                ->find($id);
        
        if ($role == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
            
        $allPermissions = $this->entityManager->getRepository(Permission::class)
                ->findBy([], ['name'=>'ASC']);
        
        $effectivePermissions = $this->roleManager->getEffectivePermissions($role);
            
        // Создание формы
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
        
        // Проверка отправлена ли форма
        if ($this->getRequest()->isPost()) {

            // Извлечение данных из запроса
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Валидация формы
            if($form->isValid()) {
                
                $data = $form->getData();
                
                // Обновить полномочия в БД
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

        // Рендер формы
        return new ViewModel([
                'form' => $form,
                'role' => $role,
                'allPermissions' => $allPermissions,
                'effectivePermissions' => $effectivePermissions
            ]);
    }

    /**
     * Удалить полномочие (permission)
     *
     * @return void|\Zend\Http\Response
     */
    public function deleteAction()
    {
        // Извлечение данных из запроса
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Найти роль по ID
        $role = $this->entityManager->getRepository(Role::class)
                ->find($id);
        
        if ($role == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Удалить роль в БД
        $this->roleManager->deleteRole($role);

        $this->flashMessenger()->addSuccessMessage('Deleted the role.');

        return $this->redirect()->toRoute('roles', ['action'=>'index']);
    }
}




