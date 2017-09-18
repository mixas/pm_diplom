<?php

namespace Project\Validator;

use Zend\Validator\AbstractValidator;
use Project\Entity\Project;
/**
 * This validator class is designed for checking if there is an existing project
 */
class ProjectExistsValidator extends AbstractValidator
{
    /**
     * Available validator options.
     * @var array
     */
    protected $options = array(
        'entityManager' => null,
        'project' => null
    );
    
    // Validation failure message IDs.
    const NOT_SCALAR  = 'notScalar';
    const PROJECT_EXISTS = 'projectExists';
        
    /**
     * Validation failure messages.
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_SCALAR  => "The code must be a scalar value",
        self::PROJECT_EXISTS  => "Another project with such code already exists"
    );
    
    /**
     * Constructor.     
     */
    public function __construct($options = null) 
    {
        // Set filter options (if provided).
        if(is_array($options)) {            
            if(isset($options['entityManager']))
                $this->options['entityManager'] = $options['entityManager'];
            if(isset($options['project']))
                $this->options['project'] = $options['project'];
        }
        
        // Call the parent class constructor
        parent::__construct($options);
    }
        
    /**
     * Check if project exists.
     */
    public function isValid($value) 
    {
        if(!is_scalar($value)) {
            $this->error(self::NOT_SCALAR);
            return false; 
        }
        
        // Get Doctrine entity manager.
        $entityManager = $this->options['entityManager'];
        
        $project = $entityManager->getRepository(Project::class)
                ->findOneByCode($value);
        
        if($this->options['project']==null) {
            $isValid = ($project==null);
        } else {
            if($this->options['project']->getCode()!=$value && $project!=null)
                $isValid = false;
            else 
                $isValid = true;
        }
        
        // If there were an error, set error message.
        if(!$isValid) {            
            $this->error(self::PROJECT_EXISTS);
        }
        
        // Return validation result.
        return $isValid;
    }
}

