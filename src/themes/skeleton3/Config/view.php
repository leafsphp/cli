<?php

return [
    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | Most templating systems load templates from disk. Here you may specify
    | an array of paths that should be checked for your views. Of course
    | the usual LeafMVC view path has already been registered for you.
    |
    */
    "viewsPath" => viewsPath("", false),

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | This option determines where all the compiled Blade templates will be
    | stored for your application. Typically, this is within the storage
    | directory. However, as usual, you are free to change this value.
    |
    */
    "cachePath" => storagePath('framework/views'),

    /*
    |--------------------------------------------------------------------------
    | Custom render method
    |--------------------------------------------------------------------------
    |
    | This render method is triggered whenever render() with the experimental
    | option above set to true. This render method allows you to even plug
    | in external view libraries, configure them and call them with `render`
    | whenever and wherever you feel the need.
    |
    */
    "render" => function(string $view, array $data = []) {
        app()->template->config("path", viewsPath("/", false));
        return response()->markup(app()->template->render($view, $data));
    },
];
