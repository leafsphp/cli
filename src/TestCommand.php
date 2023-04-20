<?php

declare(strict_types=1);

namespace Leaf\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class TestCommand extends Command
{
	protected static $defaultName = 'test';

	protected function configure()
	{
		$this
			->setHelp('Test your leaf application through leaf alchemy')
			->setDescription('Test your leaf application through leaf alchemy');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$composerJsonPath = getcwd() . '/composer.json';
		$alchemyConfig = getcwd() . '/alchemy.config.php';

		if (!file_exists($composerJsonPath)) {
			$output->writeln('<error>No composer.json found in the current directory.</error>');
			return 1;
		}

		if (!file_exists($alchemyConfig)) {
			$output->writeln('<error>No alchemy.config.php found in the current directory.</error>');
			return 1;
		}

		$process = Process::fromShellCommandline(
			"./vendor/bin/alchemy run",
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
