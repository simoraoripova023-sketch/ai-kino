/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        cinema: {
          950: '#0a0b0e',
          900: '#12141a',
          800: '#1a1d26',
          700: '#262a36',
          gold: '#f5c518',
          red: '#e50914',
        }
      }
    },
  },
  plugins: [],
}
