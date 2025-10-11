<?php
class Database {
    private $host = "127.0.0.1:3307";
    private $db   = "hr1_merch";
    private $user = "root";   // default XAMPP user
    private $pass = "";       // default walang password
    private $charset = "utf8mb4";
    public $pdo;

    public function getConnection() {
        if ($this->pdo) return $this->pdo;
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        $this->pdo = new PDO($dsn, $this->user, $this->pass, $opt);
        return $this->pdo;
    }
}
