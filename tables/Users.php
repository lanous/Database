<?php
namespace ProjectName\Tables;

use \Lanous\Database\Abstracts\Table;
use \Lanous\Database\Maker\Where;
class Users extends Table {
    /**
     * Configuring and adding columns
     */
    public function __construct() {
        // Setting the name of the data table
        $this->table_name = "users";
        // Add columns and config:
        $this->Column ("id",
            type: 'BIGINT', auto_increment : true, is_primary: true
        );
        $this->Column ("first_name",
            type: 'VARCHAR', size: 255, not_null: true, unique: true
        );
        $this->Column ("last_name",
            type: 'VARCHAR', size: 255, not_null: true, unique: true
        );
        $this->Column ("data",
            type: 'JSON', not_null: true
        );
        $this->__MAKE();
    }
    /**
     * Input function, data passes through this path before entering the data table
     */
    public function __INSERT(string $column_name, mixed $value) : string {
        if($column_name == "first_name" or $column_name == "last_name") {
            return strtolower($value);
        }
        if($column_name == "data") {
            return json_encode($value);
        }
        return $value;
    }
    /**
     * Output function, data passes through here after being retrieved from the database
     */
    public function __RETRIEVE(string $column_name, string $value) {
        if($column_name == "data") {
            return json_decode($value,1);
        }
        return $value;
    }

    # ---- Methods:

    public function GetUserbyID (int $userID) : array|bool {
        $where = new Where("id","=",$userID);
        $data = $this->Select($where);
        return $data[0] ?? false;
    }
    public function GetUserbyName (string $first_name,string $last_name) : array|bool {
        $where = new Where("first_name","=",$first_name);
        $where->And("last_name","=",$last_name);
        $data = $this->Select($where);
        return $data[0] ?? false;
    }

    public function AddUser (string $first_name,string $last_name,array $data) : bool|int {
        if(!$this->GetUserbyName ($first_name,$last_name)) {
            return $this->Insert(get_defined_vars());
        }
        return false;
    }

    public function ChangeName(int $UserID,string $new_first_name,string $new_last_name) : bool {
        $where = new Where("id","=",$UserID);
        return $this->Update(['first_name'=>$new_first_name,'last_name'=>$new_last_name],$where);
    }
}