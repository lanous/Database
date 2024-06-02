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
        $directores = glob($directory_name."/*");
        foreach ($directores as $DIR_FILE) {
            if (is_dir($DIR_FILE)) {
                $this->AutoLoad ($DIR_FILE);
            } else {
                $fileExtension = pathinfo($DIR_FILE, PATHINFO_EXTENSION);
                if ($fileExtension == 'php') {
                    // echo $DIR_FILE." Loaded!".PHP_EOL;
                    include_once($DIR_FILE);
                }
            }
        }
    }
    public function AutoMakeTables () {
        $AllClass = get_declared_classes();
        array_map(function ($class_name) {
            $Reflection = new \ReflectionClass($class_name);
            if($Reflection->isSubclassOf("\Lanous\Database\Abstracts\Table")) {
                $table_class = new $class_name();
                $table_class->__MAKE();
            }
        },$AllClass);
    }
    public function AutoConfigForiegns () {
        $AllClass = get_declared_classes();
        array_map(function ($class_name) {
            $Reflection = new \ReflectionClass($class_name);
            if($Reflection->isSubclassOf("\Lanous\Database\Abstracts\Table")) {
                $table_class = new $class_name();
                if(is_array($table_class->__references)) {
                    foreach ($table_class->__references as $column_name=>$reference) {
                        $table_class->__FOREIGN($reference['table_class'],$reference['column_name'],$column_name);
                    }
                }
            }
        },$AllClass);
    }
}