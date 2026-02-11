<?php
// ARCHIVO: api/admin/create_contract.php
// VERSIÓN: HÍBRIDA (Crea Nuevos o Renueva Existentes)

ob_start(); 
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 0);
include_once '../config/database.php';

// --- FUNCIONES ---
function fechaEspanol($fecha) {
    $meses = ["Jan"=>"Enero", "Feb"=>"Febrero", "Mar"=>"Marzo", "Apr"=>"Abril", "May"=>"Mayo", "Jun"=>"Junio", "Jul"=>"Julio", "Aug"=>"Agosto", "Sep"=>"Septiembre", "Oct"=>"Octubre", "Nov"=>"Noviembre", "Dec"=>"Diciembre"];
    $f = new DateTime($fecha);
    return $f->format('d') . " de " . $meses[$f->format('M')] . " del " . $f->format('Y');
}

$input = file_get_contents("php://input");
$data = json_decode($input);

if(empty($data->cedula) || empty($data->nombre)) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Faltan datos del cliente"]);
    exit;
}

try {
    $database = new Database();
    $db = method_exists($database, 'getCrmDB') ? $database->getCrmDB() : $database->getConnection();

    // CALCULAR FECHAS DE CONTRATO
    $hoy = date("Y-m-d");
    $mesesContrato = intval($data->tiempo); // 12, 24, 36
    $fin = date("Y-m-d", strtotime("+$mesesContrato months"));

    // 1. GESTIÓN DE CLIENTE (Lógica Híbrida)
    $check = $db->prepare("SELECT id FROM clients WHERE cedula = :cedula");
    $check->execute([':cedula' => $data->cedula]);
    
    if($check->rowCount() > 0) {
        // --- CASO: CLIENTE YA EXISTE ---
        $clientId = $check->fetchColumn();

        // Validar si es Renovación
        if ($data->tramite === 'RENOVACION') {
            // Actualizamos fechas de contrato + datos personales
            $sql = "UPDATE clients SET 
                    name=:n, address=:a, phone=:p, email=:e, 
                    contract_date=:inicio, contract_end_date=:fin
                    WHERE id=:id";
            
            $db->prepare($sql)->execute([
                ':n'=>$data->nombre, ':a'=>$data->direccion, ':p'=>$data->celular, ':e'=>$data->email, 
                ':inicio'=>$hoy, ':fin'=>$fin,
                ':id'=>$clientId
            ]);
        } else {
            // Si intenta hacer instalación nueva sobre uno existente (por seguridad)
            // Solo actualizamos contacto, no fechas (a menos que lo especifiques)
            $sql = "UPDATE clients SET name=:n, address=:a, phone=:p, email=:e WHERE id=:id";
            $db->prepare($sql)->execute([':n'=>$data->nombre, ':a'=>$data->direccion, ':p'=>$data->celular, ':e'=>$data->email, ':id'=>$clientId]);
        }

    } else {
        // --- CASO: CLIENTE NUEVO ---
        $sql = "INSERT INTO clients (name, cedula, address, phone, email, service_status, contract_date, contract_end_date, service_plan, created_at) 
                VALUES (:n, :c, :a, :p, :e, 'PENDIENTE', :inicio, :fin, :plan, NOW())";
        $db->prepare($sql)->execute([
            ':n'=>$data->nombre, ':c'=>$data->cedula, ':a'=>$data->direccion, 
            ':p'=>$data->celular, ':e'=>$data->email,
            ':inicio'=>$hoy, ':fin'=>$fin
        ]);
        $clientId = $db->lastInsertId();
    }

    // =================================================================================
    // 2. GENERACIÓN DE NUMERACIÓN (IGUAL QUE ANTES)
    // =================================================================================
    
    $rawBranch = strtoupper($data->sucursal); 
    $branchCode = 'VILCA'; 
    if (strpos($rawBranch, 'LOJA') !== false) $branchCode = 'LOJA';
    if (strpos($rawBranch, 'PALAN') !== false) $branchCode = 'PALAN';

    // CONTRATO
    $stmtC = $db->prepare("SELECT current_value, prefix FROM document_sequences WHERE doc_type = 'CONTRATO' AND branch = :br");
    $stmtC->execute([':br' => $branchCode]);
    $rowContrato = $stmtC->fetch(PDO::FETCH_ASSOC);
    $numContrato = $rowContrato ? intval($rowContrato['current_value']) : 1;
    $pfxContrato = $rowContrato ? $rowContrato['prefix'] : 'CTR-';

    // ANEXO
    $stmtA = $db->prepare("SELECT current_value, prefix FROM document_sequences WHERE doc_type = 'ANEXO' AND branch = :br");
    $stmtA->execute([':br' => $branchCode]);
    $rowAnexo = $stmtA->fetch(PDO::FETCH_ASSOC);
    $numAnexo = $rowAnexo ? intval($rowAnexo['current_value']) : 1;
    $pfxAnexo = $rowAnexo ? $rowAnexo['prefix'] : 'ANX-';

    $anio = date('Y');
    $secuenciaContrato = $pfxContrato . $branchCode . '-' . $anio . '-' . str_pad($numContrato, 6, "0", STR_PAD_LEFT);
    $secuenciaAnexo    = $pfxAnexo . $branchCode . '-' . $anio . '-' . str_pad($numAnexo, 6, "0", STR_PAD_LEFT);
    
    // =================================================================================

    // 3. VARIABLES WORD
    $marca = 'X'; $vacio = '  '; 

    $vars = [
        '{{SECUENCIA}}'      => $secuenciaContrato,
        '{{SECUENCIA_ANEXO}}'=> $secuenciaAnexo, 
        '{{SUCURSAL}}'       => mb_strtoupper($data->sucursal),
        '{{FECHA_LARGA}}'    => fechaEspanol($hoy),
        '{{NOMBRE_CLIENTE}}' => mb_strtoupper($data->nombre),
        '{{CEDULA}}'         => $data->cedula,
        '{{DIRECCION}}'      => mb_strtoupper($data->direccion),
        '{{CELULARES}}'      => $data->celular,
        '{{EMAIL}}'          => strtolower($data->email),
        '{{TIEMPO_CONTRATO}}'=> $data->tiempo,
        '{{INICIO_CONTRATO}}'=> fechaEspanol($hoy),
        '{{FIN_CONTRARO}}'   => fechaEspanol($fin),
        
        // Legales
        '{{X_SI_REN}}'       => ($data->legal_renovacion == 'SI') ? $marca : $vacio,
        '{{X_NO_REN}}'       => ($data->legal_renovacion == 'NO') ? $marca : $vacio,
        '{{X_SI_PERIODOC}}'  => ($data->legal_permanencia == 'SI') ? $marca : $vacio,
        '{{X_NO_PERIODOC}}'  => ($data->legal_permanencia == 'NO') ? $marca : $vacio,
        '{{SI_EMPRESAS}}'    => ($data->legal_arbitraje == 'SI') ? $marca : $vacio,
        '{{NO_EMPRESAS}}'    => ($data->legal_arbitraje == 'NO') ? $marca : $vacio,

        // Técnicos
        '{{X_FO}}'           => ($data->tipo_conexion == 'FIBRA' ? $marca : $vacio),
        '{{X_RE}}'           => ($data->tipo_conexion == 'RADIO' ? $marca : $vacio),
        '{{X_STANDAR}}'      => ($data->plan == 'STANDARD' ? $marca : $vacio),
        '{{X_ESENCIAL}}'     => ($data->plan == 'ESENCIAL' ? $marca : $vacio),
        '{{X_FAMILIAR}}'     => ($data->plan == 'FAMILIAR' ? $marca : $vacio),
        '{{X_STANDAR_PRO}}'   => ($data->plan == 'STANDARD_PRO' ? $marca : $vacio),
        '{{X_ESENCIAL_EXPERT}}'=> ($data->plan == 'ESENCIAL_EXPERT' ? $marca : $vacio),
        '{{X_EMPRESA_PREMIUM}}' => ($data->plan == 'EMPRESA' ? $marca : $vacio),
        
        // Trámite
        '{{X_INS}}'          => ($data->tramite == 'INSTALACION' ? $marca : $vacio),
        '{{X_REN}}'          => ($data->tramite == 'RENOVACION' ? $marca : $vacio),
        '{{X_MIG}}'          => ($data->tramite == 'MIGRACION' ? $marca : $vacio),
        '{{X_CAM}}'          => ($data->tramite == 'CAMBIO_TITULAR' ? $marca : $vacio),
        '{{X_ACT}}'          => ($data->tramite == 'INSTALACION' ? $marca : $vacio), // Activo si es instalacion
        '{{X_REACT}}'        => ($data->tramite == 'RENOVACION' ? $marca : $vacio),  // Reactivacion si es renovacion
        '{{X_DESACT}}'       => $vacio,

        // Apoyos
        '{{X_APOYO_TERCERA_EDAD}}' => ($data->prioritario == 'TERCERA_EDAD') ? $marca : $vacio,
        '{{X_APOYO_BONO}}'        => ($data->prioritario == 'BONO') ? $marca : $vacio,
        '{{X_APOYO_DISC}}'         => ($data->prioritario == 'DISCAPACIDAD') ? $marca : $vacio,
        '{{X_SI_TE_DISC}}'         => ($data->prioritario != 'NINGUNO') ? $marca : $vacio,
        '{{X_NO_TE_DISC}}'         => ($data->prioritario == 'NINGUNO') ? $marca : $vacio
    ];

    // 4. GENERAR ARCHIVO
    $template = '../../assets/docs/plantilla_contrato.docx';
    if (!file_exists($template)) throw new Exception("Falta plantilla base");
    
    $outputDir = '../../assets/docs/generados/';
    if (!file_exists($outputDir)) mkdir($outputDir, 0777, true);

    $fileName = 'Contrato_' . $data->cedula . '_' . time() . '.docx';
    $outputPath = $outputDir . $fileName;

    if (!copy($template, $outputPath)) throw new Exception("Error permisos carpeta");

    $zip = new ZipArchive;
    if ($zip->open($outputPath) === TRUE) {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (preg_match('/word\/(document|header\d+|footer\d+)\.xml/', $filename)) {
                $xml = $zip->getFromName($filename);
                foreach ($vars as $key => $val) {
                    $xml = str_replace($key, $val, $xml);
                }
                $zip->addFromString($filename, $xml);
            }
        }
        $zip->close();
        
        // 5. ACTUALIZAR SECUENCIA (+1)
        $db->prepare("UPDATE document_sequences SET current_value = current_value + 1 WHERE doc_type = 'CONTRATO' AND branch = :br")->execute([':br' => $branchCode]);
        $db->prepare("UPDATE document_sequences SET current_value = current_value + 1 WHERE doc_type = 'ANEXO' AND branch = :br")->execute([':br' => $branchCode]);

        ob_end_clean(); 
        echo json_encode([
            "success" => true,
            "document_url" => 'assets/docs/generados/' . $fileName,
            "message" => "Documento generado exitosamente"
        ]);

    } else {
        throw new Exception("Error al procesar el archivo Word.");
    }

} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>