import { defineCollection, z } from 'astro:content';

const blog = defineCollection({
  type: 'content',
  schema: z.object({
    title: z.string(),
    date: z.coerce.date(),
    excerpt: z.string().optional(),
    tags: z.array(z.string()).optional(),
    image: z.string().optional(),
    category: z.enum(['Cultura Maker', 'Teatro & Raíces', 'Outras merdas']).optional(),
    lang: z.enum(['gl', 'es']).optional().default('gl'),
    translationSlug: z.string().optional(), // Slug del post traducido (para vincular posts bilingües)
  }),
});

export const collections = { blog };

