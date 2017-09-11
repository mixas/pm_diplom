<?php

namespace Test\Model;

class Test
{

    protected $id;
    protected $name;

    public function exchangeArray(array $data){
        $this->id = $data['id'];
        $this->name = $data['name'];
    }

    public function getId(){
        return $this->id;
    }

    public function getName(){
        return $this->name;
    }

    public function settId($id){
        $this->id = $id;
        return $this;
    }

    public function setName($name){
        $this->name = $name;
        return $this;
    }

}