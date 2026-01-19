# Guía de Deploy: Astro + GitHub Actions + FTP (Raiola)

Esta guía documenta cómo configurar un proyecto Astro para desplegarse automáticamente en un hosting compartido (Raiola) mediante GitHub Actions y FTP.

---

## Índice

1. [Estructura del proyecto](#estructura-del-proyecto)
2. [Configuración de Astro](#configuración-de-astro)
3. [Workflow de GitHub Actions](#workflow-de-github-actions)
4. [Secretos necesarios en GitHub](#secretos-necesarios-en-github)
5. [Archivos PHP adicionales](#archivos-php-adicionales)
6. [Configuración paso a paso](#configuración-paso-a-paso)

---

## Estructura del proyecto

```
proyecto/
├── .github/
│   └── workflows/
│       └── deploy.yaml          # Workflow de despliegue
├── public/
│   ├── api/                     # APIs PHP (opcional)
│   │   ├── comments.php
│   │   ├── ratings.php
│   │   └── ...
│   └── contact.php              # Formulario de contacto (opcional)
├── src/
│   └── ...                      # Código fuente Astro
├── astro.config.mjs
├── package.json
├── smtp-config.example.php      # Ejemplo de configuración SMTP
└── tailwind.config.mjs          # Si usas Tailwind
```

---

## Configuración de Astro

### `astro.config.mjs` básico

```javascript
// @ts-check
import { defineConfig } from 'astro/config';
import tailwind from '@astrojs/tailwind';

export default defineConfig({
  site: 'https://tu-dominio.com',
  integrations: [tailwind()],
  // Opcional: configuración i18n
  i18n: {
    defaultLocale: "es",
    locales: ["es", "gl"],
    routing: {
      prefixDefaultLocale: false
    }
  }
});
```

### `package.json` mínimo

```json
{
  "name": "mi-proyecto",
  "type": "module",
  "version": "0.0.1",
  "scripts": {
    "dev": "astro dev",
    "build": "astro build",
    "preview": "astro preview"
  },
  "dependencies": {
    "astro": "^5.16.0"
  },
  "devDependencies": {
    "@astrojs/tailwind": "^6.0.2",
    "tailwindcss": "^3.4.18"
  }
}
```

---

## Workflow de GitHub Actions

### `.github/workflows/deploy.yaml`

```yaml
name: Deploy Raiola

on:
  push:
    branches: [master]  # o [main] según tu rama principal

jobs:
  web-deploy:
    runs-on: ubuntu-latest
    environment: Formulafarma_secrets  # Nombre de tu environment en GitHub
    steps:
      # 1. Checkout del código
      - name: Checkout code
        uses: actions/checkout@v4
      
      # 2. Setup Node.js
      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '20'
      
      # 3. Instalar dependencias
      - name: Install dependencies
        run: npm install
      
      # 4. Build de Astro
      - name: Build Astro
        run: npm run build
      
      # 5. (Opcional) Copiar archivos PHP al dist
      - name: Copy PHP files to dist
        run: |
          cp public/contact.php dist/contact.php
          cp -r public/api dist/api
      
      # 6. (Opcional) Crear directorio para rate limits
      - name: Create rate_limits directory
        run: mkdir -p dist/rate_limits && touch dist/rate_limits/.gitkeep
      
      # 7. (Opcional) Permisos para API
      - name: Set API directory permissions
        run: chmod -R 755 dist/api
      
      # 8. (Opcional) Crear archivo de configuración SMTP
      - name: Create SMTP config file
        run: |
          cat > smtp-config.php << EOF
          <?php
          return [
              'use_smtp' => true,
              'smtp_host' => '${{ secrets.SMTP_HOST }}',
              'smtp_port' => ${{ secrets.SMTP_PORT }},
              'smtp_username' => '${{ secrets.SMTP_USERNAME }}',
              'smtp_password' => '${{ secrets.SMTP_PASSWORD }}',
              'smtp_encryption' => '${{ secrets.SMTP_ENCRYPTION }}',
              'from_email' => '${{ secrets.SMTP_FROM_EMAIL }}',
              'from_name' => '${{ secrets.SMTP_FROM_NAME }}',
              'to_email' => '${{ secrets.SMTP_TO_EMAIL }}'
          ];
          EOF
      
      # 9. Validar que los secretos FTP existen
      - name: Validate FTP secrets
        run: |
          echo "Validating FTP secrets..."
          if [ -z "${{ secrets.FTP_SERVER }}" ]; then
            echo "::error::FTP_SERVER secret is missing"
            exit 1
          fi
          if [ -z "${{ secrets.FTP_USERNAME }}" ]; then
            echo "::error::FTP_USERNAME secret is missing"
            exit 1
          fi
          if [ -z "${{ secrets.FTP_PASSWORD }}" ]; then
            echo "::error::FTP_PASSWORD secret is missing"
            exit 1
          fi
          echo "✅ All FTP secrets configured"
      
      # 10. Deploy al servidor via FTP
      - name: Deploy website to public_html
        uses: SamKirkland/FTP-Deploy-Action@v4.3.5
        with:
          server: ${{ secrets.FTP_SERVER }}
          username: ${{ secrets.FTP_USERNAME }}
          password: ${{ secrets.FTP_PASSWORD }}
          local-dir: ./dist/
          server-dir: ./public_html/
          dangerous-clean-slate: true  # ⚠️ Borra todo antes de subir
      
      # 11. (Opcional) Deploy SMTP config fuera de public_html
      - name: Deploy SMTP config to root
        uses: SamKirkland/FTP-Deploy-Action@v4.3.5
        with:
          server: ${{ secrets.FTP_SERVER }}
          username: ${{ secrets.FTP_USERNAME }}
          password: ${{ secrets.FTP_PASSWORD }}
          local-dir: ./
          server-dir: ./
          include: smtp-config.php
          dangerous-clean-slate: false
```

---

## Secretos necesarios en GitHub

### Configuración del Environment

1. Ve a tu repositorio en GitHub
2. `Settings` → `Environments` → `New environment`
3. Nombre: `Formulafarma_secrets` (o el nombre que uses en el workflow)

### Secretos FTP (OBLIGATORIOS)

| Secreto | Descripción | Ejemplo |
|---------|-------------|---------|
| `FTP_SERVER` | Servidor FTP de Raiola | `ftp.tudominio.com` o IP |
| `FTP_USERNAME` | Usuario FTP | `usuario@tudominio.com` |
| `FTP_PASSWORD` | Contraseña FTP | `tu_contraseña` |

### Secretos SMTP (OPCIONALES - para formulario de contacto)

| Secreto | Descripción | Ejemplo |
|---------|-------------|---------|
| `SMTP_HOST` | Servidor SMTP | `mail.tudominio.com` |
| `SMTP_PORT` | Puerto SMTP | `465` |
| `SMTP_USERNAME` | Usuario SMTP | `contacto@tudominio.com` |
| `SMTP_PASSWORD` | Contraseña SMTP | `tu_contraseña` |
| `SMTP_ENCRYPTION` | Tipo de cifrado | `ssl` o `tls` |
| `SMTP_FROM_EMAIL` | Email remitente | `contacto@tudominio.com` |
| `SMTP_FROM_NAME` | Nombre remitente | `Formulario de Contacto` |
| `SMTP_TO_EMAIL` | Email destino | `tu@email.com` |

---

## Archivos PHP adicionales

### `smtp-config.example.php`

Archivo de ejemplo para configuración SMTP (no subir el real a git):

```php
<?php
return [
    'use_smtp' => true,
    'smtp_host' => 'mail.tudominio.com',
    'smtp_port' => 465,
    'smtp_username' => 'contacto@tudominio.com',
    'smtp_password' => 'TU_CONTRASEÑA_AQUI',
    'smtp_encryption' => 'ssl',
    'from_email' => 'contacto@tudominio.com',
    'from_name' => 'Formulario de Contacto',
    'to_email' => 'tu@email.com'
];
```

### `.gitignore` (añadir)

```
smtp-config.php
```

---

## Configuración paso a paso

### 1. Crear el proyecto Astro

```bash
npm create astro@latest mi-proyecto
cd mi-proyecto
npm install
```

### 2. Añadir Tailwind (opcional)

```bash
npx astro add tailwind
```

### 3. Crear la estructura de carpetas

```bash
mkdir -p .github/workflows
mkdir -p public/api
```

### 4. Copiar el workflow

Crea `.github/workflows/deploy.yaml` con el contenido de arriba.

### 5. Configurar secretos en GitHub

1. Sube tu código a GitHub
2. Ve a `Settings` → `Environments` → Crea `Formulafarma_secrets`
3. Añade los secretos FTP (obligatorios)
4. Añade los secretos SMTP (si usas formulario de contacto)

### 6. Push y deploy automático

```bash
git add .
git commit -m "Setup deploy workflow"
git push origin master
```

El workflow se ejecutará automáticamente en cada push a `master`.

---

## Estructura en el servidor (Raiola)

```
/home/usuario/
├── public_html/          ← Contenido de dist/ se sube aquí
│   ├── index.html
│   ├── blog/
│   ├── contact.php
│   └── api/
└── smtp-config.php       ← Fuera de public_html (seguro)
```

---

## Notas importantes

1. **`dangerous-clean-slate: true`**: Borra todo el contenido de `public_html` antes de subir. Útil para evitar archivos huérfanos, pero cuidado si tienes archivos subidos manualmente.

2. **Permisos de archivos**: El workflow establece permisos 755 para la carpeta `api/`. Ajusta según necesites.

3. **Rate limits**: El directorio `rate_limits/` se usa para almacenar límites de peticiones en APIs PHP. Necesita permisos de escritura.

4. **SMTP fuera de public_html**: Por seguridad, el archivo `smtp-config.php` se sube a la raíz, no a `public_html`.

---

## Troubleshooting

### El deploy falla con "secret is missing"

- Verifica que los nombres de los secretos son EXACTOS (mayúsculas incluidas)
- Verifica que el environment name en el workflow coincide con el de GitHub

### Los archivos PHP no funcionan

- Verifica que el hosting tiene PHP habilitado
- Revisa los permisos de los archivos (644 para archivos, 755 para directorios)

### El formulario de contacto no envía emails

- Verifica la configuración SMTP en el panel de Raiola
- Comprueba que el archivo `smtp-config.php` se subió correctamente fuera de `public_html`
