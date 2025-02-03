<?php

declare(strict_types=1);

namespace Leaf\Console;

use Leaf\Sprout\Command;

class UninstallCommand extends Command
{
    protected $signature = 'uninstall {packages*}';

    protected $description = 'Uninstall a package';

    protected function handle(): int
    {
        $packages = $this->argument('packages');
        $parsedPackages = [];

        if (!sprout()->composer()->json()) {
            $this->writeln('<error>No composer.json found in the current directory.</error>');
            return 1;
        }

        foreach ($packages as $package) {
            if (strpos($package, '/') == false) {
                $package = "leafs/$package";
            }

            $parsedPackages[] = $package;
        }

        if (!sprout()->composer()->remove(implode(' ', $parsedPackages) . ' --ansi')->isSuccessful()) {
            return 1;
        }

        $this->writeln('<comment>packages uninstalled successfully!</comment>');

        return 0;
    }
}
