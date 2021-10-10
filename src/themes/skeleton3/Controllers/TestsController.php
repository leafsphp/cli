<?php

namespace Controllers;

use Models\Test;

class TestsController extends Controller
{
    public function index()
    {
        render("test", [
            "title" => "Leaf Skeleton v3.0",
        ]);
    }

    public function test()
    {
        response()->json(Test::all());
    }
}
