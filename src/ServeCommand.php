<?php

namespace Leaf\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ServeCommand extends Command
{
	protected static $defaultName = 'serve';

	protected function configure()
	{
		$this
			->setHelp("Start the leaf app server")
			->setDescription("Run your Leaf app")
			->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Port to run app on');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$port = $input->getOption("port") ? (int) $input->getOption("port") : 5500;
		$process = Process::fromShellCommandline("php -S localhost:$port", null, null, null, null);

		return $process->run(function ($type, $line) use ($output, $port) {
			if (is_string($line) && !strpos($line, 'Failed')) {
				$output->writeln("<info>Leaf development server started: http://localhost:$port</info>");
				$output->writeln("<comment>Happy coding!</comment>");
				$output->write($line);
			} else {
				$output->write("<error>$line</error>");
			}
		});
	}
}
