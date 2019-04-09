<?php


namespace App\Models;


use App\Classes\DB;

class Product extends Model {
    protected static $table = 'products';

    //добавление нового товара в базу данных
    public static function create($params) {
        $table = static::$table;
        return DB::getInstance()->exec("INSERT INTO $table (`name`, `description`, `price`, `image`)
             VALUES (:name, :description, :price, :image)", $params) ? DB::getInstance()->pdo->lastInsertId() : false;
    }
}
