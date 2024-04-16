<?php

namespace Lanous\Database;

class Connect extends Config {
    public function __construct($database) {
        $host = parent::getConfig("database","host");
        $username = parent::getConfig("database","username");
        $password = parent::getConfig("database","password");
        $db_name = parent::getConfig("database","db_name");
        $Reflection = new \ReflectionClass($database);
        if(!$Reflection->isSubclassOf("Lanous\Database\Interfaces\Database")) {
            throw new Exceptions\Structure(Exceptions\Structure::DATABASE,"Your database class must implements from [Lanous\Database\Interfaces\Database].");
        }
        parent::setConfig("database","__connector",new $database($host,$username,$password,$db_name));
    }
    public function AutoLoad (string $directory_name) {
        $directores = glob($directory_name."\*");
        foreach ($directores as $DIR_FILE) {
            if (is_dir($DIR_FILE)) {
                $this->AutoLoad ($DIR_FILE);
            } else {
                $fileExtension = pathinfo($DIR_FILE, PATHINFO_EXTENSION);
                if ($fileExtension == 'php') {
                    include_once($DIR_FILE);
                }
            }
        }
    }
}