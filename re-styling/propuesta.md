# **Prompt para Cursor: Re-styling Completo de FormulaFarma (Estilo Brutalista/Maker)**

Actúa como un experto en Astro, Tailwind CSS y Diseño UI/UX.  
Vamos a realizar un "rebranding" total del proyecto actual formulafarma.com.  
Contexto:  
El sitio pasa de ser una farmacia corporativa a un Laboratorio Personal (Maker/Teatro/Opinión).  
El estilo visual será "Brutalismo Maker": bordes gruesos, sombras duras, tipografías monoespaciadas mezcladas con serifas clásicas.  
**Objetivos Principales:**

1. Configurar Tailwind con la nueva identidad visual.  
2. Reemplazar Layout.astro y index.astro con el nuevo diseño.  
3. **CRÍTICO:** Mantener funcionales las páginas de contacto, política de privacidad, cookies y el sistema de comentarios (PHP/SQLite). Solo debemos cambiar su estética (CSS), no su lógica (JS/PHP/HTML Forms).

## **PASO 1: Configuración de Dependencias y Tailwind**

1. Asegúrate de que lucide-astro esté instalado. Si no, sugiéreme el comando para instalarlo.  
2. Modifica el archivo tailwind.config.mjs (o .cjs) para incluir las nuevas fuentes y sombras personalizadas.  
   Copia y pega esta configuración en theme.extend:  
   fontFamily: {  
       tech: \["'Space Mono'", 'monospace'\], // Para código, datos, maker  
       human: \["'Playfair Display'", 'serif'\], // Para teatro, historia, humanidades  
   },  
   boxShadow: {  
       'brutal': '6px 6px 0px \#1a1a1a',  
       'brutal-hover': '8px 8px 0px \#1a1a1a',  
       'brutal-active': '2px 2px 0px \#1a1a1a',  
   }

## **PASO 2: Actualizar el Layout Global**

Edita src/layouts/Layout.astro.

* Fuentes: Añade el import de Google Fonts en el \<head\>:  
  \<link href="https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400\&family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400\&display=swap" rel="stylesheet"\>  
* **Estilos Globales:** Añade estos estilos (puedes usar \<style is:global\>):  
  body {  
      background-color: \#f8f7f5;  
      color: \#1a1a1a;  
      background-image: radial-gradient(\#d1d1d1 1px, transparent 1px);  
      background-size: 20px 20px;  
  }  
  /\* Scrollbar Brutalista \*/  
  ::-webkit-scrollbar { width: 10px; }  
  ::-webkit-scrollbar-track { background: \#f0f0f0; border-left: 2px solid \#1a1a1a; }  
  ::-webkit-scrollbar-thumb { background: \#1a1a1a; }

  /\* Clase de utilidad para cajas \*/  
  .brutal-box {  
      border: 2px solid \#1a1a1a;  
      box-shadow: 6px 6px 0px \#1a1a1a;  
      transition: all 0.2s ease;  
      background-color: white;  
  }  
  .brutal-box:hover {  
      transform: translate(-2px, \-2px);  
      box-shadow: 8px 8px 0px \#1a1a1a;  
  }

* **Estructura:** El \<body\> debe tener las clases: flex flex-col min-h-screen border-x-0 md:border-x-2 border-black max-w-7xl mx-auto.

## **PASO 3: Nueva Página de Inicio (Index)**

Reemplaza completamente el contenido de src/pages/index.astro con el siguiente código.  
Nota: Asegúrate de importar los iconos de lucide-astro correctamente al inicio del archivo.  
\---  
import Layout from '../layouts/Layout.astro';  
import { Cpu, Drama, Dice5, Bot, ChevronRight, Github, Linkedin, Mail } from 'lucide-astro';  
\---

\<Layout title="FormulaFarma | El Laboratorio de Abel Eiras"\>  
    \<\!-- Top Bar / Navigation \--\>  
    \<header class="border-b-2 border-black sticky top-0 bg-\[\#f8f7f5\] z-50"\>  
        \<div class="flex flex-col md:flex-row justify-between items-center p-4"\>  
            \<div class="flex items-center gap-2"\>  
                \<div class="w-8 h-8 bg-black text-white flex items-center justify-center font-tech font-bold text-xl"\>F\</div\>  
                \<h1 class="font-tech text-xl font-bold tracking-tighter uppercase"\>FormulaFarma\<span class="text-xs ml-2 bg-yellow-300 px-1 border border-black text-black"\>By Abel Eiras\</span\>\</h1\>  
            \</div\>  
            \<nav class="mt-4 md:mt-0"\>  
                \<ul class="flex space-x-6 font-tech text-sm font-bold uppercase"\>  
                    \<li\>\<a href="/\#maker" class="hover:bg-black hover:text-white px-2 py-1 transition-colors"\>Maker\</a\>\</li\>  
                    \<li\>\<a href="/\#escena" class="hover:bg-black hover:text-white px-2 py-1 transition-colors"\>Escena\</a\>\</li\>  
                    \<li\>\<a href="/contacto" class="underline decoration-2 underline-offset-4"\>Contacto\</a\>\</li\>  
                \</ul\>  
            \</nav\>  
        \</div\>  
    \</header\>

    \<\!-- Hero Section \--\>  
    \<section class="grid grid-cols-1 md:grid-cols-12 min-h-\[60vh\] border-b-2 border-black"\>  
        \<div class="md:col-span-7 p-8 md:p-16 flex flex-col justify-center border-b-2 md:border-b-0 md:border-r-2 border-black bg-white"\>  
            \<p class="font-tech text-sm text-gray-500 mb-4"\>\> init\_personal\_protocol.exe\</p\>  
            \<h2 class="font-human text-5xl md:text-7xl font-black leading-\[0.9\] mb-6"\>  
                No vendo \<br\>fórmulas. \<br\>\<span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-700 to-indigo-600"\>Las invento.\</span\>  
            \</h2\>  
            \<p class="font-tech text-base md:text-lg leading-relaxed text-gray-800 mb-8 border-l-4 border-yellow-400 pl-4"\>  
                Bienvenidos a \<strong\>FormulaFarma\</strong\>. Antes era una farmacia. Ahora es el laboratorio donde mezclo   
                impresión 3D, guiones de teatro, historia de Galicia y crítica al algoritmo.  
            \</p\>  
        \</div\>

        \<div class="md:col-span-5 bg-blue-50 relative overflow-hidden flex items-center justify-center p-8"\>  
            \<div class="relative z-10 text-center"\>  
                \<div class="w-48 h-64 border-2 border-black bg-white mx-auto relative rotate-3 shadow-\[8px\_8px\_0px\_rgba(0,0,0,1)\] flex flex-col items-center justify-center p-4"\>  
                    \<Bot class="w-16 h-16 mb-2 text-gray-800" /\>  
                    \<span class="font-tech text-xs font-bold mt-2"\>PROTOTIPO \#04\</span\>  
                \</div\>  
            \</div\>  
        \</div\>  
    \</section\>

    \<\!-- Grid de Intereses \--\>  
    \<section class="p-8 md:p-12 bg-\[\#f8f7f5\]"\>  
        \<div class="flex justify-between items-end mb-12 border-b-2 border-black pb-4"\>  
            \<h3 class="font-human text-4xl font-bold"\>Mis Ingredientes Activos\</h3\>  
        \</div\>  
        \<div class="grid grid-cols-1 md:grid-cols-3 gap-8"\>  
            \<\!-- Maker \--\>  
            \<article class="brutal-box p-6 flex flex-col h-full"\>  
                \<Cpu class="w-12 h-12 mb-4" /\>  
                \<h4 class="font-human text-2xl font-bold mb-2"\>Cultura Maker\</h4\>  
                \<p class="font-tech text-xs text-gray-600"\>Impresión 3D, robótica y reparación.\</p\>  
            \</article\>  
            \<\!-- Escena \--\>  
            \<article class="brutal-box p-6 flex flex-col h-full"\>  
                \<Drama class="w-12 h-12 mb-4" /\>  
                \<h4 class="font-human text-2xl font-bold mb-2"\>Teatro & Raíces\</h4\>  
                \<p class="font-tech text-xs text-gray-600"\>Historia de Galicia y dramaturgia.\</p\>  
            \</article\>  
            \<\!-- Opinión \--\>  
            \<article class="brutal-box p-6 flex flex-col h-full"\>  
                \<Dice5 class="w-12 h-12 mb-4" /\>  
                \<h4 class="font-human text-2xl font-bold mb-2"\>Vida Analógica\</h4\>  
                \<p class="font-tech text-xs text-gray-600"\>Juegos de mesa, baile y crítica digital.\</p\>  
            \</article\>  
        \</div\>  
    \</section\>  
\</Layout\>

## **PASO 4: Adaptación de Páginas Funcionales (Contacto, Cookies, Privacidad)**

1. Abre src/pages/contact.astro (o donde esté tu formulario).  
2. **IMPORTANTE:** No borres la etiqueta \<form\> ni su lógica JS/PHP.  
3. Envuelve el contenido principal en el nuevo \<Layout\>.  
4. Al contenedor principal del formulario, aplícale la clase brutal-box p-8 bg-white max-w-2xl mx-auto my-12.  
5. A los input y textarea, aplícales estas clases para que coincidan con el estilo:  
   w-full border-2 border-black p-3 font-tech text-sm focus:outline-none focus:bg-yellow-50 mb-4  
6. Al botón de "Enviar", aplícale:  
   bg-black text-white font-tech font-bold uppercase py-3 px-6 hover:bg-gray-800 transition-colors cursor-pointer border-2 border-transparent hover:border-black  
7. Haz lo mismo para cookies.astro y privacidad.astro:  
   * Usa el nuevo \<Layout\>.  
   * Mete el texto legal dentro de un \<div class="brutal-box p-8 bg-white my-8 prose font-human"\>.

## **PASO 5: Sistema de Comentarios**

Si tienes un componente de comentarios (ej: Comments.astro o similar):

1. Mantén la lógica de fetch a tu backend PHP SQLite intacta.  
2. Aplica los mismos estilos de input y button que definimos en el Paso 4 para que visualmente encaje con el nuevo diseño brutalista.  
3. Para la lista de comentarios existentes, usa un estilo de "tarjeta simple":  
   border-b-2 border-black py-4 font-tech text-sm.

Resumen de ejecución:  
Procede a aplicar estos cambios archivo por archivo, confirmando que la web compila correctamente y que el formulario de contacto sigue enviando datos tras el rediseño.