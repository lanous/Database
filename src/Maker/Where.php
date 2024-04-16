<?php
namespace Lanous\Database\Maker;
class Where {
    public $where;
    public function __construct($column_name,$operator,$value) {
        $this->where[] = [
            'column_name'=>$column_name,
            'comparison'=>$operator,
            'value'=>$value
        ];
    }
    public function AND($column_name,$operator,$value) {
        $this->where[] = [
            'column_name'=>$column_name,
            'comparison'=>$operator,
            'value'=>$value,
            'logical'=>"and"
        ];
    }
    public function OR($column_name,$operator,$value) {
        $this->where[] = [
            'column_name'=>$column_name,
            'comparison'=>$operator,
            'value'=>$value,
            'logical'=>"or"
        ];
    }
}