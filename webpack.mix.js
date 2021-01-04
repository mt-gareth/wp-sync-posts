let mix = require('laravel-mix');

mix.sourceMaps();
mix.js('admin/scripts/wp-sync-posts-admin.js', 'admin/dist');
mix.sass('admin/styles/wp-sync-posts-admin.scss', 'admin/dist');

mix.autoload({jquery: ['$', 'window.jQuery']});