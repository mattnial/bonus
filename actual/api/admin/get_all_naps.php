<?php
// ARCHIVO: api/admin/get_all_naps.php
// DESCRIPCIÓN: Fusiona todos los archivos .json y .geojson de la carpeta naps
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Ruta a la carpeta donde subiste los archivos
$folder = '../../assets/data/naps/';

// Buscar todos los archivos con extensión .json y .geojson
$files = array_merge(
    glob($folder . '*.json'), 
    glob($folder . '*.geojson')
);

$allFeatures = [];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $json = json_decode($content, true);
        
        // Validar que sea un mapa válido (GeoJSON)
        if ($json && isset($json['features']) && is_array($json['features'])) {
            // Unir las características (puntos, líneas) de este archivo al total
            $allFeatures = array_merge($allFeatures, $json['features']);
        }
    }
}

// Devolver un solo archivo maestro
echo json_encode([
    'type' => 'FeatureCollection',
    'features' => $allFeatures
]);
?>