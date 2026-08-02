<?php

declare(strict_types=1);

namespace Leaf\Console;

use Leaf\Sprout\Command;

class InstallCommand extends Command
{
    protected $signature = 'install {packages?*} {--d|dev=false : Install as a dev dependency}';

    protected $description = 'Install a new package';

    protected function handle(): int
    {
        $packages = $this->argument('packages');
        $parsedPackages = [];

        if (count($packages)) {
            foreach ($packages as $package) {
                if (strpos($package, '/') === false) {
                    $package = "leafs/$package";
                }

                $parsedPackages[] = str_replace('@', ':', $package);
            }

            $flags = $this->option('dev') ? ' --dev --ansi' : ' --ansi';

            if (!sprout()->composer()->install(implode(' ', $parsedPackages) . $flags)->isSuccessful()) {
                return 1;
            }

            return 0;
        }

        return sprout()->composer()->install()->isSuccessful() ? 0 : 1;
    }
}
