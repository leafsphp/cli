<?php

declare(strict_types=1);

namespace Leaf\Console;

use Leaf\Sprout\Command;
use Psy\Shell;

class InteractCommand extends Command
{
    protected $signature = 'interact';
    protected $description = 'Interact with your application';

    protected function handle(): int
    {
        $this->writeln('<info>Leaf interactive shell activated</info>');

        if (file_exists('vendor/autoload.php')) {
            require 'vendor/autoload.php';
        }

        if (file_exists('index.php') && !file_exists('leaf')) {
            require 'index.php';
        }

        if (!file_exists('vendor/autoload.php') && !file_exists('Config/bootstrap.php') && (file_exists('index.php') && file_exists('leaf'))) {
            $this->writeln('<info>App files not found, starting a bare shell...</info>');
        }

        return (new Shell())->run();
    }
}
