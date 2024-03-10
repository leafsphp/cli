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
            ->addOption('vue', null, InputOption::VALUE_NONE, 'Install vue')
            ->addOption('pm', 'pm', InputOption::VALUE_OPTIONAL, 'Package manager to use', 'npm');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('blade')) {
            return $this->installBlade($output);
        }

        if ($input->getOption('bare-ui')) {
            return $this->installBareUi($output);
        }

        if ($input->getOption('inerita')) {
            return $this->installInertia($input, $output);
        }

        if ($input->getOption('react')) {
            return $this->installReact($input, $output);
        }

        if ($input->getOption('tailwind')) {
            return $this->installTailwind($input, $output);
        }

        if ($input->getOption('vite')) {
            return $this->installVite($input, $output);
        }

        if ($input->getOption('vue')) {
            return $this->installVue($input, $output);
        }

        $output->writeln('<error>You didn\'t select an option to install</error>');

        return 1;
    }

    /**
     * Install blade
     */
    protected function installBlade($output)
    {
        $directory = getcwd();
        $isMVCApp = $this->isMVCApp();
        $composer = Utils\Core::findComposer();

        $success = Utils\Core::run("$composer require leafs/blade", $output);

        if (!$success) {
            $output->writeln("❌  <error>Failed to install blade</error>");
            return 1;
        };

        if ($isMVCApp) {
            $paths = require "$directory/config/paths.php";
            $viewsPath = trim($paths['views'] ?? 'app/views', '/');

            \Leaf\FS::superCopy(__DIR__ . '/themes/blade/theme', "$directory/$viewsPath");
            \Leaf\FS::superCopy(__DIR__ . '/themes/blade/config', "$directory/config");
        } else {
            \Leaf\FS::superCopy(__DIR__ . '/themes/blade/theme', $directory);
        }

        $output->writeln("\n🎉   <info>Blade setup successfully.</info>");
        $output->writeln("👉  Read the blade docs to create your first template.\n");

        return 0;
    }

    /**
     * Install bare ui
     */
    protected function installBareUi($output)
    {
        $directory = getcwd();
        $isMVCApp = $this->isMVCApp();
        $composer = Utils\Core::findComposer();

        $success = Utils\Core::run("$composer require leafs/bareui", $output);

        if (!$success) {
            $output->writeln("❌  <error>Failed to install Bare UI</error>");
            return 1;
        };

        if ($isMVCApp) {
            $paths = require "$directory/config/paths.php";
            $viewsPath = trim($paths['views'] ?? 'app/views', '/');

            \Leaf\FS::superCopy(__DIR__ . '/themes/bareui/theme', "$directory/$viewsPath");
            \Leaf\FS::superCopy(__DIR__ . '/themes/bareui/config', "$directory/config");

            if (file_exists("$directory/$viewsPath/index.blade.php")) {
                unlink("$directory/$viewsPath/index.blade.php");
            }

            $appRoutePartial = "$directory/app/routes/_app.php";

            if (file_exists($appRoutePartial)) {
                $appRoute = file_get_contents($appRoutePartial);
                $appRoute = str_replace(
                    "render('index');",
                    "render('index', ['name' => 'Name Variable']);",
                    $appRoute
                );
                file_put_contents($appRoutePartial, $appRoute);
            }

            $indexFile = "$directory/public/index.php";

            if (file_exists($indexFile)) {
                $index = file_get_contents($indexFile);
                $index = str_replace(
                    "Leaf\View::attach(\Leaf\Blade::class);",
                    "Leaf\View::attach(\Leaf\BareUI::class);",
                    $index
                );
                file_put_contents($indexFile, $index);
            }
        } else {
            \Leaf\FS::superCopy(__DIR__ . '/themes/bareui/theme', $directory);
        }

        $output->writeln("\n🎉   <info>Bare UI setup successfully.</info>");
        $output->writeln("👉  Read the bare ui docs to create your first template.\n");

        return 0;
    }

    /**
     * Install inerita
     */
    protected function installInertia($input, $output)
    {
        $directory = getcwd();
        $npm = Utils\Core::findNpm($input->getOption('pm'));
        $composer = Utils\Core::findComposer();

        $success = Utils\Core::run("$npm add @leafphp/vite-plugin vite", $output);

        if (!$success) {
            $output->writeln("❌  <error>Failed to install vite</error>");
            return 1;
        };

        $output->writeln("\n✅  <info>Vite installed successfully</info>");
        $output->writeln("🧱  <info>Setting up Leaf Inertia server bridge...</info>\n");

        $success = Utils\Core::run("$composer require leafs/vite leafs/inertia", $output);

        if (!$success) {
            $output->writeln("❌  <error>Failed to setup Leaf Inertia server bridge</error>");
            return 1;
        };

        $isMVCApp = $this->isMVCApp();
        $isBladeProject = $this->isBladeProject();

        foreach (glob(__DIR__ . '/themes/inertia/root/{,.}[!.,!..]*', GLOB_MARK | GLOB_BRACE) as $file) {
            if (basename($file) === 'vite.config.js' && file_exists("$directory/vite.config.js")) {
                continue;
            }

            if (is_file($file)) {
                copy($file, rtrim($directory, '/') . '/' . basename($file));
            } else {
                \Leaf\FS::superCopy($file, rtrim($directory, '/') . '/' . basename($file));
            }
        }

        if (!$isMVCApp) {
            $viteConfig = file_get_contents("$directory/vite.config.js");
            $viteConfig = str_replace(
                "leaf({",
                "leaf({\nhotFile: 'hot',",
                $viteConfig
            );
            file_put_contents("$directory/vite.config.js", $viteConfig);
        } else {
            $paths = require "$directory/config/paths.php";
            $viewsPath = trim($paths['views'] ?? 'app/views', '/');

            \Leaf\FS::superCopy(
                (__DIR__ . '/themes/inertia/views/' . ($isBladeProject ? 'blade' : 'bare-ui')),
                "$directory/$viewsPath"
            );
        }

        $package = json_decode(file_get_contents("$directory/package.json"), true);
        $package['type'] = 'module';
        $package['scripts']['dev'] = 'vite';
        $package['scripts']['build'] = 'vite build';
        file_put_contents("$directory/package.json", json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $output->writeln("\n🎉   <info>Inertia setup successfully. Inertia is best used with a framework of sorts.</info>");
        $output->writeln("👉  Get started with the following commands:\n");
        $output->writeln('    leaf view:dev <info>- start dev server</info>');
        $output->writeln("    leaf view:build <info>- build for production</info>");

        return 0;
    }

    /**
     * Install react
     */
    protected function installReact($input, $output)
    {
        $output->writeln("📦  <info>Installing react...</info>\n");

        $directory = getcwd();
        $npm = Utils\Core::findNpm($input->getOption('pm'));
        $composer = Utils\Core::findComposer();
        $success = Utils\Core::run("$npm add @leafphp/vite-plugin @vitejs/plugin-react @inertiajs/react react react-dom", $output);

        if (!$success) {
            $output->writeln("❌  <error>Failed to install react</error>");
            return 1;
        };

        $output->writeln("\n✅  <info>React installed successfully</info>");
        $output->writeln("🧱  <info>Setting up Leaf React server bridge...</info>\n");

        $success = Utils\Core::run("$composer require leafs/inertia leafs/vite", $output);

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
        $package['type'] = 'module';
        $package['scripts']['dev'] = 'vite';
        $package['scripts']['build'] = 'vite build';
        file_put_contents("$directory/package.json", json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $output->writeln("\n⚛️   <info>React setup successfully</info>");
        $output->writeln("👉  Get started with the following commands:\n");
        $output->writeln('    leaf view:dev <info>- start dev server</info>');
        $output->writeln("    leaf view:build <info>- build for production</info>");

        return 0;
    }

    /**
     * Install tailwind
     */
    protected function installTailwind($input, $output)
    {
        $directory = getcwd();
        $npm = Utils\Core::findNpm($input->getOption('pm'));
        $composer = Utils\Core::findComposer();

        $output->writeln("📦  <info>Installing tailwind...</info>\n");

        $success = Utils\Core::run("$npm add tailwindcss postcss autoprefixer @leafphp/vite-plugin vite", $output);

        if (!$success) {
            $output->writeln("❌  <error>Failed to install tailwind</error>");
            return 1;
        };

        $output->writeln("\n✅  <info>Tailwind CSS installed successfully</info>");
        $output->writeln("🧱  <info>Setting up Leaf server bridge...</info>\n");

        $success = Utils\Core::run("$composer require leafs/vite", $output);

        if (!$success) {
            $output->writeln("❌  <error>Failed to setup Leaf server bridge</error>");
            return 1;
        };

        $isMVCApp = $this->isMVCApp();

        foreach (glob(__DIR__ . '/themes/tailwind/root/{,.}[!.,!..]*', GLOB_MARK | GLOB_BRACE) as $file) {
            if (basename($file) === 'vite.config.js' && file_exists("$directory/vite.config.js")) {
                continue;
            }

            if (is_file($file)) {
                copy($file, rtrim($directory, '/') . '/' . basename($file));
            } else {
                \Leaf\FS::superCopy($file, rtrim($directory, '/') . '/' . basename($file));
            }
        }

        if ($isMVCApp) {
            $paths = require "$directory/config/paths.php";
            $viewsPath = trim($paths['views'] ?? 'app/views', '/');

            \Leaf\FS::superCopy(__DIR__ . '/themes/tailwind/view', "$directory/$viewsPath");

            if (file_exists("$directory/app/views/js/app.js")) {
                $jsApp = file_get_contents("$directory/app/views/js/app.js");
                if (strpos($jsApp, "import '../css/app.css';") === false) {
                    \Leaf\FS::prepend("$directory/app/views/js/app.js", "import '../css/app.css';\n");
                }
            }

            if (file_exists("$directory/app/views/js/app.jsx")) {
                $jsApp = file_get_contents("$directory/app/views/js/app.jsx");
                if (strpos($jsApp, "import '../css/app.css';") === false) {
                    \Leaf\FS::prepend("$directory/app/views/js/app.jsx", "import '../css/app.css';\n");
                }
            }
        } else {
            \Leaf\FS::superCopy(__DIR__ . '/themes/tailwind/view', $directory);

            $viteConfig = file_get_contents("$directory/vite.config.js");
            $viteConfig = str_replace(
                ["hotFile: 'hot',", 'hotFile: "hot",'],
                '',
                $viteConfig
            );
            $viteConfig = str_replace(
                "leaf({",
                "leaf({\nhotFile: 'hot',",
                $viteConfig
            );
            file_put_contents("$directory/vite.config.js", $viteConfig);

            if (file_exists("$directory/js/app.js")) {
                $jsApp = file_get_contents("$directory/js/app.js");
                if (strpos($jsApp, "import '../css/app.css';") === false) {
                    \Leaf\FS::prepend("$directory/js/app.js", "import '../css/app.css';\n");
                }
            }

            if (file_exists("$directory/js/app.jsx")) {
                $jsApp = file_get_contents("$directory/js/app.jsx");
                if (strpos($jsApp, "import '../css/app.css';") === false) {
                    \Leaf\FS::prepend("$directory/js/app.jsx", "import '../css/app.css';\n");
                }
            }
        }

        $package = json_decode(file_get_contents("$directory/package.json"), true);
        $package['type'] = 'module';
        $package['scripts']['dev'] = 'vite';
        $package['scripts']['build'] = 'vite build';
        file_put_contents("$directory/package.json", json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $output->writeln("\n🎉  <info>Tailwind CSS setup successfully</info>");
        $output->writeln("👉  Get started with the following commands:\n");
        $output->writeln('    leaf view:dev <info>- start dev server</info>');
        $output->writeln("    leaf view:build <info>- build for production</info>\n");

        return 0;
    }

    /**
     * Install vite
     */
    protected function installVite($input, $output)
    {
        $directory = getcwd();
        $npm = Utils\Core::findNpm($input->getOption('pm'));
        $composer = Utils\Core::findComposer();

        $success = Utils\Core::run("$npm add @leafphp/vite-plugin vite", $output);

        if (!$success) {
            $output->writeln("❌  <error>Failed to install vite</error>");
            return 1;
        };

        $output->writeln("\n✅  <info>Tailwind CSS installed successfully</info>");
        $output->writeln("🧱  <info>Setting up Leaf Vite server bridge...</info>\n");

        $success = Utils\Core::run("$composer require leafs/vite", $output);

        if (!$success) {
            $output->writeln("❌  <error>Failed to setup Leaf Vite server bridge</error>");
            return 1;
        };

        $isMVCApp = $this->isMVCApp();

        if (!file_exists("$directory/vite.config.js")) {
            \Leaf\FS::superCopy(__DIR__ . '/themes/vite', $directory);
        }

        if (!$isMVCApp) {
            $viteConfig = file_get_contents("$directory/vite.config.js");
            $viteConfig = str_replace(
                "leaf({",
                "leaf({\nhotFile: 'hot',",
                $viteConfig
            );
            file_put_contents("$directory/vite.config.js", $viteConfig);
        }

        $package = json_decode(file_get_contents("$directory/package.json"), true);
        $package['type'] = 'module';
        $package['scripts']['dev'] = 'vite';
        $package['scripts']['build'] = 'vite build';
        file_put_contents("$directory/package.json", json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $output->writeln("\n⚛️   <info>Vite setup successfully</info>");
        $output->writeln("👉  Get started with the following commands:\n");
        $output->writeln('    leaf view:dev <info>- start dev server</info>');
        $output->writeln("    leaf view:build <info>- build for production</info>\n");

        return 0;
    }

    /**
     * Install vue
     */
    protected function installVue($input, $output)
    {
        $output->writeln("📦  <info>Installing Vue...</info>\n");

        $directory = getcwd();
        $npm = Utils\Core::findNpm($input->getOption('pm'));
        $composer = Utils\Core::findComposer();
        $success = Utils\Core::run("$npm add @leafphp/vite-plugin @vitejs/plugin-vue @inertiajs/vue3 vue", $output);

        if (!$success) {
            $output->writeln("❌  <error>Failed to install Vue</error>");
            return 1;
        };

        $output->writeln("\n✅  <info>Vue installed successfully</info>");
        $output->writeln("🧱  <info>Setting up Leaf Vue server bridge...</info>\n");

        $success = Utils\Core::run("$composer require leafs/inertia leafs/vite", $output);

        if (!$success) {
            $output->writeln("❌  <error>Failed to setup Leaf Vue server bridge</error>");
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

        \Leaf\FS::superCopy(__DIR__ . '/themes/vue/root', $directory);

        if ($isMVCApp) {
            $paths = require "$directory/config/paths.php";
            $viewsPath = trim($paths['views'] ?? 'app/views', '/');
            $routesPath = trim($paths['routes'] ?? 'app/routes', '/');

            \Leaf\FS::superCopy(__DIR__ . '/themes/vue/routes',  "$directory/$routesPath");
            \Leaf\FS::superCopy(
                (__DIR__ . '/themes/vue/views/' . ($isBladeProject ? 'blade' : 'bare-ui')),
                "$directory/$viewsPath"
            );
        } else {
            \Leaf\FS::superCopy(__DIR__ . '/themes/vue/routes', $directory);
            \Leaf\FS::superCopy(
                (__DIR__ . '/themes/vue/views/' . ($isBladeProject ? 'blade' : 'bare-ui')),
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
                '<?php echo vite([\'/js/app.js\', "/js/Pages/{$page[\'component\']}.vue"]); ?>',
                '<?php echo vite([\'js/app.js\', "js/Pages/{$page[\'component\']}.vue"], \'/\'); ?>',
                $inertiaView
            );
            file_put_contents("$directory/_inertia.$ext.php", $inertiaView);
        }

        $package = json_decode(file_get_contents("$directory/package.json"), true);
        $package['type'] = 'module';
        $package['scripts']['dev'] = 'vite';
        $package['scripts']['build'] = 'vite build';
        file_put_contents("$directory/package.json", json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $output->writeln("\n⚛️   <info>Vue setup successfully</info>");
        $output->writeln("👉  Get started with the following commands:\n");
        $output->writeln('    leaf view:dev <info>- start dev server</info>');
        $output->writeln("    leaf view:build <info>- build for production</info>\n");

        return 0;
    }

    // ------------------------ utils ------------------------ //
    protected function isMVCApp()
    {
        $directory = getcwd();
        return is_dir("$directory/app/views") && file_exists("$directory/config/paths.php") && is_dir("$directory/public");
    }

    protected function isBladeProject($directory = null)
    {
        $isBladeProject = false;
        $directory = $directory ?? getcwd();

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
