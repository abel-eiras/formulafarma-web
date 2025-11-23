// @ts-check
import { defineConfig } from 'astro/config';
import tailwind from '@astrojs/tailwind';
import { remarkImageOptimize } from './src/plugins/remark-image-optimize.js';

// https://astro.build/config
export default defineConfig({
  site: 'https://formulafarma.com',
  integrations: [tailwind()],
  markdown: {
    remarkPlugins: [remarkImageOptimize],
  },
});
