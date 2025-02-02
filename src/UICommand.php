<?php

declare(strict_types=1);

namespace Leaf\Console;

use Leaf\Sprout\Command;

class UICommand extends Command
{
    protected $signature = 'ui {--port=3001}';

    protected $description = 'Open up the Leaf CLI GUI';

    protected function execute(): int
    {
        $port = $this->option('port');
        $uiDirectory = __DIR__ . '/ui/dist';
        $serveCommand = "cd $uiDirectory && php -S localhost:$port";

        $process = sprout()->process($serveCommand);

        $this->writeln("<info>CLI GUI started at <href=http://localhost:$port>http://localhost:$port</></info>");

        return $process->run();
    }
}
