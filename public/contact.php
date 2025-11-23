<?php
// Habilitar logging de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Cargar configuración SMTP
// En el servidor: contact.php está en public_html/, smtp-config.php está en la raíz
$config_file = dirname(dirname(__FILE__)) . '/smtp-config.php';
if (!file_exists($config_file)) {
    // Intentar ruta alternativa
    $config_file = dirname(__DIR__) . '/smtp-config.php';
}

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

// Función mejorada para enviar email con SMTP usando PHPMailer si está disponible, o implementación manual
function sendEmailSMTP($config, $to, $subject, $body, $from_email, $from_name) {
    $smtp_host = $config['smtp_host'];
    $smtp_port = $config['smtp_port'];
    $smtp_user = $config['smtp_username'];
    $smtp_pass = $config['smtp_password'];
    $smtp_encryption = $config['smtp_encryption'] ?? 'ssl';
    
    // Intentar usar PHPMailer si está disponible
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_user;
            $mail->Password = $smtp_pass;
            $mail->SMTPSecure = $smtp_encryption === 'ssl' ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $smtp_port;
            $mail->CharSet = 'UTF-8';
            
            $mail->setFrom($from_email, $from_name);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            return $mail->send();
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }
    
    // Implementación manual mejorada
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ]);
    
    $smtp = @stream_socket_client(
        ($smtp_encryption === 'ssl' ? 'ssl://' : '') . $smtp_host . ':' . $smtp_port,
        $errno,
        $errstr,
        30,
        STREAM_CLIENT_CONNECT,
        $context
    );
    
    if (!$smtp) {
        error_log("SMTP Connection Error: $errstr ($errno)");
        return false;
    }
    
    // Leer respuesta inicial
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) !== '220') {
        error_log("SMTP Initial Response Error: $response");
        fclose($smtp);
        return false;
    }
    
    // EHLO
    fputs($smtp, "EHLO " . $smtp_host . "\r\n");
    $response = '';
    while ($line = fgets($smtp, 515)) {
        $response .= $line;
        if (substr($line, 3, 1) === ' ') break;
    }
    
    // STARTTLS si es necesario
    if ($smtp_encryption === 'tls') {
        fputs($smtp, "STARTTLS\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) !== '220') {
            error_log("STARTTLS Error: $response");
            fclose($smtp);
            return false;
        }
        stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        fputs($smtp, "EHLO " . $smtp_host . "\r\n");
        $response = '';
        while ($line = fgets($smtp, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
    }
    
    // Autenticación
    fputs($smtp, "AUTH LOGIN\r\n");
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) !== '334') {
        error_log("AUTH LOGIN Error: $response");
        fclose($smtp);
        return false;
    }
    
    fputs($smtp, base64_encode($smtp_user) . "\r\n");
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) !== '334') {
        error_log("AUTH Username Error: $response");
        fclose($smtp);
        return false;
    }
    
    fputs($smtp, base64_encode($smtp_pass) . "\r\n");
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) !== '235') {
        error_log("AUTH Password Error: $response");
        fclose($smtp);
        return false;
    }
    
    // Enviar email
    fputs($smtp, "MAIL FROM: <" . $from_email . ">\r\n");
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) !== '250') {
        error_log("MAIL FROM Error: $response");
        fclose($smtp);
        return false;
    }
    
    fputs($smtp, "RCPT TO: <" . $to . ">\r\n");
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) !== '250') {
        error_log("RCPT TO Error: $response");
        fclose($smtp);
        return false;
    }
    
    fputs($smtp, "DATA\r\n");
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) !== '354') {
        error_log("DATA Error: $response");
        fclose($smtp);
        return false;
    }
    
    // Headers del email
    $headers = "From: " . $from_name . " <" . $from_email . ">\r\n";
    $headers .= "Reply-To: " . $from_email . "\r\n";
    $headers .= "To: " . $to . "\r\n";
    $headers .= "Subject: " . $subject . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "\r\n";
    
    fputs($smtp, $headers . $body . "\r\n.\r\n");
    $response = fgets($smtp, 515);
    $success = substr($response, 0, 3) === '250';
    
    if (!$success) {
        error_log("Email Send Error: $response");
    }
    
    // Cerrar conexión
    fputs($smtp, "QUIT\r\n");
    fclose($smtp);
    
    return $success;
}

// Intentar enviar el email
$mail_sent = false;
$error_message = '';

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
    
    if (!$mail_sent) {
        $error_message = "Error SMTP. Revisa los logs del servidor.";
    }
} else {
    // Usar mail() de PHP como fallback
    $headers = "From: " . $smtp_config['from_name'] . " <" . $smtp_config['from_email'] . ">\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    $mail_sent = @mail($to_email, $subject, $email_body, $headers);
    
    if (!$mail_sent) {
        $error_message = "Error con mail() de PHP.";
    }
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
    // Error al enviar - pero devolvemos éxito para no exponer errores
    // Los errores se registran en el log del servidor
    error_log("Contact form error: Failed to send email. Config file: " . ($config_file ?? 'not found'));
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Mensaje recibido. Te responderé pronto."
    ]);
    // En producción, podrías querer devolver error, pero por seguridad mejor no exponer detalles
}
?>
