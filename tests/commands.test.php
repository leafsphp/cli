<?php

beforeEach(function () {
    sandboxSetup();
});

afterEach(function () {
    sandboxTeardown();
});

test('install does not pass --dev unless asked', function () {
    file_put_contents(getcwd() . '/composer.json', '{"name":"t/t"}');

    [, $output] = leaf('install fs');

    // the regression that shipped: a stale flag parser sent every
    // install to require-dev
    expect($output)->toContain('COMPOSER CALLED WITH: require leafs/fs')
        ->and($output)->not->toContain('--dev');
});

test('install passes --dev when asked', function () {
    file_put_contents(getcwd() . '/composer.json', '{"name":"t/t"}');

    [, $output] = leaf('install fs --dev');

    expect($output)->toContain('require leafs/fs')
        ->and($output)->toContain('--dev');
});

test('install expands bare names to leafs/ and @ to a version constraint', function () {
    file_put_contents(getcwd() . '/composer.json', '{"name":"t/t"}');

    [, $output] = leaf('install fs@5.0 symfony/yaml');

    expect($output)->toContain('leafs/fs:5.0')
        ->and($output)->toContain('symfony/yaml');
});

test('install passes --ansi exactly once', function () {
    file_put_contents(getcwd() . '/composer.json', '{"name":"t/t"}');

    [, $output] = leaf('install fs');

    $composerLine = '';

    foreach (explode("\n", $output) as $line) {
        if (strpos($line, 'COMPOSER CALLED WITH') !== false) {
            $composerLine = $line;
        }
    }

    expect(substr_count($composerLine, '--ansi'))->toBe(1);
});

test('create builds an unpinned create-project for mvc apps', function () {
    [, $output] = leaf('create my-app --mvc');

    // the launch-day contract: no version pin, latest stable resolves
    expect($output)->toContain('create-project leafs/mvc')
        ->and($output)->not->toContain('5.0-alpha')
        ->and($output)->not->toContain('dev-');
});

test('create builds an unpinned create-project for console apps', function () {
    [, $output] = leaf('create my-tool --console');

    expect($output)->toContain('create-project leafs/seedling')
        ->and($output)->not->toContain('5.0-alpha');
});

test('up refuses to run outside a project and mentions beta', function () {
    [$exit, $output] = leaf('up');

    expect($exit)->toBe(1)
        ->and($output)->toContain('beta')
        ->and($output)->toContain('github.com/leafsphp/cli/issues')
        ->and($output)->toContain('No composer.json found');
});
