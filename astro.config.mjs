// @ts-check
import { defineConfig } from 'astro/config';
import tailwind from '@astrojs/tailwind';
import mdx from '@astrojs/mdx';
import { remarkImageOptimize } from './src/plugins/remark-image-optimize.js';

// https://astro.build/config
export default defineConfig({
  site: 'https://formulafarma.com',
  integrations: [tailwind(), mdx()],
  markdown: {
    remarkPlugins: [remarkImageOptimize],
  },
  i18n: {
    defaultLocale: "gl",
    locales: ["gl", "es"],
    routing: {
      prefixDefaultLocale: false // gl en /, es en /es/
    }
  }
});
