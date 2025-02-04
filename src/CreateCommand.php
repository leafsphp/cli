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

        $selections = sprout()->prompt([
            [
                'type' => $this->projectName ? null : 'text',
                'name' => 'name',
                'message' => 'What is your project name?',
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
                'message' => 'What type of project do you want?',
                'default' => 0,
                'choices' => [
                    ['title' => 'Basic Leaf app', 'value' => 'basic'],
                    ['title' => 'Leaf MVC app', 'value' => 'mvc'],
                    ['title' => 'Leaf MVC app for APIs', 'value' => 'api'],
                ],
            ]
        ]);

        $this->projectName ??= $selections['name'];
        $this->projectType ??= $selections['type'];

        $commands = [];
        $directory = $this->projectName !== '.' ? getcwd() . '/' . $this->projectName : getcwd();

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

            $commands[] = 'cd ' . basename($directory);
            $commands[] = 'composer install --ansi';
        } else {
            $commands[] = 'composer create-project leafs/mvc ' . basename($directory) . ' --ansi';
            $commands[] = 'cd ' . basename($directory);
        }

        if ($this->option('no-ansi')) {
            $commands = array_map(function ($value) {
                return $value . ' --no-ansi';
            }, $commands);
        }

        if ($this->option('quiet')) {
            $commands = array_map(function ($value) {
                return $value . ' --quiet';
            }, $commands);
        }

        if (sprout()->run(implode(' && ', $commands)) === 0) {
            if ($this->projectType === 'api') {
                if (\Leaf\FS\File::exists("$directory/vite.config.js")) {
                    \Leaf\FS\File::delete("$directory/vite.config.js");
                }
                
                if (\Leaf\FS\File::exists("$directory/package.json")) {
                    \Leaf\FS\File::delete("$directory/package.json");
                }

                \Leaf\FS\Directory::delete("$directory/app/views");
                \Leaf\FS\Directory::create("$directory/app/views");

                \Leaf\FS\Directory::delete("$directory/app/routes");
                \Leaf\FS\Directory::copy(__DIR__ . '/themes/api/routes', "$directory/app/routes");

                \Leaf\FS\Directory::delete("$directory/public/index.php");
                \Leaf\FS\Directory::copy(__DIR__ . '/themes/api/index.php', "$directory/public/index.php");
            }

            $this->writeln("\n🚀  Successfully created project <info>" . basename($directory) . '</info>');
            $this->writeln('👉  Get started with the following commands:');
            $this->writeln("\n    <info>cd</info> " . basename($directory));
            $this->writeln('    <info>leaf serve</info>');

            // if ($testing) {
            //     $this->writeln("\n👉  You can run tests with:");
            //     $this->writeln("\n    <info>leaf test</info>");
            // }

            $this->writeln("\n🍁  Happy gardening!");
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
