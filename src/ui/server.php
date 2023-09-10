<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');

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
    //     'viewEngine?' => ['blade', 'bareui', 'react', 'vue'],
    //     'vite?' => 'bool',
    // ]);
    $appInfo = $_POST['data'];
}


function getData($data = null) {
    $handler = fopen('php://input', 'r');
    $streamData = stream_get_contents($handler);

    $streamData = $data ? ($streamData[$data] ?? $streamData) : $streamData;
    $streamData = json_decode($streamData, true);

    return $streamData;
}
