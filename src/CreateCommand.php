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
        'Leaf Fetch' => 'leafs/fetch',
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
            ->addOption('basic', null, InputOption::VALUE_NONE, 'Create a raw leaf project')
            ->addOption('api', null, InputOption::VALUE_NONE, 'Create a new Leaf API project')
            ->addOption('mvc', null, InputOption::VALUE_NONE, 'Create a new Leaf MVC project')
            ->addOption('docker', null, InputOption::VALUE_NONE, 'Scaffold a docker environment')
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
        $output->writeln('<comment>v4.0 has been released. Updating to v4 now ...</comment>');

        $updateProcess = Process::fromShellCommandline('composer global require leafs/cli:v4.0 -W');
        $updateProcess->run();

        if ($updateProcess->isSuccessful()) {
            $output->writeln("<info>Leaf CLI updated successfully, run leaf create to build your app</info>\n");
        } else {
            $output->writeln("<error>❌ Leaf CLI update failed, please manually update using composer global require leafs/cli:v4.0 -W</error>\n");
            return 1;
        }

        return 0;
    }

    protected function buildLeafApp($input, $output, $directory): int
    {
        FS::superCopy(__DIR__ . '/themes/leaf3', $directory);

        $composer = Utils\Core::findComposer();
        $output->writeln('⚡️ ' . basename($directory) . ' scaffolded successfully');

        $commands = [
            "$composer install",
        ];

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
            implode(' && ', $commands) . ' --ansi',
            $directory,
            null,
            null,
            null
        );

        $process->run(function ($type, $line) use ($output) {
            $output->write($line);
        });

        if ($process->isSuccessful()) {
            $output->writeln("\n🚀  Successfully created project <info>" . basename($directory) . '</info>');
            $output->writeln('👉  Get started with the following commands:');
            $output->writeln("\n    <info>cd</info> " . basename($directory));
            $output->writeln('    <info>leaf serve</info>');

            $output->writeln("\n🍁  Happy gardening!");
        }

        return 0;
    }

    protected function buildAPIApp($input, $output, $directory): bool
    {
        /// Will refactor when we switch to PHP 8.2

        function deleteDir($dir)
        {
            if (!file_exists($dir)) {
                return true;
            }

            if (!is_dir($dir)) {
                return unlink($dir);
            }

            foreach (scandir($dir) as $item) {
                if ($item == '.' || $item == '..') {
                    continue;
                }

                if (!deleteDir($dir . DIRECTORY_SEPARATOR . $item)) {
                    return false;
                }
            }

            return rmdir($dir);
        }

        if (file_exists($directory . '/vite.config.js')) {
            FS::deleteFile($directory . '/vite.config.js');
        }

        if (file_exists($directory . '/package.json')) {
            FS::deleteFile($directory . '/package.json');
        }

        if (is_dir($directory . '/app/views')) {
            deleteDir($directory . '/app/views');

            FS::deleteFolder($directory . '/app/views');
            FS::createFolder($directory . '/app/views');
            FS::createFile($directory . '/app/views/.gitkeep');

            deleteDir($directory . '/app/routes');
            FS::superCopy(__DIR__ . '/themes/api/routes', $directory . '/app/routes');

            FS::deleteFile($directory . '/public/index.php');
            FS::superCopy(__DIR__ . '/themes/api/index.php', $directory . '/public/index.php');
        }

        return true;
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
        $question = new ChoiceQuestion('<info>? What kind of app do you want to create?</info> <comment>[leaf]</comment>', ['leaf', 'leaf mvc', 'leaf mvc for apis'], 'leaf');

        $question->setMultiselect(false);
        $question->setErrorMessage('❌ Invalid option selected!');

        $preset = $helper->ask($input, $output, $question);

        if ($preset === 'leaf mvc for apis') {
            return 'api';
        }

        if ($preset === 'leaf mvc') {
            return 'mvc';
        }

        return 'leaf';
    }

    protected function getAppDockPreset($input, $output)
    {
        if ($input->getOption('docker')) {
            return true;
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
