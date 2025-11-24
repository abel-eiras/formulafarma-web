<?php
/**
 * Script de inicialización de la base de datos
 * Ejecutar una vez manualmente o se ejecutará automáticamente en el primer uso
 */

require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

try {
    if (initDatabase()) {
        echo json_encode([
            'success' => true,
            'message' => 'Base de datos inicializada correctamente'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al inicializar la base de datos'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

