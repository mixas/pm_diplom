<?php
namespace User\Service;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\Crypt\Password\Bcrypt;
use User\Entity\User;

/**
 * Adapter used for authenticating user. It takes login and password on input
 * and checks the database if there is a user with such login (email) and password.
 * If such user exists, the service returns its identity (email). The identity
 * is saved to session and can be retrieved later with Identity view helper provided
 * by ZF3.
 */
class AuthAdapter implements AdapterInterface
{
    /**
     * User email.
     * @var string 
     */
    private $email;
    
    /**
     * Password
     * @var string 
     */
    private $password;
    
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager 
     */
    private $entityManager;
        
    /**
     * Constructor.
     */
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Sets user email.     
     */
    public function setEmail($email) 
    {
        $this->email = $email;        
    }
    
    /**
     * Sets password.     
     */
    public function setPassword($password) 
    {
        $this->password = (string)$password;        
    }
    
    /**
     * ��������� ������� ��������������
     */
    public function authenticate()
    {                
        // �������� ���� �� ������������ � �� � ����� email
        $user = $this->entityManager->getRepository(User::class)
                ->findOneByEmail($this->email);
        
        // ���� ��� ������ ������������ - ������
        if ($user == null) {
            return new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND, 
                null, 
                ['Invalid credentials.']);        
        }   
        
        // �������� ������� ������������ �� ����������
        if ($user->getStatus()==User::STATUS_RETIRED) {
            return new Result(
                Result::FAILURE, 
                null, 
                ['User is retired.']);        
        }
        
        // ���������� hash ������� ��� ���������� ������ � �������� � ������� � ��
        $bcrypt = new Bcrypt();
        $passwordHash = $user->getPassword();
        if ($bcrypt->verify($this->password, $passwordHash)) {
            // �������� ��������
            return new Result(
                    Result::SUCCESS, 
                    $this->email, 
                    ['Authenticated successfully.']);        
        }             
        
        // �������� �� ��������
        return new Result(
                Result::FAILURE_CREDENTIAL_INVALID, 
                null, 
                ['Invalid credentials.']);        
    }
}


