<?php

beforeEach(function () {
    sandboxSetup();
});

afterEach(function () {
    sandboxTeardown();
});

test('context scans routes in every registration shape', function () {
    fixtureProject();

    [$exit, $output] = leaf('context');

    expect($exit)->toBe(0)
        ->and($output)->toContain('GET /')
        ->and($output)->toContain('/users/{id}')
        ->and($output)->toContain('UsersController@show')
        ->and($output)->toContain('UsersController@store')
        ->and($output)->toContain('auth.required')
        ->and($output)->toContain('/admin')
        ->and($output)->toContain('/posts');
});

test('context lists models, schema files and installed modules', function () {
    fixtureProject();

    [$exit, $output] = leaf('context');

    expect($exit)->toBe(0)
        ->and($output)->toContain('User')
        ->and($output)->toContain('users.yml')
        ->and($output)->toContain('leafs/db');
});

test('context prints env key names but never values', function () {
    fixtureProject();

    [$exit, $output] = leaf('context');

    expect($exit)->toBe(0)
        ->and($output)->toContain('APP_KEY')
        ->and($output)->toContain('DB_PASSWORD')
        ->and($output)->not->toContain('supersecret')
        ->and($output)->not->toContain('secret-value-should-never-print');
});

test('a closure route does not inherit the next route\'s handler', function () {
    fixtureProject();

    [, $output] = leaf('context');

    // the tail-bleed regression: the `/` closure must not be labelled
    // with UsersController or auth.required from later registrations
    $rootLine = collect_lines($output, 'GET /');

    expect($rootLine)->not->toContain('UsersController')
        ->and($rootLine)->not->toContain('auth.required');
});

test('context appends shared memory when .leaf/CONTEXT.md exists', function () {
    fixtureProject();
    mkdir(getcwd() . '/.leaf', 0777, true);
    file_put_contents(getcwd() . '/.leaf/CONTEXT.md', "<!-- leaf.context v1 -->\n\n## Current Goal\n\nShip the fixture.\n");

    [$exit, $output] = leaf('context');

    expect($exit)->toBe(0)->and($output)->toContain('Ship the fixture');
});

test('context --raw prints only the shared memory file', function () {
    fixtureProject();
    mkdir(getcwd() . '/.leaf', 0777, true);
    file_put_contents(getcwd() . '/.leaf/CONTEXT.md', "<!-- leaf.context v1 -->\n\n## Current Goal\n\nRaw mode.\n");

    [$exit, $output] = leaf('context --raw');

    expect($exit)->toBe(0)
        ->and($output)->toContain('Raw mode')
        ->and($output)->not->toContain('GET /');
});

test('context marks itself beta with an issue link', function () {
    fixtureProject();

    [, $output] = leaf('context');

    expect($output)->toContain('(beta)')
        ->and($output)->toContain('github.com/leafsphp/cli/issues');
});

test('context fails cleanly outside a project', function () {
    [$exit, $output] = leaf('context');

    expect($exit)->toBe(1)->and($output)->toContain('No Leaf project found');
});

/**
 * First line of output containing $needle.
 */
function collect_lines(string $output, string $needle): string
{
    foreach (explode("\n", $output) as $line) {
        if (strpos($line, $needle) !== false) {
            return $line;
        }
    }

    return '';
}
