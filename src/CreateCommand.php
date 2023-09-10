<?php

declare(strict_types=1);

namespace Leaf\Console;

use RuntimeException;
use Leaf\FS;
use Leaf\Console\Utils\Package;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;

class CreateCommand extends Command
{
    /**
     * Available modules
     */
    protected $modules = [
        'Database' => 'leafs/db',
        'Authentication' => 'leafs/auth',
        'Session support' => 'leafs/session',
        'Cookie support' => 'leafs/cookie',
        'CSRF protection' => 'leafs/csrf',
        'CORS support' => 'leafs/cors',
        'Leaf Date' => 'leafs/date',
        'Leaf Fetch' => 'leafs/fetch'
    ];

    /**
     * Configure the command options.
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
        $leaf = Utils\Core::findLeaf();
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
                $output->writeln("<error>❌ Leaf CLI update failed, please try again later</error>\n");
                $output->writeln("⚙️  Creating app with current version...\n");
            }
        }

        $name = $this->getAppName($input, $output);
        $directory = $name !== '.' ? getcwd() . '/' . $name : getcwd();

        if (!$input->getOption('force')) {
            $this->verifyApplicationDoesntExist($directory);
        }

        $preset = $this->getAppPreset($input, $output);

        $output->writeln(
            "\n⚙️  Creating \""
                . basename($directory) . "\" in <info>./"
                . basename(dirname($directory)) .
                "</info> using <info>$preset@v3</info>."
        );

        if ($preset === 'leaf') {
            return $this->buildLeafApp($input, $output, $directory);
        }

        $commands = [
            "$composer create-project leafs/$preset " . basename($directory),
            'cd ' . basename($directory),
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

        if ($input->getOption('custom')) {
            if ($preset === 'mvc') {
                $viewEngine = $this->viewEngineSelection($input, $output);

                if ($viewEngine === 'react/vue') {
                    $frontendFramework = $this->frontendFrameworkSelection($input, $output);
                    $commands[] = "$leaf view:install --$frontendFramework";
                } else {
                    if ($viewEngine === 'bare-ui') {
                        $commands[] = "$leaf view:install --bare-ui";
                    }

                    $installVite = $this->installVite($input, $output);

                    if (!$installVite) {
                        $commands[] = 'rm -rf vite.config.js package.json package-lock.json';
                    } else {
                        $commands[] = "$leaf view:install --vite";
                    }
                }
            }
        }

        $testing = $this->getAppTestPreset($input, $output);

        if ($testing) {
            $commands[] = "$composer require leafs/alchemy --dev";
            $commands[] = "./vendor/bin/alchemy setup --$testing";
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

        echo "\n";

        $process->run(function ($type, $line) use ($output) {
            $output->write($line);
        });

        if ($process->isSuccessful()) {
            if ($this->getAppDockPreset($input, $output)) {
                FS::superCopy(__DIR__ . '/themes/docker', $directory);
                $output->write("\n🚀  Docker environment scaffolded successfully");
            }

            $output->writeln("\n🚀  Successfully created project <info>" . basename($directory) . "</info>");
            $output->writeln("👉  Get started with the following commands:");
            $output->writeln("\n    <info>cd</info> " . basename($directory));
            $output->writeln("    <info>leaf serve</info>");

            if ($testing) {
                $output->writeln("\n👉  You can run tests with:");
                $output->writeln("\n    <info>leaf test</info>");
            }

            $output->writeln("\n🍁  Happy gardening!");
        }

        return 0;
    }

    protected function buildLeafApp($input, $output, $directory): int
    {
        FS::superCopy(__DIR__ . '/themes/leaf3', $directory);

        $composer = Utils\Core::findComposer();
        $output->writeln('⚡️ ' . basename($directory) . " scaffolded successfully");

        $commands = [
            "$composer install",
        ];

        if ($input->getOption('custom')) {
            $modules = array_map(function ($module) {
                return $this->modules[$module];
            }, $this->moduleSelection($input, $output));

            $commands[] = "$composer require " . implode(' ', $modules);
        }

        $testing = $this->getAppTestPreset($input, $output);

        if ($testing) {
            $commands[] = "$composer require leafs/alchemy --dev";
            $commands[] = "./vendor/bin/alchemy setup --$testing";
        }

        if ($this->getAppDockPreset($input, $output)) {
            FS::superCopy(__DIR__ . '/themes/docker', $directory);
            $output->write("\n🚀  Docker environment scaffolded successfully");
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

        echo "\n";

        $process->run(function ($type, $line) use ($output) {
            $output->write($line);
        });

        if ($process->isSuccessful()) {
            $output->writeln("\n🚀  Successfully created project <info>" . basename($directory) . "</info>");
            $output->writeln("👉  Get started with the following commands:");
            $output->writeln("\n    <info>cd</info> " . basename($directory));
            $output->writeln("    <info>leaf serve</info>");

            if ($testing) {
                $output->writeln("\n👉  You can run tests with:");
                $output->writeln("\n    <info>leaf test</info>");
            }

            $output->writeln("\n🍁  Happy gardening!");
        }

        return 0;
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
        $question = new ChoiceQuestion('<info>? What kind of app do you want to create?</info> <comment>[leaf]</comment>', ['leaf', 'leaf mvc', 'leaf api'], 'leaf');

        $question->setMultiselect(false);
        $question->setErrorMessage('❌ Invalid option selected!');

        $preset = $helper->ask($input, $output, $question);

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

        if ($input->getOption('custom')) {
            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion("\n<info>? What testing framework would you like to use?</info> <comment>[none]</comment>", ['none', 'pest', 'phpunit'], 'none');

            $question->setMultiselect(false);
            $question->setErrorMessage('Invalid option selected!');

            $testing = $helper->ask($input, $output, $question);

            if ($testing === 'none') {
                $output->writeln("\n💪  No tests, hope you know what you're doing");
                return false;
            }

            $output->writeln("\n🧪  Using $testing");

            return $testing;
        }

        return false;
    }

    protected function getAppDockPreset($input, $output)
    {
        if ($input->getOption('docker')) {
            return true;
        }

        if ($input->getOption('custom')) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion("\n<info>? Would you like to scaffold a docker environment?</info> (No)", false);

            return $helper->ask($input, $output, $question);
        }

        return false;
    }

    protected function moduleSelection($input, $output)
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion("\n<info>? What modules would you like to add?</info> <comment>[none]</comment> eg: 1,2,7", array_merge(['None'], array_keys($this->modules)), '0');

        $question->setMultiselect(true);
        $question->setErrorMessage('Invalid option selected!');

        $modules = $helper->ask($input, $output, $question);

        if (in_array('None', $modules)) {
            $modules = [];
        }

        $output->writeln(count($modules) > 0 ? "\n🛠️  Selected modules will be installed" : "\n🥲  No modules selected");

        return $modules;
    }

    protected function viewEngineSelection($input, $output)
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion("\n<info>? What view engine would you like to use?</info> <comment>[Blade]</comment>", ['Blade', 'Bare UI', 'React/Vue'], 'Blade');

        $question->setMultiselect(false);
        $question->setErrorMessage('Invalid option selected!');

        $viewEngine = $helper->ask($input, $output, $question);

        return str_replace(' ', '-', strtolower($viewEngine));
    }

    protected function frontendFrameworkSelection($input, $output)
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion("\n<info>? What frontend framework would you like to use?</info>", ['React', 'Vue']);

        $question->setMultiselect(false);
        $question->setErrorMessage('Invalid option selected!');

        $frontendFramework = $helper->ask($input, $output, $question);

        return strtolower($frontendFramework);
    }

    protected function installVite($input, $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion("\n<info>? Do you want to add Vite to bundle your assets?</info> <comment>[Yes]</comment>", true);

        return $helper->ask($input, $output, $question);
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
