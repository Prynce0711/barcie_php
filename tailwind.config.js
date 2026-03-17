/** @type {import('tailwindcss').Config} */
module.exports = {
  important: true,
  content: [
    "./dashboard.php",
    "./Components/Admin/**/*.{php,js}",
    "./Components/Popup/**/*.php",
    "./Components/Table/**/*.php",
    "./assets/js/**/*.js",
  ],
  corePlugins: {
    preflight: false,
    container: false,
  },
  theme: {
    extend: {
      boxShadow: {
        card: "0 2px 8px rgba(0, 0, 0, 0.06)",
        "card-hover": "0 4px 16px rgba(0, 0, 0, 0.1)",
        btn: "0 1px 3px rgba(0, 0, 0, 0.1)",
        "btn-hover": "0 4px 12px rgba(0, 0, 0, 0.15)",
        modal: "0 20px 60px rgba(0, 0, 0, 0.2)",
        sidebar: "2px 0 16px rgba(0, 0, 0, 0.1)",
        "blue-glow": "0 4px 12px rgba(59, 130, 246, 0.3)",
        "blue-glow-lg": "0 6px 16px rgba(59, 130, 246, 0.4)",
        "red-glow": "0 4px 12px rgba(239, 68, 68, 0.3)",
        "red-glow-lg": "0 6px 16px rgba(239, 68, 68, 0.4)",
      },
    },
  },
  plugins: [],
};
