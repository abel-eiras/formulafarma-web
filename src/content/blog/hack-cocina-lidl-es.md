---
title: "Hackeando la cocina del Lidl: De juguete inofensivo a amenaza hídrica"
date: 2025-12-07
excerpt: "Cómo le metí un circuito cerrado de agua a una maravillosa cocina de 40€ del Lidl. Y por qué ahora está clausurada con el tapón de enjuague bucal."
tags:
  - "impresion3d"
  - "lidl"
  - "fail"
  - "paternidad"
  - "electronica"
category: "Cultura Maker"
lang: "es"
translationSlug: "hack-cocina-lidl"
image: "/images/blog/hack-lidl/presentando.webp"
---

Dicen que las instrucciones están para leerse. Y yo siempre les hago caso. Excepto en la parte que dicen que se necesitan dos personas contentas para montar un mueble. Con una persona especialmente optimista suele ser suficiente. Por lo demás, sigo todo al pie de la letra.

Otra cosa son las alegaciones del tipo "edad recomendada". Para mí, no son más que una opinión del fabricante. En la caja de la famosa cocina del Lidl pone: "Recomendado para mayores de 3 años". Mi hija apenas tiene un año y unos pocos meses.

¿Me detuvo esto? Evidentemente no. Costando 40€ y con toda la parafernalia que traía era imposible no comprarla.

La cocina es una fantasía: placa de inducción con sonidos y luces, horno con luz, fregadero, cubo para la basura, campana extractora, hielos de madera que caen en el vaso al empujar como en las neveras de verdad... pero tenía un fallo imperdonable: **el grifo era de mentira**. Un trozo de plástico hueco. Y en esta casa respetamos las leyes de la termodinámica: si hay grifo, tiene que salir agua.

## Fase 1: La fontanería

La idea era simple: un **circuito cerrado**.

1. Un grifo de AliExpress de esos para garrafas de 5L. Apenas 5€.
2. Un recipiente de plástico debajo del mueble como depósito. Aproveché un viejo tupper que empleaba para otros trabajos manuales.
3. Un embudo impreso en 3D (modificado de un STL que encontré por ahí) para recoger el agua del fregadero y devolverla al bote.
4. Un soporte para anclar el recipiente dentro del mueble de la cocina y colocarlo en la posición correcta para recoger el agua del embudo.

Empezando por el final hice el soporte en **Tinkercad** para fijar el grifo al mueble y lo imprimí. Después desmonté el grifo de plástico y para poder poner el nuevo tuve que hacerle una base. La diseñé también en Tinkercad para hacerla a medida. Luego de eso, taladré el fregadero de la cocina con un punzón y le pegué el embudo.

<img 
  src="/images/blog/hack-lidl/funil.webp" 
  alt="Detalle del embudo 3D" 
  class="w-full h-auto border-2 border-black shadow-[6px_6px_0px_#000] my-8"
/>
<img 
  src="/images/blog/hack-lidl/vertedoiro.webp" 
  alt="El desagüe" 
  class="w-full h-auto border-2 border-black shadow-[6px_6px_0px_#000] my-8"
/>
<img 
  src="/images/blog/hack-lidl/soporte.webp" 
  alt="Soporte dentro del mueble" 
  class="w-full h-auto border-2 border-black shadow-[6px_6px_0px_#000] my-8"
/>


Pasé el tubo del grifo por uno de los agujeros que tenía la cocina para los tornillos del grifo de plástico y pegué la base del grifo con la mítica cola termofusible. No eres nadie sin una pistola de cola termofusible. Listo, si no fuera por la impresión 3D diría que en 5 minutos estaba hecho.

<video src="/images/blog/hack-lidl/case_comp.mp4" autoplay loop muted playsinline class="w-full border-2 border-black shadow-[6px_6px_0px_#000] my-8"></video>

## Fase 2: "Houston, tenemos un par de problemas de ergonomía"

Una vez montado, me di cuenta de dos cosas:

1. **La altura:** El botón táctil del grifo quedaba demasiado alto. Mi niña no llegaba ni de broma.
2. **La carga:** El puerto USB quedaba pegado contra la "pared" de la cocina. Para cargarlo tenía que arrancar el grifo o taladrar el mueble.

Una persona normal lo dejaría estar. Un chapucero nivel pro saca el soldador.

## Fase 3: Manipulando el grifo

Nunca creí en la garantía de nada, mucho menos en lo que me llega de China. Así que sin dudarlo un segundo, desmonté la parte de los botones del grifo.

El plan: duplicar el botón y el puerto de carga en otra parte más accesible.

Soldé unos cables directamente a los pines del botón que acciona el motor de la bomba. Saqué otros dos cables desde el puerto de carga de la batería.

Instalé un **botón externo** a un par de centímetros de la base y puse un **módulo de carga USB-C** en un lateral accesible para mí.

<img 
  src="/images/blog/hack-lidl/destripandoabilla.webp" 
  alt="Esto se puede modificar" 
  class="w-full h-auto border-2 border-black shadow-[6px_6px_0px_#000] my-8"
/>
<img 
  src="/images/blog/hack-lidl/amanado.webp" 
  alt="Chapuza realizada" 
  class="w-full h-auto border-2 border-black shadow-[6px_6px_0px_#000] my-8"
/>


Vale, si sabes de electricidad sabrás que lo que hice con la batería no es lo más adecuado viendo las fotos, pero funciona. Y como el motor solo funciona si pulsas el botón, no hay riesgo de descarga completa de la batería. De ahí que pueda emplear los pines BAT en lugar de los pines OUT del módulo de carga y me evite más complicaciones. Si no sabes de qué hablo no te preocupes. El caso es que está bien.

## El resultado: éxito técnico, desastre doméstico

¿Funciona? Vaya si funciona. El agua fluye, el circuito recicla el líquido y el botón responde.

El problema es que a mi hija **le encanta** encender el agua. Lo que no le gusta tanto es **apagarla**. Tampoco comparte mi visión de que el agua debe permanecer **dentro** del fregadero. Su visión es más... expansiva. El pequeño mueble de madera cutre se convirtió en un parque acuático en cuestión de segundos. Algo para lo que no está diseñado y que probablemente reduzca drásticamente su vida útil.

<video src="/images/blog/hack-lidl/final_comp.mp4" autoplay loop muted playsinline class="w-full border-2 border-black shadow-[6px_6px_0px_#000] my-8"></video>

## La chapuza final…

Ante la amenaza de inundación, tomé una medida drástica de ingeniería civil avanzada.

No imprimí una tapa de seguridad en 3D. No programé un corte automático con un Arduino. Mucho menos un temporizador para cortar el agua cada x segundos…

Cogí el **tapón de un enjuague bucal** y un par de **gomas elásticas**.

El botón quedó mecánicamente clausurado. La tecnología punta vencida por un tapón de plástico reciclado. Ahora la cocina tiene un sistema de agua ultra-sofisticado que está desactivado hasta que la niña aprenda a retirar las gomas. A ojo, cuestión de días.

**Lección del día:** Puedes saber soldar, puedes saber diseñar e imprimir en 3D, pero nunca podrás ganarle al gusto de un bebé por el agua.

<img 
  src="/images/blog/hack-lidl/fin.webp" 
  alt="La solución final: un tapón y gomas elásticas" 
  class="w-full h-auto border-2 border-black shadow-[6px_6px_0px_#000] my-8"
/>