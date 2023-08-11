<?php

declare(strict_types=1);

namespace Leaf\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ViewDevCommand extends Command
{
    protected static $defaultName = 'view:dev';

    protected function configure()
    {
        $this
            ->setAliases(['view:serve', 'view:watch'])
            ->setHelp('Run your frontend dev command')
            ->setDescription('Run your frontend dev server');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $directory = getcwd();
        $npm = Utils\Core::findNpm();

        if (!is_dir("$directory/node_modules")) {
            $output->writeln("<info>Installing dependencies...</info>");
            $success = Utils\Core::run("$npm install", $output);

            if (!$success) {
                $output->writeln("<error>❌  Failed to install dependencies.</error>");
                return 1;
            }
        }

        $success = Utils\Core::run("$npm run dev", $output);

        if (!$success) return 1;

        return 0;
    }
}
