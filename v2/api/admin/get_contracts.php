<?php
// ARCHIVO: api/admin/get_contracts.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);

try {
    $directory = '../../assets/docs/generados/';
    $publicPath = 'assets/docs/generados/';
    
    if (!is_dir($directory)) {
        echo json_encode([]);
        exit;
    }

    $files = scandir($directory);
    $contracts = [];

    foreach ($files as $file) {
        // Filtramos solo los .docx y evitamos los puntos de sistema (. y ..)
        if ($file !== '.' && $file !== '..' && strpos($file, '.docx') !== false) {
            $filePath = $directory . $file;
            $timestamp = filemtime($filePath); // Fecha de creación/modificación
            
            // Extraer nombre limpio (si el formato es Contrato_CEDULA_TIMESTAMP.docx)
            $parts = explode('_', $file);
            $cedula = isset($parts[1]) ? $parts[1] : 'S/N';
            
            $contracts[] = [
                'name' => $file,
                'cedula' => $cedula,
                'url' => $publicPath . $file,
                'date' => date("d/m/Y H:i", $timestamp),
                'timestamp' => $timestamp,
                'size' => round(filesize($filePath) / 1024, 1) . ' KB'
            ];
        }
    }

    // Ordenar: Más recientes primero
    usort($contracts, function($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });

    echo json_encode($contracts);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>