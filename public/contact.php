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

// Keep individual requests small even if PHP's global post_max_size is set too high.
// This is checked before using any submitted value.
$max_request_size = 12 * 1024;
if (isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > $max_request_size) {
    http_response_code(413);
    header('Content-Type: application/json');
    echo json_encode(["error" => "El formulario es demasiado grande"]);
    exit;
}

// Verificar que es una petición POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

// Obtener y limpiar datos del formulario. Reject arrays and oversized values instead
// of coercing them, so attackers cannot turn a field into an unexpected structure.
function getPostString($key, $max_length, &$errors) {
    if (!isset($_POST[$key])) {
        return '';
    }

    if (!is_string($_POST[$key])) {
        $errors[] = "El campo {$key} no es válido";
        return '';
    }

    $value = $_POST[$key];
    if (strlen($value) > $max_length || strpos($value, "\0") !== false) {
        $errors[] = "El campo {$key} es demasiado largo o no es válido";
        return '';
    }

    return $value;
}

// strip_tags elimina HTML; preg_replace elimina saltos de línea para prevenir header injection
function sanitizeField($value) {
    return trim(htmlspecialchars(preg_replace('/[\r\n\t]/', ' ', strip_tags($value))));
}

$errors = [];
$raw_name = getPostString('name', 100, $errors);
$raw_email = getPostString('email', 254, $errors);
$raw_pharmacy = getPostString('pharmacy', 150, $errors);
$raw_interest = getPostString('interest', 32, $errors);
$raw_message = getPostString('message', 5000, $errors);
$raw_source = getPostString('source', 32, $errors);
$bot_field = getPostString('bot-field', 200, $errors);

$name = sanitizeField($raw_name);
$email = trim($raw_email);
$pharmacy = sanitizeField($raw_pharmacy);
$interest = sanitizeField($raw_interest);
$message = trim(strip_tags($raw_message));
$source = sanitizeField($raw_source);

// Validación básica
$allowed_interests = ['usar', 'contribuir', 'caso', 'difundir', 'formula-care', 'spd', 'aura'];
$allowed_sources = ['', 'ficha-programa'];

if (empty($name)) {
    $errors[] = "El nombre es obligatorio";
}

if (empty($email)) {
    $errors[] = "El email es obligatorio";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "El email no es válido";
}

if (!in_array($interest, $allowed_interests, true)) {
    $errors[] = "El interés no es válido";
}

if (!in_array($source, $allowed_sources, true)) {
    $errors[] = "El origen no es válido";
}

// Honeypot: si el campo bot-field tiene contenido, es spam
if (!empty($bot_field)) {
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(["success" => true, "message" => "Mensaje enviado correctamente"]);
    exit;
}

// Rate limiting: use only REMOTE_ADDR. Forwarded headers are user-controlled
// unless the web server overwrites them after validating its trusted proxies.
// A single, flock-protected and size-bounded state file avoids a file per IP and
// makes concurrent requests update the same state safely.
function checkRateLimit($ip, $max_attempts = 5, $time_window = 3600) {
    $rate_limit_dir = dirname(__DIR__) . '/rate_limits';
    
    if (!is_dir($rate_limit_dir)) {
        if (!@mkdir($rate_limit_dir, 0700, true) && !is_dir($rate_limit_dir)) {
            error_log('Contact form rate-limit directory cannot be created');
            return ['allowed' => false, 'message' => 'El formulario no está disponible temporalmente.'];
        }
    }

    $ip_hash = hash('sha256', $ip);
    $rate_limit_file = $rate_limit_dir . '/contact-rate-limit.json';
    $handle = @fopen($rate_limit_file, 'c+');
    if ($handle === false || !flock($handle, LOCK_EX)) {
        error_log('Contact form rate-limit state cannot be locked');
        if (is_resource($handle)) {
            fclose($handle);
        }
        return ['allowed' => false, 'message' => 'El formulario no está disponible temporalmente.'];
    }

    $now = time();
    $content = stream_get_contents($handle);
    $state = json_decode($content, true);
    $buckets = is_array($state) && isset($state['buckets']) && is_array($state['buckets'])
        ? $state['buckets']
        : [];

    foreach ($buckets as $key => $timestamps) {
        if (!is_array($timestamps)) {
            unset($buckets[$key]);
            continue;
        }
        $timestamps = array_values(array_filter($timestamps, function($timestamp) use ($now, $time_window) {
            return is_int($timestamp) && $timestamp <= $now && ($now - $timestamp) < $time_window;
        }));
        if (empty($timestamps)) {
            unset($buckets[$key]);
        } else {
            $buckets[$key] = $timestamps;
        }
    }

    // Retain a bounded number of clients; evict the least-recently-used bucket
    // when a new address arrives so attacker-controlled addresses cannot grow it.
    if (!isset($buckets[$ip_hash]) && count($buckets) >= 1000) {
        uasort($buckets, function($left, $right) {
            return end($left) <=> end($right);
        });
        array_shift($buckets);
    }

    $attempts = isset($buckets[$ip_hash]) ? $buckets[$ip_hash] : [];
    $attempt_count = count($attempts);
    if ($attempt_count >= $max_attempts) {
        $oldest_attempt = min($attempts);
        $time_remaining = $time_window - ($now - $oldest_attempt);
        $minutes_remaining = ceil($time_remaining / 60);

        flock($handle, LOCK_UN);
        fclose($handle);
        return [
            'allowed' => false,
            'message' => "Has alcanzado el límite de envíos. Por favor, espera {$minutes_remaining} minuto(s) antes de intentar de nuevo."
        ];
    }

    $attempts[] = $now;
    $buckets[$ip_hash] = $attempts;
    $encoded_state = json_encode(['buckets' => $buckets]);
    if ($encoded_state === false || !ftruncate($handle, 0) || rewind($handle) === false || fwrite($handle, $encoded_state) === false || !fflush($handle)) {
        flock($handle, LOCK_UN);
        fclose($handle);
        error_log('Contact form rate-limit state cannot be written');
        return ['allowed' => false, 'message' => 'El formulario no está disponible temporalmente.'];
    }

    @chmod($rate_limit_file, 0600);
    flock($handle, LOCK_UN);
    fclose($handle);
    return ['allowed' => true];
}

function getClientIP() {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    if (filter_var($ip, FILTER_VALIDATE_IP)) {
        return $ip;
    }
    return 'unknown';
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
$is_program_page = ($source === 'ficha-programa');
$is_beta = false;
$subject = ($is_program_page ? "[" . strtoupper($interest) . "] " : "") . $subject_prefix . " - " . $name;
$email_body = "Has recibido un nuevo mensaje desde el formulario de contacto de Fórmula Farma.\n\n";
$email_body .= "Nombre: " . $name . "\n";
$email_body .= "Email: " . $email . "\n";
if (!empty($pharmacy)) {
    $email_body .= "Farmacia: " . $pharmacy . "\n";
}
if (!empty($interest)) {
    $interest_labels = [
        'usar' => 'Quiere usar Fórmula Care, SPD o Aura',
        'contribuir' => 'Quiere contribuir código',
        'caso' => 'Quiere contar su caso',
        'difundir' => 'Quiere ayudar a difundir esto',
        'formula-care' => 'Fórmula Care (ficha de programa)',
        'spd' => 'SPD (ficha de programa)',
        'aura' => 'Aura (ficha de programa)'
    ];
    $email_body .= "Interés: " . ($interest_labels[$interest] ?? $interest) . "\n";
}
$email_body .= "Fecha: " . date("d/m/Y H:i:s") . "\n\n";
if (!empty($message)) {
    $email_body .= "Mensaje:\n" . $message . "\n";
}

function formatMailboxHeader($email, $display_name = '') {
    if (!is_string($email) || preg_match('/[\r\n]/', $email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    if ($display_name === '') {
        return '<' . $email . '>';
    }

    if (!is_string($display_name) || preg_match('/[\r\n]/', $display_name)) {
        return false;
    }

    return '=?UTF-8?B?' . base64_encode($display_name) . '?= <' . $email . '>';
}

function escapeSmtpData($data) {
    // SMTP terminates DATA at a line containing a single dot. Normalize line
    // endings and dot-stuff every line so submitted text cannot terminate DATA
    // or append SMTP commands.
    $data = preg_replace('/\r\n|\r|\n/', "\r\n", $data);
    return preg_replace('/(^|\r\n)\./', '$1..', $data);
}

// Función para enviar email con SMTP
function sendEmailSMTP($config, $to, $subject, $body, $from_email, $from_name) {
    $from_header = formatMailboxHeader($from_email, $from_name);
    $to_header = formatMailboxHeader($to);
    if ($from_header === false || $to_header === false) {
        error_log('SMTP configuration contains an invalid email address or display name');
        return false;
    }

    $smtp_host = $config['smtp_host'];
    $smtp_port = $config['smtp_port'];
    $smtp_user = $config['smtp_username'];
    $smtp_pass = $config['smtp_password'];
    $smtp_encryption = $config['smtp_encryption'] ?? 'ssl';
    
    $smtp_connection_string = ($smtp_encryption === 'ssl' ? 'ssl://' : '') . $smtp_host . ':' . $smtp_port;
    
    $context_options = [
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
            'allow_self_signed' => false,
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
    
    $headers = "From: " . $from_header . "\r\n";
    $headers .= "Reply-To: " . formatMailboxHeader($from_email) . "\r\n";
    $headers .= "To: " . $to_header . "\r\n";
    $headers .= "Subject: " . $encoded_subject . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";
    $headers .= "Date: " . date('r') . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "\r\n";
    
    fputs($smtp, $headers . escapeSmtpData($body) . "\r\n.\r\n");
    
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
    $from_header = formatMailboxHeader($smtp_config['from_email'], $smtp_config['from_name']);
    $reply_to_header = formatMailboxHeader($email);
    if ($from_header === false || $reply_to_header === false || !filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
        $mail_sent = false;
        $error_message = "Configuración de correo no válida.";
    } else {
        $headers = "From: " . $from_header . "\r\n";
        $headers .= "Reply-To: " . $reply_to_header . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $mail_sent = @mail($to_email, $subject, $email_body, $headers);
    }
    
    if (!$mail_sent) {
        $error_message = "Error al enviar con mail() de PHP.";
    }
}

// Enviar auto-reply al usuario si el email principal se envió bien
if ($mail_sent) {
    $reply_subject = $is_beta
        ? "Solicitud de acceso beta recibida — Fórmula Care"
        : "Hemos recibido tu mensaje — Fórmula Farma";

    $reply_body  = "Hola " . $name . ",\n\n";
    if ($is_beta) {
        $reply_body .= "He recibido tu solicitud de acceso a la beta de Fórmula Care.\n";
        $reply_body .= "Te escribo en cuanto tenga un hueco para darte acceso y contarte los detalles.\n\n";
        $reply_body .= "Si tienes cualquier pregunta mientras tanto, responde a este email.\n\n";
    } else {
        $reply_body .= "He recibido tu mensaje y te responderé lo antes posible.\n";
        $reply_body .= "Si es urgente, escríbeme directamente a abel@formulafarma.com.\n\n";
    }
    $reply_body .= "Un saludo,\nAbel\nFórmula Farma — https://formulafarma.com\n";

    if (isset($smtp_config['use_smtp']) && $smtp_config['use_smtp'] === true) {
        sendEmailSMTP(
            $smtp_config,
            $email,
            $reply_subject,
            $reply_body,
            $smtp_config['from_email'],
            $smtp_config['from_name']
        );
    } else {
        $from_header = formatMailboxHeader($smtp_config['from_email'], $smtp_config['from_name']);
        if ($from_header !== false) {
            $reply_headers  = "From: " . $from_header . "\r\n";
            $reply_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            @mail($email, $reply_subject, $reply_body, $reply_headers);
        }
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
