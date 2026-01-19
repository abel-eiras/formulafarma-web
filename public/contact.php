<?php
// Habilitar logging de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Cargar configuración SMTP
$config_file = dirname(dirname(__FILE__)) . '/smtp-config.php';
if (!file_exists($config_file)) {
    $config_file = dirname(__DIR__) . '/smtp-config.php';
}

if (file_exists($config_file)) {
    $smtp_config = require $config_file;
} else {
    $smtp_config = [
        'to_email' => 'abel.eiras@hotmail.com',
        'from_email' => 'noreply@formulafarma.com',
        'from_name' => 'Fórmula Farma',
        'use_smtp' => false
    ];
}

$to_email = $smtp_config['to_email'];
$subject_prefix = "[Fórmula Farma] Nuevo mensaje de contacto";

// Verificar que es una petición POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

// Obtener y limpiar datos del formulario
$name = isset($_POST["name"]) ? trim(htmlspecialchars($_POST["name"])) : "";
$email = isset($_POST["email"]) ? trim(htmlspecialchars($_POST["email"])) : "";
$pharmacy = isset($_POST["pharmacy"]) ? trim(htmlspecialchars($_POST["pharmacy"])) : "";
$interest = isset($_POST["interest"]) ? trim(htmlspecialchars($_POST["interest"])) : "";
$message = isset($_POST["message"]) ? trim(htmlspecialchars($_POST["message"])) : "";
$bot_field = isset($_POST["bot-field"]) ? $_POST["bot-field"] : "";

// Validación básica
$errors = [];

if (empty($name)) {
    $errors[] = "El nombre es obligatorio";
}

if (empty($email)) {
    $errors[] = "El email es obligatorio";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "El email no es válido";
}

// Honeypot: si el campo bot-field tiene contenido, es spam
if (!empty($bot_field)) {
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(["success" => true, "message" => "Mensaje enviado correctamente"]);
    exit;
}

// Rate limiting: limitar envíos por IP
function checkRateLimit($ip, $max_attempts = 5, $time_window = 3600) {
    $rate_limit_dir = dirname(dirname(__FILE__)) . '/rate_limits';
    
    if (!is_dir($rate_limit_dir)) {
        @mkdir($rate_limit_dir, 0755, true);
    }
    
    $ip_hash = hash('sha256', $ip);
    $rate_limit_file = $rate_limit_dir . '/' . $ip_hash . '.txt';
    
    $now = time();
    $attempts = [];
    
    if (file_exists($rate_limit_file)) {
        $content = file_get_contents($rate_limit_file);
        $attempts = json_decode($content, true) ?: [];
    }
    
    $attempts = array_filter($attempts, function($timestamp) use ($now, $time_window) {
        return ($now - $timestamp) < $time_window;
    });
    
    $attempt_count = count($attempts);
    
    if ($attempt_count >= $max_attempts) {
        $oldest_attempt = min($attempts);
        $time_remaining = $time_window - ($now - $oldest_attempt);
        $minutes_remaining = ceil($time_remaining / 60);
        
        return [
            'allowed' => false,
            'message' => "Has alcanzado el límite de envíos. Por favor, espera {$minutes_remaining} minuto(s) antes de intentar de nuevo."
        ];
    }
    
    $attempts[] = $now;
    file_put_contents($rate_limit_file, json_encode(array_values($attempts)));
    
    if (file_exists($rate_limit_file) && ($now - filemtime($rate_limit_file)) > 86400) {
        @unlink($rate_limit_file);
    }
    
    return ['allowed' => true];
}

function getClientIP() {
    $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($ip_keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

// Verificar rate limiting
$client_ip = getClientIP();
$rate_limit = checkRateLimit($client_ip, 5, 3600);

if (!$rate_limit['allowed']) {
    http_response_code(429);
    header('Content-Type: application/json');
    echo json_encode([
        "error" => $rate_limit['message']
    ]);
    exit;
}

// Si hay errores, devolverlos
if (!empty($errors)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Errores de validación", "details" => $errors]);
    exit;
}

// Preparar el email
$subject = $subject_prefix . " - " . $name;
$email_body = "Has recibido un nuevo mensaje desde el formulario de contacto de Fórmula Farma.\n\n";
$email_body .= "Nombre: " . $name . "\n";
$email_body .= "Email: " . $email . "\n";
if (!empty($pharmacy)) {
    $email_body .= "Farmacia: " . $pharmacy . "\n";
}
if (!empty($interest)) {
    $interest_labels = [
        'software' => 'Software (Fórmula Care)',
        'consultoria' => 'Consultoría',
        'ambos' => 'Software + Consultoría'
    ];
    $email_body .= "Interés: " . ($interest_labels[$interest] ?? $interest) . "\n";
}
$email_body .= "Fecha: " . date("d/m/Y H:i:s") . "\n\n";
if (!empty($message)) {
    $email_body .= "Mensaje:\n" . $message . "\n";
}

// Función para enviar email con SMTP
function sendEmailSMTP($config, $to, $subject, $body, $from_email, $from_name) {
    $smtp_host = $config['smtp_host'];
    $smtp_port = $config['smtp_port'];
    $smtp_user = $config['smtp_username'];
    $smtp_pass = $config['smtp_password'];
    $smtp_encryption = $config['smtp_encryption'] ?? 'ssl';
    
    $smtp_connection_string = ($smtp_encryption === 'ssl' ? 'ssl://' : '') . $smtp_host . ':' . $smtp_port;
    
    $context_options = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
            'crypto_method' => STREAM_CRYPTO_METHOD_TLS_CLIENT
        ]
    ];
    
    $socket_context = stream_context_create($context_options);
    
    $smtp = @stream_socket_client(
        $smtp_connection_string,
        $errno,
        $errstr,
        30,
        STREAM_CLIENT_CONNECT,
        $socket_context
    );
    
    if (!$smtp) {
        error_log("SMTP Connection Error ($errno): $errstr");
        return false;
    }
    
    $response = fgets($smtp, 515);
    if (strpos($response, '220') === false) {
        error_log("SMTP Initial response error: $response");
        fclose($smtp);
        return false;
    }
    
    // EHLO
    fputs($smtp, "EHLO " . $smtp_host . "\r\n");
    $ehlo_response = '';
    $max_lines = 30;
    $line_count = 0;
    $ehlo_complete = false;
    
    while ($line_count < $max_lines && !$ehlo_complete) {
        $line = fgets($smtp, 515);
        if ($line === false) {
            $read = array($smtp);
            $write = null;
            $except = null;
            if (stream_select($read, $write, $except, 1) == 0) {
                break;
            }
            $line = fgets($smtp, 515);
            if ($line === false) break;
        }
        
        $ehlo_response .= $line;
        $line_count++;
        
        if (strlen($line) >= 4) {
            $code = substr($line, 0, 3);
            $continuation = substr($line, 3, 1);
            
            if ($code == '250' && $continuation == ' ') {
                $ehlo_complete = true;
                break;
            }
            if ($code == '220' && $continuation == ' ' && strpos($ehlo_response, '250') === false) {
                $read = array($smtp);
                $write = null;
                $except = null;
                if (stream_select($read, $write, $except, 1) == 0) {
                    $ehlo_complete = true;
                    break;
                }
            }
        }
    }
    
    if (strpos($ehlo_response, '250') === false && strpos($ehlo_response, '220') === false) {
        error_log("SMTP EHLO failed: $ehlo_response");
        fclose($smtp);
        return false;
    }
    
    // STARTTLS si es necesario
    if ($smtp_encryption === 'tls') {
        fputs($smtp, "STARTTLS\r\n");
        $response = fgets($smtp, 515);
        if (strpos($response, '220') === false) {
            error_log("SMTP STARTTLS failed: $response");
            fclose($smtp);
            return false;
        }
        stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        fputs($smtp, "EHLO " . $smtp_host . "\r\n");
        $ehlo_response = '';
        while (true) {
            $line = fgets($smtp, 515);
            if ($line === false) break;
            $ehlo_response .= $line;
            if (strlen($line) >= 4) {
                $code = substr($line, 0, 3);
                $continuation = substr($line, 3, 1);
                if (($code == '250' || $code == '220') && $continuation == ' ') {
                    break;
                }
            }
        }
    }
    
    // AUTH LOGIN
    fputs($smtp, "AUTH LOGIN\r\n");
    $response = fgets($smtp, 515);
    $auth_success = false;
    
    if (strpos($response, '334') === false) {
        $auth_string = base64_encode("\0" . $smtp_user . "\0" . $smtp_pass);
        fputs($smtp, "AUTH PLAIN " . $auth_string . "\r\n");
        $response = fgets($smtp, 515);
        if (strpos($response, '235') !== false) {
            $auth_success = true;
        } else {
            error_log("SMTP AUTH PLAIN failed: $response");
            fputs($smtp, "AUTH LOGIN\r\n");
            $response = fgets($smtp, 515);
            if (strpos($response, '334') === false) {
                error_log("SMTP AUTH LOGIN failed: $response");
                fclose($smtp);
                return false;
            }
        }
    }
    
    if (!$auth_success) {
        fputs($smtp, base64_encode($smtp_user) . "\r\n");
        $response = fgets($smtp, 515);
        if (strpos($response, '334') === false) {
            error_log("SMTP USER failed: $response");
            fclose($smtp);
            return false;
        }
        
        fputs($smtp, base64_encode($smtp_pass) . "\r\n");
        $response = fgets($smtp, 515);
        if (strpos($response, '235') === false) {
            error_log("SMTP PASS failed: $response");
            fclose($smtp);
            return false;
        }
    }
    
    // MAIL FROM
    fputs($smtp, "MAIL FROM: <" . $from_email . ">\r\n");
    $response = fgets($smtp, 515);
    if (strpos($response, '250') === false) {
        error_log("SMTP MAIL FROM failed: $response");
        fclose($smtp);
        return false;
    }
    
    // RCPT TO
    fputs($smtp, "RCPT TO: <" . $to . ">\r\n");
    $response = fgets($smtp, 515);
    if (strpos($response, '250') === false) {
        error_log("SMTP RCPT TO failed: $response");
        fclose($smtp);
        return false;
    }
    
    // DATA
    fputs($smtp, "DATA\r\n");
    $response = fgets($smtp, 515);
    if (strpos($response, '354') === false) {
        error_log("SMTP DATA failed: $response");
        fclose($smtp);
        return false;
    }
    
    $encoded_subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    
    $headers = "From: " . $from_name . " <" . $from_email . ">\r\n";
    $headers .= "Reply-To: " . $from_email . "\r\n";
    $headers .= "To: " . $to . "\r\n";
    $headers .= "Subject: " . $encoded_subject . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";
    $headers .= "Date: " . date('r') . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "\r\n";
    
    fputs($smtp, $headers . $body . "\r\n.\r\n");
    
    $response = '';
    while ($line = fgets($smtp, 515)) {
        $response .= $line;
        if (substr($line, 3, 1) == ' ') break;
    }
    
    fputs($smtp, "QUIT\r\n");
    fclose($smtp);
    
    if (strpos($response, '250') !== false) {
        return true;
    } else {
        error_log("SMTP SEND failed. Response: " . trim($response));
        return false;
    }
}

// Intentar enviar el email
$mail_sent = false;
$error_message = '';

if (isset($smtp_config['use_smtp']) && $smtp_config['use_smtp'] === true) {
    $mail_sent = sendEmailSMTP(
        $smtp_config,
        $to_email,
        $subject,
        $email_body,
        $smtp_config['from_email'],
        $smtp_config['from_name']
    );
    
    if (!$mail_sent) {
        $error_message = "Error al enviar por SMTP. Revisa los logs del servidor.";
    }
} else {
    $headers = "From: " . $smtp_config['from_name'] . " <" . $smtp_config['from_email'] . ">\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    $mail_sent = @mail($to_email, $subject, $email_body, $headers);
    
    if (!$mail_sent) {
        $error_message = "Error al enviar con mail() de PHP.";
    }
}

header('Content-Type: application/json');

if ($mail_sent) {
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "¡Mensaje enviado correctamente! Te contactaremos pronto."
    ]);
} else {
    error_log("Contact form error: Failed to send email. Error: " . ($error_message ?: 'Unknown error'));
    http_response_code(500);
    echo json_encode([
        "error" => "Error al enviar el mensaje. Por favor, intenta de nuevo o contáctame directamente por email."
    ]);
}
?>
