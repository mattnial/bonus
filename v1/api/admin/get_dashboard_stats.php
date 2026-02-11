<?php
// ARCHIVO: api/admin/get_dashboard_stats.php
ob_start();
session_start(); // Vital para saber quién eres

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);
error_reporting(E_ALL);

include_once '../config/database.php';

try {
    $database = new Database();
    $db = method_exists($database, 'getConnection') ? $database->getConnection() : $database->getCrmDB();

    $user_id = $_SESSION['user_id'] ?? 0;
    $role = strtoupper($_SESSION['role'] ?? '');

    // 1. DATOS GLOBALES (Para Gerencia) - ESTO NO SE TOCA
    // Se calculan siempre para que no falten datos si el frontend los pide
    $active  = $db->query("SELECT COUNT(*) FROM clients WHERE service_status = 'ACTIVO'")->fetchColumn();
    $tickets = $db->query("SELECT COUNT(*) FROM tickets WHERE status = 'ABIERTO'")->fetchColumn();
    $urgent  = $db->query("SELECT COUNT(*) FROM tickets WHERE priority = 'URGENTE' AND status != 'CERRADO'")->fetchColumn();
    $debt    = $db->query("SELECT COUNT(*) FROM clients WHERE service_status = 'CORTADO'")->fetchColumn();

    // 2. DATOS ESPECÍFICOS (Para Técnicos)
    $my_tickets = 0;
    $my_resolved = 0;

    if (strpos($role, 'TECNICO') !== false || strpos($role, 'SOPORTE') !== false) {
        // Pendientes: Asignados a mí y que NO estén cerrados ni resueltos
        $stmt = $db->prepare("SELECT COUNT(*) FROM tickets WHERE assigned_to_staff_id = :uid AND status != 'CERRADO' AND status != 'RESUELTO'");
        $stmt->execute([':uid' => $user_id]);
        $my_tickets = $stmt->fetchColumn();

        // Resueltos: Asignados a mí y que ESTÉN Resueltos o Cerrados
        $stmt2 = $db->prepare("SELECT COUNT(*) FROM tickets WHERE assigned_to_staff_id = :uid AND (status = 'RESUELTO' OR status = 'CERRADO')");
        $stmt2->execute([':uid' => $user_id]);
        $my_resolved = $stmt2->fetchColumn();
    }

    // 3. RESPUESTA UNIFICADA
    // Enviamos las claves duplicadas para asegurar compatibilidad con AMBOS dashboards
    echo json_encode([
        // Para Gerencia (arregla que no carguen los urgentes)
        "clients" => $active,        "clients_active" => $active,
        "tickets" => $tickets,       "tickets_open" => $tickets,
        "urgent"  => $urgent,        "tickets_urgent" => $urgent, 
        "debt"    => $debt,          "clients_debt" => $debt,

        // Para Técnico
        "my_tickets" => $my_tickets,
        "my_resolved" => $my_resolved
    ]);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>