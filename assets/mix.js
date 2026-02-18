const mix = require('laravel-mix');
require('laravel-mix-merge-manifest');

mix.disableNotifications();
mix.version();

mix.options({
    postCss: [
        require('postcss-discard-comments') ({removeAll: true})
    ],
    terser: {extractComments: false}
});

mix.setPublicPath(`public/modules/crawler`);

mix.styles([
    //
], 'public/modules/crawler/css/main.min.css');

mix.combine([
    //
], 'public/modules/crawler/js/main.min.js');
