# Optimización de Imágenes

## Conversión a WebP

Para mejorar el rendimiento, todas las imágenes PNG/JPEG deben convertirse a WebP.

### Opción 1: Usar el script automatizado

```bash
npm run convert-images
```

**Nota:** Si el script falla, asegúrate de tener sharp instalado correctamente:
```bash
npm install --include=optional sharp
```

### Opción 2: Conversión manual con herramientas online

1. Ve a https://cloudconvert.com/png-to-webp o https://squoosh.app/
2. Sube las imágenes desde `public/images/`
3. Descarga las versiones WebP
4. Reemplaza los archivos PNG/JPEG con las versiones WebP

### Opción 3: Usar herramientas de línea de comandos

#### Con ImageMagick:
```bash
magick convert imagen.png imagen.webp
```

#### Con cwebp (Google):
```bash
cwebp -q 85 imagen.png -o imagen.webp
```

### Actualizar referencias en Markdown

Después de convertir las imágenes, actualiza las referencias en los archivos `.md`:

```markdown
<!-- Antes -->
![Descripción](/images/imagen.png)

<!-- Después -->
![Descripción](/images/imagen.webp)
```

## Imágenes actuales que necesitan conversión

- `01.-Excel-Vs-Calc-1024x227.png` → `01.-Excel-Vs-Calc-1024x227.webp`
- `01.-Excel-Vs-Calc.png` → `01.-Excel-Vs-Calc.webp`
- `02.-Excel-import.png` → `02.-Excel-import.webp`
- `03.-Calc-import.png` → `03.-Calc-import.webp`
- `ChatGPT-Image-14-jul-2025-22_03_24-e1752523876432.png` → `.webp`
- `ChatGPT-Image-20-jul-2025-12_48_57.png` → `.webp`
- `ChatGPT-Image-23-ago-2025-22_08_49.png` → `.webp`
- `excel.png` → `excel.webp`

## Beneficios esperados

- **Reducción de tamaño:** ~5.4 MB menos de datos
- **Mejora de LCP:** ~8.5 segundos más rápido en móvil
- **Mejor experiencia de usuario:** Carga más rápida

