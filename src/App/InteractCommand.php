<?php

namespace Leaf\Console\App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psy\Shell;

class InteractCommand extends Command {
    protected static $defaultName = "app:interact";

    protected function configure()  {
        $this
            ->setDescription("Interact with your application")
            ->setHelp("Interact with your application");
    }


    protected function execute(InputInterface $input, OutputInterface $output) {
        if (file_exists("vendor/autoload.php")) require "vendor/autoload.php";
        if (file_exists("Config/bootstrap.php")) require "Config/bootstrap.php";
        if (file_exists("index.php") && !file_exists("leaf")) require "index.php";
        
        $output->writeln("<info>Leaf interactive shell activated</info>");
        $shell = new Shell();
        $output->write($shell->run());
    }
}