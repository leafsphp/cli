<?php

declare(strict_types=1);

namespace Leaf\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;

class UICommand extends Command
{
    protected static $defaultName = 'ui';

    protected function configure()
    {
        $this
            ->setHelp('Open up the Leaf CLI GUI')
            ->setDescription('Start the Leaf CLI GUI process')
            ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Port to run app on');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $port = $input->getOption('port') ? (int) $input->getOption('port') : 5500;
        $uiDirectory = __DIR__ . '/ui/dist';

        $serveCommand = "cd $uiDirectory && php -S localhost:$port";

        $process = Process::fromShellCommandline(
            $serveCommand,
            null,
            null,
            null,
            null
        );

        $output->writeln("<info>CLI GUI started at <href=http://localhost:$port>http://localhost:$port</></info>");

        return $process->run(function ($type, $line) use ($output, $process) {
            if (is_string($line) && !strpos($line, 'Failed')) {
                $output->write($line);
            } else {
                $output->write("<error>$line</error>");
            }
        });
    }
}
