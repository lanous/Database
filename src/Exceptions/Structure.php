<?php

namespace Lanous\Database\Exceptions;
class Structure extends \Exception {
    public const CONFIG = 900;
    public const DATABASE = 899;
    public const TABLE = 888;
    public function __construct(int $code, string $detail = "") {
        $this->code = $code;
        $this->message = $this->errorText($code)."\n".$detail;
    }
    private function errorText (int $code) : bool|string {
        if($code == self::CONFIG) {
            return "Error in config";
        } elseif($code == self::DATABASE) {
            return "Error in database";
        } elseif($code == self::TABLE) {
            return "Error in table";
        }
        return false;
    }
}