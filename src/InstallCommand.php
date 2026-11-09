<?php

declare(strict_types=1);

namespace Leaf\Console;

use Leaf\Console\Utils\Upgrade;
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

            // sprout's composer helper adds --ansi itself
            $flags = $this->option('dev') ? ' --dev' : '';

            if (!sprout()->composer()->install(implode(' ', $parsedPackages) . $flags)->isSuccessful()) {
                return 1;
            }

            return $this->finish();
        }

        if (!sprout()->composer()->install()->isSuccessful()) {
            return 1;
        }

        return $this->finish();
    }

    /**
     * Print one plain line if the app is behind on Leaf majors.
     */
    protected function finish(): int
    {
        if ($nudge = Upgrade::nudge()) {
            $this->writeln($nudge);
        }

        return 0;
    }
}
