<?php

declare(strict_types=1);

namespace Leaf\Console;

use Leaf\Sprout\Command;

class UpdateCommand extends Command
{
    protected $signature = 'update';

    protected $description = 'Update leaf cli to the latest version';

    protected function handle(): int
    {
        if (sprout()->composer(true)->remove('cli --no-update --no-install')->isSuccessful()) {
            sleep(1);

            if (sprout()->composer(true)->install('cli')->isSuccessful()) {
                $this->writeln('<info>Leaf CLI installed successfully!</info>');
                return 0;
            }
        }

        $this->writeln('<error>Could not update CLI, please retry!</error>');

        return 1;
    }
}
