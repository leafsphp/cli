<?php

namespace Leaf\Console\App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServeCommand extends Command
{
	protected static $defaultName = 'app:serve';

	protected function configure()
	{
		$this
			->setHelp("Start the leaf app server")
			->setDescription("Run your Leaf app")
			->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Port to run app on');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$port = $input->getOption("port") ? (int) $input->getOption("port") : 5500;

		$output->writeln("<info>Leaf development server started: http://localhost:$port</info>");
		$output->writeln("<comment>Happy coding!</comment>");
		$output->write(shell_exec("php -S localhost:{$port}"));
	}
}
