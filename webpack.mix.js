let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.webpackConfig({
    resolve: {
        modules: [
            'node_modules',
            path.resolve(__dirname, 'resources/assets/js')
        ]
    }
});

// admin (AdminLTE)
mix.less('resources/assets/less/admin.less', 'public/css/admin-all.css');

// members (main)
mix.sass('resources/assets/sass/app.scss', 'public/css/app-all.css');
mix.js('resources/assets/js/app.js', 'public/js/app-all.js');

// general
mix.version();
