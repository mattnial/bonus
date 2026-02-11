<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include_once '../config/database.php';

try {
    $db = (new Database())->getConnection();
    // Últimos 50 movimientos
    $sql = "SELECT m.*, i.serial_number, i.type as item_type, 
            a.name as admin_name, t.name as tech_name, c.name as client_name
            FROM inventory_movements m
            JOIN inventory i ON m.inventory_id = i.id
            LEFT JOIN staff a ON m.admin_id = a.id
            LEFT JOIN staff t ON m.tech_id = t.id
            LEFT JOIN clients c ON m.client_id = c.id
            ORDER BY m.created_at DESC LIMIT 50";
            
    echo json_encode($db->query($sql)->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) { echo json_encode([]); }
?>