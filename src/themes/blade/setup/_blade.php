<?php

app()->attachView(Leaf\Blade::class);
app()->blade()->configure([
    'views' => 'views',
    'cache' => 'cache',
]);
