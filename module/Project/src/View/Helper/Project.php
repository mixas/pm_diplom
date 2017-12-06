<?php
namespace Project\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Project\Entity\Task;
use Project\Entity\TaskStatus;

class Project extends AbstractHelper
{

    public function formatTime($minutes, $only = null){
        if ($minutes < 1) {
            return;
        }
        $format = '%02dh %02dm';
        $hours = round(floor($minutes / 60));
        $minutes = ($minutes % 60);
        if($only == 'm'){
            return $minutes;
        }elseif($only == 'h'){
            return $hours;
        }
        return sprintf($format, $hours, $minutes);
    }

    public function getPriorityLabel($priority){
        $priorities = Task::getPriorityList();
        return $priorities[$priority];
    }

    public function getDefaultStatus(){
        return TaskStatus::STATUS_TO_DO;
    }

    public function getFileLink($link){
        return '/' . $link;
    }

}
