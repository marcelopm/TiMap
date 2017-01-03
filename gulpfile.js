var elixir = require('laravel-elixir');

require('laravel-elixir-materialize-css');
//require('laravel-elixir-vue');

elixir(mix => {
    mix.browserSync({
        proxy: 'localhost:8000'
    });
    mix.sass('app.scss')
            .webpack('app.js');
    mix.sass('map/app.scss', 'public/css/map')
            .materialize()
            .webpack('map/app.js', 'public/js/map');
});