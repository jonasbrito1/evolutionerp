/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#2563EB',
        secondary: '#10B981',
        accent: '#F59E0B',
        danger: '#EF4444',
        light: '#F9FAFB',
        dark: '#1F2937',
      },
      fontFamily: {
        sans: ['Inter', 'Poppins', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
