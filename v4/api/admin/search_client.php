<?php
// ARCHIVO: admin/api/admin/search_client.php (CORREGIDO)
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0); // Desactivar errores para asegurar JSON
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["status" => "error", "msg" => "Acceso denegado"]);
    exit;
}

// SOLO REQUERIMOS LA CLASE ÚNICA DE CONEXIÓN
require_once __DIR__ . '/../config/database.php'; 

$cedula = $_GET['cedula'] ?? '';
if (empty($cedula)) {
    echo json_encode(["status" => "error", "msg" => "Ingrese una cédula o RUC."]);
    exit;
}

try {
    // 1. INSTANCIACIÓN CORREGIDA: Llamamos a los métodos específicos
    $db_instance = new Database();
    $db_crm = $db_instance->getCrmDB(); // Conexión al CRM
    $db_bonus = $db_instance->getBonusDB(); // Conexión a Puntos/Cobros

    // --- 2. DATOS DEL CLIENTE (Desde vilcanet_bonus) ---
    $stmtClient = $db_bonus->prepare("SELECT id, name, email, phone, is_active_billing, last_payment_date FROM clients WHERE cedula = ?");
    $stmtClient->execute([$cedula]);
    $client = $stmtClient->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        echo json_encode(["status" => "not_found", "msg" => "Cliente no encontrado en la base de datos de Puntos."]);
        exit;
    }
    
    // --- 3. ESTADO DE MORA (Desde vilcanet_bonus - tabla clients_debt_status) ---
    $stmtDebt = $db_bonus->prepare("SELECT debt_months, status_color FROM clients_debt_status WHERE cedula = ?");
    $stmtDebt->execute([$cedula]);
    $debt = $stmtDebt->fetch(PDO::FETCH_ASSOC);
    
    // --- 4. TICKETS ABIERTOS (Desde vilcanet_crm) ---
    $stmtTickets = $db_crm->prepare("SELECT id FROM tickets WHERE client_cedula = ? AND status IN ('abierto', 'en_proceso', 'pendiente_cliente')");
    $stmtTickets->execute([$cedula]);
    $openTickets = $stmtTickets->rowCount();
    
    // --- 5. LÓGICA DEL SEMÁFORO (COLOR FINAL) ---
    $color = 'green';
    $statusText = 'OK / Sin reportes';
    
    if ($openTickets == 1) {
        $color = 'yellow';
        $statusText = 'Reporte activo (1 ticket)';
    } elseif ($openTickets > 1) {
        $color = 'blue';
        $statusText = 'Múltiples reportes activos';
    }
    
    // Sobreescribir por estados de Cobros
    if ($client['is_active_billing'] == 0) {
        $color = 'purple';
        $statusText = 'SERVICIO CORTADO / Suspendido';
    } elseif ($debt) {
        // Mora crítica (2 meses o más)
        $color = $debt['status_color']; // Naranja o Rojo
        $statusText = ($debt['debt_months'] >= 3) ? "DEUDA URGENTE ({$debt['debt_months']} meses)" : "Mora grave ({$debt['debt_months']} meses)";
    } 

    // --- 6. RESPUESTA ---
    echo json_encode([
        "status" => "ok",
        "data" => [
            "id" => $client['id'],
            "name" => $client['name'],
            "cedula" => $cedula,
            "email" => $client['email'],
            "phone" => $client['phone'],
            "open_tickets" => $openTickets,
            "service_status" => $client['is_active_billing'] ? 'Activo' : 'Suspendido',
            "mora" => $debt ? "{$debt['debt_months']} meses" : "Al día",
            "semaphore_color" => $color,
            "semaphore_text" => $statusText,
            "last_payment" => $client['last_payment_date']
        ]
    ]);

} catch (Exception $e) {
    // Captura errores de conexión o lógica (lanza el mensaje)
    echo json_encode(["status" => "error", "msg" => "Error interno: " . $e->getMessage()]);
}