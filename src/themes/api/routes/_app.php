<?php

app()->get('/', fn () => response()->json(['message' => 'Congrats!! You\'re on Leaf MVC']));
