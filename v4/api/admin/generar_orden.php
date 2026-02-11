<?php
// api/admin/generar_orden.php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
include_once '../config/database.php';

$ticket_id = $_GET['id'] ?? 0;
// Recibimos el tipo. Ejemplo: generar_orden.php?id=1&tipo=instalacion_fo
$tipo = strtolower($_GET['tipo'] ?? 'inspeccion'); 

try {
    $db = (new Database())->getConnection();

    // 1. OBTENER DATOS
    $query = "SELECT t.*, c.name, c.cedula, c.phone, c.address, c.email, c.plan_name 
              FROM tickets t 
              JOIN clients c ON t.client_id = c.id 
              WHERE t.id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$ticket_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) die("Error: Ticket no encontrado.");

    // 2. CONFIGURACIÓN DE PLANTILLAS Y CELDAS
    // Aquí definimos qué archivo usar y dónde va la DIRECCIÓN (que es lo que varía)
    $config = [
        'inspeccion'      => ['file' => 'inspeccion.xlsx',      'celda_dir' => 'C14'],
        'instalacion_fo'  => ['file' => 'instalacion_fo.xlsx',  'celda_dir' => 'C13'],
        'instalacion_re'  => ['file' => 'instalacion_re.xlsx',  'celda_dir' => 'C13'],
        'migracion'       => ['file' => 'migracion.xlsx',       'celda_dir' => 'C14'],
        'retiro'          => ['file' => 'retiro.xlsx',          'celda_dir' => 'C13'],
        'evento'          => ['file' => 'evento.xlsx',          'celda_dir' => 'C16'],
        'soporte_fo'      => ['file' => 'soporte_fo.xlsx',      'celda_dir' => 'C13'], // Reparación FO
        'soporte_re'      => ['file' => 'soporte_re.xlsx',      'celda_dir' => 'C13'], // Reparación RE
        'cambio_dom_fo'   => ['file' => 'cambio_dom_fo.xlsx',   'celda_dir' => 'C13'],
        'cambio_dom_re'   => ['file' => 'cambio_dom_re.xlsx',   'celda_dir' => 'C13']
    ];

    if (!isset($config[$tipo])) die("Error: Tipo de orden '$tipo' no existe.");

    $archivo_destino = 'templates/' . $config[$tipo]['file'];
    $celda_direccion = $config[$tipo]['celda_dir'];

    if (!file_exists($archivo_destino)) {
        die("Error: No se encuentra el archivo $archivo_destino en el servidor.");
    }

    // 3. CARGAR EXCEL
    $spreadsheet = IOFactory::load($archivo_destino);
    $sheet = $spreadsheet->getActiveSheet();

    // 4. LLENAR DATOS COMUNES (Iguales en casi todas tus hojas)
    
    // Cabecera
    $sheet->setCellValue('J3', $data['id']);              // N° Orden
    $sheet->setCellValue('H5', date('d/m/Y'));            // Fecha Actual
    
    // Datos Cliente
    $sheet->setCellValue('C10', $data['name']);           // Nombre
    $sheet->setCellValue('H10', $data['cedula']);         // Cédula
    $sheet->setCellValue('C11', $data['phone']);          // Teléfono
    $sheet->setCellValue('C12', $data['email']);          // Email
    
    // Dirección (Variable según el archivo)
    $sheet->setCellValue($celda_direccion, $data['address']); 

    // Observaciones (Casi siempre es C25, pero en Migración es C24/C25, probamos C25 seguro)
    $sheet->setCellValue('C25', $data['description']); 

    // 5. DATOS ESPECÍFICOS (Solo para Instalaciones)
    if (strpos($tipo, 'instalacion') !== false) {
        $sheet->setCellValue('B21', $data['plan_name']); // Plan
    }
    
    // 6. DESCARGAR
    // Limpiamos cualquier salida previa (espacios en blanco, errores)
    if (ob_get_length()) ob_end_clean();

    $nombre_descarga = strtoupper($tipo) . "_" . preg_replace('/[^A-Za-z0-9]/', '', $data['name']) . ".xlsx";
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$nombre_descarga.'"');
    header('Cache-Control: max-age=0');

    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    echo "Error del sistema: " . $e->getMessage();
}