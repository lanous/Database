<?php
namespace ProjectName\Tables;

use \Lanous\Database\Abstracts\Table;
use \Lanous\Database\Maker\Where;
class Wallet extends Table {
    /**
     * Configuring and adding columns
     */
    public function __construct() {
        // Setting the name of the data table
        $this->table_name = "wallet";
        $this->Column ("id",
            type: 'BIGINT', size: 255, is_primary: true
        );
        $this->Column ("usd",
            type: 'BIGINT', size: 255, not_null: true
        );
        $this->Column ("irt",
            type: 'BIGINT', size: 255, not_null: true
        );
        $this->Column ("btc",
            type: 'BIGINT', size: 255, not_null: true
        );
        $this->__MAKE();
        $this->__FOREIGN(Users::class);
    }
    public function GetUserbyID (int $userID) : Information {
        return new Information($userID,$this);
    }
}

class Information extends Wallet {
    private $where;
    private $object;
    private $data;
    public function __construct(int $userID,Wallet $object) {
        $this->object = $object;
        $this->where = new Where("id","=",$userID);
        $this->data = $object->Select($this->where);
    }
    public function getFirstname() {
        return $this->object->parent['rows'][0]['first_name'];
    }
    public function getLastname() {
        return $this->object->parent['rows'][0]['last_name'];
    }
    public function getIRT() {
        return $this->data[0]['irt'];
    }
}