<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title inertia>Document</title>
    <?php echo \Leaf\Vite::reactRefresh(); ?>
    <?php echo vite(['/js/app.jsx', "/js/Pages/{$page['component']}.jsx"]); ?>
    <?php
        if (!isset($__inertiaSsrDispatched)) {
            $__inertiaSsrDispatched = true;
            $__inertiaSsrResponse = (new \Leaf\Inertia\Ssr\Gateway())->dispatch($page);
        }

        if ($__inertiaSsrResponse) {
            echo $__inertiaSsrResponse->head;
        }
    ?>
</head>

<body>
    <?php
        if (!isset($__inertiaSsrDispatched)) {
            $__inertiaSsrDispatched = true;
            $__inertiaSsrResponse = (new \Leaf\Inertia\Ssr\Gateway())->dispatch($page);
        }

        if ($__inertiaSsrResponse) {
            echo $__inertiaSsrResponse->body;
        } else {
            echo '<div id="app" data-page="' . htmlspecialchars(json_encode($page), ENT_QUOTES, 'UTF-8', true) . '"></div>';
        }
    ?>
</body>

</html>
