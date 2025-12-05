# **Prompt para Cursor: Implementación de i18n (Gallego como idioma principal)**

Vamos a transformar el proyecto actual en un sitio **bilingüe** utilizando el sistema de enrutamiento nativo de Astro.  
**Objetivos:**

1. **Idioma Principal (Default):** Gallego (gl). La raíz / servirá contenido en gallego.  
2. **Idioma Secundario:** Español (es). El contenido estará bajo el prefijo /es/.  
3. **Componente UI:** Un selector de idioma en el menú de navegación con estilo "Brutalista".

## **PASO 1: Configuración de Astro**

Modifica el archivo astro.config.mjs para habilitar el soporte i18n experimental/nativo de Astro.  
import { defineConfig } from 'astro/config';  
// ... otros imports

export default defineConfig({  
  // ... otras configuraciones  
  i18n: {  
    defaultLocale: "gl",  
    locales: \["gl", "es"\],  
    routing: {  
        prefixDefaultLocale: false // Importante: gl en /, es en /es/  
    }  
  }  
})

## **PASO 2: Gestión de Textos de UI (Traducciones Pequeñas)**

Crea un archivo src/i18n/ui.ts para manejar los textos fijos del menú y el footer.  
export const languages \= {  
  gl: 'Galego',  
  es: 'Español',  
};

export const defaultLang \= 'gl';

export const ui \= {  
  gl: {  
    'nav.maker': 'Cultura Maker',  
    'nav.scene': 'Teatro & Raíces',  
    'nav.opinion': 'Outras merdas', // Nota: mantener tono irreverente  
    'nav.contact': 'Contacto',  
    'hero.title': 'Non vendo fórmulas. Inventoas.',  
    'hero.subtitle': 'Benvidos a FormulaFarma. Antes era unha farmacia. Agora é o laboratorio onde mesturo impresión 3D, guións de teatro, historia de Galicia e crítica ao algoritmo.',  
  },  
  es: {  
    'nav.maker': 'Cultura Maker',  
    'nav.scene': 'Teatro & Raíces',  
    'nav.opinion': 'Otras mierdas',  
    'nav.contact': 'Contacto',  
    'hero.title': 'No vendo fórmulas. Las invento.',  
    'hero.subtitle': 'Bienvenidos a FormulaFarma. Antes era una farmacia. Ahora es el laboratorio donde mezclo impresión 3D, guiones de teatro, historia de Galicia y crítica al algoritmo.',  
  },  
} as const;

## **PASO 3: Reestructuración de Archivos (Routing)**

1. **Mover a Gallego (Root):**  
   * Toma el archivo src/pages/index.astro actual.  
   * Traduce sus textos "hardcoded" (el contenido HTML) al **Gallego**.  
   * Ejemplos: "Mis Ingredientes Activos" \-\> "Os meus Ingredientes Activos", "Ver Proyectos" \-\> "Ver Proxectos".  
2. **Crear versión Español:**  
   * Crea la carpeta src/pages/es/.  
   * Duplica el archivo index.astro dentro de src/pages/es/.  
   * En este archivo nuevo, mantén los textos en **Español**.  
3. **Páginas Legales y Contacto:**  
   * Mueve contacto.astro, cookies.astro, etc., a la raíz (si no lo están) y tradúcelos al Gallego.  
   * Crea sus duplicados en src/pages/es/contacto.astro, etc., en Español.

## **PASO 4: Componente Selector de Idioma (LanguagePicker)**

Crea el componente src/components/LanguagePicker.astro.  
Debe detectar la URL actual y ofrecer el enlace al otro idioma.  
**Requisitos de Estilo:**

* Debe seguir la estética "Brutal Box" (bordes negros, fuente monoespaciada).  
* Pequeño y discreto en el Header.

**Lógica sugerida:**  
\---  
import { languages } from '../i18n/ui';  
import { getLangFromUrl, useTranslatedPath } from '../i18n/utils'; // (Si necesitas crear utils, créalos)

const lang \= getLangFromUrl(Astro.url);  
const translatePath \= useTranslatedPath(lang);  
\---  
\<div class="flex gap-2 font-tech text-xs uppercase font-bold"\>  
    {Object.entries(languages).map((\[langKey, label\]) \=\> (  
        \<a   
            href={translatePath('/', langKey)}  
            class={\`px-2 py-1 border-2 border-black transition-all ${  
                lang \=== langKey   
                ? 'bg-black text-white'   
                : 'bg-white hover:bg-yellow-300'  
            }\`}  
        \>  
            {langKey}  
        \</a\>  
    ))}  
\</div\>

## **PASO 5: Integración en el Layout**

1. Crea (si no existe) un archivo src/i18n/utils.ts con las funciones helper estándar de la documentación de Astro (getLangFromUrl, useTranslations, useTranslatedPath).  
2. Actualiza src/layouts/Layout.astro y las páginas index.astro:  
   * Importa la función de traducción useTranslations.  
   * Sustituye los textos del menú de navegación con {t('nav.maker')}, etc.  
   * Incluye el componente \<LanguagePicker /\> dentro del \<header\>, preferiblemente al lado del logo o a la derecha del menú.

Nota Final:  
Al ejecutar estos cambios, asegúrate de que al entrar en localhost:4321/ veo la web en Gallego y al pulsar "ES" o ir a localhost:4321/es/ la veo en Español.