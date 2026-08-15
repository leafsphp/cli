<?php

/*
|--------------------------------------------------------------------------
| Leaf CLI test helpers
|--------------------------------------------------------------------------
|
| Tests run inside a throwaway sandbox with a fake `composer` on PATH,
| so commands build their real shell strings without ever touching the
| network or a real project.
|
*/

function sandboxSetup(): void
{
    $GLOBALS['__cliTestCwd'] = getcwd();

    $base = PHP_OS_FAMILY === 'Windows' ? sys_get_temp_dir() : '/tmp';
    $sandbox = $base . '/leaf-cli-test-' . substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 10);

    mkdir("$sandbox/bin", 0777, true);
    file_put_contents("$sandbox/bin/composer", "#!/bin/bash\necho \"COMPOSER CALLED WITH: \$@\"\n");
    chmod("$sandbox/bin/composer", 0755);
    chdir($sandbox);

    $GLOBALS['__cliSandbox'] = $sandbox;
}

function sandboxTeardown(): void
{
    chdir($GLOBALS['__cliTestCwd']);
    removeDirRecursive($GLOBALS['__cliSandbox']);
}

function removeDirRecursive(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = "$dir/$item";
        is_dir($path) && !is_link($path) ? removeDirRecursive($path) : unlink($path);
    }

    rmdir($dir);
}

/**
 * Run the real leaf binary inside the current sandbox, with the fake
 * composer first on PATH.
 * @return array{0: int, 1: string} exit code and combined output
 */
function leaf(string $args): array
{
    $bin = dirname(__DIR__) . '/bin/leaf';
    $path = $GLOBALS['__cliSandbox'] . '/bin:' . getenv('PATH');

    exec('PATH=' . escapeshellarg($path) . ' ' . escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($bin) . " $args 2>&1", $output, $exit);

    return [$exit, implode("\n", $output)];
}

/**
 * Write a minimal fixture project into the sandbox for `leaf context`
 * to scan: routes with every registration shape the parser claims to
 * understand, a model, a schema file, and env keys.
 */
function fixtureProject(): string
{
    $root = getcwd();

    mkdir("$root/app/routes", 0777, true);
    mkdir("$root/app/models", 0777, true);
    mkdir("$root/app/database", 0777, true);

    file_put_contents("$root/composer.json", json_encode([
        'name' => 'fixture/app',
        'require' => ['leafs/leaf' => '^5.0', 'leafs/db' => '^5.0'],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    file_put_contents("$root/app/routes/index.php", <<<'PHP'
<?php

app()->get('/', function () {
    response()->json(['ok' => true]);
});

app()->get('/users/{id}', 'UsersController@show');

app()->post('/users', ['middleware' => 'auth.required', 'UsersController@store']);

app()->group('/admin', function () {
    app()->get('/dashboard', 'AdminController@index');
});

app()->resource('/posts', 'PostsController');
PHP);

    file_put_contents("$root/app/models/User.php", "<?php\n\nclass User extends Model\n{\n}\n");
    file_put_contents("$root/app/database/users.yml", "users:\n  id: id\n  name: string\n");
    file_put_contents("$root/.env.example", "APP_KEY=\nDB_HOST=localhost\nDB_PASSWORD=\n");
    file_put_contents("$root/.env", "APP_KEY=secret-value-should-never-print\nDB_HOST=localhost\nDB_PASSWORD=supersecret\n");

    return $root;
}
