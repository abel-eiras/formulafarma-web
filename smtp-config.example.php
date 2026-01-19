<?php
// Configuración SMTP - ARCHIVO DE EJEMPLO
// Copia este archivo a smtp-config.php y completa con tus credenciales reales
// El archivo smtp-config.php NO se sube a Git por seguridad

return [
    'use_smtp' => true, // Cambiar a false para usar mail() de PHP
    'smtp_host' => 'mail.formulafarma.com', // Servidor SMTP
    'smtp_port' => 465, // 587 para TLS, 465 para SSL
    'smtp_username' => 'contacto@formulafarma.com', // Usuario SMTP
    'smtp_password' => 'TU_CONTRASEÑA_AQUI', // Contraseña SMTP
    'smtp_encryption' => 'ssl', // 'tls' o 'ssl'
    'from_email' => 'contacto@formulafarma.com', // Email desde el que se envía
    'from_name' => 'Formulario de Contacto Fórmula Farma',
    'to_email' => 'abel.eiras@hotmail.com' // Email de destino
];
