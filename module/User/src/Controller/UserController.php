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
 * ����������, ������������ �� ��������� �������� ��������� � ��������������
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
     * ����������� ���� ������������� � ���� �������
     *
     * @return void|ViewModel
     */
    public function indexAction()
    {
        // �������� ���������� �������� ������������ �� ����������� �����������
        if (!$this->access('user.manage')) {
            $this->getResponse()->setStatusCode(401);
            return;
        }
        
        $users = $this->entityManager->getRepository(User::class)
                ->findBy([], ['id'=>'ASC']);

        //������ �������
        return new ViewModel([
            'users' => $users
        ]);
    }

    /**
     * ���������� ������ ������������
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function addAction()
    {
        // �������� ����� ��� ������������
        $form = new UserForm('create', $this->entityManager);
        
        // ����� ��� ����
        $allRoles = $this->entityManager->getRepository(Role::class)
                ->findBy([], ['name'=>'ASC']);
        $roleList = [];
        foreach ($allRoles as $role) {
            $roleList[$role->getId()] = $role->getName();
        }
        
        $form->get('roles')->setValueOptions($roleList);
        
        // �������� ���������� �� �����
        if ($this->getRequest()->isPost()) {
            
            // ���������� ���� ����������� ������
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // ��������� �����
            if($form->isValid()) {

                $data = $form->getData();
                
                // ��������� ������������
                $user = $this->userManager->addUser($data);
                
                // ������ �������
                return $this->redirect()->toRoute('users', 
                        ['action'=>'view', 'id'=>$user->getId()]);                
            }               
        }

        // ������ �������
        return new ViewModel([
            'form' => $form
        ]);
    }

    /**
     * ����������� ������ ������������
     *
     * @return void|ViewModel
     */
    public function viewAction() 
    {
        // ��������� ������
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // ������� ������������ �� ID
        $user = $this->entityManager->getRepository(User::class)
                ->find($id);
        
        if ($user == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // ������ �������
        return new ViewModel([
            'user' => $user
        ]);
    }

    /**
     * ������������� ������������
     *
     * @return void|\Zend\Http\Response|ViewModel
     */
    public function editAction() 
    {
        // ��������� ������
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // ������� ������������ �� ID
        $user = $this->entityManager->getRepository(User::class)
                ->find($id);
        
        if ($user == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // ������� ����� ��� ������������
        $form = new UserForm('update', $this->entityManager, $user);

        // ��������� ��� ����
        $allRoles = $this->entityManager->getRepository(Role::class)
                ->findBy([], ['name'=>'ASC']);
        $roleList = [];
        foreach ($allRoles as $role) {
            $roleList[$role->getId()] = $role->getName();
        }
        $form->get('roles')->setValueOptions($roleList);
        
        // �������� ���������� �� �����
        if ($this->getRequest()->isPost()) {
            
            // ���������� ������ �����
            $data = $this->params()->fromPost();
            
            $form->setData($data);
            
            // ��������� �����
            if($form->isValid()) {

                $data = $form->getData();
                
                // ��������� ������������ ������ ������� �� �����
                $this->userManager->updateUser($user, $data);
                
                // ������ �������
                return $this->redirect()->toRoute('users', 
                        ['action'=>'view', 'id'=>$user->getId()]);                
            }               
        } else {
            // ����������� ������ ��� ����������� �����
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

        // ������ �������
        return new ViewModel(array(
            'user' => $user,
            'form' => $form
        ));
    }

    /**
     * �������� ������
     *
     * @return void|\Zend\Http\Response|ViewModel
     */
    public function changePasswordAction() 
    {
        // ��������� ������
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // ������ ������������ �� ID
        $user = $this->entityManager->getRepository(User::class)
                ->find($id);
        
        if ($user == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // ������� �����
        $form = new PasswordChangeForm('change');

        // �������� ���������� �� �����
        if ($this->getRequest()->isPost()) {
            
            // ���������� ������ �� �������
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // ��������� �����
            if($form->isValid()) {

                $data = $form->getData();
                
                // �������� ������
                if (!$this->userManager->changePassword($user, $data)) {
                    $this->flashMessenger()->addErrorMessage(
                            'Sorry, the old password is incorrect. Could not set the new password.');
                } else {
                    $this->flashMessenger()->addSuccessMessage(
                            'Changed the password successfully.');
                }
                
                // ������ �������
                return $this->redirect()->toRoute('users', 
                        ['action'=>'view', 'id'=>$user->getId()]);                
            }               
        }

        // ������ �������
        return new ViewModel([
            'user' => $user,
            'form' => $form
        ]);
    }

    /**
     * �������� ������
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function resetPasswordAction()
    {
        // �������� �����
        $form = new PasswordResetForm();
        
        // �������� ���������� �� �����
        if ($this->getRequest()->isPost()) {
            
            // ���������� ������ �� �������
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // ��������� �����
            if($form->isValid()) {
                
                // ����� ������������ �� email
                $user = $this->entityManager->getRepository(User::class)
                        ->findOneByEmail($data['email']);                
                if ($user!=null) {
                    // ��������� ������ ������ � �������� �� email
                    $this->userManager->generatePasswordResetToken($user);
                    
                    // ��������
                    return $this->redirect()->toRoute('users', 
                            ['action'=>'message', 'id'=>'sent']);                 
                } else {
                    return $this->redirect()->toRoute('users', 
                            ['action'=>'message', 'id'=>'invalid-email']);                 
                }
            }               
        }

        // ������ �������
        return new ViewModel([                    
            'form' => $form
        ]);
    }

    /**
     * ������� � ����������� ������������ � ���������� ��������
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

        // ������ �������
        return new ViewModel([
            'id' => $id
        ]);
    }
    
    /**
     * ����������� "Reset Password" ��������.
     */
    public function setPasswordAction()
    {
        $token = $this->params()->fromQuery('token', null);
        
        // ��������� ����� token
        if ($token!=null && (!is_string($token) || strlen($token)!=32)) {
            throw new \Exception('Invalid token type or length');
        }
        
        if($token===null || 
           !$this->userManager->validatePasswordResetToken($token)) {
            return $this->redirect()->toRoute('users', 
                    ['action'=>'message', 'id'=>'failed']);
        }
                
        // �������� ����� �����
        $form = new PasswordChangeForm('reset');
        
        // �������� ���������� �� �����
        if ($this->getRequest()->isPost()) {
            
            // ���������� ������ �� �������
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // ��������� �����
            if($form->isValid()) {
                
                $data = $form->getData();
                                               
                // ��������� ������ ������
                if ($this->userManager->setNewPasswordByToken($token, $data['new_password'])) {
                    
                    // �������� ��� ������ ���������
                    return $this->redirect()->toRoute('users', 
                            ['action'=>'message', 'id'=>'set']);                 
                } else {
                    // �������� ��� ������ ���������
                    return $this->redirect()->toRoute('users', 
                            ['action'=>'message', 'id'=>'failed']);                 
                }
            }               
        } 

        // ������ �����
        return new ViewModel([                    
            'form' => $form
        ]);
    }
}


