<?php


namespace App\Models;


use App\Classes\DB;

//это вот такой вот наспех вместо фреймворашной красаты

class User extends Model {
    protected static $table = 'users';

    //создание нового пользователя в $params должно быть [name, login, password]
    public static function createUser($params) {
        $table = static::$table;
        return DB::getInstance()->exec("INSERT INTO $table (`name`, `login`, `password`) VALUES (?, ?, ?)", $params);
    }

    //проверка на существование зарегистрированного пользователя
    public static function userIsRegistred($params) {
        $table = static::$table;
        return DB::getInstance()->fetchOne("SELECT * FROM $table WHERE login = ? AND password = ?", $params);
    }

    //проверка на галичие повышенных привелегий у текущего пользователя в сессии
    public static function isAdmin() {
        $userRole = (int)$_SESSION['login']['role'];
        return $userRole ? true : false;
    }


    //получение списка заказов всех пользователей
    public static function fetchOrders() {
        return DB::getInstance()->fetchAll("SELECT * FROM `orders`");
    }

    //получение списка заказов не всех пользователей
    public static function fetchUserOrders($params) {
        return DB::getInstance()->fetchAll("SELECT * FROM `orders` WHERE `user_id` = ?", $params);
    }

    //получение комбинированного массива из двух таблиц (заказов и продуктов)
    public static function fetchOrderProducts($params) {
        return DB::getInstance()->fetchAll("SELECT * FROM orders_products as op JOIN products as p 
        ON p.id = op.product_id WHERE `op`.`order_id` = ?", $params);
    }

    //создание нового заказа из корзины и возвращение идентификатора заказа созданного автоматически субд
    public static function insertOrder($params) {
        return DB::getInstance()->exec("INSERT INTO `orders` (`user_id`) VALUES (?)", $params)
            ? DB::getInstance()->pdo->lastInsertId() : null; //кто бы мог подумать, что без lastInsertId вместо
        //вставленного автоключа, как было раньше,  возвращается 1
    }

    //создание записи о связях во вспомогательной таблице после создания нового заказа
    public static function insertOrderProducts($sql) {
        return DB::getInstance()->exec($sql);
    }

    //удаление заказа
    public static function delOrder($params) {
        return DB::getInstance()->exec("DELETE FROM orders WHERE id = ?", $params) &&
        DB::getInstance()->exec("DELETE FROM orders_products WHERE order_id = ?", $params) ? 1 : 0;
    }

    //получение текущего статуса заказа
    public static function currentOrderStatus($params) {
        return DB::getInstance()->fetchOne("SELECT * FROM orders WHERE id = ?", $params)['status'];
    }

    //изменение статуса заказа
    public static function updateOrderStatus($params) {
        return DB::getInstance()->exec("UPDATE orders SET status = ? WHERE id = ?", $params);
    }
}