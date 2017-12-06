<?php
namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Project\Entity\TaskStatus;

/**
 * This class represents a role.
 * @ORM\Entity()
 * @ORM\Table(name="role")
 */
class Role
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id")
     * @ORM\GeneratedValue
     */
    protected $id;

    /** 
     * @ORM\Column(name="name")  
     */
    protected $name;
    
    /** 
     * @ORM\Column(name="description")  
     */
    protected $description;

    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;

    /**
     * @ORM\Column(name="default_status_filter")
     */
    protected $defaultStatusFilter;

    /**
     * @ORM\ManyToMany(targetEntity="User\Entity\Role")
     * @ORM\JoinTable(name="role_hierarchy",
     *      joinColumns={@ORM\JoinColumn(name="child_role_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="parent_role_id", referencedColumnName="id")}
     *      )
     */
    private $parentRoles;
    
    /**
     * @ORM\ManyToMany(targetEntity="User\Entity\Role")
     * @ORM\JoinTable(name="role_hierarchy",
     *      joinColumns={@ORM\JoinColumn(name="parent_role_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="child_role_id", referencedColumnName="id")}
     *      )
     */
    protected $childRoles;
    
    /**
     * @ORM\ManyToMany(targetEntity="User\Entity\Permission")
     * @ORM\JoinTable(name="role_permission",
     *      joinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="permission_id", referencedColumnName="id")}
     *      )
     */
    private $permissions;

    /**
     * @ORM\ManyToMany(targetEntity="User\Entity\User")
     * @ORM\JoinTable(name="user_role",
     *      joinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     *      )
     */
    private $users;
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->parentRoles = new ArrayCollection();
        $this->childRoles = new ArrayCollection();
        $this->permissions = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function getUsers(){
        return $this->users;
    }
    
    /**
     * Returns role ID.
     * @return integer
     */
    public function getId() 
    {
        return $this->id;
    }

    /**
     * Sets role ID. 
     * @param int $id    
     */
    public function setId($id) 
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function getDescription()
    {
        return $this->description;
    }
    
    public function setDescription($description)
    {
        $this->description = $description;
    }
    
    public function getDateCreated()
    {
        return $this->dateCreated;
    }
    
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
    }

    public function getDefaultStatusFilter()
    {
        return $this->defaultStatusFilter;
    }

    public function setDefaultStatusFilter($defaultStatusFilter)
    {
        $this->defaultStatusFilter = $defaultStatusFilter;
    }

    public function getParentRoles()
    {
        return $this->parentRoles;
    }
    
    public function getChildRoles()
    {
        return $this->childRoles;
    }
    
    public function getPermissions()
    {
        return $this->permissions;
    }
}



