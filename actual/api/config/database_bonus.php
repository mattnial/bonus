<?php
// ARCHIVO: api/config/database.php
class Database {
    private $host = "localhost";
    private $db_name = "vilcanet_bonus";
    private $username = "vilcanet_bonus";
    private $password = "W8JNZXGAGgtfHKYnkPsr"; //

    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // ERROR CRÍTICO SOLUCIONADO:
            // No devolver null. Matar el proceso y enviar JSON.
            header("Content-Type: application/json; charset=UTF-8");
            http_response_code(500);
            echo json_encode(["error" => "Error de conexión BD: " . $exception->getMessage()]);
            exit; 
        }
        return $this->conn;
    }

    public function getCrmDB() { return $this->getConnection(); }
    public function getBonusDB() { return $this->getConnection(); }
}
?>