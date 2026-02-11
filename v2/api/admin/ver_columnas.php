<?php
// ARCHIVO: api/admin/ver_columnas.php
header("Content-Type: text/plain");
include_once '../../config/database.php';

$database = new Database();
$db = method_exists($database, 'getCrmDB') ? $database->getCrmDB() : $database->getConnection();

echo "--- COLUMNAS DE LA TABLA ticket_replies ---\n";
try {
    $q = $db->query("SHOW COLUMNS FROM ticket_replies");
    while($row = $q->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . "\n";
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>