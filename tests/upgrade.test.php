<?php

use Leaf\Console\Utils\Upgrade;

beforeEach(function () {
    sandboxSetup();
    file_put_contents('composer.json', '{"name":"t/t"}');
});

afterEach(function () {
    sandboxTeardown();
});

/**
 * Write a composer.lock with the given name => version pairs.
 */
function lockWith(array $packages): void
{
    file_put_contents('composer.lock', json_encode([
        'packages' => array_map(
            fn ($name, $version) => ['name' => $name, 'version' => $version],
            array_keys($packages),
            $packages
        ),
        'packages-dev' => [],
    ]));
}

/**
 * Core::findComposer() prefers ./composer.phar, so a PHP script there
 * stands in for composer on every platform.
 */
function fakeComposer(string $outdatedJson, int $exitCode = 0): void
{
    file_put_contents('composer.phar', '<?php echo ' . var_export($outdatedJson, true) . "; exit($exitCode);");
}

test('nudges once when leafs/leaf is below 5', function () {
    lockWith(['leafs/leaf' => 'v4.4.1', 'leafs/http' => 'v4.2']);

    expect(Upgrade::nudge())
        ->toBe('leafs/leaf 4.4.1 installed, Leaf 5 is available. See https://leafphp.dev/docs/upgrade-guide');
});

test('lists leafs modules with a newer major', function () {
    lockWith(['leafs/leaf' => 'v5.1.0']);
    fakeComposer(json_encode(['installed' => [
        ['name' => 'leafs/form', 'version' => 'v3.2', 'latest' => 'v5.0.1'],
        ['name' => 'symfony/yaml', 'version' => 'v6.4.0', 'latest' => 'v7.1.0'],
    ]]));

    expect(Upgrade::nudge())
        ->toBe('Newer Leaf majors available: leafs/form 3.2 -> 5.0.1. See https://leafphp.dev/docs/upgrade-guide');
});

test('stays silent when everything is current', function () {
    lockWith(['leafs/leaf' => 'v5.1.0']);
    fakeComposer(json_encode(['installed' => []]));

    expect(Upgrade::nudge())->toBeNull();
});

test('stays silent when composer outdated fails (offline)', function () {
    lockWith(['leafs/leaf' => 'v5.1.0']);
    fakeComposer('The "https://repo.packagist.org/packages.json" file could not be downloaded', 1);

    expect(Upgrade::nudge())->toBeNull();
});

test('stays silent outside a composer project', function () {
    unlink('composer.json');

    expect(Upgrade::nudge())->toBeNull();
});

test('ignores dev-branch versions of leafs/leaf', function () {
    lockWith(['leafs/leaf' => 'dev-main']);
    fakeComposer(json_encode(['installed' => []]));

    expect(Upgrade::nudge())->toBeNull();
});
