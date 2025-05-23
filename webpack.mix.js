const mix = require("laravel-mix");

mix.postCss("resources/css/app.css", "public/css", [
    require("tailwindcss"),
    // …other PostCSS plugins
])
    .js("resources/js/app.js", "public/js")
    .setPublicPath("public");
