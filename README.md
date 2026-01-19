# Fórmula Farma Web

Nueva web de Fórmula Farma (en desarrollo).

## Deploy

Este repositorio está configurado con GitHub Actions para despliegue automático via FTP.

Consulta `GUIA_DEPLOY_ASTRO_RAIOLA.md` para la documentación completa del proceso de deploy.

### Secretos configurados

Los secretos de GitHub ya están configurados en el environment `Formulafarma_secrets`:
- FTP_SERVER, FTP_USERNAME, FTP_PASSWORD
- SMTP_HOST, SMTP_PORT, SMTP_USERNAME, SMTP_PASSWORD, etc.

## Desarrollo

```bash
npm install
npm run dev
```
