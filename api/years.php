<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit();
}

try {
    $years = getAvailableYears();
    
    // Convertir a formato esperado por el frontend
    $yearList = array_map(function($year) {
        return [
            'año' => (int)$year['titulo'],
            'enlace' => $year['enlace'],
            'imagen' => $year['imagen']
        ];
    }, $years);
    
    echo json_encode([
        'success' => true,
        'data' => $yearList
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
