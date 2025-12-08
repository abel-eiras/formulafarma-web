# 🧪 Guía: Probar Nuevos Diseños Sin Afectar Producción

## ✅ Respuesta Rápida

**Sí, crear una nueva rama es el primer paso**, pero necesitas hacer algunas cosas más para probar correctamente antes de desplegar a producción.

## 📋 Proceso Completo Recomendado

### 1. **Crear una Rama de Desarrollo** ✅

```bash
# Crear y cambiar a una nueva rama
git checkout -b re-styling-brutalista

# O si prefieres otro nombre:
git checkout -b feature/nuevo-diseno
```

**¿Por qué funciona?** Tu workflow de GitHub Actions solo se activa cuando haces push a `master` (línea 5 de `.github/workflows/deploy.yaml`). Cualquier otra rama es segura.

### 2. **Probar Localmente** 🔍

Mientras trabajas en la rama, prueba todo localmente:

```bash
# Iniciar servidor de desarrollo
npm run dev

# Abre http://localhost:4321 en tu navegador
# Prueba todas las páginas: inicio, blog, contacto, etc.
```

### 3. **Verificar que el Build Funciona** 🏗️

Antes de hacer merge, asegúrate de que todo compila correctamente:

```bash
# Construir el proyecto
npm run build

# Si el build es exitoso, previsualizar la versión de producción
npm run preview

# Abre http://localhost:4321 (o el puerto que indique)
# Verifica que todo se vea bien
```

### 4. **Hacer Push de la Rama (Opcional pero Recomendado)** 📤

Aunque no se despliegue, es buena práctica subir tu rama a GitHub:

```bash
# Subir la rama al repositorio remoto
git push -u origin re-styling-brutalista
```

**Ventajas:**
- Tienes backup en la nube
- Puedes crear un Pull Request para revisar cambios
- Otros pueden ver tu trabajo

### 5. **Cuando Estés Listo: Merge a Master** 🚀

Solo cuando hayas probado todo y estés seguro:

```bash
# Volver a la rama master
git checkout master

# Actualizar master con los últimos cambios
git pull origin master

# Hacer merge de tu rama de desarrollo
git merge re-styling-brutalista

# Subir a master (esto SÍ activará el despliegue automático)
git push origin master
```

## 🎯 Opciones Avanzadas (Opcional)

### Opción A: Entorno de Staging

Si quieres probar en un servidor real antes de producción, puedes crear un segundo workflow que se active con otra rama (ej: `staging`):

1. Crear `.github/workflows/deploy-staging.yaml`
2. Configurar para que despliegue a un subdirectorio (ej: `public_html/staging/`)
3. Solo se activa con push a `staging`

### Opción B: Pull Request Preview

Usar servicios como Vercel o Netlify que generan previews automáticos de cada Pull Request.

## ⚠️ Checklist Antes de Hacer Merge a Master

Antes de hacer `git push origin master`, verifica:

- [ ] ✅ El proyecto compila sin errores (`npm run build`)
- [ ] ✅ La preview local se ve correcta (`npm run preview`)
- [ ] ✅ Todas las páginas funcionan (inicio, blog, contacto, etc.)
- [ ] ✅ El formulario de contacto funciona
- [ ] ✅ Los comentarios funcionan
- [ ] ✅ Las imágenes se cargan correctamente
- [ ] ✅ El diseño es responsive (móvil, tablet, desktop)
- [ ] ✅ No hay errores en la consola del navegador

## 🔒 Seguridad Adicional

Si quieres estar 100% seguro, puedes:

1. **Desactivar temporalmente el workflow** (no recomendado, pero posible)
2. **Usar una rama de protección** en GitHub que requiera revisión antes de merge
3. **Hacer un backup manual** del `public_html` antes del despliegue

## 📝 Resumen

| Acción | ¿Afecta Producción? | Cuándo Usar |
|--------|---------------------|-------------|
| Crear rama nueva | ❌ NO | Siempre para nuevos cambios |
| `npm run dev` | ❌ NO | Durante desarrollo |
| `npm run build` | ❌ NO | Antes de hacer merge |
| Push a rama (no master) | ❌ NO | Para backup/revisión |
| Push a `master` | ✅ **SÍ** | Solo cuando estés 100% seguro |

## 🎨 Para tu Re-styling Brutalista

Basándome en tu archivo `re-styling/propuesta.md`, te recomiendo:

1. **Crear la rama:**
   ```bash
   git checkout -b re-styling-brutalista
   ```

2. **Trabajar en los cambios** siguiendo los pasos de la propuesta

3. **Probar localmente** con `npm run dev` después de cada cambio importante

4. **Cuando termines**, hacer `npm run build` y `npm run preview` para verificar

5. **Solo entonces** hacer merge a `master` y push

---

**Conclusión:** Crear una rama es suficiente para proteger producción, pero siempre prueba localmente antes de hacer merge a `master`. 🚀





