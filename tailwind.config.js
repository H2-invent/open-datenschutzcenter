const colors = require('tailwindcss/colors');

/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
    extend: {
      colors: {
        primary: colors.blue,
        secondary: colors.slate,
        neutral: colors.neutral,
        error: colors.red,
        surface: colors.slate,
        surfacedark: colors.slate,
        // odc colors,
        vvt: colors.emerald,
        dsfa: colors.green,
        tom: colors.sky,
        kontakt: colors.purple,
        datenweitergabe: colors.violet,
        av: colors.fuchsia,
        form: colors.amber,
        policy: colors.zinc,
        software: colors.blue,
        task: colors.cyan,
        loeschkonzept: colors.orange,
        datenkategorie: colors.lime
      }
    },
  },
  plugins: [
  ],
}

