<?php

namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\Result;
use Zend\Uri\Uri;
use User\Form\LoginForm;
use User\Entity\User;

/**
 * Контроллер, ответсвенный за login и logout
 */
class AuthController extends AbstractActionController
{
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager 
     */
    private $entityManager;
    
    /**
     * Auth manager.
     * @var User\Service\AuthManager 
     */
    private $authManager;
    
    /**
     * Auth service.
     * @var \Zend\Authentication\AuthenticationService
     */
    private $authService;
    
    /**
     * User manager.
     * @var User\Service\UserManager
     */
    private $userManager;
    
    public function __construct($entityManager, $authManager, $authService, $userManager)
    {
        $this->entityManager = $entityManager;
        $this->authManager = $authManager;
        $this->authService = $authService;
        $this->userManager = $userManager;
    }
    
    /**
     * Аутентификация пользователя по email и паролю.
     */
    public function loginAction()
    {
        $redirectUrl = (string)$this->params()->fromQuery('redirectUrl', '');
        if (strlen($redirectUrl)>2048) {
            throw new \Exception("Too long redirectUrl argument passed");
        }
        
        $this->userManager->createAdminUserIfNotExists();
        
        // Создание логин формы
        $form = new LoginForm(); 
        $form->get('redirect_url')->setValue($redirectUrl);

        $isLoginError = false;

        // Проверка отправлена ли форма
        if ($this->getRequest()->isPost()) {

            // Извлечение данных из запроса
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Валидация формы
            if($form->isValid()) {
                
                $data = $form->getData();
                
                // Попытка залогировать пользователя
                $result = $this->authManager->login($data['email'], 
                        $data['password'], $data['remember_me']);
                
                // Проверка результата
                if ($result->getCode() == Result::SUCCESS) {
                    
                    $redirectUrl = $this->params()->fromPost('redirect_url', '');
                    
                    if (!empty($redirectUrl)) {
                        $uri = new Uri($redirectUrl);
                        if (!$uri->isValid() || $uri->getHost()!=null)
                            throw new \Exception('Incorrect redirect URL: ' . $redirectUrl);
                    }

                    if(empty($redirectUrl)) {
                        return $this->redirect()->toRoute('home');
                    } else {
                        $this->redirect()->toUrl($redirectUrl);
                    }
                } else {
                    $isLoginError = true;
                }                
            } else {
                $isLoginError = true;
            }           
        } 
        
        return new ViewModel([
            'form' => $form,
            'isLoginError' => $isLoginError,
            'redirectUrl' => $redirectUrl
        ]);
    }
    
    /**
     * Выполнение logout пользователя
     */
    public function logoutAction() 
    {        
        $this->authManager->logout();
        
        return $this->redirect()->toRoute('login');
    }
    
    /**
     * Отображение ошибки авторизации (отображение 403 страницы)
     */
    public function notAuthorizedAction()
    {
        $this->getResponse()->setStatusCode(403);
        
        return new ViewModel();
    }
}
