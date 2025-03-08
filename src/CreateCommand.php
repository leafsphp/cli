<?php

declare(strict_types=1);

namespace Leaf\Console;

use Leaf\Console\Utils\Package;
use RuntimeException;
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
        {--basic? : Create a raw leaf project}
        {--api? : Create a new Leaf MVC project for APIs}
        {--mvc? : Create a new Leaf MVC project}
        {--docker? : Scaffold a docker environment}
        {--force? : Forces install even if the directory already exists}';

    // {--custom? : Scaffold a personalized Leaf app}

    protected $description = 'Create a new Leaf project';

    protected function handle(): int
    {
        $needsUpdate = Package::updateAvailable();

        if ($needsUpdate) {
            $this->writeln('<comment>Update found, updating to the latest stable version...</comment>');

            if (sprout()->run('php ' . dirname(__DIR__) . '/bin/leaf update')) {
                $this->writeln("<info>Leaf CLI updated successfully, building your app...</info>\n");

                return sprout()->run('php ' . implode(' ', (array) $_SERVER['argv']));
            } else {
                $this->writeln("<error>❌ Leaf CLI update failed, please try again later</error>\n");
                $this->writeln("⚙️  Creating app with current version...\n");
            }
        }

        $this->projectName = $this->argument('project-name');
        $this->projectType = $this->option('basic') ? 'basic' : ($this->option('api') ? 'api' : ($this->option('mvc') ? 'mvc' : null));

        $this->writeln("\033[32m
 _                __   _  _    ___  
| |    ___  __ _ / _| | || |  / _ \ 
| |   / _ \/ _` | |_  | || |_| | | |
| |__|  __/ (_| |  _| |__   _| |_| |
|_____\___|\__,_|_|      |_|(_)___/
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
                }
            ],
            [
                'type' => $this->projectType ? null : 'select',
                'name' => 'type',
                'message' => 'Select a preset',
                'default' => 0,
                'choices' => [
                    ['title' => 'Basic Leaf app', 'value' => 'basic'],
                    ['title' => 'Full-stack MVC app', 'value' => 'mvc'],
                    ['title' => 'Leaf MVC API app', 'value' => 'api'],
                ],
            ],
        ]);

        $this->projectName ??= $scaffoldOptions['name'];
        $this->projectType ??= $scaffoldOptions['type'];

        $commands = [];
        $directory = path($this->projectName !== '.' ? getcwd() . '/' . $this->projectName : getcwd())->normalize();

        if (!$this->option('force')) {
            $this->verifyApplicationDoesntExist($directory);
        }

        $this->writeln(
            "\n⚙️  Creating \""
            . basename($directory) . '" in <info>./'
            . basename(dirname($directory)) .
            "</info> using preset <info>{$this->projectType}</info>."
        );

        if ($this->projectType === 'basic') {
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
        } else {
            $commands[] = "composer create-project leafs/mvc:v4.x-dev \"$directory\" --ansi";
            $commands[] = "cd \"$directory\"";
        }

        if ($this->option('no-ansi')) {
            $commands = array_map(function ($value) {
                return "$value --no-ansi";
            }, $commands);
        }

        if ($this->option('quiet')) {
            $commands = array_map(function ($value) {
                return "$value --quiet";
            }, $commands);
        }

        if (sprout()->process(implode(' && ', $commands))->setTimeout(null)->run() === 0) {
            if ($this->projectType === 'api') {
                if (\Leaf\FS\File::exists("$directory/vite.config.js")) {
                    \Leaf\FS\File::delete("$directory/vite.config.js");
                }

                if (\Leaf\FS\File::exists("$directory/package.json")) {
                    \Leaf\FS\File::delete("$directory/package.json");
                }

                \Leaf\FS\Directory::delete("$directory/app/views");
                \Leaf\FS\Directory::delete("$directory/app/routes");

                \Leaf\FS\Directory::copy(__DIR__ . '/themes/api/routes', "$directory/app/routes");

                \Leaf\FS\File::write("$directory/leaf", function ($content) {
                    return str_replace(
                        'Leaf\Core::loadConsole()',
                        "Leaf\Core::mode('api');\nLeaf\Core::loadConsole()",
                        $content
                    );
                });
            }

            $this->writeln("\n🚀 Successfully created project <info>" . basename($directory) . "</info>\n");

            $extraOptions = sprout()->prompt([
                [
                    'type' => $this->projectType !== 'basic' ? 'confirm' : null,
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
                        ['title' => 'Default', 'value' => 'blade only'],
                        ['title' => 'Blade + Alpine', 'value' => 'tailwind'],
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
                    'type' => 'confirm',
                    'name' => 'docker',
                    'message' => 'Set up docker?',
                    'default' => false,
                ],
            ]);

            $extraCommands = ["cd '$directory'"];

            if (isset($extraOptions['view']) && $extraOptions['view'] !== 'blade only') {
                $extraCommands[] = 'php leaf view:install --' . $extraOptions['view'];
            }

            if ($extraOptions['auth'] ?? false) {
                $extraCommands[] = "php leaf scaffold:auth" . ($this->projectType === 'api' ? ' --api' : '');
            }

            if ($extraOptions['tests'] ?? false) {
                $extraCommands[] = 'composer require --dev --ansi leafs/alchemy && ./vendor/bin/alchemy install --ansi';
            }

            $this->write("\n");

            if ($extraOptions['docker']) {
                \Leaf\FS\Directory::copy(__DIR__ . '/themes/docker', $directory, [
                    'recursive' => true,
                ]);
            }

            if ($this->projectType !== 'basic') {
                \Leaf\FS\File::write("$directory/.env", function ($content) use ($directory) {
                    return str_replace(
                        ['LEAF_DB_NAME', 'LEAF_DB_USERNAME'],
                        [basename($directory), 'root'],
                        $content
                    );
                });
            }

            if (sprout()->process(implode(' && ', $extraCommands))->setTimeout(null)->run() === 0 || count($extraCommands) === 1) {
                $this->writeln("\n🚀  Application scaffolded successfully");
                $this->writeln('👉  Get started with the following commands:');
                $this->writeln("\n    <info>cd</info> " . basename($directory));
                $this->writeln('    <info>leaf serve</info>');

                if ($extraOptions['tests']) {
                    $this->writeln("\n👉  You can run tests with:");
                    $this->writeln("\n    <info>leaf run test</info>");
                }

                $this->writeln("\n🍁  How fast can you ship?");
            } else {
                $this->writeln("\n❌  Could not scaffold extra options for <info>" . basename($directory) . '</info>');
                $this->writeln('👉  Get started with the following commands:');
                $this->writeln("\n    <info>cd</info> " . basename($directory));
                $this->writeln('    <info>leaf serve</info>');

                $this->writeln("\n🍁  Happy gardening!");
            }
        }

        return 0;
    }

    /**
     * Verify that the application does not already exist.
     *
     * @param string $directory
     * @return void
     */
    protected function verifyApplicationDoesntExist(string $directory)
    {
        if ((is_dir($directory) || is_file($directory)) && $directory != getcwd()) {
            throw new RuntimeException('Application already exists!');
        }
    }
}
