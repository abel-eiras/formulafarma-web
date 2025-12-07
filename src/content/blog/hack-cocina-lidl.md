---
title: "Hacking á cociña do Lidl: De xoguete inofensivo a ameaza hídrica"
date: 2025-12-07
excerpt: "Como lle metín un circuíto pechado de auga a unha marabillosa cociña de 40€ do Lidl. E por que agora está clausurada co tapón de colutorio."
tags:
  - "impresion3d"
  - "lidl"
  - "fail"
  - "paternidade"
  - "electronica"
category: "Cultura Maker"
image: "/images/blog/hack-lidl/presentando.webp"
---

Din que as instrucións están para lelas. E eu sempre fago caso delas. Excepto na parte que din que se precisan dúas persoas contentas para montar un moble. Cunha persoa especialmente optimista sole ser suficiente. Polo demais, sigo todo ao pé da letra.

Outra cousa son as alegacións do tipo "idade recomendada". Para min, non son máis que unha opinión do fabricante. Na caixa da famosa cociña do Lidl pon: "Recomendado para maiores de 3 anos". A miña filla apenas ten un ano e uns poucos meses.

¿Detívome isto? Evidentemente non. Valendo 40€ e con toda a trangallada que traía era imposible non mercala.

![A cociña do Lidl montada e lista para o hack](/images/blog/hack-lidl/presentando.webp)

A cociña é unha fantasía: placa de indución con sons e luces, forno con luz, lavalouza, cubo para o lixo, campá extractora, xeos de madeira que caen no vaso ao empurrar como nas neveiras de verdade... pero tiña un fallo imperdoable: **a billa era de mentira**. Un cacho de plástico oco. E nesta casa respectamos as leis da termodinámica: se hai billa, ten que saír auga.

## Fase 1: A fontanería

A idea era simple: un **circuíto pechado**.

1. Unha billa de AliExpress desas para garrafas de 5L. Apenas 5€.
2. Un cacharro de plástico debaixo do moble como depósito. Aproveitei un vello tupper que empregaba para outros traballos manuais.
3. Un funil impreso en 3D (modificado dun STL que atopei por aí) para recoller a auga do pío e devolvela ao bote.
4. Un soporte para anclar o cacharro dentro do moble da cociña e colocalo na posición correcta para recoller a auga do funil.

![Detalle do funil 3D](/images/blog/hack-lidl/funil.webp)
![O vertedoiro furado](/images/blog/hack-lidl/vertedoiro.webp)

Comezando polo final fixen o soporte en **Tinkercad** para fixar a billa ao moble e imprimino. Despois desmontei a billa de plástico e para poder poñer a nova tiven que facerlle unha base. Deseñeina tamén no Tinkercad para facela a medida. Logo diso, furei o vertedoiro da cociña cun punzón e pegueille o funil.

![Soporte do depósito dentro do moble](/images/blog/hack-lidl/soporte.webp)

Pasei o tubo da billa por un dos buratos que tiña a cociña para os parafusos da billa de plástico e peguei a base da billa coa mítica cola termofusible. Non es ninguén sen unha pistoliña de cola termofusible. Listo, se non fose pola impresión 3D diría que en 5 minutos estaba feito.

## Fase 2: "Houston, temos un par de problemas de ergonomía"

Unha vez montado, deime de conta de dúas cousas:

1. **A altura:** O botón táctil da billa quedaba demasiado alto. A miña nena non chegaba nin de broma.
2. **A carga:** O porto USB quedaba pegado contra a "parede" da cociña. Para cargalo tiña que arrincar a billa ou furar o moble.

Unha persoa normal deixaríao estar. Un chambón nivel pro saca o soldador.

## Fase 3: Manipulando a billa

Nunca crin na garantía de nada, moito menos no que me chega de China. Así que sen dubidalo un segundo, desmontei a parte dos botóns da billa.

O plan: duplicar o botón e o porto de carga noutra parte máis accesible.

Soldei uns cables directamente aos pins do botón que acciona o motor da bomba. Tirei outros dous cables dende o porto de carga da batería.

![Soldando os cables á placa da billa](/images/blog/hack-lidl/destripandoabilla.webp)

Instalei un **botón externo** a un par de centímetros da base e puxen un **módulo de carga USB-C** nun lateral accesible para min.

Vale, se sabes de electricidade saberás que o que fixen coa batería non é o máis axeitado vendo as fotos, pero funciona. E como o motor só funciona se pulsas o botón, non hai risco de descarga completa da batería. De aí que poida empregar os pins BAT en lugar dos pins OUT do módulo de carga e me evite máis complicacións. Se non sabes de que falo non te preocupes. O caso é que está ben.

## O resultado: éxito técnico, desastre doméstico

Funciona? Vaites se funciona. A auga flúe, o circuíto recicla o líquido e o botón responde.

<video src="/images/blog/hack-lidl/final_comp.mp4" autoplay loop muted playsinline class="w-full border-2 border-black shadow-[6px_6px_0px_#000] my-8"></video>

O problema é que á miña nena **encántalle** acender a auga. O que non lle gusta tanto é **apagala**. Tampouco comparte a miña visión de que a auga debe permanecer **dentro** do pío. A súa visión é máis... expansiva. O pequeno moble de madeira cutre converteuse nun parque acuático en cuestión de segundos. Algo para o que non está deseñado e que probablemente reduza drasticamente a súa vida útil.

## A chambonada final…

Ante a ameaza de inundación, tomei unha medida drástica de enxeñería civil avanzada.

Non imprimín unha tapa de seguridade en 3D. Non programei un corte automático cun Arduino. Moito menos un temporizador para cortar a auga cada x segundos…

Collín o **tapón dun colutorio** e un par de **gomas elásticas**.

![A solución final: un tapón e gomas elásticas](/images/blog/hack-lidl/amanado.webp)

O botón quedou mecanicamente clausurado. A tecnoloxía punta vencida por un tapón de plástico reciclado. Agora a cociña ten un sistema de auga ultra-sofisticado que está desactivado ata que a nena aprenda como retirar as gomas. A ollo, cuestión de días.

**Lección do día:** Podes saber soldar, podes saber deseñar e imprimir en 3D, pero nunca poderás gañarlle ao gusto dun bebé pola auga.

