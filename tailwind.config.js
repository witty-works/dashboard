const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        fontFamily: {
          sans: ['Lato', 'sans-serif'],
        },
        extend: {
          colors: {
            red: {
              500: '#F06464',
              600: '#D42035',
            },
            magenta: '#F277D0',
            blue: '#55B8E9',
            purple: '#9489DB',
            cyan: '#37D1E5',
            green: '#5ACFB9',
          },
        },
    },

    plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
};
