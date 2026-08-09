/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./app/**/*.{js,jsx}",
    "./components/**/*.{js,jsx}",
    "./lib/**/*.{js,jsx}",
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          50: "#eef4ff",
          100: "#dfe9ff",
          200: "#c5d7ff",
          300: "#a2bcff",
          400: "#7d97fc",
          500: "#5d72f6",
          600: "#464eea",
          700: "#393ccf",
          800: "#3134a7",
          900: "#2e3384",
          950: "#1c1d4d",
        },
        ink: {
          DEFAULT: "#0f1222",
          soft: "#3d4257",
          mute: "#6b7186",
        },
      },
      fontFamily: {
        sans: ["var(--font-en)", "var(--font-ar)", "system-ui", "sans-serif"],
      },
      boxShadow: {
        soft: "0 2px 8px -2px rgba(28,29,77,.08), 0 12px 32px -8px rgba(28,29,77,.12)",
        lift: "0 4px 12px -2px rgba(28,29,77,.10), 0 24px 56px -12px rgba(70,78,234,.22)",
        glow: "0 0 0 1px rgba(93,114,246,.15), 0 8px 40px -8px rgba(93,114,246,.35)",
      },
      borderRadius: {
        "2.5xl": "1.25rem",
        "4xl": "2rem",
      },
      transitionTimingFunction: {
        "out-expo": "cubic-bezier(0.16, 1, 0.3, 1)",
      },
      transitionDuration: {
        400: "400ms",
      },
    },
  },
  plugins: [],
};
