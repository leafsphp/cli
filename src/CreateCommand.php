<?php

namespace Leaf\Console;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Process\Process;

class CreateCommand extends Command
{
	/**
	 * Leaf version to use
	 */
	protected $version = "v3";

	/**
	 * Configure the command options.
	 *
	 * @return void
	 */
	protected function configure()
	{
		$this
			->setName('create')
			->setDescription('Create a new Leaf PHP project')
			->addArgument('project-name', InputArgument::REQUIRED)
			->addOption('basic', null, InputOption::VALUE_NONE, 'Create a raw leaf project')
			->addOption('api', null, InputOption::VALUE_NONE, 'Create a new Leaf API project')
			->addOption('mvc', null, InputOption::VALUE_NONE, 'Create a new Leaf MVC project')
			->addOption('skeleton', null, InputOption::VALUE_NONE, 'Create a new leaf project with skeleton')
			->addOption('v3', null, InputOption::VALUE_NONE, 'Use leaf v3')
			->addOption('v2', null, InputOption::VALUE_NONE, 'Use leaf v2')
			->addOption('force', 'f', InputOption::VALUE_NONE, 'Forces install even if the directory already exists');
	}

	protected function scaffold($input, $output)
	{
		$helper = $this->getHelper("question");
		$question = new ChoiceQuestion("Please pick a preset ", ["leaf", "leaf mvc", "leaf api", "skeleton"], "leaf");

		$question->setMultiselect(false);
		$question->setErrorMessage("Please select a valid option");

		return $helper->ask($input, $output, $question);
	}

	protected function scaffoldVersion($input, $output)
	{
		$helper = $this->getHelper("question");
		$question = new ChoiceQuestion("Select a version to use ", ["v3", "v2"], "v3");

		$question->setMultiselect(false);
		$question->setErrorMessage("Please select a valid option");

		return $helper->ask($input, $output, $question);
	}

	protected function leaf($input, $output, $directory)
	{
		if ($this->version === "v3") {
			$output->writeln("<comment>Using leaf v3</comment>\n");
			\Leaf\FS::superCopy(__DIR__ . "/themes/leaf3", $directory);
		} else {
			$output->writeln("<comment>Using leaf v2 LTS</comment>\n");
			\Leaf\FS::superCopy(__DIR__ . "/themes/leaf2", $directory);
		}

		$output->writeln(basename($directory) . " created successfully\n");

		$composer = $this->findComposer();

		$commands = [
			$composer . ' install'
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

		$process = Process::fromShellCommandline(
			implode(' && ', $commands), $directory, null, null, null
		);

		if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
			$process->setTty(true);
		}

		$process->run(function ($type, $line) use ($output) {
			$output->write($line);
		});

		if ($process->isSuccessful()) {
			$output->writeln("\nYou can start with:");
			$output->writeln("\n  <info>cd</info> " . basename($directory));
			$output->writeln("  <info>leaf app:serve</info>");
			$output->writeln("\nHappy gardening!");
		}

		return 0;
	}

	/**
	 * Execute the command.
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input
	 * @param  \Symfony\Component\Console\Output\OutputInterface  $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$composer = $this->findComposer();
		$needsUpdate = \Leaf\Console\Utils\Package::updateAvailable();

		if ($needsUpdate) {
			$output->writeln("<comment>Update found, updating to latest stable version...</comment>");
			$updateProcess = Process::fromShellCommandline("php " . dirname(__DIR__) . "/bin/leaf update");

			$updateProcess->run();

			if ($updateProcess->isSuccessful()) {
				$output->writeln("<info>Leaf CLI updated successfully, building your app...</info>\n");
			}
		}

		$name = $input->getArgument('project-name');

		$directory = $name !== '.' ? getcwd() . '/' . $name : getcwd();

		if (!$input->getOption('force')) {
			$this->verifyApplicationDoesntExist($directory);
		}

		$preset = $this->getPreset($input, $output);
		$this->getVersion($input, $output);

		$output->writeln(
			"Creating \""
			. basename($directory) . "\" in <info>./"
			. basename(dirname($directory)) .
			"</info> using <info>$preset@" . $this->version .  "</info>.\n"
		);

		if ($preset === "leaf") {
			return $this->leaf($input, $output, $directory);
		}

		$installCommand = $composer . " create-project leafs/$preset " . basename($directory);

		if ($this->version === "v3") {
			$installCommand .= " v3.x-dev";
		}

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

		$process = Process::fromShellCommandline(
			implode(' && ', $commands), dirname($directory), null, null, null
		);

		if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
			$process->setTty(true);
		}

		$process->run(function ($type, $line) use ($output) {
			$output->write($line);
		});

		if ($process->isSuccessful()) {
			$output->writeln("\nYou can start with:");
			$output->writeln("\n  <info>cd</info> " . basename($directory));
			$output->writeln("  <info>leaf app:serve</info>");
			$output->writeln("\nHappy gardening!");
		}

		return 0;
	}

	/**
	 * Verify that the application does not already exist.
	 *
	 * @param  string  $directory
	 * @return void
	 */
	protected function verifyApplicationDoesntExist($directory)
	{
		if ((is_dir($directory) || is_file($directory)) && $directory != getcwd()) {
			throw new RuntimeException('Application already exists!');
		}
	}

	/**
	 * Get the version that should be downloaded.
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input
	 * @return string
	 */
	protected function getVersion(InputInterface $input, $output)
	{
		if ($input->getOption("v3")) {
			$this->version = "v3";
			return;
		}

		if ($input->getOption("v2")) {
			$this->version = "v2";
			return;
		}

		$this->version = $this->scaffoldVersion($input, $output);
	}

	/**
	 * Get the preset that should be downloaded.
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input
	 * @return string
	 */
	protected function getPreset(InputInterface $input, $output)
	{
		if ($input->getOption("basic")) {
			return "leaf";
		}
		
		if ($input->getOption("api")) {
			return "api";
		}
		
		if ($input->getOption("mvc")) {
			return "mvc";
		}
		
		if ($input->getOption("skeleton")) {
			return "skeleton";
		}
		
		$preset = $this->scaffold($input, $output);

		if ($preset == "leaf api") {
			return "api";
		}

		if ($preset == "leaf mvc") {
			return "mvc";
		}

		return $preset;
	}

	/**
	 * Get the composer command for the environment.
	 *
	 * @return string
	 */
	protected function findComposer()
	{
		$composerPath = getcwd() . '/composer.phar';

		if (file_exists($composerPath)) {
			return '"' . PHP_BINARY . '" ' . $composerPath;
		}

		return 'composer';
	}
}
