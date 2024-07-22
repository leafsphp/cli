<?php

app()->get('/hello', function () {
	inertia('Hello');
});
