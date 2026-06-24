import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            colors: {
                'gaf-green': '#14532d',
                'gaf-dark-green-green': '#0f2f1f',
                'gaf-khaki-green': '#166534',
                'gaf-accent': '#5fa489',
                'gaf-olive': '#4a7a65',
                'gaf-khaki': '#D4AF37',
                'gaf-gold': '#D4AF37',
                'gaf-red': '#9B2226',
                'gaf-brown': '#7F4F24',
                'gaf-flag-red': '#b22234',
                'gaf-flag-gold': '#fcd116',
                'gaf-flag-green': '#006b3f',
                'gaf-slate': {
                    700: '#334155',
                    500: '#64748b',
                    300: '#cbd5e1',
                    100: '#f1f5f9',
                },
                'gaf-section': '#2d3748',
            },
            fontFamily: {
                sans: ['Inter', 'Montserrat', ...defaultTheme.fontFamily.sans],
            },
            animation: {
                'count-up': 'countUp 2s ease-out forwards',
                'pulse-glow': 'pulseGlow 2s ease-in-out infinite',
                'slide-in': 'slideIn 0.3s ease-out forwards',
                'fade-in': 'fadeIn 0.3s ease-out forwards',
            },
            keyframes: {
                countUp: {
                    '0%': { opacity: '0', transform: 'translateY(10px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                pulseGlow: {
                    '0%, 100%': { boxShadow: '0 0 5px rgba(20, 83, 45, 0.5)' },
                    '50%': { boxShadow: '0 0 20px rgba(20, 83, 45, 0.8)' },
                },
                slideIn: {
                    '0%': { opacity: '0', transform: 'translateX(-20px)' },
                    '100%': { opacity: '1', transform: 'translateX(0)' },
                },
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
            },
        },
    },

    plugins: [forms],
};
