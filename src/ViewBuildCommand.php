<?php

declare(strict_types=1);

namespace Leaf\Console;

use Leaf\Sprout\Command;

class ViewBuildCommand extends Command
{
    protected $signature = 'view:build {--pm=npm}';

    protected $description = 'Build your frontend assets';

    protected function handle(): int
    {
        if (!sprout()->npm()->json()) {
            $this->writeln('<error>No package.json found in the current directory.</error>');
            return 1;
        }

        if (!sprout()->npm()->hasDependencies()) {
            $this->writeln('<info>Installing dependencies...</info>');
            
            if (!sprout()->npm($this->option('pm'))->install()->isSuccessful()) {
                $this->writeln('<error>❌  Failed to install dependencies.</error>');
                return 1;
            }
        }

        $this->writeln('<info>Building assets...</info>');

        return (int) sprout()
            ->npm($this->option('pm'))
            ->runScript('build')
            ->isSuccessful();
    }
}
