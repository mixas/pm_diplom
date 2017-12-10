<?php

namespace Project\Validator;

use Zend\Validator\AbstractValidator;
use Project\Entity\Project;

class ProjectExistsValidator extends AbstractValidator
{
    protected $options = array(
        'entityManager' => null,
        'project' => null
    );
    
    const NOT_SCALAR  = 'notScalar';
    const PROJECT_EXISTS = 'projectExists';
        
    protected $messageTemplates = array(
        self::NOT_SCALAR  => "The code must be a scalar value",
        self::PROJECT_EXISTS  => "Another project with such code already exists"
    );
    
    public function __construct($options = null)
    {
        if(is_array($options)) {
            if(isset($options['entityManager']))
                $this->options['entityManager'] = $options['entityManager'];
            if(isset($options['project']))
                $this->options['project'] = $options['project'];
        }
        
        parent::__construct($options);
    }
        
    public function isValid($value)
    {
        if(!is_scalar($value)) {
            $this->error(self::NOT_SCALAR);
            return false; 
        }
        
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
        
        if(!$isValid) {
            $this->error(self::PROJECT_EXISTS);
        }
        
        return $isValid;
    }
}

