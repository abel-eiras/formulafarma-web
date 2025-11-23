<?php
// Cargar configuración SMTP (fuera de public/)
$config_file = dirname(__DIR__) . '/smtp-config.php';
if (file_exists($config_file)) {
    $smtp_config = require $config_file;
} else {
    // Fallback si no existe el archivo de configuración
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
    // Silenciosamente rechazar (parece spam)
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

// Función para enviar email con SMTP
function sendEmailSMTP($config, $to, $subject, $body, $from_email, $from_name) {
    $smtp_host = $config['smtp_host'];
    $smtp_port = $config['smtp_port'];
    $smtp_user = $config['smtp_username'];
    $smtp_pass = $config['smtp_password'];
    $smtp_encryption = $config['smtp_encryption'] ?? 'tls';
    
    // Crear conexión SMTP
    $smtp = fsockopen(
        ($smtp_encryption === 'ssl' ? 'ssl://' : '') . $smtp_host,
        $smtp_port,
        $errno,
        $errstr,
        30
    );
    
    if (!$smtp) {
        return false;
    }
    
    // Leer respuesta inicial
    $response = fgets($smtp, 515);
    
    // EHLO
    fputs($smtp, "EHLO " . $smtp_host . "\r\n");
    $response = fgets($smtp, 515);
    
    // STARTTLS si es necesario
    if ($smtp_encryption === 'tls') {
        fputs($smtp, "STARTTLS\r\n");
        $response = fgets($smtp, 515);
        stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        fputs($smtp, "EHLO " . $smtp_host . "\r\n");
        $response = fgets($smtp, 515);
    }
    
    // Autenticación
    fputs($smtp, "AUTH LOGIN\r\n");
    $response = fgets($smtp, 515);
    fputs($smtp, base64_encode($smtp_user) . "\r\n");
    $response = fgets($smtp, 515);
    fputs($smtp, base64_encode($smtp_pass) . "\r\n");
    $response = fgets($smtp, 515);
    
    // Enviar email
    fputs($smtp, "MAIL FROM: <" . $from_email . ">\r\n");
    $response = fgets($smtp, 515);
    fputs($smtp, "RCPT TO: <" . $to . ">\r\n");
    $response = fgets($smtp, 515);
    fputs($smtp, "DATA\r\n");
    $response = fgets($smtp, 515);
    
    // Headers del email
    $headers = "From: " . $from_name . " <" . $from_email . ">\r\n";
    $headers .= "Reply-To: " . $from_email . "\r\n";
    $headers .= "To: " . $to . "\r\n";
    $headers .= "Subject: " . $subject . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "\r\n";
    
    fputs($smtp, $headers . $body . "\r\n.\r\n");
    $response = fgets($smtp, 515);
    
    // Cerrar conexión
    fputs($smtp, "QUIT\r\n");
    fclose($smtp);
    
    return strpos($response, '250') === 0;
}

// Intentar enviar el email
$mail_sent = false;

if (isset($smtp_config['use_smtp']) && $smtp_config['use_smtp'] === true) {
    // Usar SMTP
    $mail_sent = sendEmailSMTP(
        $smtp_config,
        $to_email,
        $subject,
        $email_body,
        $smtp_config['from_email'],
        $smtp_config['from_name']
    );
} else {
    // Usar mail() de PHP como fallback
    $headers = "From: " . $smtp_config['from_name'] . " <" . $smtp_config['from_email'] . ">\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    $mail_sent = @mail($to_email, $subject, $email_body, $headers);
}

header('Content-Type: application/json');

if ($mail_sent) {
    // Email enviado correctamente
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "¡Mensaje enviado correctamente! Te responderé pronto."
    ]);
} else {
    // Error al enviar
    http_response_code(500);
    echo json_encode([
        "error" => "Error al enviar el mensaje. Por favor, intenta de nuevo o contáctame directamente por email."
    ]);
}
?>
