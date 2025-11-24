---
title: "BUSCARX: El día que dejé de llorar con BUSCARV (y tú también deberías)"
date: 2025-11-30
tags:
  - "excel-para-farmacia"
  - "excel-farmacia"  
  - "buscarx"  
  - "gestion-stock"  
  - "productividad"  
  - "analisis-de-datos"
  - "tutorial-excel"
image: "/images/buscarx.webp"
---
¿Alguna vez has sentido ganas de tirar el monitor por la ventana al ver un \#N/A en Excel?
A mí me pasaba constantemente.
Estás ahí, intentando cruzar el listado de lo que pediste al mayorista con el albarán de lo que te ha llegado, contando columnas con el dedo en la pantalla como si fueras un niño de preescolar: "A ver... la columna del código nacional es la A, y quiero el precio que está en la... 1, 2, 3... ¿14? ¿15?".
Escribes tu fórmula BUSCARV le das a Enter y... ¡PUM\! Error.
O peor aún. Funciona. Pero al día siguiente, tu compañero inserta una columna nueva porque quería añadir una nota, y de repente todo tu Excel se rompe y te devuelve ceros o datos que no tienen nada que ver.
Si te sientes identificado, tengo una buena noticia: **el sufrimiento se ha acabado.**
Hoy venimos a jubilar al dinosaurio. Hoy vamos a hablar de por qué BUSCARV es el Windows 95 de las fórmulas y por qué BUSCARX va a convertirse en tu nuevo mejor amigo. Siempre y cuándo tengas una versión reciente de Excel...

### **El problema de vivir en el pasado**

Durante años, los farmacéuticos que nos atrevíamos a tocar Excel hemos vivido esclavizados por las limitaciones de BUSCARV. Es una función que:

1. **Es lenta** (si tienes miles de referencias, vete a por café).
2. **Es tonta** (solo sabe buscar de izquierda a derecha; si el Código Nacional no está a la izquierda del nombre, estás jodido).
3. **Es frágil** (si mueves columnas, se rompe).

Pero Microsoft, en un alarde de piedad infinita, lanzó en la versión 2021 su sucesor: **BUSCARX**.
Y créeme, una vez que lo pruebas, no hay vuelta atrás. Es como pasar de contar monedas al cierre del turno a tener una máquina de cobro automática.

### **¿Cómo funciona esta maravilla?**

Vamos a un caso práctico, que es como se entienden las cosas.Imagínate que tienes dos listas:

* **Lista A (Tu Pedido):** Tienes los Códigos Nacionales (CN) de lo que pediste y una columan con la cantidad y el descuento acordado.
* **Lista B (Catálogo del Mayorista):** Tienes los CN, los nombres de los productos, el PVL y el tipo de IVA.

Con toda esa información podrías saber cuánto va a dar exactamente el pedido antes de recepcionarlo. La idea es simple, vamos a unir la información de la lista A con la de la lista B usando el CN como llave o dato en común. 
La estructura de la fórmula es insultantemente lógica:
\=BUSCARX(qué\_buscas; dónde\_lo\_buscas; qué\_quieres\_que\_te\_devuelva)

Ya está. No hay que contar columnas. No hay que poner "FALSO" al final para que la coincidencia sea exacta. La función BUSCARX lo hace por defecto, porque es más listo que BUSCARV.

#### **Ejemplo paso a paso**

Vamos a suponer lo siguiente:

* Queremos traernos el PVL a la lista A con los artículos de mi pedido. En mi lista los CN están en la primera columna. Por lo que el primer CN estará en la celda A2. Obviamente A1 es la celda del encabezado de la tabla dónde pondrá "Cn." o similar.
* La columna de CNs del catálogo está en la columna D.
* La columna de PVL del catálogo está en la columna E.

Tu fórmula sería:
\=BUSCARX(A2; D:D; E:E)

Traducción para humanos:
"Excel, búscame lo que hay en A2 dentro de la columna D, y cuando lo encuentres, devuélveme lo que haya en la misma fila de la columna E".

### **Los 3 superpoderes ocultos de BUSCARX**

Si lo anterior no te ha convencido, aquí vienen las tres razones por las que deberías empezar a usarlo hoy mismo:

#### 1\. El parámetro anti-errores

¿Qué pasa si buscas un CN que no existe en el catálogo? Con BUSCARV te saldría el odioso \#N/A. Con BUSCARX, puedes decirle qué poner si no lo encuentra, directamente en la fórmula.
\=BUSCARX(A2; D:D; E:E; "No existe este CN") o, lo que más suelo hacer yo, es poner "". Así dejará la celda vacía si no lo encuentra.

Adiós a tener que anidar funciones con SI.ERROR. Limpio, fácil de entender y elegante.

#### 2\. Busca hacia donde te dé la gana

Con BUSCARV, la columna donde buscabas tenía que estar a la izquierda del dato que querías recuperar. Si no, tenías que cortar y pegar columnas como un loco.
BUSCARX es ambidiestro. Busca a la izquierda, a la derecha, arriba o abajo. Le da igual. Tú solo señalas las columnas y él hace la búsqueda.

#### 3\. No se rompe si tocas la tabla

Como seleccionas columnas enteras (o rangos específicos) y no le dices "dame la columna número 5", si mañana insertas tres columnas nuevas entre medias, Excel actualiza las referencias solo y la fórmula sigue funcionando.

### **¿Tu Excel no tiene BUSCARX?**

Aquí viene el jarro de agua fría. Esta función solo está disponible en las versiones más recientes de Excel (Office 365 y Excel 2021 en adelante) y en la versión web gratuita.Si en la farmacia seguís usando Excel 2007 (que nos conocemos...), tienes dos opciones:

1. Usar la versión de Excel Online o Google Sheets (ahí se llama XLOOKUP).
2. Imprimir este post en la parte de atrás de un albarán de la cooperativa (para salvar el planeta), llevárselo a tu titular y decirle: "Por favor, actualicemos el Office, que cuesta menos menos que la cuota colegial y es mucho más útil".

### **Reto de la semana**

La próxima vez que tengas que cruzar datos —ya sea para revisar caducidades, comprobar precios o puntear facturas— **no uses BUSCARV**.
Fuerza a tu cerebro a usar BUSCARX. La primera vez tardarás 30 segundos más en escribirla porque no te saldrá de memoria. Para eso está el asistente de funciones. No la hagas en modo estoico a mano como lo haría yo. Empieza con el asistente. Verás que la segunda vez tardarás poco o nada. A la tercera, te preguntarás cómo has podido vivir sin ella tanto tiempo.
Y si te atascas, ya sabes dónde estoy. Mándame un mensaje y lloramos juntos si tu titular se niega a pagar un Excel nuevo.
