<?php

namespace Project;

class Module
{
    /**
     * ���������� ���� � module.config.php �����.
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

}
