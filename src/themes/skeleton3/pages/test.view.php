<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link rel="stylesheet" href="<?php echo viewsPath('styles.css'); ?>">
</head>

<body>
    <main class="container">
        <header>
            <a href="https://leafphp.dev" style="text-decoration: none">
                <img src="https://www.leafphp.dev/logo-circle.png" alt="Leaf PHP Framework" style="width: 110px;">
            </a>
        </header>
        <?php
            // You can use another render in here to include bare UI partials
            render("body", [
                "title" => $title
            ]);
        ?>
        <a href="https://github.com/leafsphp" target="_blank">
            View other Leaf projects
        </a>
    </main>
</body>

</html>
