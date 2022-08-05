<?php

declare(strict_types=1);

namespace Leaf\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class TestSetupCommand extends Command
{
	protected static $defaultName = 'test:setup';

	protected function configure()
	{
		$this
			->setHelp('Setup tests with Pest PHP or PHPUnit')
			->setDescription('Add tests to your application')
			->addOption('pest', null, InputOption::VALUE_NONE, 'Setup tests with Pest PHP (default)')
			->addOption('phpunit', null, InputOption::VALUE_NONE, 'Setup tests with PHPUnit');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$engine = 'pest';
		$composerJsonPath = getcwd() . '/composer.json';

		if ($input->getOption('phpunit')) {
			$engine = 'phpunit';
		}

		if (!file_exists($composerJsonPath)) {
			$output->writeln('<error>No composer.json found in the current directory.</error>');
			return 1;
		}

		$process = Process::fromShellCommandline(
			"./vendor/bin/alchemy setup --$engine",
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
