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
        if ($this->isMVCApp()) {
            return (int) sprout()->run("php leaf serve --ansi");
        }

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

        $port = $this->option('port');
        $isDockerProject = file_exists(getcwd() . '/docker-compose.yml');
        $useConcurrent = !$this->option('no-concurrent') && (file_exists(getcwd() . '/vite.config.js') && file_exists(getcwd() . '/package.json'));
        $serveCommand = !$useConcurrent ? "php -S localhost:$port" : "npx concurrently -c \"#3eaf7c,#bd34fe\" \"php -S localhost:$port\" \"npm run dev\" --names=server,vite --colors";

        return sprout()
            ->process($isDockerProject ? 'docker compose up' : $serveCommand)
            ->setTimeout(null)
            ->run();
    }

    protected function isMVCApp()
    {
        $directory = getcwd();

        return is_dir("$directory/app/views") && file_exists("$directory/leaf") && is_dir("$directory/public");
    }
}
