<?php

declare(strict_types=1);

namespace Leaf\Console;

use Leaf\Console\Utils\Package;
use Leaf\FS;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;

class CreateCommand extends Command
{
	/**
	 * Add testing to app?
	 */
	protected $testing = false;

	/**
	 * Configure the command options.
	 *
	 * @return void
	 */
	protected function configure()
	{
		$this
			->setName('create')
			->setAliases(['init', 'new'])
			->setDescription('Create a new Leaf PHP project')
			->addArgument('project-name', InputArgument::OPTIONAL, 'The name of the project')
			->addOption('custom', 'c', InputOption::VALUE_NONE, 'Add custom options to your project')
			->addOption('basic', null, InputOption::VALUE_NONE, 'Create a raw leaf project')
			->addOption('api', null, InputOption::VALUE_NONE, 'Create a new Leaf API project')
			->addOption('mvc', null, InputOption::VALUE_NONE, 'Create a new Leaf MVC project')
			->addOption('docker', null, InputOption::VALUE_NONE, 'Scaffold a docker environment')
			->addOption('phpunit', null, InputOption::VALUE_NONE, 'Add testing with phpunit')
			->addOption('pestphp', null, InputOption::VALUE_NONE, 'Add testing with pest')
			->addOption('pest', null, InputOption::VALUE_NONE, 'Add testing with pest')
			->addOption('no-tests', 'nt', InputOption::VALUE_NONE, 'Create app without tests')
			->addOption('force', 'f', InputOption::VALUE_NONE, 'Forces install even if the directory already exists');
	}

	protected function getAppName($input, $output): string
	{
		$name = $input->getArgument('project-name');

		if (!$name) {
			$helper = $this->getHelper('question');
			$question = new Question('<info>? What is the name of your project?</info> (leaf-app) ', 'leaf-app');

			$name = $helper->ask($input, $output, $question);
		}

		return $name;
	}

	/**
	 * Get the preset that should be downloaded.
	 *
	 * @param InputInterface $input
	 * @param $output
	 * @return string
	 */
	protected function getAppPreset(InputInterface $input, $output): string
	{
		if ($input->getOption('basic')) {
			return 'leaf';
		}

		if ($input->getOption('api')) {
			return 'api';
		}

		if ($input->getOption('mvc')) {
			return 'mvc';
		}

		$helper = $this->getHelper('question');
		$question = new ChoiceQuestion('<info>? What preset would you like to use?</info> (0) ', ['leaf', 'leaf mvc', 'leaf api'], 'leaf');

		$question->setMultiselect(false);
		$question->setErrorMessage('Invalid option selected!');

		$preset = $helper->ask($input, $output, $question);
		$output->writeln("\n<comment> - </comment>Using preset $preset\n");

		return str_replace('leaf ', '', $preset);
	}

	protected function getAppTestPreset($input, $output)
	{
		if ($input->getOption('pest') || $input->getOption('pestphp')) {
			return 'pest';
		}

		if ($input->getOption('phpunit')) {
			return 'phpunit';
		}

		return false;
	}

	protected function buildLeafApp($input, $output, $directory): int
	{
		FS::superCopy(__DIR__ . '/themes/leaf3', $directory);
		$output->writeln('<comment> - </comment>' . basename($directory) . " scaffolded successfully\n");

		$composer = Utils\Core::findComposer();

		$commands = [
			$composer . ' install',
		];

		if ($this->testing) {
			$commands[] = "composer require leafs/alchemy --dev";
			$commands[] = "./vendor/bin/alchemy setup --{$this->testing}";
		}

		if ($input->getOption('docker')) {
			FS::superCopy(__DIR__ . '/themes/docker', $directory);
			$output->writeln("<comment> - </comment>Docker environment scaffolded successfully\n");
		}

		if ($input->getOption('no-ansi')) {
			$commands = array_map(function ($value) {
				return $value . ' --no-ansi';
			}, $commands);
		}

		if ($input->getOption('quiet')) {
			$commands = array_map(function ($value) {
				return $value . ' --quiet';
			}, $commands);
		}

		$process = Process::fromShellCommandline(
			implode(' && ', $commands),
			$directory,
			null,
			null,
			null
		);

		if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
			$process->setTty(true);
		}

		$process->run(function ($type, $line) use ($output) {
			$output->write($line);
		});

		if ($process->isSuccessful()) {
			$output->writeln("\n🚀  Successfully created project <info>" . basename($directory) . "</info>");
			$output->writeln("👉  Get started with the following commands:");
			$output->writeln("\n    <info>cd</info> " . basename($directory));
			$output->writeln("    <info>leaf serve</info>");

			if ($this->testing) {
				$output->writeln("\n👉  You can run tests with:");
				$output->writeln("\n    <info>leaf test</info>");
			}

			$output->writeln("\n🍁  Happy gardening!");
		}

		return 0;
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
		$composer = Utils\Core::findComposer();
		$needsUpdate = Package::updateAvailable();

		if ($needsUpdate) {
			$output->writeln('<comment>Update found, updating to the latest stable version...</comment>');
			$updateProcess = Process::fromShellCommandline('php ' . dirname(__DIR__) . '/bin/leaf update');

			$updateProcess->run();

			if ($updateProcess->isSuccessful()) {
				$output->writeln("<info>Leaf CLI updated successfully, building your app...</info>\n");

				$createProcess = Process::fromShellCommandline('php ' . implode(' ', $_SERVER['argv']));
				$createProcess->run(function ($type, $line) use ($output) {
					$output->write($line);
				});

				return 0;
			} else {
				$output->writeln("<error>Leaf CLI update failed, please try again later</error>\n");
				$output->writeln("<comment> - </comment>Creating app with current version...\n");
			}
		}

		$name = $this->getAppName($input, $output);
		$directory = $name !== '.' ? getcwd() . '/' . $name : getcwd();

		if (!$input->getOption('force')) {
			$this->verifyApplicationDoesntExist($directory);
		}

		$preset = $this->getAppPreset($input, $output);
		$this->testing = $this->getAppTestPreset($input, $output);

		$output->writeln(
			"\n<comment> - </comment>Creating \""
				. basename($directory) . "\" in <info>./"
				. basename(dirname($directory)) .
				"</info> using <info>$preset@v3</info>."
		);

		if ($preset === 'leaf') {
			return $this->buildLeafApp($input, $output, $directory);
		}

		$installCommand = "$composer create-project leafs/$preset " . basename($directory);

		$commands = [
			$installCommand,
		];

		if ($input->getOption('no-ansi')) {
			$commands = array_map(function ($value) {
				return $value . ' --no-ansi';
			}, $commands);
		}

		if ($input->getOption('quiet')) {
			$commands = array_map(function ($value) {
				return $value . ' --quiet';
			}, $commands);
		}

		if ($this->testing) {
			$commands[] = "cd " . basename($directory);
			$commands[] = "composer require leafs/alchemy --dev";
			$commands[] = "./vendor/bin/alchemy setup --{$this->testing}";
		}

		$process = Process::fromShellCommandline(
			implode(' && ', $commands),
			dirname($directory),
			null,
			null,
			null
		);

		if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
			$process->setTty(true);
		}

		$process->run(function ($type, $line) use ($output) {
			$output->write($line);
		});

		if ($process->isSuccessful()) {
			if ($input->getOption('docker')) {
				FS::superCopy(__DIR__ . '/themes/docker', $directory);
				$output->write("\n🚀  Docker environment scaffolded successfully");
			}

			$output->writeln("\n🚀  Successfully created project <info>" . basename($directory) . "</info>");
			$output->writeln("👉  Get started with the following commands:");
			$output->writeln("\n    <info>cd</info> " . basename($directory));
			$output->writeln("    <info>leaf serve</info>");

			if ($this->testing) {
				$output->writeln("\n👉  You can run tests with:");
				$output->writeln("\n    <info>leaf test</info>");
			}

			$output->writeln("\n🍁  Happy gardening!");
		}

		return 0;
	}

	/**
	 * Verify that the application does not already exist.
	 *
	 * @param string $directory
	 * @return void
	 */
	protected function verifyApplicationDoesntExist(string $directory)
	{
		if ((is_dir($directory) || is_file($directory)) && $directory != getcwd()) {
			throw new RuntimeException('Application already exists!');
		}
	}
}
