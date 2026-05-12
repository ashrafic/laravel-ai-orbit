/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                orbit: {
                    50: '#eef2ff',
                    100: '#e0e7ff',
                    200: '#c7d2fe',
                    300: '#a5b4fc',
                    400: '#818cf8',
                    500: '#6366f1',
                    600: '#4f46e5',
                    700: '#4338ca',
                    800: '#3730a3',
                    900: '#312e81',
                    950: '#1e1b4b',
                },
            },
            backgroundImage: {
                'orbit-gradient': 'linear-gradient(135deg, #6366f1, #8b5cf6)',
                'orbit-gradient-hover': 'linear-gradient(135deg, #4f46e5, #7c3aed)',
            },
            boxShadow: {
                'glass': '0 1px 3px rgba(0, 0, 0, 0.04)',
                'glass-dark': '0 1px 3px rgba(0, 0, 0, 0.2)',
                'glow-indigo': '0 0 20px rgba(99, 102, 241, 0.15)',
                'glow-emerald': '0 0 20px rgba(16, 185, 129, 0.15)',
                'glow-purple': '0 0 20px rgba(139, 92, 246, 0.15)',
                'glow-amber': '0 0 20px rgba(245, 158, 11, 0.15)',
            },
            backdropBlur: {
                'glass': '12px',
                'glass-lg': '20px',
            },
        },
    },
    plugins: [],
};
