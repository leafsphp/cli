<?php

/*
|--------------------------------------------------------------------------
| Set up 404 handler
|--------------------------------------------------------------------------
|
| Create a handler for 404 errors. Uncomment the lines below to
| add a custom error 404 handler.
|
*/
// app()->set404(function() {
//     echo "error 404";
// });

/*
|--------------------------------------------------------------------------
| Set up Controller namespace
|--------------------------------------------------------------------------
|
| This allows you to directly use controller names instead of typing
| the controller namespace first.
|
*/
app()->setNamespace("\Controllers");

// You can break up routes into modules
require __DIR__ . "/_app.php";
