<?php
namespace User\Service;

use Zend\Authentication\Result;

/**
 * The AuthManager service is responsible for user's login/logout and simple access 
 * filtering. The access filtering feature checks whether the current visitor 
 * is allowed to see the given page or not.  
 */
class AuthManager
{
    // Constants returned by the access filter.
    const ACCESS_GRANTED = 1; // Access to the page is granted.
    const AUTH_REQUIRED  = 2; // Authentication is required to see the page.
    const ACCESS_DENIED  = 3; // Access to the page is denied.
    
    /**
     * Authentication service.
     * @var \Zend\Authentication\AuthenticationService
     */
    private $authService;
    
    /**
     * Session manager.
     * @var Zend\Session\SessionManager
     */
    private $sessionManager;
    
    /**
     * Contents of the 'access_filter' config key.
     * @var array 
     */
    private $config;
    
    /**
     * RBAC manager.
     * @var User\Service\RbacManager
     */
    private $rbacManager;
    
    /**
     * Constructs the service.
     */
    public function __construct($authService, $sessionManager, $config, $rbacManager) 
    {
        $this->authService = $authService;
        $this->sessionManager = $sessionManager;
        $this->config = $config;
        $this->rbacManager = $rbacManager;
    }
    
    /**
     * ¬ыполн€ет попытку логина. ≈сли $rememberMe аргумент = true, это принуждает сессию
     * хранитьс€ 1 мес€ц (в противном случае - один час).
     */
    public function login($email, $password, $rememberMe)
    {   
        // ѕроверка зарегистрирован ли пользователь
        if ($this->authService->getIdentity()!=null) {
            throw new \Exception('Already logged in');
        }
            
        // јутетифицировать по логину и паролю
        $authAdapter = $this->authService->getAdapter();
        $authAdapter->setEmail($email);
        $authAdapter->setPassword($password);
        $result = $this->authService->authenticate();

        if ($result->getCode()==Result::SUCCESS && $rememberMe) {
            // установить вр€м€ истечению cookies в 1 мес€ц.
            $this->sessionManager->rememberMe(60*60*24*30);
        }
        
        return $result;
    }
    
    /**
     * ¬ыполн€ет logout.
     */
    public function logout()
    {
        // ѕроверка зарегистрирован ли пользователь
        if ($this->authService->getIdentity()==null) {
            throw new \Exception('The user is not logged in');
        }
        
        // очистить сессию
        $this->authService->clearIdentity();               
    }
    
    public function filterAccess($controllerName, $actionName)
    {
        $mode = isset($this->config['options']['mode'])?$this->config['options']['mode']:'restrictive';
        if ($mode!='restrictive' && $mode!='permissive')
            throw new \Exception('Invalid access filter mode (expected either restrictive or permissive mode');
        
        if (isset($this->config['controllers'][$controllerName])) {
            $items = $this->config['controllers'][$controllerName];
            foreach ($items as $item) {
                $actionList = $item['actions'];
                $allow = $item['allow'];
                if (is_array($actionList) && in_array($actionName, $actionList) ||
                    $actionList=='*') {
                    if ($allow=='*')
                        // Anyone is allowed to see the page.
                        return self::ACCESS_GRANTED; 
                    else if (!$this->authService->hasIdentity()) {
                        // Only authenticated user is allowed to see the page.
                        return self::AUTH_REQUIRED;                        
                    }
                        
                    if ($allow=='@') {
                        // Any authenticated user is allowed to see the page.
                        return self::ACCESS_GRANTED;                         
                    } else if (substr($allow, 0, 1)=='@') {
                        // Only the user with specific identity is allowed to see the page.
                        $identity = substr($allow, 1);
                        if ($this->authService->getIdentity()==$identity)
                            return self::ACCESS_GRANTED; 
                        else
                            return self::ACCESS_DENIED;
                    } else if (substr($allow, 0, 1)=='+') {
                        // Only the user with this permission is allowed to see the page.
                        $permission = substr($allow, 1);
                        if ($this->rbacManager->isGranted(null, $permission))
                            return self::ACCESS_GRANTED; 
                        else
                            return self::ACCESS_DENIED;
                    } else {
                        throw new \Exception('Unexpected value for "allow" - expected ' .
                                'either "?", "@", "@identity" or "+permission"');
                    }
                }
            }            
        }
        
        // In restrictive mode, we require authentication for any action not 
        // listed under 'access_filter' key and deny access to authorized users 
        // (for security reasons).
        if ($mode=='restrictive') {
            if(!$this->authService->hasIdentity())
                return self::AUTH_REQUIRED;
            else
                return self::ACCESS_DENIED;
        }
        
        // Permit access to this page.
        return self::ACCESS_GRANTED;
    }
}