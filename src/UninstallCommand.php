<?php

namespace Leaf\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class UninstallCommand extends Command
{
	protected static $defaultName = 'uninstall';

	protected function configure()
	{
		$this
			->setHelp('The uninstall command removes a package from the current
  list of installed packages')
			->setDescription('Uninstall a  package')
			->addArgument('packages', InputArgument::IS_ARRAY, 'package(s) to uninstall.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$packages = $input->getArgument('packages');

		$composerJsonPath = getcwd() . '/composer.json';

		if (!file_exists($composerJsonPath)) {
			$output->writeln('<error>No composer.json found in the current directory.</error>');
			return 1;
		}

		$composer = Core::findComposer();

		foreach ($packages as $package) {
			if (strpos($package, '/') == false) {
				$package = "leafs/$package";
			}

			$process = Process::fromShellCommandline(
				"$composer remove $package",
				null,
				null,
				null,
				null
			);

			$process->run(function ($type, $line) use ($output) {
				$output->write($line);
			});

			if (!$process->isSuccessful()) return 1;
		}

		$output->writeln('<comment>packages uninstalled successfully!</comment>');

		return 0;
	}
}
