/** @type {import('tailwindcss').Config} */
export default {
  content: ['./src/**/*.{astro,html,js,jsx,md,mdx,svelte,ts,tsx,vue}'],
  theme: {
    extend: {
      colors: {
        pharma: {
          bg: '#FAFAFA',
          dark: '#212121',
          grey: '#424242',
          accent: '#2962FF',
          soft: '#E3F2FD',
          coral: '#FF8A80',
        }
      },
      fontFamily: {
        tech: ["'Space Mono'", 'monospace'], // Para código, datos, maker
        human: ["'Playfair Display'", 'serif'], // Para teatro, historia, humanidades
        sans: ['Inter', 'sans-serif'],
        serif: ['Merriweather', 'serif'],
      },
      boxShadow: {
        'brutal': '6px 6px 0px #1a1a1a',
        'brutal-hover': '8px 8px 0px #1a1a1a',
        'brutal-active': '2px 2px 0px #1a1a1a',
      }
    },
  },
  plugins: [require('@tailwindcss/typography')],
}

