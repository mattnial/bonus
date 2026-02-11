<?php
// ARCHIVO: api/admin/get_alerts.php
ob_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0); // En producción 0, para debug puedes poner 1 temporalmente

include_once '../config/database.php';

$staff_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$role = isset($_GET['role']) ? $_GET['role'] : '';

try {
    $database = new Database();
    $db = $database->getCrmDB(); // Ahora si falla, el script muere en database.php con un JSON válido
    $dbBonus = $database->getBonusDB();

    $alerts = [];

    // --- REVISIÓN: Asegurar que las tablas existan antes de consultar ---

    // 1. TUS TICKETS ASIGNADOS
    // Verificamos tabla 'tickets'
    $qMy = "SELECT COUNT(*) as total FROM tickets WHERE assigned_to_staff_id = :id AND status != 'CERRADO'";
    $stmt = $db->prepare($qMy);
    $stmt->bindParam(":id", $staff_id);
    $stmt->execute();
    $myTickets = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    if ($myTickets > 0) {
        $alerts[] = [
            "type" => "info",
            "icon" => "fa-user-tag",
            "color" => "text-blue-500",
            "msg" => "Tienes $myTickets tickets asignados.",
            "action" => "MY_TICKETS",
            "count" => $myTickets
        ];
    }

    // 2. TICKETS URGENTES
    $qUrg = "SELECT COUNT(*) as total FROM tickets WHERE priority = 'URGENTE' AND status != 'CERRADO'";
    $stmtU = $db->query($qUrg);
    $urgent = $stmtU->fetch(PDO::FETCH_ASSOC)['total'];

    if ($urgent > 0) {
        $alerts[] = [
            "type" => "urgent",
            "icon" => "fa-exclamation-triangle",
            "color" => "text-red-600",
            "msg" => "¡Alerta! $urgent Tickets URGENTES.",
            "action" => "URGENT_TICKETS",
            "count" => $urgent
        ];
    }

    // 3. DEUDORES
    if (in_array($role, ['GERENCIA', 'COBROS', 'FACTURACION'])) {
        // Verificamos tabla 'clients'
        $qDebt = "SELECT COUNT(*) as total FROM clients WHERE current_debt_months > 0";
        $stmtD = $dbBonus->query($qDebt);
        $debtors = $stmtD->fetch(PDO::FETCH_ASSOC)['total'];

        if ($debtors > 0) {
            $alerts[] = [
                "type" => "warning",
                "icon" => "fa-hand-holding-usd",
                "color" => "text-purple-600",
                "msg" => "$debtors Clientes en mora.",
                "action" => "DEBTORS",
                "count" => $debtors
            ];
        }
    }

    ob_clean();
    echo json_encode($alerts);

} catch (Throwable $e) { // 'Throwable' atrapa tanto Exception como Error fatal
    ob_clean();
    http_response_code(500);
    echo json_encode(["error" => "Error en Alertas: " . $e->getMessage()]);
}
?>