<?php
// ARCHIVO: public_html/api/config/database.php
class Database {
    private $host = "localhost";
    private $db_name = "vilcanet_bonus"; 
    private $username = "root";
    private $password = ""; 

    public $conn;

    public function getConnection() {
        date_default_timezone_set('America/Guayaquil');
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->exec("SET time_zone = '-05:00'");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // ESTO ES LO QUE FALTA PARA EVITAR EL NetworkError
            header("Access-Control-Allow-Origin: *"); 
            header("Content-Type: application/json");
            http_response_code(500);
            echo json_encode(["error" => "Error de Conexión BD: " . $exception->getMessage()]);
            exit;
        }
        return $this->conn;
    }

    public function getCrmDB() { return $this->getConnection(); }
    public function getBonusDB() { return $this->getConnection(); }
}