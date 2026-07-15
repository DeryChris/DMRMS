import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
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
                'gaf-dark-green': '#0f2f1f',
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
            backgroundImage: {
                'gradient-hero': 'linear-gradient(135deg, #14532d 0%, #0f2f1f 50%, #000000 100%)',
                'gradient-card': 'linear-gradient(135deg, #ffffff 0%, #f0f7f0 100%)',
                'gradient-section': 'linear-gradient(180deg, #14532d 0%, #166534 100%)',
                'gradient-auth': 'linear-gradient(135deg, #14532d 0%, #0f2f1f 70%, #9B2226 100%)',
                'gradient-gold': 'linear-gradient(135deg, #D4AF37 0%, #fcd116 100%)',
                'gradient-success': 'linear-gradient(135deg, #166534 0%, #14532d 100%)',
                'gradient-red': 'linear-gradient(135deg, #9B2226 0%, #7F1D1D 100%)',
                'gradient-amber': 'linear-gradient(135deg, #D97706 0%, #92400E 100%)',
                'gradient-teal': 'linear-gradient(135deg, #0D9488 0%, #115E59 100%)',
            },
            animation: {
                'count-up': 'countUp 2s ease-out forwards',
                'pulse-glow': 'pulseGlow 2s ease-in-out infinite',
                'slide-in': 'slideIn 0.3s ease-out forwards',
                'fade-in': 'fadeIn 0.3s ease-out forwards',
                'float': 'float 8s ease-in-out infinite',
                'float-slow': 'float 12s ease-in-out infinite',
                'gradient-shift': 'gradientShift 15s ease infinite',
                'pulse-soft': 'pulseSoft 3s ease-in-out infinite',
                'bounce': 'bounce 1s infinite',
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
                float: {
                    '0%, 100%': { transform: 'translateY(0) rotate(0deg)' },
                    '33%': { transform: 'translateY(-15px) rotate(1deg)' },
                    '66%': { transform: 'translateY(5px) rotate(-1deg)' },
                },
                gradientShift: {
                    '0%': { backgroundPosition: '0% 50%' },
                    '50%': { backgroundPosition: '100% 50%' },
                    '100%': { backgroundPosition: '0% 50%' },
                },
                pulseSoft: {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.7' },
                },
                bounce: {
                    '0%, 100%': {
                        transform: 'translateY(-25%)',
                        animationTimingFunction: 'cubic-bezier(0.8, 0, 1, 1)',
                    },
                    '50%': {
                        transform: 'translateY(0)',
                        animationTimingFunction: 'cubic-bezier(0, 0, 0.2, 1)',
                    },
                },
            },
        },
    },

    plugins: [forms],
};
