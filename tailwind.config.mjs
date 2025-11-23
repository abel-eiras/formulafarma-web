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
        sans: ['Inter', 'sans-serif'],
        serif: ['Merriweather', 'serif'],
      }
    },
  },
  plugins: [require('@tailwindcss/typography')],
}

