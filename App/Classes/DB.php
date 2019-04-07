<?php

namespace App\Classes;

use App\Traits\SingletonTrait;
use \PDO;


class DB {
    use SingletonTrait;
    public $pdo;

    public function __construct() {
        $this->pdo = new PDO('mysql:dbname=' . DB_NAME . ';host=' . DB_HOST, DB_USER, DB_PASS);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getAmountRow($table) {
        return $this->pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
    }

    public function fetchAll($sql, $params = []) {
        $sth = $this->pdo->prepare($sql);
        $sth->execute($params);
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchOne($sql, $params = []) {
        return $this->fetchAll($sql, $params)[0] ?? null;
    }

    public function exec($sql, $params = []) {
        $sth = $this->pdo->prepare($sql);
        return $sth->execute($params);
    }
}