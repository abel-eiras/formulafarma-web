---
title: "Excel para Farmacéuticos: Cómo domar el caos de tu programa de gestión (Clase 1)"
date: 2025-07-27
tags:
  - "analisis-de-datos"
  - "datos-en-bruto"
  - "excel-para-farmacia"
  - "formato-de-celdas"
  - "formato-de-tabla"
  - "gestion-de-farmacia"
  - "hojas-de-calculo"
  - "importar-csv"
  - "libreoffice-calc"
  - "limpiar-datos"
  - "tutorial-excel"
image: "/images/excel.webp"
---


**Una advertencia antes de empezar:** si ya sabes algo sobre Excel o Calc, este post no es para ti. Puedes saltártelo, ir a por un café y volver la semana que viene. Te lo has ganado.

Para el resto de nosotros, los mortales que abrimos una hoja de cálculo y sentimos un sudor frío recorriéndonos la espalda, bienvenidos. Esta es vuestra clase.

Hoy no vamos a hablar de visiones de futuro ni de inteligencia artificial. Hoy vamos a bajar al barro. Vamos a enfrentarnos a esa bestia que nuestro programa de gestión nos escupe sin piedad: los datos en bruto.

### El Gran Desencuentro: Por qué tu programa de gestión parece que te odia un poco

El problema fundamental de la mayoría de los programas de gestión de farmacia es que **están hechos por informáticos, no por farmacéuticos.**

Está claro que los hicieron con su mejor intención, de eso estoy seguro. Pero no conocen el trabajo real de sus usuarios. No entienden el caos de un martes por la mañana, la urgencia de encontrar un dato mientras tienes a tres personas en la cola, ni la frustración de querer un informe concreto sobre algo y no poder conseguirlo.

Esto provoca cierto desapego que hace que los farmacéuticos y técnicos de la farmacia no se involucren en aprender a usar el programa a fondo. Aprendemos lo justo para salvar el día: para dispensar, para facturar y para enviar los pedidos. Y el resto, lo seguimos haciendo a mano o por fuera del programa. El resultado es un divorcio tecnológico en toda regla. Por ejemplo, a la hora de analizar datos, el programa te ofrece dos opciones, y ambas son malas:

1. **La avalancha de datos en bruto:** Te exporta la información en un Excel o CSV sin formato, con columnas duplicadas y datos que tienes que limpiar a mano durante horas. Es más, habitualmente la información que buscas no está en una sola pestaña del programa, por lo que necesitarás saber cómo combinar información de diferentes archivos. Llegaremos a eso, pero hoy no.

3. **El resumen inútil:** Te ofrece un informe predefinido, un gráfico bonito pero inamovible, con las cuatro métricas que el programador consideró relevantes en 2012. No puedes profundizar, no puedes cruzar datos, no puedes hacerte nuevas preguntas o filtros que no vengan por defecto. Te tienes que conformar con lo que te dan y creértelo.

Y es aquí donde empieza nuestra liberación. Para poder hacer análisis a medida, necesitamos esos datos en bruto. Pero antes de analizarlos, tenemos que domarlos. Tenemos que prepararlos para la batalla.

### El Paciente Cero: Preparando el Quirófano

Vale, ya tenemos claro el problema. Ahora vamos a la solución. En esta primera clase, nuestro único objetivo es uno: coger el archivo de datos caótico que te he preparado (nuestro "paciente cero"), meterlo en nuestra hoja de cálculo y formatearlo (y veremos qué significa eso). No vamos a analizar nada. No vamos a hacer fórmulas complejas. Solo vamos a preparar la mesa de operaciones para la próxima clase.

#### **Paso 0: Elige tus armas - Excel vs. LibreOffice Calc**

Antes de empezar, necesitas saber cuál es tu herramienta. En este blog hablaré siempre de estas dos opciones:

- **Microsoft Excel:** El estándar de la industria. Es de pago, pero es probable que ya lo tengas instalado. Es potentísimo y lo que aprendas te servirá en cualquier entorno profesional. Las licencias son muy asequibles y, ya que en la farmacia lo pagas, te animo a que confirmes que tienes una versión actualizada. No un Excel 2013...

- **LibreOffice Calc:** La alternativa gratuita y de código abierto. Es como el primo feo de Excel, pero igual de fuerte y casi tan listo. Si no tienes Excel y no quieres pagar (ni arriesgarte a usar una versión pirata), esta es tu opción. Es un programa excelente.

![Captura de Excel y LibreOffice Calc](/images/01.-Excel-Vs-Calc-1024x227.webp)

En este tutorial, te explicaré cómo hacer cada paso en ambos programas. Tú solo tienes que seguir las instrucciones del que tengas. También lo podrías hacer con Google Sheets, la alternativa online de Google. Pero a mí ya no me apetece meterme la paliza de explicarlo para los tres. Me gusta hacerlo con Calc por ser software libre, que si no, ni eso. Si esto del software libre, el código abierto y demás vocablos te suenan a chino, no te preocupes, que probablemente lo explicaré en cuanto nos metamos con temitas de inteligencia artificial.

#### **Paso 1: Descargando a nuestro "paciente"**

Para poder practicar, necesitas el archivo de datos. He utilizado Gemini, la IA de Google, para crear un CSV de ejemplo con 90 registros de ventas, con toda la "basura" típica de un export de farmacia.

[Archivo CSV de ventas](https://formulafarma.com/wp-content/uploads/2025/07/ventas_falsas.csv)[Descarga](https://formulafarma.com/wp-content/uploads/2025/07/ventas_falsas.csv)

Descárgalo y guárdalo en «Descargas», en una carpeta llamada «Formulafarma» o en el «Escritorio» con el resto de archivos basura que tienes por ahí.

#### **Paso 2: La Importación**

**(Aquí iría tu desplegable para "Microsoft Excel")**

##### **Opción A: Importar con Microsoft Excel**

Ahora viene el momento de la verdad. No vamos a hacer doble clic en el archivo. No, eso en Excel es un error. Nos encontraremos con textos mal escritos, sin tildes y con símbolos raros. Vamos a enseñarle a nuestro programa a leerlo correctamente.

1. Abre Excel y crea un **Libro en blanco**.

3. Ve a la pestaña de arriba que dice **`Datos`**.

5. Busca un botón que ponga algo como **`Desde texto/CSV`**. Haz clic ahí.

7. Se abrirá una ventana para que busques el archivo que acabas de descargar. Selecciónalo.

9. Ahora Excel te mostrará una ventana de cómo se van a ver los datos cuando los cargues. Fíjate en dos opciones clave:
    - **Origen del archivo:** Para que Excel entienda bien las tildes, las "ñ" y los símbolos raros, busca la opción que ponga **`65001: Unicode (UTF-8)`**. Debería venir por defecto.
    
    - **Delimitador:** Asegúrate de que esté seleccionada la opción **`Punto y coma`**. Verás cómo los datos se ordenan en columnas perfectas en la vista previa. Lo suele detectar sin problema.

11. Una vez ajustado, haz clic en el botón verde de abajo que dice **`Cargar`**. Nos quedarán los datos ya en formato tabla. Veremos en otras clases lo que implica este formato.

![Ventana de importación de datos en Excel mostrando la vista previa de los datos CSV con delimitador punto y coma](/images/02.-Excel-import.webp)

##### **Opción B: Importar con LibreOffice Calc**

1. Ábrelo con LibreOffice Calc. A diferencia de Excel, aquí sí puedes abrir directamente el archivo CSV que descargaste.

3. Al hacerlo, se abrirá automáticamente el asistente de importación, que es muy parecido al de Excel.

5. Asegúrate de que la configuración sea la correcta:
    - **Juego de caracteres:** Aquí es donde le decimos el "idioma". Elige **`Unicode (UTF-8)`**.
    
    - **Opciones de separador:** Marca la casilla **`Punto y coma`** y desmarca cualquier otra que pueda estar seleccionada (como "Tabulador").

7. En la vista previa de abajo, deberías ver tus datos perfectamente ordenados en columnas. Si se ve bien, haz clic en **`Aceptar`**.

![Asistente de importación de LibreOffice Calc mostrando la configuración de importación CSV con separador punto y coma](/images/03.-Calc-import.webp)

### ¡Empieza la Cirugía! Limpieza y Formateo paso a paso

Si todo ha ido bien, ahora tienes delante una tabla perfectamente estructurada. Pero "estructurada" no significa "limpia". Ahora empieza lo divertido. Las fórmulas y los pasos que vienen ahora son prácticamente idénticos en Excel y en LibreOffice Calc.

#### **Paso 3: La Cirugía de Fecha y Hora**

Nuestro programa, en su infinita sabiduría, nos ha dado la fecha y la hora en la misma celda (ej: `01/07/2025 09:15:23`). Esto es inútil para analizar. Necesitamos separarlo.

> **Momento Friki: ¿Sabías que Excel cree que vivimos en 1900?** Haz la siguiente prueba. Selecciona una fecha y formatéala como número. Te saldrá algo como 45838. ¿Qué significa eso? Para Excel (y Calc lo imita), el tiempo no empezó con el Big Bang, sino el 1 de enero de 1900. Ese es el día 1. El número 45838 es, simplemente, el número de días que han pasado desde entonces. Es su forma de entender las fechas. ¿Y qué pasa con la hora? La hora son los decimales. Es decir, 0,5 serán las 12:00 h y 0,75 serán las 18:00 h.

1. **Inserta tres columnas nuevas:** Haz clic derecho sobre la letra de la columna `B` (la de `Vendedor`) y selecciona `Insertar` tres veces. Llama a estas nuevas columnas `Fecha`, `Hora` y `Día Semana`.

3. **Extraer la Fecha:** En la primera celda de tu nueva columna `Fecha` (la `B2`), escribe la fórmula `=ENTERO(` y pulsa en la celda `A2`. Automáticamente, Excel pondrá `=ENTERO([@[Fecha y Hora]])` y a ti solo te quedará cerrar el paréntesis. Tampoco es obligatorio; si le das a Enter, te advertirá que la función no estaba cerrada y el programa lo pondrá automáticamente. Sin hacer nada más, se habrá rellenado la función en toda la columna. Esto se debe a que está en formato tabla. Fíjate en que te aparecerán todos los días con la hora 00:00, porque nos quedamos con el número entero, sin los decimales.

5. **Extraer la Hora:** Ahora queremos justo lo contrario, solo los decimales para saber la hora, la fracción de día que ha transcurrido. En la primera celda de tu nueva columna `Hora` (la `C2`), tenemos que restar la "Fecha" al dato de "Fecha y hora". Pones el símbolo `=` y con el ratón pinchas primero en `A2` y pones el símbolo de restar «-». Ahora pulsas en la celda `B2` y te quedará la siguiente función: `=[@[Fecha y Hora]]-[@Fecha]`. Aquí no hacen falta paréntesis. El cálculo se replicará por toda la columna de nuevo. Aquí quedará aún más feo, porque pondrá bien la hora con horas, minutos y segundos, pero en todos los datos pondrá que estamos en el 1 de enero de 1900.

7. **Formatear los datos correctamente:** Vale, no te asustes. No lo vamos a dejar así. Selecciona toda la columna `B` y con el ratón puedes pulsar el botón derecho y darle a "Formato de celdas". Pero a mí me gusta más usar la barra de herramientas. Ahí tenemos ya unos accesos rápidos para los formatos más usados y un desplegable para el resto. Selecciona «Fecha corta» y listo. Ahora selecciona toda la columna `C`, haz lo mismo y elige `Hora`. ¡Magia! Ya están como queremos.

9. **Bonus track - El Día de la Semana:** En la primera celda de la columna `Día Semana` (`D2`), escribe `=TEXTO([@Fecha];"dddd")`. Te devolverá "lunes", "martes", etc. Esto será muy útil si queremos analizar qué días vendes más. Pero para poder usarlo debemos copiar todos estos valores seleccionando del primero al último. Lo podemos hacer con el ratón, pinchando en el primer dato; luego, bajamos hasta el final y, con la tecla Shift pulsada (la flechita hacia arriba que está justo encima de la tecla Ctrl), clicamos en el último dato. Le damos a copiar y después a "Pegar valores". En lugar de pegar normal, que estaríamos pegando la misma función, lo que queremos es que solo pegue el valor resultante de la función como texto. Otro día vemos por qué esto es importante.

**En LibreOffice Calc** es mucho más feo, sin duda. Pero se llega al mismo resultado con un poco más de esfuerzo:

Si ves que las funciones no funcionan, selecciona la celda y pulsa en "Formato" -> "Limpiar formato directo". Le añadirá un apóstrofe al inicio de tu función. Quítala y ya podrás usarla. Cuando funcione y le des el formato con el botón derecho y "Formato de celdas", solo tendrás que arrastrar la función hacia abajo por todos los registros. Esto es súper sencillo de hacer. Solo tienes que hacer doble clic en el cuadradito de abajo a la derecha de la celda seccionada. Mira el vídeo y lo entenderás (y si no me preguntas, claro):

#### **Paso 4: Poniendo en orden los números y el dinero**

Ahora vamos con las columnas numéricas.

1. **La Cantidad:** Selecciona la columna `Cantidad`. En la pestaña `Inicio` (en Excel) o en la barra de formato (en Calc), busca los iconos con ceros y flechitas. Son para **aumentar o disminuir decimales**. Como las cantidades son unidades enteras, quítale los decimales para que se vea más limpio.

3. **El Dinero:** Selecciona a la vez todas las columnas que contengan dinero (`PVP`, `Dto`, `P. Neto`). Puedes hacerlo manteniendo pulsada la tecla `Ctrl` mientras haces clic en las letras de las columnas. Una vez seleccionadas, busca el icono del billete y la moneda (€) para aplicar el formato de **Moneda**. NOTA: en este ejemplo de datos inventados Excel al cargar el CSV se cargó los decimales y puso los precios y los descuentos multiplicados por 100. No me había dado cuenta hasta ahora y paso de corregirlo ahora que me da pereza. Te prometo que con tu programa de gestión no te pasará. Esto es por fiarme de Gemini...

#### **Paso 4: Poniendo en orden los números y el dinero**

Ahora vamos con las columnas numéricas.

1. **La Cantidad:** Selecciona la columna `Cantidad`. En la pestaña `Inicio` (en Excel) o en la barra de formato (en Calc), busca los iconos con ceros y flechitas. Son para aumentar o disminuir decimales. Como las cantidades son unidades enteras, quítale los decimales para que se vea más limpio.

3. **El Dinero:** Selecciona a la vez todas las columnas que contengan dinero (`PVP`, `Dto`, `P. Neto`). Puedes hacerlo manteniendo pulsada la tecla `Ctrl` mientras haces clic en las letras de las columnas. Una vez seleccionadas, busca el icono del billete y la moneda (€) para aplicar el formato de `Moneda`. _NOTA: En este ejemplo de datos inventados, Excel, al cargar el CSV, se comió los decimales y puso los precios y los descuentos multiplicados por 100. No me había dado cuenta hasta ahora y paso de corregirlo porque me da pereza. Te prometo que con tu programa de gestión no te pasará. Esto es por fiarme de Gemini..._

#### **Paso 5: El Toque Final - Estructura y Totales**

Aquí hay una pequeña diferencia entre programas, pero el resultado es similar.

##### **La Fila de Totales de Excel**

Al importar los datos desde el CSV, esto con lo que trabajamos ya es una tabla. De ahí sus colorines. Las tablas en Excel tienen muchas virtudes. Pero hoy solo voy a mencionar una muy brevemente. Si nos vamos a la derecha de la barra de herramientas veremos que aparece una pestaña nueva que se llama "Diseño de tabla". Ahí tendremos la opción de ponerle nombre a la tabla, algo que veremos que puede ser muy útil en futuras clases. Pero lo más importante que quiero señalar ahora es el check de "Fila de totales". Si lo pulsamos, por defecto intentará hacer un sumatorio de la última columna. Pero nosotros podremos modificar de forma muy sencilla y rápida qué datos queremos ver y en qué columna. De esta forma podríamos hacer el sumatorio de todas las unidades vendidas o el promedio de los precios de venta. Así, a golpe de clic:

##### **El Método Manual de LibreOffice Calc**

Calc no tiene un "formato como tabla" tan vistoso, pero sí tiene algo igual de útil: los autofiltros. Haz clic en cualquier celda de tus datos y ve al menú `Datos` -> `Autofiltro`. Verás que aparecen las mismas flechitas en los encabezados que en Excel para ordenar y filtrar.

Para los totales, Calc no tiene una "fila de totales" automática, pero podemos crearla de forma muy sencilla. Ve a la última fila de tus datos. En la celda donde quieras el total (por ejemplo, debajo de `P. Neto`), escribe la fórmula `=SUMA(` y selecciona todo el rango de celdas de esa columna. Cierra el paréntesis y pulsa Enter. Puedes hacer lo mismo con `PROMEDIO`, `MAX`, etc. En este caso, al intentar hacerlo, vi que no reconocía los datos numéricos con decimales separados por puntos. Hay varias formas de corregir esto. Pero para mí lo más simple es lo que os muestro en el vídeo: seleccionar las columnas con decimales y darle a "Editar" -> "Buscar y reemplazar". Ponemos «.» en el campo de buscar y «,» en el espacio de reemplazar y pulsamos "Reemplazar todo". Listo, ahora ya funcionan las funciones:

### ¡Lo has conseguido!

Felicidades. Has sobrevivido a tu primera clase.

Hemos pasado de tener un archivo caótico y sucio a tener una base de datos estructurada, limpia y lista para ser interrogada.

En la próxima entrada de esta serie, con nuestra tabla ya impoluta, vamos a empezar a operar de verdad. Vamos a desatar el poder de la herramienta de análisis más potente y sencilla que existe en las hojas de cálculo: la **tabla dinámica**. Y te prometo que, cuando veas lo que se puede hacer con ella en tres clics, tu forma de ver los datos cambiará para siempre.

De momento, te dejo con un reto:

**¿Te ha sorprendido lo fácil que puede ser este primer paso cuando te explican los trucos clave?**

Cuéntamelo en los comentarios y, si te da vergüenza decir que algo no lo has entendido, lo más probable es que sea culpa mía por no saber explicarlo. Probablemente, doy muchas cosas por sabidas que no debería. No tengas miedo de preguntar lo que sea, aunque pienses que es algo muy básico o tonto. Más tonto sería quedarte con la duda.

¡Nos vemos en la próxima clase!
