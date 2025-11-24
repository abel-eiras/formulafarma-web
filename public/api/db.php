<?php
/**
 * Conexión a SQLite y funciones helper para el sistema de comentarios
 */

/**
 * Obtener conexión a la base de datos SQLite
 * @return PDO|null
 */
function getDB() {
    static $db = null;
    
    if ($db === null) {
        $dbPath = __DIR__ . '/comments.db';
        
        try {
            $db = new PDO('sqlite:' . $dbPath);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error conectando a la base de datos: " . $e->getMessage());
            return null;
        }
    }
    
    return $db;
}

/**
 * Inicializar la base de datos creando las tablas si no existen
 * @return bool
 */
function initDatabase() {
    $db = getDB();
    if (!$db) {
        return false;
    }
    
    try {
        // Crear tabla de comentarios
        $db->exec("
            CREATE TABLE IF NOT EXISTS comments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                post_slug TEXT NOT NULL,
                author_name TEXT NOT NULL,
                author_email TEXT,
                content TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                ip_address TEXT,
                approved INTEGER DEFAULT 1
            )
        ");
        
        // Crear tabla de valoraciones
        $db->exec("
            CREATE TABLE IF NOT EXISTS ratings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                post_slug TEXT NOT NULL,
                rating INTEGER NOT NULL CHECK(rating >= 0 AND rating <= 5),
                ip_address TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(post_slug, ip_address)
            )
        ");
        
        // Crear índices
        $db->exec("CREATE INDEX IF NOT EXISTS idx_comments_post ON comments(post_slug)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_ratings_post ON ratings(post_slug)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_comments_created ON comments(created_at DESC)");
        
        return true;
    } catch (PDOException $e) {
        error_log("Error inicializando base de datos: " . $e->getMessage());
        return false;
    }
}

/**
 * Sanitizar string para prevenir XSS
 * @param string $input
 * @return string
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validar email (opcional, puede estar vacío)
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    if (empty($email)) {
        return true; // Email es opcional
    }
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Obtener IP del cliente
 * @return string
 */
function getClientIP() {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

