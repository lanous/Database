<?php

namespace Lanous\Database\Abstracts;
use \Lanous\Database\Config;
use \Lanous\Database\Maker\Where;
use \Lanous\Database\Maker\ReturnRow;
use \Lanous\Database\Exceptions;

abstract class Table extends Config {
    public string $table_name = '';
    public $__columns;
    private $__rows;
    private $__autofill;
    public $__references;
    
    /**
     * Setting the data table columns
     */
    final public function Column (string $column_name,string $type,string|int $size=null,bool $is_primary=false,bool $auto_increment=false,bool $not_null=false,bool $unique=false,string $default=null) {
        // Do not change the name of the parameters
        // it forms an array through the name of the parameter
        $this->__VALIDATION();
        $this->__EXCEPTION_COLUMN_EXISTS ($column_name);
        $this->__columns[$column_name] = get_defined_vars();
    }
    /**
     * Extract Rows
     */
    final public function Select (Where $where=null,string $columns=null) : ReturnRow {
        // Face: Select (string $table_name,Where $where=null,string $columns=null) : array
        $this->__VALIDATION();
        $database = Config::getConfig("database","__connector");
        $result = $database-> Select ($this->table_name,$where,$columns);
        foreach($result as $key=>$data) {
            array_walk ($data,function ($value,$column_name) use (&$result,$key,$data) {
                $result[$key][$column_name] = $this->__RETRIEVE($column_name,$value);
            });
        }
        $this->__rows = $result;
        return new ReturnRow($result,$this);
    }
    /**
     * Update a row
     */
    final public function Update (array $update_data,Where $where=null) : bool|array {
        //Face: Update ($table_name, array $update_data, Where $where_list=null)
        $this->__VALIDATION();
        array_walk($update_data,function (&$column_value,$column_name) {
            $this->__EXCEPTION_COLUMN_NOTEXISTS ($column_name);
            $column_value = $this->__INSERT($column_name,$column_value);
        });
        $database = Config::getConfig("database","__connector");
        if($database-> Update ($this->table_name,$update_data,$where)) {
            return $update_data;
        } else {
            return false;
        }
    }
    /**
     * Insert a data
     * @param array $insert_data [['column_name'=>'column_value'],...]
     */
    final public function Insert (array $insert_data) : bool|int {
        // Face: Insert (string $table_name,array $insert_data) : bool|int
        $this->__VALIDATION();
        if (is_array($this->__autofill)) {
            $insert_data = array_merge($insert_data,$this->__autofill);
        }
        array_walk($insert_data,function (&$column_value,$column_name) {
            $this->__EXCEPTION_COLUMN_NOTEXISTS ($column_name);
            $column_value = $this->__INSERT($column_name,$column_value);
        });
        $database = Config::getConfig("database","__connector");
        return $database-> Insert ($this->table_name,$insert_data);
    }

    final public function InsertArgs (...$insert_data) : bool|int {
        $this->__VALIDATION();
        return $this->Insert($insert_data);
    }


    /**
     * Forign setting for data table
     */
    final public function __SetReference (string $column_name,string $table_reference,string $column_name_reference) : void {
        $this->__EXCEPTION_COLUMN_NOTEXISTS ($column_name);
        $this->__references[$column_name] = ["table_class"=>$table_reference,"column_name"=>$column_name_reference];
    }
    final public function __GetReference ($column_name) : object|array {
        $this->__EXCEPTION_COLUMN_NOTEXISTS ($column_name);
        $reference = $this->__references[$column_name] ?? throw new Exceptions\Structure(Exceptions\Structure::TABLE,"No reference has been set for this column.");
        if(!is_array($this->__rows)) {
            throw new Exceptions\Structure(Exceptions\Structure::TABLE,"Before using this method, it is necessary to take an output from the table.");
        }
        $ids = [];
        foreach ($this->__rows as $value) {
            $ids[] = $value[$column_name];
        }
        $ids = array_unique($ids);
        $Where = new Where($reference["column_name"],"=",$ids[0]);
        unset($ids[0]);
        if(count($ids) != null) {
            foreach ($ids as $value) {
                $Where->OR($reference["column_name"],"=",$value);
            }
        }
        $reference_table = new $reference['table_class'];
        $reference_result = $reference_table->Select($Where);
        return $reference_result;
    }
    /**
     * Set foreign and reference to table (need primary key)
     */
    final public function __FOREIGN(string $table_reference,string $column_reference,string $column_name) : string|bool {
        // Face: Foreign($table_name,$column_name,$table_ref,$column_ref)
        $this->__VALIDATION();

        $table_class_ref = new $table_reference();
        $table_name_ref = $table_class_ref->table_name;
        $Reflection = new \ReflectionClass($table_class_ref);
        if($Reflection->getParentClass()?->getName() != __CLASS__) {
            throw new Exceptions\Structure(Exceptions\Structure::DATABASE,"Your table reference class must extends from [".__CLASS__."] - extended : ".$Reflection->getParentClass()?->getName() ?? "NO EXTENDED");
        }
        $database = Config::getConfig("database","__connector");

        if(!isset($this->__columns[$column_name])) {
            $ReflectionThis = new \ReflectionClass($this);
            throw new Exceptions\Structure(Exceptions\Structure::TABLE,"ReferenceError: The column you intend to set a reference for has not been configured in the table class.\n".$ReflectionThis->getShortName().":".$column_name);
        } elseif (!isset($table_class_ref->__columns[$column_reference])) {
            $ReflectionThis = new \ReflectionClass($this);
            throw new Exceptions\Structure(Exceptions\Structure::TABLE,"ReferenceError: The column you want to reference is not defined in the data table.\nThe ".$Reflection->getShortName().":".$column_reference." column does not exist to set a reference for the ".$ReflectionThis->getShortName().":".$column_name." column");
        } elseif ($table_class_ref->__columns[$column_reference]["type"] != $this->__columns[$column_name]["type"]) {
            $ReflectionThis = new \ReflectionClass($this);
            throw new Exceptions\Structure(Exceptions\Structure::TABLE,"ReferenceError: The data types defined in the classes table are not consistent.\n".$ReflectionThis->getShortName().":".$column_name." (".$this->__columns[$column_name]["type"].") != ".$Reflection->getShortName().":".$column_reference." (".$table_class_ref->__columns[$column_reference]["type"].")");
        }
        
        return $database-> Foreign($this->table_name,$column_name,$table_name_ref,$column_reference);
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
    public function __INSERT(string $column_name,mixed $value) : null|string {
        return $value;
    }
    /**
     * Select Handler
     */
    public function __RETRIEVE(string $column_name,null|string $value) {
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
    final public function __WHEREPRIMARY ($value) : Where {
        return new Where($this->__PRIMARY(),"=",$value);
    }
    final public function __VALUE(string $column_name,callable $value) : void {
        $this->__EXCEPTION_COLUMN_NOTEXISTS ($column_name);
        $this->__autofill[$column_name] = $value();
    }
    final public function __UPDATE_FOR_ROWRETURN (array $rows,array $columns_data) {
        foreach ($rows as $key=>$row) {
            if(!is_array($row)) {
                $where = $this->__MAKE_WHERE_FOR_UPDATE_ROW_RETURN ($rows);
                $result = $this->Update($columns_data,$where);
                return ($result == false) ? false : array_merge($rows,$result);
            } else {
                $where = $this->__MAKE_WHERE_FOR_UPDATE_ROW_RETURN ($row);
                $result = $this->Update($columns_data,$where);
                if($result != false) {
                    $row[$key] = array_merge($rows,$result);
                } else {
                    return false;
                }
            }
        }
        return $row;
    }
    private function __MAKE_WHERE_FOR_UPDATE_ROW_RETURN ($row) : Where {
        $primary = $this->__PRIMARY();
        if($primary != false) {
            return new Where($primary,"=",$row[$primary]);
        } else {
            $where = null;
            foreach ($row as $key=>$value) {
                if ($where == null) {
                    $where = new Where($key,"=",$value);
                } else {
                    $where->AND($key,"=",$value);
                }
            }
            return $where;
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
    private function __VALIDATION () {
        if (is_null($this->table_name) or $this->table_name == '') {
            throw new Exceptions\Structure(Exceptions\Structure::TABLE,"\$this->table_name property must be set in the constructor.");
        }
    }
    private function __EXCEPTION_COLUMN_NOTEXISTS (string $column_name) : void {
        if(!isset($this->__columns[$column_name])) {
            $ReflectionThis = new \ReflectionClass($this);
            throw new Exceptions\Structure(Exceptions\Structure::TABLE,"The column ".$ReflectionThis->getShortName().":".$column_name." is not defined.");
        }
    }
    private function __EXCEPTION_COLUMN_EXISTS (string $column_name) : void {
        if(isset($this->__columns[$column_name])) {
            $ReflectionThis = new \ReflectionClass($this);
            throw new Exceptions\Structure(Exceptions\Structure::TABLE,"The column ".$ReflectionThis->getShortName().":".$column_name." is defined.");
        }
    }
}