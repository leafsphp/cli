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

    return [
        'status' => 'success',
        'message' => 'App created success',
        'data' => $appInfo,
        'output' => $process->getOutput(),
    ];
}
