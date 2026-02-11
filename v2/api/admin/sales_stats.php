<?php
// ARCHIVO: api/admin/sales_stats.php
ob_start();
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);

include_once '../config/database.php';

try {
    $user_id = $_SESSION['user_id'] ?? 0;
    $database = new Database();
    $db = method_exists($database, 'getCrmDB') ? $database->getCrmDB() : $database->getConnection();

    // 1. Prospectos (Leads) - Igual que antes
    $sqlLeads = "SELECT COUNT(*) FROM tickets 
                 WHERE assigned_to_staff_id = :uid 
                 AND status != 'CERRADO' 
                 AND status != 'RESUELTO'";
    $stmt1 = $db->prepare($sqlLeads);
    $stmt1->execute([':uid' => $user_id]);
    $pending_leads = $stmt1->fetchColumn();

    // 2. CONTRATOS GENERADOS ESTE MES (Escaneando carpeta)
    $directory = '../../assets/docs/generados/';
    $month_contracts = 0;
    
    if (is_dir($directory)) {
        $files = scandir($directory);
        $currentMonth = date('Y-m');
        
        foreach ($files as $file) {
            if (strpos($file, '.docx') !== false) {
                // Verificamos la fecha del archivo
                if (date('Y-m', filemtime($directory . $file)) === $currentMonth) {
                    $month_contracts++;
                }
            }
        }
    }

    ob_clean();
    echo json_encode([
        "pending_leads" => $pending_leads,
        "month_sales"   => $month_contracts // Ahora esto es "Contratos del Mes"
    ]);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(["pending_leads" => 0, "month_sales" => 0]);
}
?>