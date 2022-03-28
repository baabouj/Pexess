<?php

namespace Pexess\Models;

use Pexess\Database\Database;

abstract class Model
{
//    protected bool $isPersisted = false;

    private function attributes(): array
    {
        $attributes = [];
        foreach (array_keys(get_class_vars(get_class($this))) as $attr) {
            $attributes[$attr] = $this->$attr;
        }
        return $attributes;
    }

    public function create(): bool
    {
        return Database::from($this->table())->create(["data" => $this->attributes()]);
    }

//    public function save(): bool
//    {
//        $attributes = [];
//        foreach (array_keys(get_class_vars(get_class($this))) as $attr) {
//            $attributes[$attr] = $this->$attr;
//        }
////        unset($attributes["isPersisted"]);
//        $db = Database::from($this->table());
//        if (!$this->isPersisted){
//            var_dump($attributes);
//            if ($db->create([
//                "data"=>$attributes
//            ])) $this->isPersisted = true;
//        }else{
//            var_dump($attributes);
////            unset($attributes["id"]);
//            $db->update([
//                "where"=>[
//                    "id"=>$this->id
//                ],
//                "data"=>$attributes
//            ]);
//        }
//        return true;
//
//    }

    abstract public function schema(): array;
}