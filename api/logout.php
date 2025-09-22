<?php
require_once '../includes/auth.php';

header('Content-Type: application/json');

// Hacer logout
logoutUser();

echo json_encode([
    'success' => true,
    'message' => 'Sesión cerrada exitosamente'
]);
?>
