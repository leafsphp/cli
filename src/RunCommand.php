<?php

declare(strict_types=1);

namespace Leaf\Console;

use Leaf\Sprout\Command;

class RunCommand extends Command
{
    protected $signature = 'run {script : Command to run.}';

    protected $description = 'Run a script in your composer.json';

    protected function handle(): int
    {
        if (!sprout()->composer()->json()) {
            $this->writeln('<error>No composer.json found in the current directory.</error>');
            return 1;
        }

        return (int) sprout()
            ->composer()
            ->runScript($this->argument('script'))
            ->isSuccessful();
    }
}
