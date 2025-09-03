import type { Config } from 'tailwindcss'

const config: Config = {
    darkMode: 'class', // ESSENCIAL para o tema funcionar
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.ts',
        './resources/js/**/*.tsx',
        './resources/js/Pages/**/*.vue',
        './resources/js/pages/**/*.vue',
        './resources/js/Components/**/*.vue',
        './resources/js/components/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                // Cores customizadas se necessário
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', 'sans-serif'],
            },
        },
    },
    plugins: [],
}

export default config