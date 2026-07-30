<?php

declare(strict_types=1);

namespace Leaf\Console;

use Leaf\Sprout\Command;

class ServeCommand extends Command
{
    protected $signature = 'serve
        {filename? : The PHP script to run}
        {--p|port=5500 : Port to run app on}
        {--nc|no-concurrent? : Run PHP server without Vite server}';

    protected $description = 'Run a server to serve your Leaf app';

    protected function handle(): int
    {
        if (!sprout()->composer()->json()) {
            $this->writeln('<error>No composer.json found in the current directory.</error>');

            return 1;
        }

        if (!sprout()->composer()->hasDependencies()) {
            $this->writeln('<info>Installing dependencies...</info>');

            if (!sprout()->composer()->install()->isSuccessful()) {
                $this->writeln('<error>❌  Failed to install dependencies.</error>');

                return 1;
            }
        }

        if ($this->isMVCApp()) {
            // delegate to the app's own console by explicit path — a bare
            // `leaf` can resolve to another package's vendor/bin shim
            $appConsole = escapeshellarg(getcwd() . '/leaf');

            return (int) sprout()->run("php $appConsole serve --port={$this->option('port')} --ansi", null);
        }

        $port = $this->option('port');

        if (file_exists(getcwd() . '/docker-compose.yml')) {
            return sprout()->process('docker compose up')->setTimeout(null)->run();
        }

        $useConcurrent = !$this->option('no-concurrent') && (file_exists(getcwd() . '/vite.config.js') && file_exists(getcwd() . '/package.json'));
        $server = sprout()->process("php -S localhost:$port")->setTimeout(null);

        if (!$useConcurrent) {
            return $server->run();
        }

        // run vite alongside the PHP server ourselves — no npx/concurrently,
        // whose nested quoting breaks on Windows shells
        $vite = sprout()->process('npm run dev')->setTimeout(null);
        $vite->start(function ($type, $buffer) {
            echo "\033[0;35m[vite]\033[0m $buffer";
        });

        $exitCode = $server->run(function ($type, $buffer) {
            echo "\033[0;32m[server]\033[0m $buffer";
        });

        $vite->stop();

        return $exitCode;
    }

    protected function isMVCApp()
    {
        $directory = getcwd();

        return is_dir("$directory/app/views") && file_exists("$directory/leaf") && is_dir("$directory/public");
    }
}
