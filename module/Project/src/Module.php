<?php

namespace Project;

class Module
{
    /**
     * Возвращает путь к module.config.php файлу.
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

}
