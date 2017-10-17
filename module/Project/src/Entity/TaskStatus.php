<?php

namespace Project\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * This class represents a registered project.
 * @ORM\Entity()
 * @ORM\Table(name="task_status")
 */
class TaskStatus
{
    // User status constants.
    const STATUS_TO_DO       = 1; // To do.
    const STATUS_IN_PROCESS      = 2; // In process.
    const STATUS_TO_BE_CHECKED      = 3; // To be checked.
    const STATUS_IN_QA      = 4; // In QA.
    const STATUS_CHECKED      = 5; // Checked by QA.
    const STATUS_DONE      = 6; // Done.

    /**
     * @ORM\Id
     * @ORM\Column(name="id")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(name="label")
     * @ORM\GeneratedValue
     */
    protected $label;

    /**
     * Returns status ID.
     * @return integer
     */
    public function getId() 
    {
        return $this->id;
    }

    /**
     * Sets status ID.
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    
    /**
     * Returns status.
     * @return int     
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Sets status label
     *
     * @param $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusList() 
    {
        return [
            self::STATUS_TO_DO => 'To do',
            self::STATUS_IN_PROCESS => 'In process',
            self::STATUS_TO_BE_CHECKED => 'To be checked',
            self::STATUS_IN_QA => 'In QA',
            self::STATUS_CHECKED => 'Checked',
            self::STATUS_DONE => 'Done'
        ];
    }    


}



