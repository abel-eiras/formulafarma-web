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
    
    // EHLO - Leer TODAS las líneas de respuesta (pueden ser múltiples)
    fputs($smtp, "EHLO " . $smtp_host . "\r\n");
    $ehlo_response = '';
    
    echo "Leyendo respuesta EHLO...\n";
    // Leer todas las líneas hasta que encontremos una que termine con espacio (no guión)
    while (true) {
        $line = fgets($smtp, 515);
        if ($line === false) break;
        
        $ehlo_response .= $line;
        echo "EHLO línea: $line";
        
        // La respuesta termina cuando el 4º carácter es un espacio (no guión)
        // Y el código es 250 o 220 (no 250- o 220-)
        if (strlen($line) >= 4) {
            $code = substr($line, 0, 3);
            $continuation = substr($line, 3, 1);
            
            // Si es un código 250 o 220 y termina con espacio (no guión), es el final
            if (($code == '250' || $code == '220') && $continuation == ' ') {
                echo "✅ Fin de respuesta EHLO (código: $code, continuación: '$continuation')\n";
                break;
            }
        }
    }
    
    echo "\nRespuesta EHLO completa:\n$ehlo_response\n";
    
    // Aceptar respuestas 250 (éxito) o 220 (algunos servidores responden así)
    if (strpos($ehlo_response, '250') === false && strpos($ehlo_response, '220') === false) {
        echo "❌ EHLO falló\n";
        fclose($smtp);
        return false;
    }
    echo "✅ EHLO exitoso\n\n";
    
    // Intentar AUTH LOGIN primero
    fputs($smtp, "AUTH LOGIN\r\n");
    $response = fgets($smtp, 515);
    echo "AUTH LOGIN: $response";
    
    // Si el servidor no soporta AUTH LOGIN, intentar AUTH PLAIN
    if (strpos($response, '334') === false) {
        echo "\nIntentando AUTH PLAIN como alternativa...\n";
        $auth_string = base64_encode("\0" . $smtp_user . "\0" . $smtp_pass);
        fputs($smtp, "AUTH PLAIN " . $auth_string . "\r\n");
        $response = fgets($smtp, 515);
        echo "AUTH PLAIN: $response";
        if (strpos($response, '235') !== false) {
            echo "✅ Autenticación exitosa (PLAIN)\n";
            // Continuar con el envío
        } else {
            echo "❌ AUTH PLAIN falló, intentando AUTH LOGIN paso a paso...\n";
            fputs($smtp, "AUTH LOGIN\r\n");
            $response = fgets($smtp, 515);
            echo "AUTH LOGIN (reintento): $response";
            if (strpos($response, '334') === false) {
                echo "❌ AUTH LOGIN falló\n";
                fclose($smtp);
                return false;
            }
        }
    }
    
    // Si AUTH LOGIN fue aceptado, continuar con usuario y contraseña
    if (strpos($response, '334') !== false) {
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
        echo "✅ Autenticación exitosa (LOGIN)\n";
    }
    
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

