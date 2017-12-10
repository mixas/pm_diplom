<?php
namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Класс отображает пользователя системы (сущность БД)
 *
 * @ORM\Entity()
 * @ORM\Table(name="user")
 */
class User 
{
    // User status constants.
    const STATUS_ACTIVE       = 1;
    const STATUS_RETIRED      = 2;

    /**
     * @ORM\Id
     * @ORM\Column(name="id")
     * @ORM\GeneratedValue
     */
    protected $id;

    /** 
     * @ORM\Column(name="email")  
     */
    protected $email;
    
    /** 
     * @ORM\Column(name="full_name")  
     */
    protected $fullName;

    /**
     * @ORM\Column(name="salary_rate")
     */
    protected $salaryRate;

    /** 
     * @ORM\Column(name="password")  
     */
    protected $password;

    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;
    
    /**
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;
        
    /**
     * @ORM\Column(name="pwd_reset_token")  
     */
    protected $passwordResetToken;
    
    /**
     * @ORM\Column(name="pwd_reset_token_creation_date")  
     */
    protected $passwordResetTokenCreationDate;

    /**
     * @ORM\ManyToMany(targetEntity="User\Entity\Role")
     * @ORM\JoinTable(name="user_role",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     *      )
     */
    private $roles;

    /**
     * @ORM\ManyToMany(targetEntity="Project\Entity\Project")
     * @ORM\JoinTable(name="user_project",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")}
     *      )
     */
    private $projects;


    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->projects = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param $email
     */
    public function setEmail($email) 
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getFullName() 
    {
        return $this->fullName;
    }

    /**
     * @param $fullName
     */
    public function setFullName($fullName) 
    {
        $this->fullName = $fullName;
    }

    /**
     * @return mixed
     */
    public function getSalaryRate()
    {
        return $this->salaryRate;
    }

    /**
     * @param $salaryRate
     * @return $this
     */
    public function setSalaryRate($salaryRate)
    {
        $this->salaryRate = $salaryRate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus() 
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public static function getStatusList() 
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_RETIRED => 'Retired'
        ];
    }

    /**
     * @return string
     */
    public function getStatusAsString()
    {
        $list = self::getStatusList();
        if (isset($list[$this->status]))
            return $list[$this->status];
        
        return 'Unknown';
    }

    /**
     * @param $status
     */
    public function setStatus($status) 
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getPassword() 
    {
       return $this->password; 
    }

    /**
     * @param $password
     */
    public function setPassword($password) 
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }

    /**
     * @param $dateCreated
     */
    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
    }

    /**
     * @return mixed
     */
    public function getResetPasswordToken()
    {
        return $this->passwordResetToken;
    }

    /**
     * @param $token
     */
    public function setPasswordResetToken($token) 
    {
        $this->passwordResetToken = $token;
    }

    /**
     * @return mixed
     */
    public function getPasswordResetTokenCreationDate()
    {
        return $this->passwordResetTokenCreationDate;
    }

    /**
     * @param $date
     */
    public function setPasswordResetTokenCreationDate($date) 
    {
        $this->passwordResetTokenCreationDate = $date;
    }

    /**
     * @return ArrayCollection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @return string
     */
    public function getRolesAsString()
    {
        $roleList = '';
        
        $count = count($this->roles);
        $i = 0;
        foreach ($this->roles as $role) {
            $roleList .= $role->getName();
            if ($i<$count-1)
                $roleList .= ', ';
            $i++;
        }
        
        return $roleList;
    }

    /**
     * @param $role
     */
    public function addRole($role)
    {
        $this->roles->add($role);
    }

    /**
     * @return ArrayCollection
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * @param $project
     */
    public function addProject($project)
    {
        $this->projects->add($project);
    }
}



