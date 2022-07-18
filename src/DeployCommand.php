<?php

declare(strict_types=1);

namespace Leaf\Console;

use Leaf\Console\Utils\Package;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class DeployCommand extends Command
{
	/**
	 * Configure the command options.
	 *
	 * @return void
	 */
	protected function configure()
	{
		$this
			->setName('deploy')
			->setAliases(['publish'])
			->setDescription('Deploy your leaf project')
			->addOption('to', null, InputOption::VALUE_OPTIONAL, 'Provider to deploy to eg: git, heroku', 'git')
			->addOption('project', null, InputOption::VALUE_OPTIONAL, 'Project name for provider', '')
			->addOption('branch', null, InputOption::VALUE_OPTIONAL, 'Branch to deploy', 'main')
			->addOption('switch-to-main', null, InputOption::VALUE_NONE, 'Switch default branch to main')
			->addOption('force', 'f', InputOption::VALUE_NONE, 'Forces deploy');
	}

	/**
	 * Execute the command.
	 *
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$needsUpdate = Package::updateAvailable();

		if ($needsUpdate) {
			$output->writeln('<comment>Update found, updating to the latest stable version...</comment>');
			$updateProcess = Process::fromShellCommandline('php ' . dirname(__DIR__) . '/bin/leaf update');

			$updateProcess->run();

			if ($updateProcess->isSuccessful()) {
				$output->writeln("<info>Leaf CLI updated successfully, building your app...</info>\n");
			}
		}

		$vendors = [
			'git' => ['git push'],
			'heroku' => [
				"heroku git:remote -a {$input->getOption('project')}",
				$input->getOption('branch') !== 'main' ? "git checkout {$input->getOption('branch')}" : 'echo ""',
				$input->getOption('switch-to-main') ? 'git checkout -b main && git branch -D master && git push heroku main' : 'echo ""',
				"git push heroku {$input->getOption('branch')}"
			],
		];

		$output->writeln('<comment>Deploying ' . basename(getcwd()) . '...</comment>');

		if ($input->getOption('to') === 'heroku' && !Utils\Core::commandExists('heroku')) {
			$output->writeln('<info>Heroku not installed, attempting to install heroku CLI...</info>');
			$installHerokuProcess = Process::fromShellCommandline(
				'brew tap heroku/brew && brew install heroku',
				null,
				null,
				null,
				null
			);

			$installHerokuProcess->run(function ($type, $line) use ($output) {
				$output->write($line);
			});

			if (!$installHerokuProcess->isSuccessful()) {
				$output->writeln('<error>Could not install heroku CLI. Manually install it and try again.</error>');
				return 1;
			}
		}

		$process = Process::fromShellCommandline(
			str_replace('&& &&', '&&', implode(' && ', $vendors[$input->getOption('to')])),
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
