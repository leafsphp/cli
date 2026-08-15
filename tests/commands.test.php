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

test('--version prints the version and nothing else', function () {
    [$exit, $output] = leaf('--version');

    expect($exit)->toBe(0)
        ->and(trim($output))->not->toBe('')
        ->and($output)->not->toContain('Available commands');
});

test('the banner prints the version exactly once', function () {
    [, $version] = leaf('--version');
    [, $banner] = leaf('');

    expect(substr_count($banner, trim($version)))->toBe(1);
});

test('the lite theme requires stable leaf', function () {
    $theme = json_decode(file_get_contents(dirname(__DIR__) . '/src/themes/leaf3/composer.json'), true);

    // the lite preset copies this file into new projects — an exact or
    // pre-release pin here breaks `leaf create --lite` for everyone
    expect($theme['require']['leafs/leaf'])->toStartWith('^');
});

test('the post-update re-exec keeps the terminal', function () {
    $source = file_get_contents(dirname(__DIR__) . '/src/CreateCommand.php');

    // a piped re-exec (sprout()->run) can't prompt — the fresh cli then
    // answers every question with its default and scaffolds the wrong app
    expect($source)->toContain("passthru('php ' . implode")
        ->and($source)->not->toContain("return sprout()->run('php ' . implode");
});

test('view:install pins every vite-adjacent npm package', function () {
    $source = file_get_contents(dirname(__DIR__) . '/src/ViewInstallCommand.php');

    // unpinned @vitejs/* and vite resolve to whatever npm's latest is —
    // when vite 8 shipped, latest plugin-react moved to a peer range
    // @leafphp/vite-plugin doesn't allow, and every install ERESOLVE'd
    preg_match_all('/->install\(\'([^\']+)\'\)/', $source, $matches);

    foreach ($matches[1] as $packageList) {
        foreach (explode(' ', $packageList) as $package) {
            if (preg_match('/^(@vitejs\/|@sveltejs\/|@inertiajs\/|vite$)/', $package)) {
                expect($package)->toContain('@^');
            }
        }
    }
});

test('lite apps are born AI-ready', function () {
    leaf('create my-app --lite');

    // composer install fails against the shim, but the theme copy happens
    // first — the AI context files must be in place
    expect(file_exists(getcwd() . '/my-app/AGENTS.md'))->toBeTrue()
        ->and(file_exists(getcwd() . '/my-app/.leaf/CONTEXT.md'))->toBeTrue()
        ->and(file_get_contents(getcwd() . '/my-app/.leaf/CONTEXT.md'))->toContain('<!-- leaf.context v1 -->')
        ->and(file_exists(getcwd() . '/my-app/.htaccess'))->toBeTrue();
});
