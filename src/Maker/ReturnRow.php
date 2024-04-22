<?php
namespace Lanous\Database\Maker;
class ReturnRow implements \ArrayAccess {
    private $rows;
    private $table_class;
    public bool $is_have;
    public function __construct(array $rows,object $table_class) {
        $this->rows = $rows;
        $this->is_have = (count($rows) > 0) ? true : false;
        $this->table_class = $table_class;
    }
    public function Last () : object|array {
        return new ReturnRow($this->rows[array_key_last($this->rows)],$this->table_class);
    }
    public function First () : object|array {
        return new ReturnRow($this->rows[array_key_first($this->rows)],$this->table_class);
    }
    public function Find ($column_name,$column_value) : object|array {
        return new ReturnRow($this->rows[array_search($column_value,array_column($this->rows,$column_name))],$this->table_class);
    }
    public function Filter (callable|null $callback) : object|array {
        return new ReturnRow(array_filter($this->rows,$callback),$this->table_class);
    }
    public function Count() : int {
        return count($this->rows);
    }
    public function Map3D(callable|null $callback) : object|array {
        $result = [];
        foreach ($this->rows as $row=>$values) {
            foreach($values as $key=>$value) {
                $result[$row][$key] = $callback($row,$key,$value) ?? $value;
            }
        }
        return new ReturnRow($result,$this->table_class);
    }
    public function Map2D(callable|null $callback) : object|array  {
        $result = [];
        foreach($this->rows as $key=>$value) {
            $result[$key] = $callback($key,$value) ?? $value;
        }
        return new ReturnRow($result,$this->table_class);
    }
    public function Update(...$columns) {
        return new ReturnRow($this->table_class->__UPDATE_FOR_ROWRETURN($this->rows,$columns),$this->table_class);
    }
    public function ToArray () {
        return $this->rows;
    }
    public function Paging(int $page,int $per_page) : array|bool {
        return array_chunk($this->rows,$per_page)[$page] ?? false;
    }

    public function offsetSet(mixed $offset, mixed $value): void {
        if (is_null($offset)) {
            $this->rows[] = $value;
        } else {
            $this->rows[$offset] = $value;
        }
    }

    public function offsetExists(mixed $offset) : bool {
        return isset($this->rows[$offset]);
    }

    public function offsetUnset(mixed $offset) : void {
        unset($this->rows[$offset]);
    }

    public function offsetGet(mixed $offset) : mixed {
        return isset($this->rows[$offset]) ? $this->rows[$offset] : null;
    }
}