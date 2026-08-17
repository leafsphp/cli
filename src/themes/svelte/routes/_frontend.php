<?php

app()->get('/hello', fn () => response()->inertia('hello'));
