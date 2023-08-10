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

class ServeCommand extends Command
{
	protected static $defaultName = 'serve';

	protected function configure()
	{
		$this
			->setHelp('Start the leaf app server')
			->setDescription('Run your Leaf app')
			->addArgument('filename', InputArgument::OPTIONAL, 'The PHP script to run')
			->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Port to run app on')
			->addOption('watch', 'w', InputOption::VALUE_NONE, 'Run your leaf app with hot reloading [experimental]');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$vendorPath = getcwd() . '/vendor';
		$composerJsonPath = getcwd() . '/composer.json';

		if (!is_dir($vendorPath) && file_exists($composerJsonPath)) {
			$output->writeln('<info>Installing dependencies...</info>');
			$leaf = Utils\Core::findLeaf();
			$installProcess = Process::fromShellCommandline("$leaf install");

			$installProcess->run(function ($type, $line) use ($output) {
				$output->write($line);
			});

			if (!$installProcess->isSuccessful()) {
				$output->writeln('<error>Failed to install dependencies</error>');
			}
		}

		if ($input->getArgument('filename')) {
			$input->setOption('watch', true);
		}

		if ($input->getOption('watch')) {
			$leafWatcherInstalled = Utils\Core::commandExists('leaf-watcher');
			$node = Utils\Core::findNodeJS();
			$npm = Utils\Core::findNpm();
			$watcher = Utils\Core::findWatcher();
			$leaf = Utils\Core::findLeaf();

			if (!$node || !$npm) {
				$output->writeln('<error>Can\'t find NodeJS on the system. Watching will be disabled.</error>');
				return $this->startServer($input, $output);
			}

			if (!$leafWatcherInstalled) {
				$installWatcher = $this->askToInstallWatcher($input, $output);

				if (!$installWatcher && !file_exists($watcher)) {
					$output->writeln('<error>Watcher install cancelled. Watching will be disabled.</error>');
					return $this->startServer($input, $output);
				}

				$output->writeln('<info>Installing leaf watcher...</info>');
				$installProcess = Process::fromShellCommandline("$npm install -g @leafphp/watcher");

				$installProcess->run(function ($type, $line) use ($output) {
					$output->write($line);
				});

				if (!$installProcess->isSuccessful()) {
					$output->writeln('<error>Failed to install leaf watcher. Watching will be disabled.</error>');
					return $this->startServer($input, $output);
				}
			}

			$port = $input->getOption('port') ? (int) $input->getOption('port') : 5500;
			$process = Process::fromShellCommandline("$watcher --exec $leaf serve --port $port", null, null, null, null);

			if ($input->getArgument('filename')) {
				$filename = $input->getArgument('filename');
				$process = Process::fromShellCommandline("$watcher --exec " . PHP_BINARY . " $filename", null, null, null, null);
			}

			return $process->run(function ($type, $line) use ($output) {
				$output->write($line);
			});
		}

		return $this->startServer($input, $output);
	}

	protected function askToInstallWatcher($input, $output)
	{
		$helper = $this->getHelper('question');
		$question = new ConfirmationQuestion('<info>* Leaf Watcher is required to enable monitoring. Install package?</info> ', true);

		return $helper->ask($input, $output, $question);
	}

	protected function startServer(InputInterface $input, OutputInterface $output): int
	{
		$port = $input->getOption('port') ? (int) $input->getOption('port') : 5500;
		$isDockerProject = file_exists(getcwd() . '/docker-compose.yml');
		$process = Process::fromShellCommandline(
			$isDockerProject ? 'docker compose up' : "php -S localhost:$port",
			null,
			null,
			null,
			null
		);

		$output->writeln(
			$isDockerProject ?
				'<info>Serving Leaf application using Docker Compose...</info>' :
				"<info>Starting Leaf development server on <href=http://localhost:$port>http://localhost:$port</></info>"
		);

		return $process->run(function ($type, $line) use ($output, $process) {
			if (is_string($line) && !strpos($line, 'Failed')) {
				$output->write($line);
			} else {
				$output->write("<error>$line</error>");
			}
		});
	}
}
