<?php

namespace Lanous\Database\Interfaces;
use \Lanous\Database\Maker\Where;

Interface Database {
    /**
     * Establishing a connection with the database
     */
    public function __construct ($host,$username,$password,$db_name);
    /**
     * Selecting a row from the data table
     * @param Where $where $where->where = [['column_name'=>$column_name,'comparison'=>$operator,'value'=>$value,?'logical'=>"and|or"],...]
     * @param string $columns "column1,column2,..." or null
     */
    public function Select (string $table_name,Where $where=null,string $columns=null) : array;
    /**
     * Inserting data into the data table
     * @param array $insert_data [['column_name'=>'column_value'],...]
     */
    public function Insert (string $table_name,array $insert_data) : bool|int;
    /**
     * Creating a data table
     */
    public function MakeTable (string $table_name,array $columns) : bool;
    /**
     * Checking the existence of a data table
     */
    public function TableExists (string $table_name) : bool;
    /**
     * Foriegn table
     */
    public function Foreign($table_name,$column_name,$table_ref,$column_ref) : bool;
    /**
     * Update Row
     * @param array $update_data [['column_name'=>'column_value'],...]
     * @param Where $where $where->where = [['column_name'=>$column_name,'comparison'=>$operator,'value'=>$value,?'logical'=>"and|or"],...]
     */
    public function Update($table_name,array $update_data,Where $where=null) : bool;
}