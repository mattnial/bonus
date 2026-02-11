<?php
// ARCHIVO: api/admin/cruce_maestro.php
header("Content-Type: text/html; charset=UTF-8");
set_time_limit(1000); 
include_once '../config/database.php';

try {
    $db = (new Database())->getConnection();
    echo "<h2>Iniciando Sincronización Maestra</h2>";

    // 1. CARGAR PRECIOS (Lista de planes.csv)
    // Usamos ';' como delimitador y cambiamos ',' por '.' en los precios
    $mapaPrecios = [];
    if (($h = fopen("Lista de planes.csv", "r")) !== FALSE) {
        while (($data = fgetcsv($h, 1000, ";")) !== FALSE) {
            if (empty($data[0])) continue;
            $nombrePlan = trim($data[0]);
            $valor = str_replace(',', '.', trim($data[9] ?? '0.00'));
            $mapaPrecios[$nombrePlan] = $valor;
        }
        fclose($h);
        echo "✅ Planes/Precios mapeados correctamente.<br>";
    }

    // 2. PROCESAR CONTRATOS (Listado contratos.csv)
    $actualizados = 0;
    if (($h = fopen("Listado contratos.csv", "r")) !== FALSE) {
        while (($data = fgetcsv($h, 3000, ";")) !== FALSE) {
            $partner = trim($data[3] ?? '');
            if (empty($partner) || $partner == 'Partner') continue;

            // Extraer Cédula (lo que esté antes del primer espacio)
            $cedula = explode(" ", $partner)[0];
            
            // Mapeo según tus archivos:
            $nombrePlanCsv = trim($data[4] ?? '');
            $direccion     = trim($data[12] ?? '');
            $telefono      = trim($data[17] ?? '');
            $f_inicio_raw  = trim($data[19] ?? ''); 
            $f_fin_raw     = trim($data[20] ?? ''); 

            // Convertir fechas DD/MM/YYYY a YYYY-MM-DD
            $f_inicio = !empty($f_inicio_raw) ? date('Y-m-d', strtotime(str_replace('/', '-', $f_inicio_raw))) : null;
            $f_fin    = !empty($f_fin_raw) ? date('Y-m-d', strtotime(str_replace('/', '-', $f_fin_raw))) : null;

            // Obtener precio y crear el nombre combinado
            $precio = $mapaPrecios[$nombrePlanCsv] ?? '0.00';
            $planTexto = "$nombrePlanCsv ($$precio)";

            // 3. ACTUALIZAR CLIENTE
            $sql = "UPDATE clients SET 
                    address = :dir, 
                    phone = :tel, 
                    plan_price = :price, 
                    plan_name = :pname,
                    contract_date = :fi,
                    contract_end_date = :ff
                    WHERE cedula LIKE :ced";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':dir'   => $direccion,
                ':tel'   => $telefono,
                ':price' => $precio,
                ':pname' => $planTexto,
                ':fi'    => $f_inicio,
                ':ff'    => $f_fin,
                ':ced'   => "%$cedula"
            ]);

            if($stmt->rowCount() > 0) $actualizados++;
        }
        fclose($h);
    }

    echo "<br><strong>¡PROCESO COMPLETADO!</strong><br>";
    echo "Se han actualizado <strong>$actualizados</strong> clientes con dirección, teléfono, plan y fechas.";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}