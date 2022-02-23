<?php

namespace Leaf\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
	protected static $defaultName = 'install';

	protected function configure()
	{
		$this
			->setHelp('Install a new package')
			->setDescription('Add a new package to your leaf app')
			->addArgument('packages', InputArgument::IS_ARRAY, 'package(s) to install. Can also include a version constraint, e.g. foo/bar or foo/bar@1.0.0');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$packages = $input->getArgument('packages');

		if (count($packages)) {
			return $this->install($packages, $output);
		}

		return $this->installDependencies($output);
	}

	protected function installDependencies($output)
	{
		$composerJsonPath = getcwd() . '/composer.json';
		$composerLockPath = getcwd() . '/composer.lock';

		if (!file_exists($composerJsonPath)) {
			$output->writeln('<error>No composer.json found in the current directory. Pass in a package to add if you meant to install something.</error>');
			return 1;
		}

		$composer = Core::findComposer();
		$process = Process::fromShellCommandline(
			file_exists($composerLockPath) ? "$composer install" : "$composer update",
			null,
			null,
			null,
			null
		);

		$process->run(function ($type, $line) use ($output) {
			$output->write($line);
		});

		if (!$process->isSuccessful()) return 1;

		$output->writeln('<comment>packages installed successfully!</comment>');

		return 0;
	}

	/**
	 * Install packages
	 */
	protected function install($packages, $output)
	{
		foreach ($packages as $package) {
			if (strpos($package, '/') == false) {
				$package = "leafs/$package";
			}
			$package = str_replace('@', ':', $package);

			$output->writeln("<info>Installing $package...</info>");
			$composer = Core::findComposer();
			$process = Process::fromShellCommandline(
				"$composer require $package",
				null,
				null,
				null,
				null
			);

			$process->run(function ($type, $line) use ($output) {
				$output->write($line);
			});

			if (!$process->isSuccessful()) return 1;

			$output->writeln("<comment>$package installed successfully!</comment>");
		}

		if (count($packages) > 1) {
			$output->writeln('<info>All packages installed</info>');
		}

		return 0;
	}
}
