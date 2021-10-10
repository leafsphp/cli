<?php

require __DIR__ . "/vendor/autoload.php";

app()->get("/", function () {
	response()->json(["message" => "Hello World!"]);
});

app()->run();
