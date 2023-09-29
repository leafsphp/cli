<?php

use Symfony\Component\Process\Process;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');

require __DIR__ . '/vendor/autoload.php';

$action = $_GET['action'] ?? null;

if ($action === 'getConfig') {
    $config = __DIR__ . '/ui.config.json';

    if (!file_exists($config)) {
        touch($config);
        file_put_contents($config, json_encode([
            'dir' => null,
            'phpDir' => null,
        ]));
    }

    $applicationConfig = file_get_contents($config);
    $applicationConfig = json_decode($applicationConfig, true);

    echo json_encode([
        'status' => 'success',
        'data' => $applicationConfig,
    ]);
}

if ($action === 'setConfig') {
    $configFile = __DIR__ . '/ui.config.json';
    $config = file_get_contents($configFile);
    $config = json_decode($config, true);
    $config = array_merge($config, getData('data')['data'] ?? []);

    file_put_contents($configFile, json_encode($config));

    echo json_encode([
        'status' => 'success',
        'message' => 'Config saved success',
        'data' => $config
    ]);
}

if ($action === 'createApp') {
    // @type('AppInfo', [
    //     'name' => 'string',
    //     'type' => ['leaf', 'mvc', 'api'],
    //     'modules?' => 'string[]',
    //     'docker?' => 'bool',
    //     'tests?' => ['pest', 'phpunit'],
    //     'templateEngine?' => ['blade', 'bareui'],
    //     'frontendFramework?' => ['react', 'vue'],
    //     'additionalFrontendOptions?' => ['vite', 'tailwind']
    // ]);
    $appInfo = getData('data')['data'] ?? '{}';
    $appInfo = json_decode($appInfo, true);

    $config = __DIR__ . '/ui.config.json';
    $applicationConfig = file_get_contents($config);
    $applicationConfig = json_decode($applicationConfig, true);

    $directory = $applicationConfig['dir'];

    if ($appInfo['type'] === 'leaf') {
        $appInfo['type'] = 'basic';
    }

    if ($directory) {
        $appInfo['directory'] = $directory;
    }

    $data = createApp($appInfo);

    if ($data) {
        echo json_encode($data);
        return;
    }
}


function getData($data = null)
{
    $handler = fopen('php://input', 'r');
    $streamData = stream_get_contents($handler);

    $streamData = $data ? ($streamData[$data] ?? $streamData) : $streamData;
    $streamData = json_decode($streamData, true);

    return $streamData;
}

function hashDirectory($directory)
{
    if (!is_dir($directory)) {
        return false;
    }

    $files = array();
    $dir = dir($directory);

    while (false !== ($file = $dir->read())) {
        if ($file != '.' and $file != '..') {
            if (is_dir($directory . '/' . $file)) {
                $files[] = hashDirectory($directory . '/' . $file);
            } else {
                $files[] = md5_file($directory . '/' . $file);
            }
        }
    }

    $dir->close();

    return md5(implode('', $files));
}

/**
 * Copy a file, or recursively copy a folder and its contents
 * 
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.1
 * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
 * @param       string   $source    Source path
 * @param       string   $dest      Destination path
 * @param       int      $permissions New folder creation permissions
 * @return      bool     Returns true on success, false on failure
 */
function superCopy($source, $dest, $permissions = 0755)
{
    $sourceHash = hashDirectory($source);
    // Check for symlinks
    if (is_link($source)) {
        return symlink(readlink($source), $dest);
    }

    // Simple copy for a file
    if (is_file($source)) {
        return copy($source, $dest);
    }

    // Make destination directory
    if (!is_dir($dest)) {
        mkdir($dest, $permissions);
    }

    // Loop through the folder
    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep copy directories
        if ($sourceHash != hashDirectory($source . "/" . $entry)) {
            superCopy("$source/$entry", "$dest/$entry", $permissions);
        }
    }

    // Clean up
    $dir->close();
    return true;
}

function deleteDirectory($dir)
{
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }

    return rmdir($dir);
}

function createApp($appInfo)
{
    $directory = $appInfo['directory'] ?? null;
    $appName = $appInfo['name'] ?? null;
    $type = $appInfo['type'] ?? null;

    if (is_dir($directory . '/' . $appName)) {
        return [
            'status' => 'error',
            'message' => 'An app with this name already exists',
            'data' => $appInfo,
        ];
    }

    if ($type === 'basic') {
        $leaf3Theme = dirname(__DIR__) . '/themes/leaf3';

        superCopy($leaf3Theme, $directory . '/' . $appName);

        $process = new Process(['composer', 'install']);
        $process->setWorkingDirectory($directory . '/' . $appName);
        $process->setTimeout(null);
        $process->setTty(true);
        $process->run();
    } else {
        $process = new Process(['composer', 'create-project', 'leafs/' . $type, $appName, '--no-install', '--no-scripts', '--no-progress', '--no-interaction']);
        $process->setWorkingDirectory($directory);
        $process->setTimeout(null);
        $process->setTty(true);
        $process->run();

        $process = new Process(['composer', 'install']);
        $process->setWorkingDirectory($directory . '/' . $appName);
        $process->setTimeout(null);
        $process->setTty(true);
        $process->run();

        $process = new Process(['composer', 'run', 'post-root-package-install']);
        $process->setWorkingDirectory($directory . '/' . $appName);
        $process->setTimeout(null);
        $process->setTty(true);
        $process->run();

        $process = new Process(['composer', 'run', 'post-create-project-cmd']);
        $process->setWorkingDirectory($directory . '/' . $appName);
        $process->setTimeout(null);
        $process->setTty(true);
        $process->run();
    }

    if (isset($appInfo['templateEngine'])) {
        $process = new Process(['leaf', 'view:install', '--' . $appInfo['templateEngine']]);
        $process->setWorkingDirectory($directory . '/' . $appName);
        $process->setTimeout(null);
        $process->setTty(true);
        $process->run();
    }

    if (isset($appInfo['frontendFramework'])) {
        $process = new Process(['leaf', 'view:install', '--' . $appInfo['frontendFramework']]);
        $process->setWorkingDirectory($directory . '/' . $appName);
        $process->setTimeout(null);
        $process->setTty(true);
        $process->run();
    }

    if (isset($appInfo['additionalFrontendOptions']) || isset($appInfo['frontendFramework'])) {
        foreach ($appInfo['additionalFrontendOptions'] as $option) {
            $process = new Process(['leaf', 'view:install', '--' . $option]);
            $process->setWorkingDirectory($directory . '/' . $appName);
            $process->setTimeout(null);
            $process->setTty(true);
            $process->run();
        }

        if ($appInfo['type'] === 'basic') {
            $indexFile = $directory . '/' . $appName . '/index.php';

            $indexFileContent = file_get_contents($indexFile);

            if ($appInfo['templateEngine'] === 'blade') {
                $indexFileContent = str_replace(
                    ["require __DIR__ . '/vendor/autoload.php';", "response()->page('./welcome.html');", 'app()->run();'],
                    [
                        "require __DIR__ . '/vendor/autoload.php';

\Leaf\View::attach(\Leaf\Blade::class);

app()->config([
	'views.path' => 'views',
	'views.cache' => 'views/cache'
]);
app()->blade->configure('views', 'views/cache');",
                        "response()->markup(
		app()->blade->render('index', ['name' => 'Leaf'])
	);",
                        isset($appInfo['frontendFramework']) ?
                            "app()->get('/hello', function () {
	echo inertia('Hello');
});

app()->run();"
                            : "app()->run();"
                    ],
                    $indexFileContent
                );
            } else {
                $indexFileContent = str_replace(
                    ["require __DIR__ . '/vendor/autoload.php';", "response()->page('./welcome.html');", 'app()->run();'],
                    [
                        "require __DIR__ . '/vendor/autoload.php';

app()->config('views.path', 'views');
app()->template->config('path', __DIR__ . '/views');",
                        "response()->markup(
		app()->template->render('index', [
			'name' => 'Leaf',
		])
	);",
                        isset($appInfo['frontendFramework']) ?
                            "app()->get('/hello', function () {
	echo inertia('Hello');
});

app()->run();"
                            : "app()->run();"
                    ],
                    $indexFileContent
                );
            }

            file_put_contents($indexFile, $indexFileContent);
            mkdir($directory . '/' . $appName . '/views');

            if ($appInfo['templateEngine'] === 'blade') {
                mkdir($directory . '/' . $appName . '/views/cache');
                rename($directory . '/' . $appName . '/index.blade.php', $directory . '/' . $appName . '/views/index.blade.php');

                if (isset($appInfo['frontendFramework'])) {
                    rename($directory . '/' . $appName . '/_inertia.blade.php', $directory . '/' . $appName . '/views/_inertia.blade.php');
                }
            } else {
                rename($directory . '/' . $appName . '/index.view.php', $directory . '/' . $appName . '/views/index.view.php');

                if (isset($appInfo['frontendFramework'])) {
                    rename($directory . '/' . $appName . '/_inertia.view.php', $directory . '/' . $appName . '/views/_inertia.view.php');
                }
            }

            if (is_dir($directory . '/' . $appName . '/js')) {
                superCopy($directory . '/' . $appName . '/js', $directory . '/' . $appName . '/views/js');
                deleteDirectory($directory . '/' . $appName . '/js');
            }

            if (is_dir($directory . '/' . $appName . '/css')) {
                superCopy($directory . '/' . $appName . '/css', $directory . '/' . $appName . '/views/css');
                deleteDirectory($directory . '/' . $appName . '/css');
            }

            unlink($directory . '/' . $appName . '/welcome.html');

            if (isset($appInfo['frontendFramework'])) {
                $inertiaFile = $directory . '/' . $appName . '/views/_inertia' .
                    ($appInfo['templateEngine'] === 'blade' ? '.blade.php' : '.view.php');

                $inertiaFileContent = file_get_contents($inertiaFile);
                $indexFileContent = str_replace(
                    ["'js/", '"js/'],
                    ["'views/js/", '"views/js/'],
                    $inertiaFileContent
                );
                file_put_contents($inertiaFile, $indexFileContent);

                unlink($directory . '/' . $appName . '/_frontend.php');
            }

            $templateContent = file_get_contents($directory . '/' . $appName .
                ($appInfo['templateEngine'] === 'blade' ? '/views/index.blade.php' : '/views/index.view.php'));
            $templateContent = str_replace(
                ['<title>Document</title>', 'Hello <?php echo $name; ?>', 'Hello {{ $name }}', '<body>'],
                [
                    '<title>Welcome to Leaf</title>' . ((isset($appInfo['frontendFramwork']) || isset($appInfo['additionalFrontendOptions'])) ?
                        "
        <?php echo vite('/css/app.css', 'views'); ?>" : ''
                    ),
                    ((in_array('tailwind', $appInfo['additionalFrontendOptions'] ?? []) ? '<h1 class="text-4xl mb-2">Hello <?php echo $name; ?></h1>' : 'Hello <?php echo $name; ?>') . '
	<p>BareUI' . (in_array('tailwind', $appInfo['additionalFrontendOptions'] ?? []) ? ' + Tailwind</p>' : (in_array('vite', $appInfo['additionalFrontendOptions']) ? ' + Vite</p>' : ' + Leaf</p>'))),
                    ((in_array('tailwind', $appInfo['additionalFrontendOptions'] ?? []) ? '<h1 class="text-4xl mb-2">Hello {{ $name }}</h1>' : 'Hello {{ $name }}') . '
    <p>Blade' . (in_array('tailwind', $appInfo['additionalFrontendOptions'] ?? []) ? ' + Tailwind</p>' : (in_array('vite', $appInfo['additionalFrontendOptions']) ? ' + Vite</p>' : ' + Leaf</p>'))),
                    (in_array('tailwind', $appInfo['additionalFrontendOptions'] ?? []) ? '<body class="flex flex-col justify-center items-center h-screen">' : '<body>')
                ],
                $templateContent
            );
            file_put_contents($directory . '/' . $appName . ($appInfo['templateEngine'] === 'blade' ? '/views/index.blade.php' : '/views/index.view.php'), $templateContent);

            if (is_file($directory . '/' . $appName . '/vite.config.js')) {
                $viteConfig = file_get_contents($directory . '/' . $appName . '/vite.config.js');
                $viteConfig = str_replace(
                    ['refresh: true', "'app/views//js", '"app/views//js', 'css/app.css', 'js/app.js'],
                    ["refresh: ['views/**']", "'views/js", '"views/js', '/css/app.css', '/js/app.js'],
                    $viteConfig
                );
                file_put_contents($directory . '/' . $appName . '/vite.config.js', $viteConfig);
            }

            if (is_file($directory . '/' . $appName . '/tailwind.config.js')) {
                $tailwindConfig = file_get_contents($directory . '/' . $appName . '/tailwind.config.js');
                $tailwindConfig = str_replace(
                    ["'./app/views/**/*.blade.php',", './app/'],
                    ["'./views/**/*.blade.php', './views/**/*.view.php',", './'],
                    $tailwindConfig
                );
                file_put_contents($directory . '/' . $appName . '/tailwind.config.js', $tailwindConfig);
            }
        }
    }

    if (isset($appInfo['testing'])) {
        $process = new Process(['composer', 'require', 'leafs/alchemy', '--dev']);
        $process->setWorkingDirectory($directory . '/' . $appName);
        $process->setTimeout(null);
        $process->setTty(true);
        $process->run();

        $process = new Process(['leaf', 'test:setup', '--' . $appInfo['testing']]);
        $process->setWorkingDirectory($directory . '/' . $appName);
        $process->setTimeout(null);
        $process->setTty(true);
        $process->run();
    }

    if (isset($appInfo['modules'])) {
        foreach ($appInfo['modules'] as $module) {
            $process = new Process(['composer', 'require', 'leafs/' . $module]);
            $process->setWorkingDirectory($directory . '/' . $appName);
            $process->setTimeout(null);
            $process->setTty(true);
            $process->run();
        }
    }

    if (isset($appInfo['docker'])) {
        superCopy(dirname(__DIR__) . '/themes/docker', $directory . '/' . $appName . '/docker');
    }

    return [
        'status' => 'success',
        'message' => 'App created success',
        'data' => $appInfo,
    ];
}
