import { visit } from 'unist-util-visit';

/**
 * Plugin de remark para optimizar imágenes en markdown
 * Añade atributos width, height, loading y decoding
 */
export function remarkImageOptimize() {
  return (tree) => {
    visit(tree, 'image', (node) => {
      // Añadir atributos de optimización
      if (!node.data) {
        node.data = {};
      }
      if (!node.data.hProperties) {
        node.data.hProperties = {};
      }

      // Añadir loading lazy por defecto (las imágenes en markdown suelen estar más abajo)
      node.data.hProperties.loading = 'lazy';
      node.data.hProperties.decoding = 'async';

      // Intentar detectar dimensiones basándose en el nombre del archivo
      // Por ejemplo: "imagen-1024x227.png" -> width="1024" height="227"
      const filenameMatch = node.url.match(/(\d+)x(\d+)/);
      if (filenameMatch) {
        node.data.hProperties.width = filenameMatch[1];
        node.data.hProperties.height = filenameMatch[2];
      } else {
        // Valores por defecto razonables
        node.data.hProperties.width = '800';
        node.data.hProperties.height = '600';
      }
    });
  };
}

