<?php

namespace Lanous\Database\Abstracts;
use \Lanous\Database\Config;
use \Lanous\Database\Maker\Where;
use \Lanous\Database\Exceptions;

abstract class Table extends Config {
    public string $table_name = '';
    private $__columns;
    private array $__parent = [];
    public array $parent = [];
    
    /**
     * Setting the data table columns
     */
    final public function Column (string $column_name,string $type,string|int $size=null,bool $is_primary=false,bool $auto_increment=false,bool $not_null=false,bool $unique=false,string $default=null) {
        // Do not change the name of the parameters
        // it forms an array through the name of the parameter
        $this->__VALIDATION();
        $this->__columns[$column_name] = get_defined_vars();
    }
    /**
     * Extract Row
     */
    public function Select (Where $where=null,string $columns=null) : array {
        // Face: Select (string $table_name,Where $where=null,string $columns=null) : array
        $this->__VALIDATION();
        $database = Config::getConfig("database","__connector");
        $result = $database-> Select ($this->table_name,$where,$columns);
        foreach($result as $key=>$data) {
            array_walk ($data,function ($value,$column_name) use (&$result,$key) {
                $result[$key][$column_name] = $this->__RETRIEVE($column_name,$value);
            });
            $this->__MakeParent ($data);
        }
        return $result;
    }
    /**
     * Update a row
     */
    public function Update (array $update_data,Where $where=null) {
        //Face: Update ($table_name, array $update_data, Where $where_list=null)
        $this->__VALIDATION();
        array_walk($update_data,function (&$column_value,$column_name) {
            $column_value = $this->__INSERT($column_name,$column_value);
        });
        $database = Config::getConfig("database","__connector");
        return $database-> Update ($this->table_name,$update_data,$where);

    }
    /**
     * Insert a data
     * @param array $insert_data [['column_name'=>'column_value'],...]
     */
    public function Insert (array $insert_data) : bool|int {
        // Face: Insert (string $table_name,array $insert_data) : bool|int
        $this->__VALIDATION();
        array_walk($insert_data,function (&$column_value,$column_name) {
            $column_value = $this->__INSERT($column_name,$column_value);
        });
        $database = Config::getConfig("database","__connector");
        return $database-> Insert ($this->table_name,$insert_data);
    }
    private function __VALIDATION () {
        if (is_null($this->table_name) or $this->table_name == '') {
            throw new Exceptions\Structure(Exceptions\Structure::TABLE,"\$this->table_name property must be set in the constructor.");
        }
    }
    /**
     * Forign setting for data table
     */
    private function __MakeParent ($data) {
        if ($this->__parent != []) {
            $table_class_ref = $this->__parent["class_name"];
            if(!isset($this->parent['class'])) {
                $this->parent['class'] = $table_class_ref;
            }
            $PrimaryKey_Parent = $table_class_ref->__PRIMARY();
            $PrimaryKey_this = $this->__PRIMARY();
            $PrimaryValue_this = $data[$PrimaryKey_this];
            $Where = new Where($PrimaryKey_Parent,"=",$PrimaryValue_this);
            $ParentData = $table_class_ref->Select($Where);
            $this->parent['rows'][] = $ParentData[0];
        }
    }
    /**
     * Set foreign and reference to table (need primary key)
     */
    final public function __FOREIGN(string $table_class) : string|bool {
        // Face: Foreign($table_name,$column_name,$table_ref,$column_ref)
        $this->__VALIDATION();
        $table_class_ref = new $table_class();
        $table_name_ref = $table_class_ref->table_name;
        $Reflection = new \ReflectionClass($table_class_ref);
        $ParentClass = $Reflection?->getParentClass();
        $ParentClassName = $ParentClass->getName() ?? false;
        if($ParentClassName != __CLASS__) {
            throw new Exceptions\Structure(Exceptions\Structure::DATABASE,"Your database class must extends from [".__CLASS__."]");
        }
        $PrimaryKey_Parent = $table_class_ref->__PRIMARY();
        $PrimaryKey_this = $this->__PRIMARY();
        $database = Config::getConfig("database","__connector");
        $this->__parent = ["class_name"=>$table_class_ref,"column_name"=>$PrimaryKey_Parent];
        return $database-> Foreign($this->table_name,$PrimaryKey_this,$table_name_ref,$PrimaryKey_Parent);
    }
    /**
     * Get primary column name
     */ 
    final public function __PRIMARY () : string|bool {
        $this->__VALIDATION();
        foreach($this->__columns as $column_name=>$column_data) {
            if($column_data["is_primary"] == true) {
                return $column_name;
            }
        }
        return false;
    }
    /**
     * Insert|Update Handler
     */
    public function __INSERT(string $column_name,mixed $value) : string {
        return $value;
    }
    /**
     * Select Handler
     */
    public function __RETRIEVE(string $column_name,string $value) {
        return $value;
    }
    /**
     * Make Table if not exists
     */
    final public function __MAKE () : bool {
        // Face: MakeTable (string $table_name,array $columns) : bool
        if($this->__EXISTS() == false) {
            $database = Config::getConfig("database","__connector");
            $data = $database-> MakeTable ($this->table_name,$this->__columns);
            return $data;
        } else {
            return true;
        }
    }
    /**
     * Checking the existence of data table
     */
    private function __EXISTS () : bool {
        // Face: TableExists (string $table_name) : bool
        $this->__VALIDATION();
        $database = Config::getConfig("database","__connector");
        $data = $database-> TableExists ($this->table_name);
        return $data;
    }
}