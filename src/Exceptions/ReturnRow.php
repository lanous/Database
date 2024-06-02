<?php

namespace Lanous\Database\Exceptions;
class ReturnRow extends \Exception {
    public const NO_DATA = 900;
    public function __construct(int $code, string $detail = "") {
        $this->code = $code;
        $this->message = $this->errorText($code)."\n \n".$detail."\n \n";
    }
    private function errorText (int $code) : bool|string {
        if($code == self::NO_DATA) {
            return "no data found";
        }
        return false;
    }
}