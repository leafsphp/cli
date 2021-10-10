<?php

app()->get("/", function () {
    response()->page(viewsPath("index.html", false));
});

app()->get("/home", "TestsController@index");

app()->get("/test", "TestsController@test");
