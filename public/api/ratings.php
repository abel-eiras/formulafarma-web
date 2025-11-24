<?php
/**
 * API endpoint para valoraciones
 * GET: Obtener estadísticas de valoraciones de un post
 * POST: Crear o actualizar valoración de un post
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/utils.php';

// Inicializar base de datos si no existe
initDatabase();

// Configurar CORS si es necesario
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Obtener estadísticas de valoraciones
    $postSlug = $_GET['post'] ?? '';
    
    if (empty($postSlug)) {
        sendJSON(['success' => false, 'message' => 'El parámetro post es requerido'], 400);
    }
    
    $db = getDB();
    if (!$db) {
        sendJSON(['success' => false, 'message' => 'Error de conexión a la base de datos'], 500);
    }
    
    try {
        // Obtener estadísticas generales
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as count,
                AVG(rating) as average
            FROM ratings
            WHERE post_slug = ?
        ");
        $stmt->execute([$postSlug]);
        $stats = $stmt->fetch();
        
        $count = (int)$stats['count'];
        $average = $stats['average'] ? round((float)$stats['average'], 1) : 0;
        
        // Obtener valoración del usuario actual (si existe)
        $ip = getClientIP();
        $userRating = null;
        
        $stmt = $db->prepare("
            SELECT rating
            FROM ratings
            WHERE post_slug = ? AND ip_address = ?
        ");
        $stmt->execute([$postSlug, $ip]);
        $userRatingResult = $stmt->fetch();
        
        if ($userRatingResult) {
            $userRating = (int)$userRatingResult['rating'];
        }
        
        sendJSON([
            'success' => true,
            'average' => $average,
            'count' => $count,
            'user_rating' => $userRating
        ]);
        
    } catch (PDOException $e) {
        error_log("Error obteniendo valoraciones: " . $e->getMessage());
        sendJSON(['success' => false, 'message' => 'Error al obtener valoraciones'], 500);
    }
    
} elseif ($method === 'POST') {
    // Crear o actualizar valoración
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST; // Fallback a POST tradicional
    }
    
    // Sanitizar inputs
    $data = [
        'post_slug' => sanitizeInput($input['post_slug'] ?? ''),
        'rating' => isset($input['rating']) ? (int)$input['rating'] : null
    ];
    
    // Validar datos
    $validation = validateRating($data);
    if (!$validation['valid']) {
        sendJSON([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $validation['errors']
        ], 400);
    }
    
    // Verificar si ya existe una valoración de esta IP para este post
    $ip = getClientIP();
    $alreadyRated = hasRatedPost($data['post_slug'], $ip);
    
    $db = getDB();
    if (!$db) {
        sendJSON(['success' => false, 'message' => 'Error de conexión a la base de datos'], 500);
    }
    
    try {
        if ($alreadyRated) {
            // Actualizar valoración existente
            $stmt = $db->prepare("
                UPDATE ratings
                SET rating = ?, created_at = CURRENT_TIMESTAMP
                WHERE post_slug = ? AND ip_address = ?
            ");
            $stmt->execute([$data['rating'], $data['post_slug'], $ip]);
        } else {
            // Crear nueva valoración
            $stmt = $db->prepare("
                INSERT INTO ratings (post_slug, rating, ip_address)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$data['post_slug'], $data['rating'], $ip]);
        }
        
        // Obtener estadísticas actualizadas
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as count,
                AVG(rating) as average
            FROM ratings
            WHERE post_slug = ?
        ");
        $stmt->execute([$data['post_slug']]);
        $stats = $stmt->fetch();
        
        $count = (int)$stats['count'];
        $average = $stats['average'] ? round((float)$stats['average'], 1) : 0;
        
        sendJSON([
            'success' => true,
            'message' => $alreadyRated ? 'Valoración actualizada correctamente' : 'Valoración creada correctamente',
            'rating' => $data['rating'],
            'average' => $average,
            'count' => $count
        ], $alreadyRated ? 200 : 201);
        
    } catch (PDOException $e) {
        error_log("Error guardando valoración: " . $e->getMessage());
        sendJSON(['success' => false, 'message' => 'Error al guardar la valoración'], 500);
    }
    
} else {
    sendJSON(['success' => false, 'message' => 'Método no permitido'], 405);
}

