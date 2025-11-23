# 🚀 ROADMAP: Refactorización Fórmula Farma (Pharma Tech Minimal)

Este documento sirve como guía maestra para la migración de formulafarma.com de WordPress a Astro + Tailwind CSS.
**Objetivo:** Crear un sitio estático, ultrarrápido, con estética minimalista "Pharma Tech" y despliegue automático en Raiola Networks.

## 🎨 Contexto de Diseño (Pharma Tech)
- **Estilo:** Minimalista, limpio, orientado a datos y tecnología.
- **Fuentes:** `Inter` (UI/Títulos) y `Merriweather` (Lectura/Posts).
- **Paleta de Colores:**
  - Fondo: `#FAFAFA` (Blanco roto/clínico)
  - Texto Principal: `#212121` (Gris carbón)
  - Acento Tech: `#2962FF` (Azul eléctrico - Botones/Links)
  - Acento Humano: `#FF8A80` (Coral suave - Detalles empáticos)

---

## 📅 FASE 1: Configuración del Entorno (Scaffolding)

- [ ] **Inicializar Proyecto Astro**
    - Ejecutar: `npm create astro@latest formulafarma-new`
    - Selección: Plantilla "Empty" o "Blog", TypeScript (Relaxed o Strict), Instalar dependencias.
- [ ] **Instalar Tailwind CSS**
    - Ejecutar: `npx astro add tailwind`
- [ ] **Instalar Fuentes**
    - Ejecutar: `npm install @fontsource/inter @fontsource/merriweather`
- [ ] **Configurar `tailwind.config.mjs`**
    - Copiar y pegar la configuración de identidad visual:
    ```javascript
    /** @type {import('tailwindcss').Config} */
    export default {
      content: ['./src/**/*.{astro,html,js,jsx,md,mdx,svelte,ts,tsx,vue}'],
      theme: {
        extend: {
          colors: {
            pharma: {
              bg: '#FAFAFA',
              dark: '#212121',
              grey: '#424242',
              accent: '#2962FF',
              soft: '#E3F2FD',
              coral: '#FF8A80',
            }
          },
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
            serif: ['Merriweather', 'serif'],
          }
        },
      },
      plugins: [require('@tailwindcss/typography')],
    }
    ```

---

## 🧩 FASE 2: Arquitectura y Componentes

- [ ] **Crear Layout Principal (`src/layouts/Layout.astro`)**
    - Implementar estructura HTML base.
    - Importar fuentes (`@fontsource`).
    - Crear Header minimalista (Logo tipográfico + Navegación simple).
    - Crear Footer sencillo.
- [ ] **Crear Componente Tarjeta (`src/components/PostCard.astro`)**
    - Diseño tipo "Card" con sombra suave (`shadow-sm`).
    - Efecto hover (`hover:-translate-y-1`).
    - Mostrar: Fecha, Tags (tipo píldora), Título y Extracto.
- [ ] **Configurar Colecciones de Contenido (`src/content/config.ts`)**
    - Definir esquema Zod para los posts:
    ```typescript
    import { defineCollection, z } from 'astro:content';
    const blog = defineCollection({
      schema: z.object({
        title: z.string(),
        date: z.coerce.date(),
        excerpt: z.string().optional(),
        tags: z.array(z.string()).optional(), // o z.string() si viene simple
        image: z.string().optional(),
      }),
    });
    export const collections = { blog };
    ```

---

## 📦 FASE 3: Migración de Contenido (WordPress -> Markdown)

- [ ] **Exportar XML de WordPress**
    - Ir a WP Admin -> Herramientas -> Exportar -> Todo el contenido.
- [ ] **Ejecutar Script de Conversión**
    - Crear carpeta temporal fuera del proyecto.
    - Ejecutar: `npx wordpress-export-to-markdown`
    - **Configuración crítica del wizard:**
      - *Download images:* Yes
      - *Image folder:* `images`
      - *Image prefix:* `/images/`
- [ ] **Importar al Proyecto Astro**
    - Mover archivos `.md` a `src/content/blog/`.
    - Mover carpeta de imágenes a `public/images/`.
- [ ] **Sanear Frontmatter**
    - Revisar que los `.md` cumplan con el esquema definido en `config.ts` (Fase 2).
    - Borrar campos innecesarios de WordPress (id, post_id, status, etc) si molestan.

---

## 📄 FASE 4: Páginas y Rutas

- [ ] **Crear Página de Inicio (`src/pages/index.astro`)**
    - Añadir sección "Hero" (Foto + Bio corta + "Farmacéutico Híbrido").
    - Listar los últimos posts usando `getCollection('blog')`.
    - Implementar Grid de `PostCard`.
- [ ] **Crear Plantilla de Post Individual (`src/pages/blog/[...slug].astro`)**
    - Usar `getStaticPaths` para generar rutas.
    - Usar clase `prose` de Tailwind para estilizar el Markdown automáticamente:
      `<article class="prose prose-lg prose-slate max-w-none">`

---

## 🚀 FASE 5: Despliegue (CI/CD en Raiola)

- [ ] **Preparar GitHub**
    - Crear repo privado/público.
    - Subir código (sin `node_modules` ni `dist`).
- [ ] **Configurar Secretos en GitHub**
    - Settings -> Secrets -> Actions.
    - Añadir: `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`.
- [ ] **Crear Workflow (.github/workflows/deploy.yaml)**
    - Copiar script de despliegue FTP:
    ```yaml
    name: Deploy Raiola
    on: { push: { branches: [main] } }
    jobs:
      web-deploy:
        runs-on: ubuntu-latest
        steps:
        - uses: actions/checkout@v4
        - run: npm install && npm run build
        - uses: SamKirkland/FTP-Deploy-Action@v4.3.5
          with:
            server: ${{ secrets.FTP_SERVER }}
            username: ${{ secrets.FTP_USERNAME }}
            password: ${{ secrets.FTP_PASSWORD }}
            local-dir: ./dist/
            server-dir: ./public_html/ 
            dangerous-clean-slate: true
    ```
- [ ] **Backup Manual (IMPORTANTE)**
    - Entrar al FTP de Raiola y mover todo el `public_html` actual a una carpeta `_OLD_WP`.

---

## ✅ Checklist Final de Lanzamiento

- [ ] Revisar SEO (Meta tags en Layout).
- [ ] Comprobar enlaces rotos de imágenes migradas.
- [ ] Validar responsive en móvil.
- [ ] Hacer Push a Main y verificar que la web cambia sola.