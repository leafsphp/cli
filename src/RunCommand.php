<?php

declare(strict_types=1);

namespace Leaf\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class RunCommand extends Command
{
	protected static $defaultName = 'run';

	protected function configure()
	{
		$this
			->setHelp('Run a composer script')
			->setDescription('Run a script in your composer.json')
			->addArgument('scriptName', InputArgument::REQUIRED, 'Command to run.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$composerJsonPath = getcwd() . '/composer.json';

		if (!file_exists($composerJsonPath)) {
			$output->writeln('<error>No composer.json found in the current directory.</error>');
			return 1;
		}

		$script = $input->getArgument('scriptName');
		$composer = Utils\Core::findComposer();

		$process = Process::fromShellCommandline(
			"$composer run $script",
			null,
			null,
			null,
			null
		);

		$process->run(function ($type, $line) use ($output) {
			$output->write($line);
		});

		if (!$process->isSuccessful()) return 1;

		return 0;
	}
}
