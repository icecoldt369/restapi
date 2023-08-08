<?php

class Database {
    private $host = 
    private $user = 
    private $passwd = 
    private $database = 
    public $conn;
    public function __construct() {
$opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
PDO::ATTR_EMULATE_PREPARES => false);
$this->conn = null;
try {
$this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' .
$this->database . ';charset=utf8mb4',
$this->user,$this->passwd,$opt);
} catch (PDOException $e) {
throw new Exception($e->getMessage(),500);
}
}
public function getConnection() {
        return $this->conn;
}
}

