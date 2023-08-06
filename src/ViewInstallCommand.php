<?php

declare(strict_types=1);

namespace Leaf\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ViewInstallCommand extends Command
{
	protected static $defaultName = 'view:install';

	protected function configure()
	{
		$this
			->setHelp('Run a composer script')
			->setDescription('Run a script in your composer.json')
			->addOption('blade', null, InputOption::VALUE_NONE, 'Install blade')
			->addOption('bare-ui', null, InputOption::VALUE_NONE, 'Install bare ui')
			->addOption('inerita', null, InputOption::VALUE_NONE, 'Setup inerita files')
			->addOption('react', null, InputOption::VALUE_NONE, 'Install react')
			->addOption('tailwind', null, InputOption::VALUE_NONE, 'Install tailwind')
			->addOption('vite', null, InputOption::VALUE_NONE, 'Setup vite files')
			->addOption('vue', null, InputOption::VALUE_NONE, 'Install vue');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		// $composer = Utils\Core::findComposer();

		if ($input->getOption('blade')) {
			return $this->installBlade($output);
		}

		if ($input->getOption('bare-ui')) {
			return $this->installBareUi($output);
		}

		if ($input->getOption('inerita')) {
			return $this->installInertia($output);
		}

		if ($input->getOption('react')) {
			return $this->installReact($output);
		}

		if ($input->getOption('tailwind')) {
			return $this->installTailwind($output);
		}

		if ($input->getOption('vite')) {
			return $this->installVite($output);
		}

		if ($input->getOption('vue')) {
			return $this->installVue($output);
		}

		$output->writeln('<error>You didn\'t select an option to install</error>');
		return 1;
	}

	/**
	 * Install blade
	 */
	protected function installBlade($output)
	{
		$npm = Utils\Core::findNpm();
		$success = Utils\Core::run("$npm install blade-ui-kit", $output);

		if (!$success) return 1;

		return 0;
	}

	/**
	 * Install bare ui
	 */
	protected function installBareUi($output)
	{
		$npm = Utils\Core::findNpm();
		$success = Utils\Core::run("$npm install bare-ui", $output);

		if (!$success) return 1;

		return 0;
	}

	/**
	 * Install inerita
	 */
	protected function installInertia($output)
	{
		$npm = Utils\Core::findNpm();
		$success = Utils\Core::run("$npm install @inertiajs/inertia @inertiajs/inertia-vue3", $output);

		if (!$success) return 1;

		return 0;
	}

	/**
	 * Install react
	 */
	protected function installReact($output)
	{
		$output->writeln("📦  <info>Installing react...</info>\n");

		$directory = getcwd();
		$npm = Utils\Core::findNpm();
		$composer = Utils\Core::findComposer();
		$success = Utils\Core::run("$npm install @leafphp/vite-plugin @vitejs/plugin-react @inertiajs/react react react-dom", $output);

		if (!$success) {
			$output->writeln("❌  <error>Failed to install react</error>");
			return 1;
		};

		$output->writeln("\n✅  <info>React installed successfully</info>");
		$output->writeln("🧱  <info>Setting up Leaf React server bridge...</info>\n");

		$success = Utils\Core::run("$composer require leafs/inertia:dev-main leafs/vite:dev-main", $output);

		if (!$success) {
			$output->writeln("❌  <error>Failed to setup Leaf React server bridge</error>");
			return 1;
		};

		$isMVCApp = $this->isMVCApp();
		$isBladeProject = $this->isBladeProject();
		$ext = $isBladeProject ? 'blade' : 'view';

		if (!$isBladeProject) {
			$output->writeln("\n🎨  <info>Setting up BareUI as main view engine.</info>\n");
			$success = Utils\Core::run("$composer require leafs/bareui", $output);

			if (!$success) {
				$output->writeln("❌  <error>Could not install BareUI, run leaf install bareui</error>\n");
				return 1;
			};
		}

		\Leaf\FS::superCopy(__DIR__ . '/themes/react/root', $directory);

		if ($isMVCApp) {
			$paths = require "$directory/config/paths.php";
			$viewsPath = trim($paths['views'] ?? 'app/views', '/');
			$routesPath = trim($paths['routes'] ?? 'app/routes', '/');

			\Leaf\FS::superCopy(__DIR__ . '/themes/react/routes',  "$directory/$routesPath");
			\Leaf\FS::superCopy(
				(__DIR__ . '/themes/react/views/' . ($isBladeProject ? 'blade' : 'bare-ui')),
				"$directory/$viewsPath"
			);
		} else {
			\Leaf\FS::superCopy(__DIR__ . '/themes/react/routes', $directory);
			\Leaf\FS::superCopy(
				(__DIR__ . '/themes/react/views/' . ($isBladeProject ? 'blade' : 'bare-ui')),
				$directory
			);

			$viteConfig = file_get_contents("$directory/vite.config.js");
			$viteConfig = str_replace(
				"leaf({",
				"leaf({\nhotFile: 'hot',",
				$viteConfig
			);
			file_put_contents("$directory/vite.config.js", $viteConfig);

			$inertiaView = file_get_contents("$directory/_inertia.$ext.php");
			$inertiaView = str_replace(
				'<?php echo vite([\'/js/app.jsx\', "/js/Pages/{$page[\'component\']}.jsx"]); ?>',
				'<?php echo vite([\'js/app.jsx\', "js/Pages/{$page[\'component\']}.jsx"], \'/\'); ?>',
				$inertiaView
			);
			file_put_contents("$directory/_inertia.$ext.php", $inertiaView);
		}

		$package = json_decode(file_get_contents("$directory/package.json"), true);
		$package['scripts']['dev'] = 'vite';
		$package['scripts']['build'] = 'vite build';
		file_put_contents("$directory/package.json", json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

		$output->writeln("\n⚛️   <info>React setup successfully</info>");
		$output->writeln("👉  Get started with the following commands:\n");
		$output->writeln('    leaf view:dev <info>- start dev server</info>');
		$output->writeln('    leaf view:build <info>- build for production</info>');
		$output->writeln('');

		return 0;
	}

	/**
	 * Install tailwind
	 */
	protected function installTailwind($output)
	{
		$directory = getcwd();
		$npm = Utils\Core::findNpm();
		$composer = Utils\Core::findComposer();

		$success = Utils\Core::run("$npm install tailwindcss postcss autoprefixer @vitejs/plugin-vue vite", $output);

		if (!$success) {
			$output->writeln("❌  <error>Failed to install tailwind</error>");
			return 1;
		};

		$output->writeln("\n✅  <info>Tailwind CSS installed successfully</info>");
		$output->writeln("🧱  <info>Setting up Leaf server bridge...</info>\n");

		$success = Utils\Core::run("$composer require leafs/vite:dev-main", $output);

		if (!$success) {
			$output->writeln("❌  <error>Failed to setup Leaf server bridge</error>");
			return 1;
		};

		$isMVCApp = $this->isMVCApp();

		\Leaf\FS::superCopy(__DIR__ . '/themes/tailwind/root', $directory);

		if ($isMVCApp) {
			$paths = require "$directory/config/paths.php";
			$viewsPath = trim($paths['views'] ?? 'app/views', '/');

			\Leaf\FS::superCopy(__DIR__ . '/themes/tailwind/view', "$directory/$viewsPath");
		} else {
			\Leaf\FS::superCopy(__DIR__ . '/themes/tailwind/view', $directory);

			$viteConfig = file_get_contents("$directory/vite.config.js");
			$viteConfig = str_replace(
				"leaf({",
				"leaf({\nhotFile: 'hot',",
				$viteConfig
			);
			file_put_contents("$directory/vite.config.js", $viteConfig);
		}

		$package = json_decode(file_get_contents("$directory/package.json"), true);
		$package['scripts']['dev'] = 'vite';
		$package['scripts']['build'] = 'vite build';
		file_put_contents("$directory/package.json", json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

		$output->writeln("\n⚛️   <info>Tailwind CSS setup successfully</info>");
		$output->writeln("👉  Get started with the following commands:\n");
		$output->writeln('    leaf view:dev <info>- start dev server</info>');
		$output->writeln('    leaf view:build <info>- build for production</info>');
		$output->writeln('');

		return 0;
	}

	/**
	 * Install vite
	 */
	protected function installVite($output)
	{
		$npm = Utils\Core::findNpm();
		$success = Utils\Core::run("$npm install vite", $output);

		if (!$success) return 1;

		return 0;
	}

	/**
	 * Install vue
	 */
	protected function installVue($output)
	{
		$npm = Utils\Core::findNpm();
		$success = Utils\Core::run("$npm install vue@next", $output);

		if (!$success) return 1;

		return 0;
	}

	// ------------------------ utils ------------------------ //
	protected function isMVCApp()
	{
		$directory = getcwd();
		return is_dir("$directory/app/views") && file_exists("$directory/config/paths.php") && is_dir("$directory/public");
	}

	protected function isBladeProject()
	{
		$directory = getcwd();
		$isBladeProject = false;

		if (file_exists("$directory/config/view.php")) {
			$viewConfig = require "$directory/config/view.php";
			$isBladeProject = strpos(strtolower($viewConfig['viewEngine'] ?? $viewConfig['view_engine'] ?? ''), 'blade') !== false;
		} else if (file_exists("$directory/composer.lock")) {
			$composerLock = json_decode(file_get_contents("$directory/composer.lock"), true);
			$packages = $composerLock['packages'] ?? [];
			foreach ($packages as $package) {
				if ($package['name'] === 'leafs/blade') {
					$isBladeProject = true;
					break;
				}
			}
		}

		return $isBladeProject;
	}
}
