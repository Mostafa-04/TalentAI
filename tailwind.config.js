/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class', 
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.jsx",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#6C63FF',
          light: '#A78BFA',
          dark: '#4F46E5',
        },
        secondary: {
          DEFAULT: '#38BDF8',
        },
        bg: {
          DEFAULT: '#0A0A0F',
          2: '#111118',
          3: '#17171F',
        }
      }
    },
  },
  plugins: [],
}