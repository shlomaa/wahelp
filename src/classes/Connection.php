<?php

namespace classes;

use PDO;

class Connection {

    private $conn;

    public function __construct() {
        $host = Application::$config['db']['host'];
        $dbname = Application::$config['db']['database'];
        $user = Application::$config['db']['user'];
        $password = Application::$config['db']['password'];
        $type = Application::$config['db']['type'];

        $this->conn = new PDO("$type:host=$host;dbname=$dbname", $user, $password);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function makeRequest($sql, $args = []) {
        $sth = $this->conn->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        $sth->execute($args);

        return $sth->fetchAll();
    }
}
