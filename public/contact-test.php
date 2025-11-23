<?php
// Archivo de prueba para testear el envío SMTP completo
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Cargar configuración SMTP
$config_file = dirname(dirname(__FILE__)) . '/smtp-config.php';
if (!file_exists($config_file)) {
    $config_file = dirname(__DIR__) . '/smtp-config.php';
}

echo "<h2>Test de Envío SMTP Completo</h2>";
echo "<pre>";

if (!file_exists($config_file)) {
    echo "❌ No se encontró smtp-config.php\n";
    echo "Rutas intentadas:\n";
    echo "  - " . dirname(dirname(__FILE__)) . '/smtp-config.php' . "\n";
    echo "  - " . dirname(__DIR__) . '/smtp-config.php' . "\n";
    exit;
}

$smtp_config = require $config_file;

// Función de envío SMTP (copiada de contact.php)
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
    
    echo "Conectando a: $smtp_connection_string\n";
    $smtp = @stream_socket_client(
        $smtp_connection_string,
        $errno,
        $errstr,
        30,
        STREAM_CLIENT_CONNECT,
        $socket_context
    );
    
    if (!$smtp) {
        echo "❌ Error de conexión: $errstr ($errno)\n";
        return false;
    }
    echo "✅ Conexión establecida\n";
    
    // Leer respuesta inicial
    $response = fgets($smtp, 515);
    echo "Respuesta inicial: $response";
    if (strpos($response, '220') === false) {
        echo "❌ Respuesta inicial inválida\n";
        fclose($smtp);
        return false;
    }
    
    // EHLO
    fputs($smtp, "EHLO " . $smtp_host . "\r\n");
    $response = '';
    while ($line = fgets($smtp, 515)) {
        $response .= $line;
        if (substr($line, 3, 1) == ' ') break;
    }
    echo "EHLO: $response";
    if (strpos($response, '250') === false) {
        echo "❌ EHLO falló\n";
        fclose($smtp);
        return false;
    }
    
    // AUTH LOGIN
    fputs($smtp, "AUTH LOGIN\r\n");
    $response = fgets($smtp, 515);
    echo "AUTH LOGIN: $response";
    if (strpos($response, '334') === false) {
        echo "❌ AUTH LOGIN falló\n";
        fclose($smtp);
        return false;
    }
    
    // Usuario
    fputs($smtp, base64_encode($smtp_user) . "\r\n");
    $response = fgets($smtp, 515);
    echo "USER: $response";
    if (strpos($response, '334') === false) {
        echo "❌ USER falló\n";
        fclose($smtp);
        return false;
    }
    
    // Contraseña
    fputs($smtp, base64_encode($smtp_pass) . "\r\n");
    $response = fgets($smtp, 515);
    echo "PASS: $response";
    if (strpos($response, '235') === false) {
        echo "❌ PASS falló\n";
        fclose($smtp);
        return false;
    }
    echo "✅ Autenticación exitosa\n";
    
    // MAIL FROM
    fputs($smtp, "MAIL FROM: <" . $from_email . ">\r\n");
    $response = fgets($smtp, 515);
    echo "MAIL FROM: $response";
    if (strpos($response, '250') === false) {
        echo "❌ MAIL FROM falló\n";
        fclose($smtp);
        return false;
    }
    
    // RCPT TO
    fputs($smtp, "RCPT TO: <" . $to . ">\r\n");
    $response = fgets($smtp, 515);
    echo "RCPT TO: $response";
    if (strpos($response, '250') === false) {
        echo "❌ RCPT TO falló\n";
        fclose($smtp);
        return false;
    }
    
    // DATA
    fputs($smtp, "DATA\r\n");
    $response = fgets($smtp, 515);
    echo "DATA: $response";
    if (strpos($response, '354') === false) {
        echo "❌ DATA falló\n";
        fclose($smtp);
        return false;
    }
    
    // Construir email
    $headers = "From: " . $from_name . " <" . $from_email . ">\r\n";
    $headers .= "Reply-To: " . $from_email . "\r\n";
    $headers .= "To: " . $to . "\r\n";
    $headers .= "Subject: " . $subject . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Date: " . date('r') . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "\r\n";
    
    echo "\nEnviando email:\n";
    echo "---\n";
    echo $headers . $body;
    echo "\n---\n";
    
    fputs($smtp, $headers . $body . "\r\n.\r\n");
    
    $response = '';
    while ($line = fgets($smtp, 515)) {
        $response .= $line;
        if (substr($line, 3, 1) == ' ') break;
    }
    echo "Respuesta final: $response";
    
    fputs($smtp, "QUIT\r\n");
    fclose($smtp);
    
    if (strpos($response, '250') === 0) {
        echo "✅ Email enviado correctamente\n";
        return true;
    } else {
        echo "❌ Error al enviar: $response\n";
        return false;
    }
}

// Probar envío
$test_result = sendEmailSMTP(
    $smtp_config,
    $smtp_config['to_email'],
    "Test desde contact-test.php",
    "Este es un email de prueba desde contact-test.php\n\nSi recibes este email, el SMTP funciona correctamente.",
    $smtp_config['from_email'],
    $smtp_config['from_name']
);

echo "\n";
echo $test_result ? "✅ TEST EXITOSO" : "❌ TEST FALLIDO";
echo "</pre>";
?>

