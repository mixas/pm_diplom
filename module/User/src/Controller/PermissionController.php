<?php
namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use User\Entity\Permission;
use User\Form\PermissionForm;

/**
 * Контроллер отвечает за управлениями полномочиями
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
     * Список всех полномочий
     *
     * @return ViewModel
     */
    public function indexAction() 
    {
        // Выбрать все полномочия в БД
        $permissions = $this->entityManager->getRepository(Permission::class)
                ->findBy([], ['name'=>'ASC']);

        // Рендер шаблона
        return new ViewModel([
            'permissions' => $permissions
        ]);
    }

    /**
     * Добавить новое полномочие
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function addAction()
    {
        // Создать форму
        $form = new PermissionForm('create', $this->entityManager);

        // Проверка отправлена ли форма
        if ($this->getRequest()->isPost()) {

            // Извлечение данных из запроса
            $data = $this->params()->fromPost();            
            
            $form->setData($data);

            // Валидация формы
            if($form->isValid()) {
                
                $data = $form->getData();
                
                // Добавить полномочие в БД
                $this->permissionManager->addPermission($data);
                
                $this->flashMessenger()->addSuccessMessage('Added new permission.');
                
                return $this->redirect()->toRoute('permissions', ['action'=>'index']);
            }               
        }

        // Рендер шаблона
        return new ViewModel([
                'form' => $form
            ]);
    }

    /**
     * Отображение информации для полномочий
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

        // Найти полномочие в БД по ID
        $permission = $this->entityManager->getRepository(Permission::class)
                ->find($id);
        
        if ($permission == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Рендер шаблона
        return new ViewModel([
            'permission' => $permission
        ]);
    }

    /**
     * Редактирование полномочий
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

        // Найти полномочие по ID
        $permission = $this->entityManager->getRepository(Permission::class)
                ->find($id);
        
        if ($permission == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Создать форму
        $form = new PermissionForm('update', $this->entityManager, $permission);

        // Проверка отправлена ли форма
        if ($this->getRequest()->isPost()) {

            // Извлечение данных из запроса
            $data = $this->params()->fromPost();            
            
            $form->setData($data);

            // Валидация формы
            if($form->isValid()) {
                
                $data = $form->getData();
                
                // Обновить полномочие в БД
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

        // Рендер шаблона
        return new ViewModel([
                'form' => $form,
                'permission' => $permission
            ]);
    }

    /**
     * Удавить полномочие
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

        // Найти полномочие в БД по ID
        $permission = $this->entityManager->getRepository(Permission::class)
                ->find($id);
        
        if ($permission == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Удалить полномочие в БД
        $this->permissionManager->deletePermission($permission);
        
        $this->flashMessenger()->addSuccessMessage('Deleted the permission.');

        return $this->redirect()->toRoute('permissions', ['action'=>'index']);
    }
}






