<?php
/**
 * Utilidades compartidas: rate limiting, validación, sanitización
 */

require_once __DIR__ . '/db.php';

/**
 * Verificar rate limiting para comentarios
 * Máximo 3 comentarios por IP en 1 hora
 * @param string $ip
 * @return array ['allowed' => bool, 'remaining' => int, 'reset_time' => int]
 */
function checkCommentRateLimit($ip) {
    $db = getDB();
    if (!$db) {
        return ['allowed' => false, 'remaining' => 0, 'reset_time' => 0];
    }
    
    try {
        $oneHourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));
        
        $stmt = $db->prepare("
            SELECT COUNT(*) as count 
            FROM comments 
            WHERE ip_address = ? AND created_at > ?
        ");
        $stmt->execute([$ip, $oneHourAgo]);
        $result = $stmt->fetch();
        
        $count = (int)$result['count'];
        $maxComments = 3;
        $allowed = $count < $maxComments;
        $remaining = max(0, $maxComments - $count);
        
        // Calcular tiempo de reset (1 hora desde el comentario más antiguo)
        $resetTime = 0;
        if ($count > 0) {
            $stmt = $db->prepare("
                SELECT MIN(created_at) as oldest 
                FROM comments 
                WHERE ip_address = ? AND created_at > ?
            ");
            $stmt->execute([$ip, $oneHourAgo]);
            $oldest = $stmt->fetch();
            if ($oldest && $oldest['oldest']) {
                $resetTime = strtotime($oldest['oldest']) + 3600;
            }
        }
        
        return [
            'allowed' => $allowed,
            'remaining' => $remaining,
            'reset_time' => $resetTime
        ];
    } catch (PDOException $e) {
        error_log("Error en rate limiting: " . $e->getMessage());
        return ['allowed' => false, 'remaining' => 0, 'reset_time' => 0];
    }
}

/**
 * Verificar si una IP ya ha valorado un post
 * @param string $postSlug
 * @param string $ip
 * @return bool
 */
function hasRatedPost($postSlug, $ip) {
    $db = getDB();
    if (!$db) {
        return false;
    }
    
    try {
        $stmt = $db->prepare("
            SELECT COUNT(*) as count 
            FROM ratings 
            WHERE post_slug = ? AND ip_address = ?
        ");
        $stmt->execute([$postSlug, $ip]);
        $result = $stmt->fetch();
        
        return (int)$result['count'] > 0;
    } catch (PDOException $e) {
        error_log("Error verificando rating: " . $e->getMessage());
        return false;
    }
}

/**
 * Validar datos de comentario
 * @param array $data
 * @return array ['valid' => bool, 'errors' => array]
 */
function validateComment($data) {
    $errors = [];
    
    // Validar post_slug
    if (empty($data['post_slug']) || !is_string($data['post_slug'])) {
        $errors[] = 'El slug del post es requerido';
    }
    
    // Validar author_name
    if (empty($data['author_name']) || !is_string($data['author_name'])) {
        $errors[] = 'El nombre es requerido';
    } elseif (strlen($data['author_name']) < 2) {
        $errors[] = 'El nombre debe tener al menos 2 caracteres';
    } elseif (strlen($data['author_name']) > 100) {
        $errors[] = 'El nombre no puede exceder 100 caracteres';
    }
    
    // Validar author_email (opcional)
    if (!empty($data['author_email']) && !isValidEmail($data['author_email'])) {
        $errors[] = 'El email no es válido';
    }
    
    // Validar content
    if (empty($data['content']) || !is_string($data['content'])) {
        $errors[] = 'El contenido del comentario es requerido';
    } elseif (strlen(trim($data['content'])) < 10) {
        $errors[] = 'El comentario debe tener al menos 10 caracteres';
    } elseif (strlen($data['content']) > 2000) {
        $errors[] = 'El comentario no puede exceder 2000 caracteres';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Validar datos de rating
 * @param array $data
 * @return array ['valid' => bool, 'errors' => array]
 */
function validateRating($data) {
    $errors = [];
    
    // Validar post_slug
    if (empty($data['post_slug']) || !is_string($data['post_slug'])) {
        $errors[] = 'El slug del post es requerido';
    }
    
    // Validar rating
    if (!isset($data['rating']) || !is_numeric($data['rating'])) {
        $errors[] = 'La valoración es requerida';
    } else {
        $rating = (int)$data['rating'];
        if ($rating < 0 || $rating > 5) {
            $errors[] = 'La valoración debe estar entre 0 y 5';
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Enviar respuesta JSON
 * @param mixed $data
 * @param int $statusCode
 */
function sendJSON($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

