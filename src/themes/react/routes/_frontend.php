<?php

app()->get('/hello', fn () => response()->inertia('hello'));
app()->get('/login', fn () => response()->inertia('login'));
