<?php

require __DIR__ . "/vendor/autoload.php";

$app = new Leaf\App;

$app->get("/", function () use($app) {
	$app->response()->json(["message" => "Hello World!"]);
});

$app->run();
