<?php

use \Lanous\Database\Maker\Where;
use \Lanous\Database\Interfaces\Database;
class MyDatabase implements Database {
    private $database;
    public function __construct ($host, $username, $password, $db_name) {
        $this->database = new \PDO("mysql:host=$host;dbname=".$db_name, $username, $password);
        $this->database->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }
    public function Insert (string $table_name, array $insert_data) : bool|int {
        $column_name = array_keys($insert_data);
        $column_value = array_values($insert_data);
        array_walk($column_value,function (&$value,$key){
            $value = "'".$value."'";
        });
        $query = "INSERT INTO `".$table_name."`(".implode(",",$column_name).") VALUES (".implode(",",$column_value).")";
        if($this->database->exec($query)) {
            return $this->database->lastInsertId();
        } else {
            return false;
        }
    }
    public function Select (string $table_name, Where $where_list=null,string $columns=null): array {
        $query = "SELECT ".(($columns == null) ? "*" : $columns)." FROM `".$table_name."`";
        if(isset($where_list)) {
            $query .= " WHERE";
            foreach ($where_list->where as $where) {
                $column_name = $where['column_name'];
                $comparison = $where['comparison'];
                $value = $where['value'];
                $logical = $where['logical'] ?? "";
                $query .= " $logical $column_name $comparison '$value'";
            }
        }
        $query = $this->database->query($query);
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function MakeTable(string $table_name, array $columns): bool {
        $primary_column_name = null;
        $query = "CREATE TABLE IF NOT EXISTS `".$table_name."` (";
        foreach ($columns as $column_data) {
            $column_name = $column_data['column_name'];
            $column_type = $column_data['type'];
            $column_size = $column_data['size'];
            $is_primary = $column_data['is_primary'];
            $is_auto_increment = $column_data['auto_increment'];
            $is_not_null = $column_data['not_null'];
            $is_unique = $column_data['unique'];
            $default = $column_data['default'];

            if($is_primary == true) {
                $primary_column_name = $column_name;
            }

            $query .= "`$column_name` $column_type ";
            $query .= $column_size != null ? "($column_size) " : " ";
            $query .= ($is_auto_increment == true) ? "NOT NULL AUTO_INCREMENT " : "";
            $query .= ($is_auto_increment == false && $is_not_null == true) ? "NOT NULL " : "";
            $query .= ($is_auto_increment == false && $is_unique == true) ? "UNIQUE " : "";
            
            if ($default != null) {
                $is_function = $default[-1] == ")" and $default[-2] == "("; //example: CURRENT_DATE()
                $default = $is_function == true ? $default : "'".$default."'";
                $query .= " DEFAULT ".$default." ";
            }

            $query .= ",";
        }
        $query = rtrim($query," ,");
        if($primary_column_name) {
            $query .= ", PRIMARY KEY (`".$primary_column_name."`)";
        }
        $query .= ")";
        return $this->database->exec($query);
    }
    public function TableExists(string $table_name) : bool {
        $query = "SELECT * FROM `".$table_name."`";
        try {
            $query = $this->database->query($query);
            $query->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $error) {
            return false;
        }
        return true;
    }
    public function Foreign ($table_name,$column_name,$table_ref,$column_ref) : bool {
        $query = "ALTER TABLE ".$table_name;
        $query .= " ADD FOREIGN KEY (".$column_name.")";
        $query .= " REFERENCES ".$table_ref."(".$column_ref.")";
        return $this->database->exec($query);
    }
    public function Update ($table_name, array $update_data, Where $where_list=null): bool {
        $update = "";
        foreach ($update_data as $key=>$value) {
            $update .= "$key = '$value',";
        }
        $update = rtrim($update,",");
        $query = "UPDATE `".$table_name."` SET $update";
        if(isset($where_list)) {
            $query .= " WHERE";
            foreach ($where_list->where as $where) {
                $column_name = $where['column_name'];
                $comparison = $where['comparison'];
                $value = $where['value'];
                $logical = $where['logical'] ?? "";
                $query .= " $logical $column_name $comparison '$value'";
            }
        }
        return $this->database->exec($query);
    }
}

/*

*/