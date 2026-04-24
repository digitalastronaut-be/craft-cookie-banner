import mix from "laravel-mix";

mix.js("./src/web/assets/frontend/src/js/index.js", "./src/web/assets/frontend/dist");
mix.sass("./src/web/assets/frontend/src/scss/index.scss", "./src/web/assets/frontend/dist");

mix.js("./src/web/assets/cp/src/js/index.js", "./src/web/assets/cp/dist");
mix.sass("./src/web/assets/cp/src/scss/index.scss", "./src/web/assets/cp/dist");
