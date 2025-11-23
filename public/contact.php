<?php
// Cargar PHPMailer
// Intentar varias rutas posibles para encontrar vendor
$vendor_paths = [
    __DIR__ . '/vendor/autoload.php',           // vendor en public_html/vendor
    __DIR__ . '/../vendor/autoload.php',        // vendor en raíz del proyecto
    dirname(__DIR__) . '/vendor/autoload.php',  // vendor en raíz (alternativa)
];

$vendor_loaded = false;
foreach ($vendor_paths as $vendor_path) {
    if (file_exists($vendor_path)) {
        require_once $vendor_path;
        $vendor_loaded = true;
        break;
    }
}

if (!$vendor_loaded) {
    error_log("ERROR: No se pudo encontrar vendor/autoload.php. Rutas intentadas: " . implode(', ', $vendor_paths));
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Error de configuración del servidor. Por favor, contacta al administrador."]);
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

// Intentar enviar el email
$mail_sent = false;
$error_message = '';

try {
    $mail = new PHPMailer(true);
    
    if (isset($smtp_config['use_smtp']) && $smtp_config['use_smtp'] === true) {
        // Configuración SMTP
        $mail->isSMTP();
        $mail->Host = $smtp_config['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_config['smtp_username'];
        $mail->Password = $smtp_config['smtp_password'];
        $mail->SMTPSecure = $smtp_config['smtp_encryption'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $smtp_config['smtp_port'];
        $mail->CharSet = 'UTF-8';
        
        // Opciones adicionales para SSL
        if ($smtp_config['smtp_encryption'] === 'ssl') {
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
        }
    } else {
        // Usar mail() de PHP como fallback
        $mail->isMail();
    }
    
    // Configuración del email
    $mail->setFrom($smtp_config['from_email'], $smtp_config['from_name']);
    $mail->addAddress($to_email);
    $mail->addReplyTo($email, $name);
    
    $mail->Subject = $subject;
    $mail->Body = $email_body;
    $mail->AltBody = strip_tags($email_body);
    
    // Enviar
    $mail_sent = $mail->send();
    
} catch (Exception $e) {
    $error_message = $mail->ErrorInfo;
    error_log("PHPMailer Error: " . $error_message);
    $mail_sent = false;
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
    error_log("Contact form error: Failed to send email. Error: " . ($error_message ?: 'Unknown error'));
    http_response_code(500);
    echo json_encode([
        "error" => "Error al enviar el mensaje. Por favor, intenta de nuevo o contáctame directamente por email."
    ]);
}
?>
