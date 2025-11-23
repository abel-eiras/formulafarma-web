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

if (empty($message)) {
    $errors[] = "El mensaje es obligatorio";
}

// Honeypot: si el campo bot-field tiene contenido, es spam
if (!empty($bot_field)) {
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(["success" => true, "message" => "Mensaje enviado correctamente"]);
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
$email_body .= "Fecha: " . date("d/m/Y H:i:s") . "\n\n";
$email_body .= "Mensaje:\n" . $message . "\n";

// Función mejorada para enviar email con SMTP
function sendEmailSMTP($config, $to, $subject, $body, $from_email, $from_name) {
    $smtp_host = $config['smtp_host'];
    $smtp_port = $config['smtp_port'];
    $smtp_user = $config['smtp_username'];
    $smtp_pass = $config['smtp_password'];
    $smtp_encryption = $config['smtp_encryption'] ?? 'ssl';
    
    // Construir la cadena de conexión
    $smtp_connection_string = ($smtp_encryption === 'ssl' ? 'ssl://' : '') . $smtp_host . ':' . $smtp_port;
    
    // Opciones de contexto SSL mejoradas
    $context_options = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
            'crypto_method' => STREAM_CRYPTO_METHOD_TLS_CLIENT
        ]
    ];
    
    $socket_context = stream_context_create($context_options);
    
    // Conectar al servidor SMTP
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
    
    // Leer respuesta inicial
    $response = fgets($smtp, 515);
    if (strpos($response, '220') === false) {
        error_log("SMTP Initial response error: $response");
        fclose($smtp);
        return false;
    }
    
    // EHLO - Leer TODAS las líneas de respuesta (pueden ser múltiples)
    fputs($smtp, "EHLO " . $smtp_host . "\r\n");
    $ehlo_response = '';
    
    // Leer todas las líneas hasta que encontremos una que termine con espacio (no guión)
    // El servidor puede enviar múltiples líneas con códigos 220- o 250-
    // IMPORTANTE: El EHLO puede tener líneas 220- al inicio y luego líneas 250- después
    $max_lines = 30; // Límite de seguridad aumentado
    $line_count = 0;
    $ehlo_complete = false;
    
    while ($line_count < $max_lines && !$ehlo_complete) {
        // Leer línea directamente (fgets bloquea hasta que hay datos o EOF)
        $line = fgets($smtp, 515);
        if ($line === false) {
            // Si fgets devuelve false, puede ser EOF o error
            // Esperar un momento y verificar si hay más datos
            $read = array($smtp);
            $write = null;
            $except = null;
            if (stream_select($read, $write, $except, 1) == 0) {
                // No hay más datos disponibles
                break;
            }
            // Intentar leer de nuevo
            $line = fgets($smtp, 515);
            if ($line === false) break;
        }
        
        $ehlo_response .= $line;
        $line_count++;
        
        // La respuesta termina cuando el 4º carácter es un espacio (no guión)
        // Buscar específicamente una línea que termine con "250 " (código 250 con espacio)
        // ya que el EHLO normalmente termina con 250, no con 220
        if (strlen($line) >= 4) {
            $code = substr($line, 0, 3);
            $continuation = substr($line, 3, 1);
            
            // El EHLO normalmente termina con "250 " (no "250-")
            // Aunque algunos servidores pueden terminar con "220 ", preferimos "250 "
            if ($code == '250' && $continuation == ' ') {
                $ehlo_complete = true;
                break;
            }
            // También aceptar "220 " como final si no encontramos "250 "
            if ($code == '220' && $continuation == ' ' && strpos($ehlo_response, '250') === false) {
                // Si no hemos visto ninguna línea 250, puede que el servidor solo use 220
                // Pero esperar un poco más por si hay líneas 250 después
                $read = array($smtp);
                $write = null;
                $except = null;
                if (stream_select($read, $write, $except, 1) == 0) {
                    // No hay más datos, aceptar 220 como final
                    $ehlo_complete = true;
                    break;
                }
            }
        }
    }
    
    // Aceptar respuestas 250 (éxito) o 220 (algunos servidores responden así)
    if (strpos($ehlo_response, '250') === false && strpos($ehlo_response, '220') === false) {
        error_log("SMTP EHLO failed: $ehlo_response");
        fclose($smtp);
        return false;
    }
    
    // STARTTLS si es necesario (para TLS)
    if ($smtp_encryption === 'tls') {
        fputs($smtp, "STARTTLS\r\n");
        $response = fgets($smtp, 515);
        if (strpos($response, '220') === false) {
            error_log("SMTP STARTTLS failed: $response");
            fclose($smtp);
            return false;
        }
        stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        // Re-enviar EHLO después de STARTTLS
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
    
    // Intentar AUTH LOGIN primero
    fputs($smtp, "AUTH LOGIN\r\n");
    $response = fgets($smtp, 515);
    $auth_success = false;
    
    // Si el servidor no soporta AUTH LOGIN (responde con 5xx o 250), intentar AUTH PLAIN
    if (strpos($response, '334') === false) {
        // Intentar AUTH PLAIN como alternativa
        $auth_string = base64_encode("\0" . $smtp_user . "\0" . $smtp_pass);
        fputs($smtp, "AUTH PLAIN " . $auth_string . "\r\n");
        $response = fgets($smtp, 515);
        if (strpos($response, '235') !== false) {
            // AUTH PLAIN exitoso - continuar con el envío
            $auth_success = true;
        } else {
            error_log("SMTP AUTH PLAIN failed: $response");
            // Volver a intentar AUTH LOGIN paso a paso
            fputs($smtp, "AUTH LOGIN\r\n");
            $response = fgets($smtp, 515);
            if (strpos($response, '334') === false) {
                error_log("SMTP AUTH LOGIN failed: $response");
                fclose($smtp);
                return false;
            }
        }
    }
    
    // Si AUTH PLAIN no funcionó, continuar con AUTH LOGIN paso a paso
    if (!$auth_success) {
        // Usuario
        fputs($smtp, base64_encode($smtp_user) . "\r\n");
        $response = fgets($smtp, 515);
        if (strpos($response, '334') === false) {
            error_log("SMTP USER failed: $response");
            fclose($smtp);
            return false;
        }
        
        // Contraseña
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
    
    // Construir headers y cuerpo del email
    // Codificar el subject para evitar problemas con caracteres especiales
    $encoded_subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    
    $headers = "From: " . $from_name . " <" . $from_email . ">\r\n";
    $headers .= "Reply-To: " . $from_email . "\r\n";
    $headers .= "To: " . $to . "\r\n";
    $headers .= "Subject: " . $encoded_subject . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";
    $headers .= "Date: " . date('r') . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "\r\n"; // Línea vacía entre headers y body
    
    // Enviar email completo
    fputs($smtp, $headers . $body . "\r\n.\r\n");
    
    // Leer respuesta completa
    $response = '';
    while ($line = fgets($smtp, 515)) {
        $response .= $line;
        if (substr($line, 3, 1) == ' ') break;
    }
    
    // QUIT
    fputs($smtp, "QUIT\r\n");
    fclose($smtp);
    
    // Verificar si el envío fue exitoso
    // La respuesta puede empezar con 250 o tener 250 en cualquier parte
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
    // Fallback a mail() de PHP
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
        "message" => "¡Mensaje enviado correctamente! Te responderé pronto."
    ]);
} else {
    error_log("Contact form error: Failed to send email. Error: " . ($error_message ?: 'Unknown error'));
    http_response_code(500);
    echo json_encode([
        "error" => "Error al enviar el mensaje. Por favor, intenta de nuevo o contáctame directamente por email."
    ]);
}
?>
