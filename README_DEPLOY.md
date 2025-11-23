# Guía de Despliegue - Fórmula Farma

## Configuración de Secretos en GitHub

Para que el despliegue automático funcione, necesitas configurar los siguientes secretos en GitHub:

### Secretos FTP (para el despliegue)
1. Ve a: `Settings` → `Secrets and variables` → `Actions`
2. Añade estos secretos:
   - `FTP_SERVER`: Servidor FTP de Raiola (ej: `ftp.formulafarma.com`)
   - `FTP_USERNAME`: Usuario FTP
   - `FTP_PASSWORD`: Contraseña FTP

### Secretos SMTP (para el formulario de contacto)
Añade también estos secretos para generar automáticamente el archivo de configuración SMTP:
   - `SMTP_HOST`: `mail.formulafarma.com`
   - `SMTP_PORT`: `465`
   - `SMTP_USERNAME`: `contacto@formulafarma.com`
   - `SMTP_PASSWORD`: `tu_contraseña_smtp`
   - `SMTP_ENCRYPTION`: `ssl`
   - `SMTP_FROM_EMAIL`: `contacto@formulafarma.com`
   - `SMTP_FROM_NAME`: `Formulario de Contacto Fórmula Farma`
   - `SMTP_TO_EMAIL`: `abel.eiras@hotmail.com`

## Despliegue Manual (Alternativa)

Si prefieres no usar GitHub Secrets, puedes:

1. **Después del despliegue automático**, conectarte por FTP a Raiola
2. Subir manualmente el archivo `smtp-config.php` a la raíz del proyecto (mismo nivel que `public_html`)
3. Asegurarte de que el archivo tenga los permisos correctos (644)

## Estructura en el Servidor

```
public_html/          (contenido de dist/)
├── index.html
├── blog/
└── ...

smtp-config.php       (en la raíz, fuera de public_html/)
contact.php           (en public_html/ o en la raíz)
```

## Notas de Seguridad

- El archivo `smtp-config.php` NO se sube a GitHub (está en `.gitignore`)
- Se genera automáticamente durante el despliegue usando GitHub Secrets
- O se sube manualmente después del primer despliegue

