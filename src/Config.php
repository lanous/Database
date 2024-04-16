<?php

namespace Lanous\Database;

class Config {
    private static array $config = [];
    private function __construct() {}
    private function __clone() {}
    public static function getConfig (string $branch,string $key) {
        if(!isset(self::$config[$branch][$key])) {
            throw new Exceptions\Structure(Exceptions\Structure::CONFIG,"There is no data in this route - config -> ".$branch." -> ".$key);
        }
        return self::$config[$branch][$key];
    }
    public static function setConfig (string $branch,string $key,mixed $value) {
        if(isset(self::$config[$branch][$key])) {
            throw new Exceptions\Structure(Exceptions\Structure::CONFIG,"Apply new data to previous data - config -> ".$branch." -> ".$key);
        }
        self::$config[$branch][$key] = $value;
    }
}