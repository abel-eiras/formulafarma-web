<?php
/**
 * API endpoint para comentarios
 * GET: Obtener comentarios de un post
 * POST: Crear nuevo comentario
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
    // Obtener comentarios de un post
    $postSlug = $_GET['post'] ?? '';
    
    if (empty($postSlug)) {
        sendJSON(['success' => false, 'message' => 'El parámetro post es requerido'], 400);
    }
    
    $db = getDB();
    if (!$db) {
        sendJSON(['success' => false, 'message' => 'Error de conexión a la base de datos'], 500);
    }
    
    try {
        $stmt = $db->prepare("
            SELECT id, author_name, content, created_at
            FROM comments
            WHERE post_slug = ? AND approved = 1
            ORDER BY created_at DESC
        ");
        $stmt->execute([$postSlug]);
        $comments = $stmt->fetchAll();
        
        // Formatear fechas
        foreach ($comments as &$comment) {
            $date = new DateTime($comment['created_at']);
            $comment['created_at'] = $date->format('c'); // ISO 8601
            $comment['created_at_formatted'] = $date->format('d/m/Y H:i');
        }
        
        sendJSON([
            'success' => true,
            'comments' => $comments,
            'count' => count($comments)
        ]);
    } catch (PDOException $e) {
        error_log("Error obteniendo comentarios: " . $e->getMessage());
        sendJSON(['success' => false, 'message' => 'Error al obtener comentarios'], 500);
    }
    
} elseif ($method === 'POST') {
    // Crear nuevo comentario
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST; // Fallback a POST tradicional
    }
    
    // Sanitizar inputs
    $data = [
        'post_slug' => sanitizeInput($input['post_slug'] ?? ''),
        'author_name' => sanitizeInput($input['author_name'] ?? ''),
        'author_email' => sanitizeInput($input['author_email'] ?? ''),
        'content' => sanitizeInput($input['content'] ?? '')
    ];
    
    // Validar datos
    $validation = validateComment($data);
    if (!$validation['valid']) {
        sendJSON([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $validation['errors']
        ], 400);
    }
    
    // Verificar rate limiting
    $ip = getClientIP();
    $rateLimit = checkCommentRateLimit($ip);
    
    if (!$rateLimit['allowed']) {
        sendJSON([
            'success' => false,
            'message' => 'Has alcanzado el límite de comentarios. Intenta de nuevo más tarde.',
            'reset_time' => $rateLimit['reset_time']
        ], 429);
    }
    
    // Insertar comentario
    $db = getDB();
    if (!$db) {
        sendJSON(['success' => false, 'message' => 'Error de conexión a la base de datos'], 500);
    }
    
    try {
        $stmt = $db->prepare("
            INSERT INTO comments (post_slug, author_name, author_email, content, ip_address)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['post_slug'],
            $data['author_name'],
            $data['author_email'] ?: null,
            $data['content'],
            $ip
        ]);
        
        $commentId = $db->lastInsertId();
        
        // Obtener el comentario creado
        $stmt = $db->prepare("
            SELECT id, author_name, content, created_at
            FROM comments
            WHERE id = ?
        ");
        $stmt->execute([$commentId]);
        $comment = $stmt->fetch();
        
        // Formatear fecha
        $date = new DateTime($comment['created_at']);
        $comment['created_at'] = $date->format('c');
        $comment['created_at_formatted'] = $date->format('d/m/Y H:i');
        
        sendJSON([
            'success' => true,
            'message' => 'Comentario creado correctamente',
            'comment' => $comment,
            'remaining' => $rateLimit['remaining'] - 1
        ], 201);
        
    } catch (PDOException $e) {
        error_log("Error creando comentario: " . $e->getMessage());
        sendJSON(['success' => false, 'message' => 'Error al crear el comentario'], 500);
    }
    
} else {
    sendJSON(['success' => false, 'message' => 'Método no permitido'], 405);
}

