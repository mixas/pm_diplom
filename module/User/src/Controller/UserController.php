<?php
namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use User\Entity\User;
use User\Entity\Role;
use User\Form\UserForm;
use User\Form\PasswordChangeForm;
use User\Form\PasswordResetForm;

/**
 * Контроллер, ответсвенный за обработку запросов связанных с пользователями
 * Class UserController
 * @package User\Controller
 */
class UserController extends AbstractActionController 
{
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * User manager.
     * @var User\Service\UserManager 
     */
    private $userManager;
    
    public function __construct($entityManager, $userManager)
    {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
    }

    /**
     * Отобращение всех пользователей в виде теблицы
     *
     * @return void|ViewModel
     */
    public function indexAction()
    {
        // Проверка полномочий текущего пользоватлея на соответсвие требованиям
        if (!$this->access('user.manage')) {
            $this->getResponse()->setStatusCode(401);
            return;
        }
        
        $users = $this->entityManager->getRepository(User::class)
                ->findBy([], ['id'=>'ASC']);

        //рендер шаблона
        return new ViewModel([
            'users' => $users
        ]);
    }

    /**
     * Добавление нового пользователя
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function addAction()
    {
        // Создание формы для пользователя
        $form = new UserForm('create', $this->entityManager);
        
        // Берем все роли
        $allRoles = $this->entityManager->getRepository(Role::class)
                ->findBy([], ['name'=>'ASC']);
        $roleList = [];
        foreach ($allRoles as $role) {
            $roleList[$role->getId()] = $role->getName();
        }
        
        $form->get('roles')->setValueOptions($roleList);
        
        // проверка отправлена ли форма
        if ($this->getRequest()->isPost()) {
            
            // извлечение всех заполненных данных
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Валидация формы
            if($form->isValid()) {

                $data = $form->getData();
                
                // Добавляем пользователя
                $user = $this->userManager->addUser($data);
                
                // рендер шаблона
                return $this->redirect()->toRoute('users', 
                        ['action'=>'view', 'id'=>$user->getId()]);                
            }               
        }

        // рендер шаблона
        return new ViewModel([
            'form' => $form
        ]);
    }

    /**
     * Отображение данных пользователя
     *
     * @return void|ViewModel
     */
    public function viewAction() 
    {
        // Принимаем данные
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Находим пользователя по ID
        $user = $this->entityManager->getRepository(User::class)
                ->find($id);
        
        if ($user == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // рендер шаблона
        return new ViewModel([
            'user' => $user
        ]);
    }

    /**
     * Редактировани пользователя
     *
     * @return void|\Zend\Http\Response|ViewModel
     */
    public function editAction() 
    {
        // Принимаем данные
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Находим пользователя по ID
        $user = $this->entityManager->getRepository(User::class)
                ->find($id);
        
        if ($user == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // создаем форму для пользователя
        $form = new UserForm('update', $this->entityManager, $user);

        // Извлекаем все роли
        $allRoles = $this->entityManager->getRepository(Role::class)
                ->findBy([], ['name'=>'ASC']);
        $roleList = [];
        foreach ($allRoles as $role) {
            $roleList[$role->getId()] = $role->getName();
        }
        $form->get('roles')->setValueOptions($roleList);
        
        // Проверка отправлена ли форма
        if ($this->getRequest()->isPost()) {
            
            // Извлечение данных формы
            $data = $this->params()->fromPost();
            
            $form->setData($data);
            
            // Валидация формы
            if($form->isValid()) {

                $data = $form->getData();
                
                // Обновляем пользователя новыми данными из формы
                $this->userManager->updateUser($user, $data);
                
                // Рендер шаблона
                return $this->redirect()->toRoute('users', 
                        ['action'=>'view', 'id'=>$user->getId()]);                
            }               
        } else {
            // Определение данных для изначальной формы
            $userRoleIds = [];
            foreach ($user->getRoles() as $role) {
                $userRoleIds[] = $role->getId();
            }
            
            $form->setData(array(
                    'full_name'=>$user->getFullName(),
                    'email'=>$user->getEmail(),
                    'status'=>$user->getStatus(), 
                    'salary_rate'=>$user->getSalaryRate(),
                    'roles' => $userRoleIds
                ));
        }

        // Рендер шаблона
        return new ViewModel(array(
            'user' => $user,
            'form' => $form
        ));
    }

    /**
     * Изменить пароль
     *
     * @return void|\Zend\Http\Response|ViewModel
     */
    public function changePasswordAction() 
    {
        // Принимаем данные
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Находи пользователя по ID
        $user = $this->entityManager->getRepository(User::class)
                ->find($id);
        
        if ($user == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Создаем форму
        $form = new PasswordChangeForm('change');

        // Проверка отправлена ли форма
        if ($this->getRequest()->isPost()) {
            
            // Извлечение данных из запроса
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Валидация формы
            if($form->isValid()) {

                $data = $form->getData();
                
                // Изменить пароль
                if (!$this->userManager->changePassword($user, $data)) {
                    $this->flashMessenger()->addErrorMessage(
                            'Sorry, the old password is incorrect. Could not set the new password.');
                } else {
                    $this->flashMessenger()->addSuccessMessage(
                            'Changed the password successfully.');
                }
                
                // Рендер шаблона
                return $this->redirect()->toRoute('users', 
                        ['action'=>'view', 'id'=>$user->getId()]);                
            }               
        }

        // Рендер шаблона
        return new ViewModel([
            'user' => $user,
            'form' => $form
        ]);
    }

    /**
     * Сбросить пароль
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function resetPasswordAction()
    {
        // Создание формы
        $form = new PasswordResetForm();
        
        // Проверка отправлена ли форма
        if ($this->getRequest()->isPost()) {
            
            // Извлечение данных из запроса
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Валидация формы
            if($form->isValid()) {
                
                // Поиск пользователя по email
                $user = $this->entityManager->getRepository(User::class)
                        ->findOneByEmail($data['email']);                
                if ($user!=null) {
                    // Генерация нового пароля и отправка на email
                    $this->userManager->generatePasswordResetToken($user);
                    
                    // Редирект
                    return $this->redirect()->toRoute('users', 
                            ['action'=>'message', 'id'=>'sent']);                 
                } else {
                    return $this->redirect()->toRoute('users', 
                            ['action'=>'message', 'id'=>'invalid-email']);                 
                }
            }               
        }

        // Рендер шаблона
        return new ViewModel([                    
            'form' => $form
        ]);
    }

    /**
     * Страциа с уведомление пользователя о завершении действия
     *
     * @return ViewModel
     * @throws \Exception
     */
    public function messageAction() 
    {
        $id = (string)$this->params()->fromRoute('id');

        if($id!='invalid-email' && $id!='sent' && $id!='set' && $id!='failed') {
            throw new \Exception('Invalid message ID specified');
        }

        // Рендер шаблона
        return new ViewModel([
            'id' => $id
        ]);
    }
    
    /**
     * Отображение "Reset Password" страницы.
     */
    public function setPasswordAction()
    {
        $token = $this->params()->fromQuery('token', null);
        
        // Валидация длины token
        if ($token!=null && (!is_string($token) || strlen($token)!=32)) {
            throw new \Exception('Invalid token type or length');
        }
        
        if($token===null || 
           !$this->userManager->validatePasswordResetToken($token)) {
            return $this->redirect()->toRoute('users', 
                    ['action'=>'message', 'id'=>'failed']);
        }
                
        // Создание новой формы
        $form = new PasswordChangeForm('reset');
        
        // Проверка отправлена ли форма
        if ($this->getRequest()->isPost()) {
            
            // Извлечение данных из запроса
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Валидация формы
            if($form->isValid()) {
                
                $data = $form->getData();
                                               
                // Установка нового пароля
                if ($this->userManager->setNewPasswordByToken($token, $data['new_password'])) {
                    
                    // Редирект для показа сообщение
                    return $this->redirect()->toRoute('users', 
                            ['action'=>'message', 'id'=>'set']);                 
                } else {
                    // Редирект для показа сообщение
                    return $this->redirect()->toRoute('users', 
                            ['action'=>'message', 'id'=>'failed']);                 
                }
            }               
        } 

        // Рендер формы
        return new ViewModel([                    
            'form' => $form
        ]);
    }
}


