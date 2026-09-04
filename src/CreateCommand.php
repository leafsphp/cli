<?php

declare(strict_types=1);

namespace Leaf\Console;

use Leaf\Console\Utils\Package;
use Leaf\FS;
use Leaf\Sprout\Command;

class CreateCommand extends Command
{
    /**
     * Available modules
     */
    protected $modules = [
        'Database' => 'leafs/db',
        'Authentication' => 'leafs/auth',
        'Session support' => 'leafs/session',
        'Cookie support' => 'leafs/cookie',
        'CSRF protection' => 'leafs/csrf',
        'CORS support' => 'leafs/cors',
        'Leaf Date' => 'leafs/date',
        'Leaf Fetch' => 'leafs/fetch',
    ];

    protected $projectName;
    protected $projectType;

    protected $signature = 'create
        {project-name? : The name of the project}
        {--lite? : Create a raw lite leaf project}
        {--basic? : Create a raw lite leaf project (alias for --lite)}
        {--api? : Create a new Leaf MVC project for APIs}
        {--mvc? : Create a new Leaf MVC project}
        {--console? : Create a new Leaf console project}
        {--docker? : Scaffold a docker environment}
        {--force? : Forces install even if the directory already exists}';

    // {--custom? : Scaffold a personalized Leaf app}

    protected $description = 'Create a new Leaf project';

    protected function handle(): int
    {
        $needsUpdate = Package::updateAvailable();

        if ($needsUpdate) {
            $this->writeln('Update found, updating to the latest stable version...');

            if (sprout()->run('php ' . dirname(__DIR__) . '/bin/leaf update') === 0) {
                $this->writeln("Leaf CLI updated successfully, building your app...\n");

                passthru('php ' . implode(' ', array_map('escapeshellarg', (array) $_SERVER['argv'])), $exitCode);

                return $exitCode;
            } else {
                $this->writeln("❌ Leaf CLI update failed, please try again later\n");
                $this->writeln("⚙️  Creating app with current version...\n");
            }
        }

        $this->projectName = $this->argument('project-name');
        $this->projectType = ($this->option('lite') || $this->option('basic')) ? 'lite' : ($this->option('api') ? 'api' : ($this->option('mvc') ? 'mvc' : ($this->option('console') ? 'console' : null)));

        $this->writeln("\033[32m
 _                __    ____ 
| |    ___  __ _ / _|  | ___|
| |   / _ \/ _` | |_   |___ \
| |__|  __/ (_| |  _|   ___) |
|_____\___|\__,_|_|    |____/
        \033[0m\n");

        $scaffoldOptions = sprout()->prompt([
            [
                'type' => $this->projectName ? null : 'text',
                'name' => 'name',
                'message' => 'Project name',
                'default' => 'my-leaf-project',
                'validate' => function ($value) {
                    if (empty($value)) {
                        return 'Name cannot be empty';
                    }

                    return true;
                },
            ],
            [
                'type' => $this->projectType ? null : 'select',
                'name' => 'type',
                'message' => 'Select a preset',
                'default' => 0,
                'choices' => [
                    ['title' => 'Lite Leaf app', 'value' => 'lite'],
                    ['title' => 'Full-stack MVC app', 'value' => 'mvc'],
                    ['title' => 'Leaf MVC API app', 'value' => 'api'],
                    ['title' => 'Console app via Seedling', 'value' => 'console'],
                ],
            ],
        ]);

        $this->projectName ??= $scaffoldOptions['name'];
        $this->projectType ??= $scaffoldOptions['type'];

        $commands = [];
        $directory = path($this->projectName !== '.' ? getcwd() . '/' . $this->projectName : getcwd())->normalize();

        if (!$this->option('force') && $this->applicationExists($directory)) {
            $this->writeln('');
            $this->error('"' . basename($directory) . '" already exists in ./' . basename(dirname($directory)));
            $this->writeln('Choose a different name, or re-run with <info>--force</info> to build in the existing folder.');

            return 1;
        }

        $this->writeln(
            "\n⚙️  Creating \""
            . basename($directory) . '" in ./'
            . basename(dirname($directory)) .
            " using preset {$this->projectType}."
        );

        if ($this->projectType === 'lite') {
            if (
                !FS\Directory::copy(__DIR__ . '/themes/leaf3', $directory, [
                    'recursive' => true,
                ])
            ) {
                $this->writeln('<error>❌  Failed to create project</error>');

                return 1;
            }

            $commands[] = "cd \"$directory\"";
            $commands[] = 'composer install --ansi';
        } elseif ($this->projectType === 'console') {
            $commands[] = "composer create-project leafs/seedling \"$directory\" --ansi";
            $commands[] = "cd \"$directory\"";
        } else {
            $commands[] = "composer create-project leafs/mvc \"$directory\" --ansi";
            $commands[] = "cd \"$directory\"";
        }

        if ($this->option('no-ansi')) {
            $commands = array_map(function ($value) {
                return strpos($value, 'cd ') === 0 ? $value : "$value --no-ansi";
            }, $commands);
        }

        if ($this->option('quiet')) {
            $commands = array_map(function ($value) {
                return strpos($value, 'cd ') === 0 ? $value : "$value --quiet";
            }, $commands);
        }

        if (sprout()->process(implode(' && ', $commands))->setTimeout(null)->run() === 0) {
            if ($this->projectType === 'console') {
                \Leaf\FS\File::move("$directory/bin/sprout", "$directory/bin/" . basename($directory));

                \Leaf\FS\File::write("$directory/composer.json", function ($content) use ($directory) {
                    return str_replace('"bin/sprout"', '"bin/' . basename($directory) . '"', $content);
                });

                $this->writeln("\n🚀 Successfully created project " . basename($directory) . "\n");
                $this->writeln('👉  Get started with the following commands:');
                $this->writeln("\n    cd " . basename($directory));
                $this->writeln('    php leaf greet');

                $this->writeln("\n🍁  Happy gardening!\n");

                return 0;
            }

            if ($this->projectType === 'api') {
                if (\Leaf\FS\File::exists("$directory/vite.config.js")) {
                    \Leaf\FS\File::delete("$directory/vite.config.js");
                }

                if (\Leaf\FS\File::exists("$directory/package.json")) {
                    \Leaf\FS\File::delete("$directory/package.json");
                }

                \Leaf\FS\Directory::delete("$directory/app/views", ['recursive' => true]);
                \Leaf\FS\Directory::delete("$directory/app/routes", ['recursive' => true]);
                \Leaf\FS\Directory::delete("$directory/public/assets", ['recursive' => true]);

                \Leaf\FS\Directory::copy(__DIR__ . '/themes/api/routes', "$directory/app/routes");

                \Leaf\FS\File::write("$directory/leaf", function ($content) {
                    return str_replace(
                        'Leaf\Core::loadConsole()',
                        "Leaf\Core::mode('api');\nLeaf\Core::loadConsole()",
                        $content
                    );
                });
            }

            $this->writeln("\n🚀 Successfully created project " . basename($directory) . "\n");

            $extraOptions = sprout()->prompt([
                [
                    'type' => $this->projectType !== 'lite' ? 'confirm' : null,
                    'name' => 'auth',
                    'message' => $this->projectType === 'api' ? 'Setup auth flow?' : 'Install application starter?',
                    'default' => true,
                ],
                [
                    'type' => $this->projectType === 'mvc' ? 'select' : null,
                    'name' => 'view',
                    'message' => 'Select a view engine',
                    'default' => 0,
                    'choices' => [
                        [
                            'title' => 'Default',
                            'value' => 'default',
                            'disabled' => function ($answers) {
                                return $answers['auth'] ?? false;
                            },
                        ],
                        ['title' => 'Blade + Tailwind', 'value' => 'tailwind'],
                        ['title' => 'React JS', 'value' => 'react'],
                        ['title' => 'Vue JS', 'value' => 'vue'],
                        ['title' => 'Svelte', 'value' => 'svelte'],
                    ],
                ],
                [
                    'type' => 'confirm',
                    'name' => 'tests',
                    'message' => 'Set up tests?',
                    'default' => true,
                ],
                [
                    'type' => $this->option('docker') ? null : 'confirm',
                    'name' => 'docker',
                    'message' => 'Set up docker?',
                    'default' => false,
                ],
            ]);

            $extraCommands = ["cd \"$directory\""];

            if (isset($extraOptions['view']) && $extraOptions['view'] !== 'default') {
                $extraCommands[] = 'php leaf view:install --' . $extraOptions['view'];
            }

            if ($extraOptions['auth'] ?? false) {
                $extraCommands[] = 'php leaf scaffold:auth';
            }

            if ($extraOptions['tests'] ?? false) {
                $extraCommands[] = 'composer require --dev --ansi leafs/alchemy && php vendor/bin/alchemy config:install --ansi';
            }

            if ($this->projectType === 'api') {
                $extraCommands[] = 'composer require leafs/cors --ansi';
            }

            $this->write("\n");

            if (($extraOptions['docker'] ?? false) || $this->option('docker')) {
                \Leaf\FS\Directory::copy(__DIR__ . '/themes/docker', $directory, [
                    'recursive' => true,
                ]);

                if ($this->projectType !== 'lite') {
                    // MVC/API apps serve from public/, and the app root must
                    // never be the docroot (it holds .env, app/, vendor/)
                    \Leaf\FS\File::write("$directory/docker/000-default.conf", function ($content) {
                        return str_replace(
                            ['DocumentRoot /var/www', '<Directory /var/www>'],
                            ['DocumentRoot /var/www/public', '<Directory /var/www/public>'],
                            $content
                        );
                    });
                }
            }

            if ($this->projectType !== 'lite') {
                \Leaf\FS\File::write("$directory/.env", function ($content) use ($directory) {
                    return str_replace(
                        ['LEAF_DB_NAME', 'LEAF_DB_USERNAME'],
                        [str_replace('-', '_', basename($directory)), 'root'],
                        $content
                    );
                });
            }

            if (sprout()->process(implode(' && ', $extraCommands))->setTimeout(null)->run() === 0 || count($extraCommands) === 1) {
                $this->writeln("\n🚀  Application scaffolded successfully");
                $this->writeln('👉  Get started with the following commands:');
                $this->writeln("\n    cd " . basename($directory));
                $this->writeln('    leaf serve');

                if ($extraOptions['tests']) {
                    $this->writeln("\n👉  You can run tests with:");
                    $this->writeln("\n    leaf run test");
                }

                $this->writeln("\n🍁  How fast can you ship?");
            } else {
                $this->writeln("\n❌  Could not scaffold extra options for " . basename($directory));
                $this->writeln('👉  Get started with the following commands:');
                $this->writeln("\n   cd " . basename($directory));
                $this->writeln('    leaf serve');

                $this->writeln("\n🍁  Happy gardening!");
            }

            return 0;
        }

        $this->writeln('<error>❌  Failed to create project</error>');

        return 1;
    }

    /**
     * Check whether something already exists at the target directory.
     */
    protected function applicationExists(string $directory): bool
    {
        return (is_dir($directory) || is_file($directory)) && $directory != getcwd();
    }
}
