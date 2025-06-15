/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",  // Scans all Blade files
    "./resources/**/*.js",         // Scans all JavaScript files
    "./resources/**/*.vue",        // Scans all Vue files (if you use Vue)
  ],
  theme: {
    extend: {
      // You can add custom colors for your bank here, for example:
      colors: {
        'azania-blue': '#1a365d',
        'azania-accent': '#007bff',
      },
    },
  },
  plugins: [],
}