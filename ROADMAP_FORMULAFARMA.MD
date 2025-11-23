# 🚀 ROADMAP: Refactorización Fórmula Farma (Pharma Tech Minimal)

Este documento sirve como guía maestra para la migración de formulafarma.com de WordPress a Astro + Tailwind CSS.
**Objetivo:** Crear un sitio estático, ultrarrápido, con estética minimalista "Pharma Tech" y despliegue automático en Raiola Networks.

## 🎉 ESTADO ACTUAL: PROYECTO COMPLETADO

✅ **Lighthouse: 100% en todos los marcadores** (Performance, Accessibility, Best Practices, SEO)
✅ **Migración completa de WordPress a Astro**
✅ **Despliegue automático funcionando**
✅ **Todas las optimizaciones implementadas**

**El sitio está listo para producción y funcionando perfectamente.**

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

- [X] **Inicializar Proyecto Astro**
    - Ejecutar: `npm create astro@latest formulafarma-new`
    - Selección: Plantilla "Empty" o "Blog", TypeScript (Relaxed o Strict), Instalar dependencias.
- [X] **Instalar Tailwind CSS**
    - Ejecutar: `npx astro add tailwind`
- [X] **Instalar Fuentes**
    - Ejecutar: `npm install @fontsource/inter @fontsource/merriweather`
- [X] **Configurar `tailwind.config.mjs`**
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

- [X] **Crear Layout Principal (`src/layouts/Layout.astro`)**
    - Implementar estructura HTML base.
    - Importar fuentes (`@fontsource`).
    - Crear Header minimalista (Logo tipográfico + Navegación simple).
    - Crear Footer sencillo.
- [X] **Crear Componente Tarjeta (`src/components/PostCard.astro`)**
    - Diseño tipo "Card" con sombra suave (`shadow-sm`).
    - Efecto hover (`hover:-translate-y-1`).
    - Mostrar: Fecha, Tags (tipo píldora), Título y Extracto.
- [X] **Configurar Colecciones de Contenido (`src/content/config.ts`)**
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

- [X] **Exportar XML de WordPress**
    - Ir a WP Admin -> Herramientas -> Exportar -> Todo el contenido.
- [X] **Ejecutar Script de Conversión**
    - Crear carpeta temporal fuera del proyecto.
    - Ejecutar: `npx wordpress-export-to-markdown`
    - **Configuración crítica del wizard:**
      - *Download images:* Yes
      - *Image folder:* `images`
      - *Image prefix:* `/images/`
- [X] **Importar al Proyecto Astro**
    - Mover archivos `.md` a `src/content/blog/`.
    - Mover carpeta de imágenes a `public/images/`.
- [X] **Sanear Frontmatter**
    - Revisar que los `.md` cumplan con el esquema definido en `config.ts` (Fase 2).
    - Borrar campos innecesarios de WordPress (id, post_id, status, etc) si molestan.
- [X] **Migración de Páginas Estáticas (EXTRA)**
    - Páginas creadas: Sobre mí, Contacto, Aviso Legal, Cookies, Privacidad.
    - Contenido migrado y convertido a HTML con estilos prose.
- [X] **Formulario de Contacto Funcional (EXTRA)**
    - Formulario con validación y envío SMTP.
    - Honeypot anti-spam implementado.
    - Rate limiting (5 envíos por hora por IP).
    - Casilla de privacidad obligatoria con enlace a política.

---

## 📄 FASE 4: Páginas y Rutas

- [X] **Crear Página de Inicio (`src/pages/index.astro`)**
    - Añadir sección "Hero" (Foto + Bio corta + "Farmacéutico Híbrido").
    - Listar los últimos posts usando `getCollection('blog')`.
    - Implementar Grid de `PostCard`.
- [X] **Crear Plantilla de Post Individual (`src/pages/blog/[...slug].astro`)**
    - Usar `getStaticPaths` para generar rutas.
    - Usar clase `prose` de Tailwind para estilizar el Markdown automáticamente.
    - Navegación anterior/siguiente post implementada.
    - Metadata (fecha, tags, imagen destacada) incluida.
- [X] **Crear Página de Listado de Blog (`src/pages/blog/index.astro`)**
    - Listar todos los posts del blog.
    - Implementar Grid de `PostCard`.

---

## 🚀 FASE 5: Despliegue (CI/CD en Raiola)

- [X] **Preparar GitHub**
    - Crear repo privado/público.
    - Subir código (sin `node_modules` ni `dist`).
- [X] **Configurar Secretos en GitHub**
    - Settings -> Secrets -> Actions.
    - Añadir: `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`, y todos los secretos SMTP.
- [X] **Crear Workflow (.github/workflows/deploy.yaml)**
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
- [X] **Backup Manual (IMPORTANTE)**
    - Entrar al FTP de Raiola y mover todo el `public_html` actual a una carpeta `_OLD_WP`.

---

## ✅ Checklist Final de Lanzamiento

- [X] Revisar SEO (Meta tags en Layout, Open Graph, JSON-LD).
- [X] Comprobar enlaces rotos de imágenes migradas.
- [X] Validar responsive en móvil/tablet.
- [X] Hacer Push a Main y verificar que la web cambia sola.
- [X] Formulario de contacto funcional y probado.
- [X] Protección anti-spam implementada (honeypot + rate limiting).

---

## 🎯 Próximos Pasos Recomendados

### Prioridad Alta (Pre-Lanzamiento)

1. **Mejorar SEO**
   - Añadir Open Graph tags en Layout para redes sociales
   - Implementar meta description dinámica por página/post
   - Añadir structured data (JSON-LD) para artículos
   - [X] Verificar que todas las imágenes tengan alt text
   - Crear sitemap.xml automático

2. **Validación y Testing**
   - [X] Comprobar enlaces rotos de imágenes migradas
   - [X] Validar responsive en móvil/tablet/desktop (menú hamburguesa añadido)
   - [X] Verificar performance con Lighthouse ✅ **100% en todos los marcadores conseguido**
   - [X] Implementar lazy loading en imágenes
   - [X] Añadir atributos width/height a imágenes
   - [X] Convertir imágenes PNG/JPEG a WebP (8 imágenes convertidas, referencias actualizadas)
   - [X] Probar formulario de contacto en producción
   - [X] Revisar que todos los posts se renderizan correctamente (build verificado, 4 posts generados sin errores)

3. **Backup y Despliegue Final**
   - [X] Hacer backup completo del sitio WordPress actual
   - [X] Mover WordPress a carpeta `_OLD_WP` en el servidor
   - [X] Verificar que el despliegue automático funciona correctamente
   - [X] Probar la web en producción antes de hacer el switch final

### Prioridad Media (Post-Lanzamiento)

4. **Optimizaciones de Performance**
   - [X] Añadir lazy loading para imágenes
   - [X] Añadir atributos width/height a imágenes
   - [X] Optimizar imágenes (convertir a formato WebP) - 8 imágenes convertidas exitosamente
   - [ ] Implementar preload para fuentes críticas
   - [X] Minificar CSS/JS en producción (Astro lo hace automáticamente)
   - [ ] Considerar CDN para assets estáticos

5. **Mejoras de UX**
   - Añadir animaciones sutiles en transiciones
   - Mejorar feedback visual en formularios
   - Considerar modo oscuro (opcional)
   - Añadir breadcrumbs en posts

### Prioridad Baja (Mejoras Futuras)

6. **Funcionalidades Adicionales**
   - Sistema de búsqueda de posts
   - Filtros por categorías/tags
   - RSS feed para suscriptores
   - [X] Sitemap.xml automático (ya implementado en src/pages/sitemap.xml.ts)
   - [ ] Sistema de comentarios con backend propio (preferencia: sin herramientas externas, sin anuncios)
     - **Nota:** Cuando se implemente, será con backend propio (PHP/Node.js)
     - Login con Google/Facebook mediante OAuth
     - Base de datos propia para comentarios
     - Moderación manual/automática
     - **Complejidad estimada:** 8-15 horas de desarrollo
     - **Requisitos:** Servidor con base de datos (MySQL/PostgreSQL)

---

## 🔍 Cómo hacer prueba de Lighthouse

Lighthouse es una herramienta de Google que analiza el rendimiento, accesibilidad, mejores prácticas y SEO de tu sitio web.

### Opción 1: Desde Chrome DevTools (Recomendado)

1. **Abre tu sitio en Chrome**: Ve a `https://formulafarma.com` (o la URL de producción)

2. **Abre DevTools**:
   - Presiona `F12` o `Ctrl+Shift+I` (Windows/Linux)
   - O `Cmd+Option+I` (Mac)
   - O clic derecho → "Inspeccionar"

3. **Abre Lighthouse**:
   - Ve a la pestaña **"Lighthouse"** (si no la ves, busca en el menú `>>`)
   - O presiona `Ctrl+Shift+P` y escribe "Lighthouse"

4. **Configura la prueba**:
   - Selecciona las categorías que quieres analizar:
     - ✅ Performance (Rendimiento)
     - ✅ Accessibility (Accesibilidad)
     - ✅ Best Practices (Mejores Prácticas)
     - ✅ SEO
   - Elige el dispositivo: **Desktop** o **Mobile**
   - Haz clic en **"Analyze page load"** o **"Generar informe"**

5. **Revisa los resultados**:
   - Cada categoría mostrará una puntuación de 0-100
   - **Objetivo**: Conseguir 90+ en todas las categorías
   - Revisa las sugerencias de mejora que aparecen

### Opción 2: Desde la línea de comandos (CLI)

Si prefieres hacerlo desde terminal:

```bash
# Instalar Lighthouse globalmente
npm install -g lighthouse

# Ejecutar análisis
lighthouse https://formulafarma.com --view

# O guardar el informe en HTML
lighthouse https://formulafarma.com --output html --output-path ./lighthouse-report.html
```

### Opción 3: Extensiones de navegador

- **Lighthouse Chrome Extension**: Búscala en Chrome Web Store
- **PageSpeed Insights**: https://pagespeed.web.dev/ (herramienta online de Google)

### Qué revisar en los resultados

1. **Performance (Rendimiento)**:
   - First Contentful Paint (FCP) < 1.8s
   - Largest Contentful Paint (LCP) < 2.5s
   - Time to Interactive (TTI) < 3.8s

2. **Accessibility (Accesibilidad)**:
   - Todas las imágenes tienen alt text ✅
   - Los elementos interactivos son accesibles
   - El contraste de colores es adecuado

3. **Best Practices (Mejores Prácticas)**:
   - HTTPS habilitado
   - Sin errores en consola
   - Imágenes optimizadas

4. **SEO**:
   - Meta tags presentes ✅
   - Structured data (JSON-LD) ✅
   - Sitemap.xml accesible ✅

### Mejoras comunes después de Lighthouse

Si encuentras problemas, las soluciones típicas son:
- Optimizar imágenes (convertir a WebP, comprimir)
- Añadir `loading="lazy"` a imágenes
- Minificar CSS/JS
- Preload de fuentes críticas
- Eliminar recursos no utilizados

---

## 🎯 PRÓXIMOS PASOS RECOMENDADOS (Post-100% Lighthouse)

Ahora que has conseguido **100% en todos los marcadores de Lighthouse**, aquí tienes las opciones para continuar:

### Opción 1: Mantener y Crear Contenido (Recomendado) ⭐

**El sitio está perfecto técnicamente. Ahora es momento de:**
- ✅ Escribir nuevos posts para el blog
- ✅ Compartir el contenido en redes sociales
- ✅ Monitorear el rendimiento y analytics
- ✅ Escuchar feedback de usuarios

**Ventaja:** El sitio ya es excelente, enfócate en el contenido.

### Opción 2: Mejoras de UX (Opcional)

Si quieres añadir funcionalidades adicionales:

1. **Breadcrumbs en posts** (Navegación mejorada)
   - Impacto: UX medio
   - Esfuerzo: Bajo (1-2 horas)
   - Valor: Ayuda a la navegación y SEO

2. **Sistema de búsqueda** (Funcionalidad útil)
   - Impacto: UX alto
   - Esfuerzo: Medio (4-6 horas)
   - Valor: Muy útil cuando tengas más posts

3. **Filtros por tags** (Organización)
   - Impacto: UX medio
   - Esfuerzo: Medio (3-4 horas)
   - Valor: Útil con más contenido

4. **RSS Feed** (Para suscriptores)
   - Impacto: Engagement
   - Esfuerzo: Bajo (1 hora)
   - Valor: Permite suscripciones

5. **Sistema de comentarios con backend propio** (Futuro)
   - Impacto: Engagement alto
   - Esfuerzo: Alto (8-15 horas)
   - Valor: Permite feedback directo de usuarios
   - **Decisión:** Implementar con backend propio, sin herramientas externas, sin anuncios
   - **Tecnología sugerida:** PHP (ya tienes servidor PHP) + MySQL + OAuth (Google/Facebook)
   - **Componentes necesarios:**
     - API REST para comentarios (crear, leer, moderar)
     - Base de datos para almacenar comentarios
     - Sistema de autenticación OAuth (Google/Facebook)
     - Panel de moderación
     - Protección anti-spam (similar al formulario de contacto)

### Opción 3: Optimizaciones Adicionales (No Prioritarias)

Ya tienes 100%, pero si quieres ir más allá:

- **Preload de fuentes críticas**: +1-2 puntos (ya estás en 100%)
- **CDN para assets**: Mejora global, pero el sitio ya es rápido
- **Animaciones sutiles**: Mejora percepción, no métricas

### Recomendación Final

**🎯 Enfócate en crear contenido de calidad.** 

El sitio técnicamente es perfecto. Los usuarios valoran más el contenido útil que funcionalidades extra. Cuando tengas 10-15 posts, entonces considera añadir búsqueda y filtros.

**Mantén el sitio actualizado:**
- Revisa Lighthouse cada mes
- Actualiza dependencias trimestralmente
- Añade contenido regularmente

¡Felicidades por el logro! 🎉