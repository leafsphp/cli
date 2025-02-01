<?php

declare(strict_types=1);

namespace Leaf\Console;

use Leaf\Sprout\Command;

class InstallCommand extends Command
{
    protected $signature = 'install {packages*} {--d|dev}';

    protected $description = 'Install a new package';

    protected function execute(): int
    {
        $packages = $this->argument('packages');

        if (count($packages)) {
            foreach ($packages as $package) {
                if (strpos($package, '/') == false) {
                    $package = "leafs/$package";
                }

                $package = str_replace('@', ':', $package);
                $package = $this->option('dev') ? "$package --dev" : $package;

                $this->writeln("<info>Installing $package...</info>");

                if (!sprout()->composer()->install($package)->isSuccessful()) {
                    return 1;
                }

                $this->writeln("<comment>$package installed successfully!</comment>");
            }
        }

        return (int) sprout()->composer()->install()->isSuccessful();
    }
}
