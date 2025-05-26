<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'webpage';
    private $username = 'root';
    private $password = '';
    private $pdo;

    public function connect() {
        if ($this->pdo == null) {
            try {
                $this->pdo = new PDO("mysql:host=$this->host;dbname=$this->db_name;charset=utf8", $this->username, $this->password);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die('Connection error: ' . $e->getMessage());
            }
        }
        return $this->pdo;
    }
}
