<?php

declare(strict_types=1);

namespace Leaf\Console;

use Leaf\Sprout\Command;

class ViewInstallCommand extends Command
{
    protected $signature = 'view:install
        {--blade? : Install blade}
        {--bare-ui? : Install bare ui}
        {--react? : Install react}
        {--svelte? : Install svelte}
        {--tailwind? : Install tailwind}
        {--vite? : Setup vite files}
        {--vue? : Install vue}
        {--pm=npm : Package manager to use}';

    protected $description = 'Set up a new view engine';

    protected function handle(): int
    {
        if ($this->option('blade')) {
            return $this->installBlade();
        }

        if ($this->option('bare-ui')) {
            return $this->installBareUi();
        }

        if ($this->option('react')) {
            return $this->installReact();
        }

        if ($this->option('svelte')) {
            return $this->installSvelte();
        }

        if ($this->option('tailwind')) {
            return $this->installTailwind();
        }

        if ($this->option('vite')) {
            return $this->installVite();
        }

        if ($this->option('vue')) {
            return $this->installVue();
        }

        // $selections = sprout()->prompt([
        //     [
        //         'type' => 'select',
        //         'name' => 'type',
        //         'message' => 'What do you want to install?',
        //         'default' => 0,
        //         'choices' => [
        //             ['title' => 'React', 'value' => 'react'],
        //             ['title' => 'Svelte', 'value' => 'svelte'],
        //             ['title' => 'Vue', 'value' => 'vue'],
        //             ['title' => 'Tailwind CSS', 'value' => 'tailwind'],
        //             ['title' => 'Vite', 'value' => 'vite'],
        //             ['title' => 'Blade', 'value' => 'blade'],
        //             ['title' => 'Bare UI', 'value' => 'bare-ui'],
        //         ],
        //     ]
        // ]);

        // $this->projectType ??= $selections['type'];

        return 1;
    }

    /**
     * Install blade
     */
    protected function installBlade()
    {
        $directory = getcwd();
        $isMVCApp = $this->isMVCApp();

        if ($isMVCApp) {
            $this->writeln('❌  <error>Blade is already installed in this project</error>');

            return 1;
        }

        $this->writeln("📦  <info>Installing blade...</info>\n");

        if (
            !sprout()->composer()->install('leafs/blade')->isSuccessful() || !\Leaf\FS\Directory::copy(__DIR__ . '/themes/blade', $directory, [
                'recursive' => true,
            ])
        ) {
            $this->writeln('❌  <error>Failed to install blade</error>');

            return 1;
        }

        $this->writeln("\n🎉   <info>Blade setup successfully. Include the setup/_blade.php file to get started</info>");
        $this->writeln("👉  Read the blade docs to create your first template.\n");

        return 0;
    }

    /**
     * Install bare ui
     */
    protected function installBareUi()
    {
        $directory = getcwd();
        $isMVCApp = $this->isMVCApp();

        if ($isMVCApp) {
            $this->writeln('❌  <error>Blade detected, skipping...</error>');

            return 1;
        }

        $this->writeln("📦  <info>Installing bare-ui...</info>\n");

        if (
            !sprout()->composer()->install('leafs/bareui')->isSuccessful() || !\Leaf\FS\Directory::copy(__DIR__ . '/themes/bareui', $directory, [
                'recursive' => true,
            ])
        ) {
            $this->writeln('❌  <error>Failed to install bareui</error>');

            return 1;
        }

        $this->writeln("\n🎉   <info>Bare UI setup successfully. Include the setup/_bareui.php file to get started</info>");
        $this->writeln("👉  Read the blade docs to create your first template.\n");

        return 0;
    }

    /**
     * Install react
     */
    protected function installReact()
    {
        $directory = getcwd();

        if ($this->isMVCApp()) {
            return (int) sprout()->run("php $directory/leaf view:install --react --ansi");
        }

        $this->writeln("📦  <info>Installing react...</info>\n");

        if (!sprout()->npm($this->option('pm'))->install('@leafphp/vite-plugin @vitejs/plugin-react@^5.0 @inertiajs/react@^2.0 react@18 react-dom@18')->isSuccessful()) {
            $this->writeln('❌  <error>Failed to install react</error>');

            return 1;
        }

        $this->writeln("\n✅  <info>React installed successfully</info>");
        $this->writeln("🧱  <info>Setting up Leaf React server bridge...</info>\n");

        if (!sprout()->composer()->install('leafs/inertia leafs/blade leafs/vite')->isSuccessful()) {
            $this->writeln('❌  <error>Failed to setup Leaf React server bridge</error>');

            return 1;
        }

        \Leaf\FS\Directory::copy(__DIR__ . '/themes/react', $directory, [
            'recursive' => true,
        ]);

        $package = json_decode(file_get_contents("$directory/package.json"), true);
        $package['type'] = 'module';
        $package['scripts']['dev'] = 'vite';
        $package['scripts']['build'] = 'vite build';
        file_put_contents("$directory/package.json", json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        if (\Leaf\FS\File::exists("$directory/vite.config.js")) {
            \Leaf\FS\File::write("$directory/vite.config.js", function ($content) {
                if (strpos($content, '@vitejs/plugin-react') === false) {
                    $content = str_replace(
                        ["import leaf from '@leafphp/vite-plugin';", 'import leaf from "@leafphp/vite-plugin";'],
                        "import leaf from '@leafphp/vite-plugin';\nimport react from '@vitejs/plugin-react';",
                        $content
                    );
                }

                if (strpos($content, 'react()') === false) {
                    $content = str_replace('leaf({', "react(),\nleaf({", $content);
                }

                return $content;
            });
        }

        $this->writeln("\n💙   <info>React setup successfully</info>");
        $this->writeln("👉  Get started with the following commands:\n");
        $this->writeln('    leaf serve <info>- start dev server</info>');
        $this->writeln("    leaf view:build <info>- build for production</info>\n");

        return 0;
    }

    /**
     * Install svelte
     */
    protected function installSvelte()
    {
        $directory = getcwd();

        if ($this->isMVCApp()) {
            return (int) sprout()->run("php $directory/leaf view:install --svelte --ansi");
        }

        $this->writeln("📦  <info>Installing svelte...</info>\n");

        if (!sprout()->npm($this->option('pm'))->install('@leafphp/vite-plugin svelte @sveltejs/vite-plugin-svelte@^6.0 @inertiajs/svelte@^2.0')->isSuccessful()) {
            $this->writeln('❌  <error>Failed to install svelte</error>');

            return 1;
        }

        $this->writeln("\n✅  <info>Svelte installed successfully</info>");
        $this->writeln("🧱  <info>Setting up Leaf Svelte server bridge...</info>\n");

        if (!sprout()->composer()->install('leafs/inertia leafs/blade leafs/vite')->isSuccessful()) {
            $this->writeln('❌  <error>Failed to setup Leaf svelte server bridge</error>');

            return 1;
        }

        \Leaf\FS\Directory::copy(__DIR__ . '/themes/svelte', $directory, [
            'recursive' => true,
        ]);

        $package = json_decode(file_get_contents("$directory/package.json"), true);
        $package['type'] = 'module';
        $package['scripts']['dev'] = 'vite';
        $package['scripts']['build'] = 'vite build';
        file_put_contents("$directory/package.json", json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        if (\Leaf\FS\File::exists("$directory/vite.config.js")) {
            \Leaf\FS\File::write("$directory/vite.config.js", function ($content) {
                if (strpos($content, '@sveltejs/vite-plugin-svelte') === false) {
                    $content = str_replace(
                        ["import leaf from '@leafphp/vite-plugin';", 'import leaf from "@leafphp/vite-plugin";'],
                        "import leaf from '@leafphp/vite-plugin';\nimport { svelte } from '@sveltejs/vite-plugin-svelte'",
                        $content
                    );
                }

                if (strpos($content, 'svelte()') === false) {
                    $content = str_replace('leaf({', "svelte(),\nleaf({", $content);
                }

                return $content;
            });
        }

        $this->writeln("\n🧡   <info>Svelte setup successfully</info>");
        $this->writeln("👉  Get started with the following commands:\n");
        $this->writeln('    leaf serve <info>- start dev server</info>');
        $this->writeln("    leaf view:build <info>- build for production</info>\n");

        return 0;
    }

    /**
     * Install tailwind
     */
    protected function installTailwind()
    {
        $directory = getcwd();

        if ($this->isMVCApp()) {
            return (int) sprout()->run("php $directory/leaf view:install --tailwind --ansi");
        }

        $this->writeln("📦  <info>Installing tailwind...</info>\n");

        if (!sprout()->npm($this->option('pm'))->install('@leafphp/vite-plugin tailwindcss @tailwindcss/vite')->isSuccessful()) {
            $this->writeln('❌  <error>Failed to install tailwind</error>');

            return 1;
        }

        $this->writeln("\n✅  <info>Tailwind installed successfully</info>");
        $this->writeln("🧱  <info>Setting up Leaf Tailwind server bridge...</info>\n");

        if (!sprout()->composer()->install('leafs/vite')->isSuccessful()) {
            $this->writeln('❌  <error>Failed to setup Leaf Tailwind server bridge</error>');

            return 1;
        }

        \Leaf\FS\Directory::copy(__DIR__ . '/themes/tailwind', $directory, [
            'recursive' => true,
        ]);

        $package = json_decode(file_get_contents("$directory/package.json"), true);
        $package['type'] = 'module';
        $package['scripts']['dev'] = 'vite';
        $package['scripts']['build'] = 'vite build';
        file_put_contents("$directory/package.json", json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        if (file_exists("$directory/views/js/app.js")) {
            $jsApp = file_get_contents("$directory/views/js/app.js");

            if (strpos($jsApp, "import '../css/app.css';") === false) {
                \Leaf\FS\File::write("$directory/views/js/app.js", function ($content) {
                    return "import '../css/app.css';\n$content";
                });
            }
        } elseif (file_exists("$directory/views/js/app.jsx")) {
            $jsApp = file_get_contents("$directory/views/js/app.jsx");

            if (strpos($jsApp, "import '../css/app.css';") === false) {
                \Leaf\FS\File::write("$directory/views/js/app.jsx", function ($content) {
                    return "import '../css/app.css';\n$content";
                });
            }
        }

        if (file_exists("$directory/views/css/app.css")) {
            \Leaf\FS\File::write("$directory/views/css/app.css", function ($content) {
                if (strpos($content, '@import "tailwindcss";') === false) {
                    return "@import \"tailwindcss\";\n@source \"../\";\n\n$content";
                }

                return $content;
            });
        }

        $this->writeln("\n🩵  <info>Tailwind CSS setup successfully</info>");
        $this->writeln("👉  Get started with the following commands:\n");
        $this->writeln('    leaf serve <info>- start dev server</info>');
        $this->writeln("    leaf view:build <info>- build for production</info>\n");

        return 0;
    }

    /**
     * Install vite
     */
    protected function installVite()
    {
        $directory = getcwd();

        if ($this->isMVCApp()) {
            return (int) sprout()->run("php $directory/leaf view:install --vite --ansi");
        }

        $this->writeln("📦  <info>Installing vite...</info>\n");

        if (!sprout()->npm($this->option('pm'))->install('@leafphp/vite-plugin vite@^7.0')->isSuccessful()) {
            $this->writeln('❌  <error>Failed to install vite</error>');

            return 1;
        }

        $this->writeln("\n✅  <info>Vite installed successfully</info>");
        $this->writeln("🧱  <info>Setting up Leaf Vite server bridge...</info>\n");

        if (!sprout()->composer()->install('leafs/vite')->isSuccessful()) {
            $this->writeln('❌  <error>Failed to setup Leaf Vite server bridge</error>');

            return 1;
        }

        \Leaf\FS\Directory::copy(__DIR__ . '/themes/vite', $directory, [
            'recursive' => true,
        ]);

        $this->writeln("\n💜   <info>Vite setup successfully</info>");
        $this->writeln("👉  Get started with the following commands:\n");
        $this->writeln('    leaf serve <info>- start dev server</info>');
        $this->writeln("    leaf view:build <info>- build for production</info>\n");

        return 0;
    }

    /**
     * Install vue
     */
    protected function installVue()
    {
        $directory = getcwd();

        if ($this->isMVCApp()) {
            return (int) sprout()->run("php $directory/leaf view:install --vue --ansi");
        }

        $this->writeln("📦  <info>Installing Vue...</info>\n");

        if (!sprout()->npm($this->option('pm'))->install('@leafphp/vite-plugin @vitejs/plugin-vue@^6.0 @inertiajs/vue3@^2.0 vue')->isSuccessful()) {
            $this->writeln('❌  <error>Failed to install Vue</error>');

            return 1;
        }

        $this->writeln("\n✅  <info>Vue installed successfully</info>");
        $this->writeln("🧱  <info>Setting up Leaf Vue server bridge...</info>\n");

        if (!sprout()->composer()->install('leafs/inertia leafs/blade leafs/vite')->isSuccessful()) {
            $this->writeln('❌  <error>Failed to setup Leaf Vue server bridge</error>');

            return 1;
        }

        \Leaf\FS\Directory::copy(__DIR__ . '/themes/tailwind', $directory, [
            'recursive' => true,
        ]);

        $package = json_decode(file_get_contents("$directory/package.json"), true);
        $package['type'] = 'module';
        $package['scripts']['dev'] = 'vite';
        $package['scripts']['build'] = 'vite build';
        file_put_contents("$directory/package.json", json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        if (\Leaf\FS\File::exists("$directory/vite.config.js")) {
            \Leaf\FS\File::write("$directory/vite.config.js", function ($content) {
                if (strpos($content, '@vitejs/plugin-vue') === false) {
                    $content = str_replace(
                        ["import leaf from '@leafphp/vite-plugin';", 'import leaf from "@leafphp/vite-plugin";'],
                        "import leaf from '@leafphp/vite-plugin';\nimport vue from '@vitejs/plugin-vue';",
                        $content
                    );
                }

                if (strpos($content, 'vue(') === false) {
                    $content = str_replace('leaf({', "vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),\nleaf({", $content);
                }

                return $content;
            });
        }

        $this->writeln("\n💚   <info>Vue setup successfully</info>");
        $this->writeln("👉  Get started with the following commands:\n");
        $this->writeln('    leaf serve <info>- start dev server</info>');
        $this->writeln("    leaf view:build <info>- build for production</info>\n");

        return 0;
    }

    // ------------------------ utils ------------------------ //
    protected function isMVCApp()
    {
        $directory = getcwd();

        return is_dir("$directory/app/views") && file_exists("$directory/leaf") && is_dir("$directory/public");
    }

    protected function isBladeProject($directory = null)
    {
        $isBladeProject = false;
        $directory ??= getcwd();

        if (file_exists("$directory/composer.lock")) {
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
