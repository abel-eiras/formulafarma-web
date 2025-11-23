<?php
// Versión de debug para probar el formulario
// Este archivo muestra errores para ayudar a diagnosticar el problema

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cargar configuración SMTP
$config_file = dirname(dirname(__FILE__)) . '/smtp-config.php';
if (!file_exists($config_file)) {
    $config_file = dirname(__DIR__) . '/smtp-config.php';
}

echo "<h2>Debug del Formulario de Contacto</h2>";
echo "<pre>";

echo "1. Buscando archivo de configuración...\n";
echo "   Ruta intentada 1: " . dirname(dirname(__FILE__)) . '/smtp-config.php' . "\n";
echo "   Ruta intentada 2: " . dirname(__DIR__) . '/smtp-config.php' . "\n";
echo "   Archivo encontrado: " . ($config_file ?? 'NO ENCONTRADO') . "\n";
echo "   Existe: " . (file_exists($config_file) ? 'SÍ' : 'NO') . "\n\n";

if (file_exists($config_file)) {
    $smtp_config = require $config_file;
    echo "2. Configuración SMTP cargada:\n";
    echo "   use_smtp: " . ($smtp_config['use_smtp'] ?? 'no definido') . "\n";
    echo "   smtp_host: " . ($smtp_config['smtp_host'] ?? 'no definido') . "\n";
    echo "   smtp_port: " . ($smtp_config['smtp_port'] ?? 'no definido') . "\n";
    echo "   smtp_username: " . ($smtp_config['smtp_username'] ?? 'no definido') . "\n";
    echo "   smtp_password: " . (isset($smtp_config['smtp_password']) ? '***' : 'no definido') . "\n";
    echo "   smtp_encryption: " . ($smtp_config['smtp_encryption'] ?? 'no definido') . "\n";
    echo "   from_email: " . ($smtp_config['from_email'] ?? 'no definido') . "\n";
    echo "   to_email: " . ($smtp_config['to_email'] ?? 'no definido') . "\n\n";
    
    // Probar conexión SMTP
    if (isset($smtp_config['use_smtp']) && $smtp_config['use_smtp'] === true) {
        echo "3. Probando conexión SMTP...\n";
        $smtp_host = $smtp_config['smtp_host'];
        $smtp_port = $smtp_config['smtp_port'];
        $smtp_encryption = $smtp_config['smtp_encryption'] ?? 'ssl';
        
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);
        
        $connection_string = ($smtp_encryption === 'ssl' ? 'ssl://' : '') . $smtp_host . ':' . $smtp_port;
        echo "   Intentando conectar a: $connection_string\n";
        
        $smtp = @stream_socket_client(
            $connection_string,
            $errno,
            $errstr,
            10,
            STREAM_CLIENT_CONNECT,
            $context
        );
        
        if ($smtp) {
            echo "   ✅ Conexión SMTP exitosa\n";
            fclose($smtp);
        } else {
            echo "   ❌ Error de conexión: $errstr ($errno)\n";
        }
    }
} else {
    echo "❌ No se encontró el archivo de configuración SMTP\n";
    echo "   Asegúrate de que smtp-config.php esté en la raíz del servidor\n";
}

echo "</pre>";
?>

