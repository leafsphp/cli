<?php

declare(strict_types=1);

namespace Leaf\Console;

use Leaf\Sprout\Command;

class DeployCommand extends Command
{
    protected $signature = 'deploy
        {--to=fly : The provider to deploy to (has support for Fly.io, more coming soon)}';
    protected $description = 'Setup files needed for deployment to a provider of your choice';

    protected function handle(): int
    {
        if (!sprout()->composer()->json()) {
            $this->writeln('<error>No composer.json found in the current directory.</error>');
            return 1;
        }

        if (!sprout()->composer()->hasDependencies()) {
            $this->writeln('<info>Installing dependencies...</info>');

            if (!sprout()->composer()->install()->isSuccessful()) {
                $this->writeln('<error>❌  Failed to install dependencies.</error>');
                return 1;
            }
        }

        if ($this->isMVCApp()) {
            $this->writeln('<info>Building for Leaf MVC...</info>');
        }

        $provider = $this->option('to');

        if ($provider === 'fly' || $provider === 'fly.io') {
            $this->writeln('<info>Setting up Fly.io deployment...</info>');

            if (!$this->setupFlyDeployment()) {
                $this->writeln('<error>❌  Failed to set up Fly.io deployment.</error>');
                return 1;
            }
        } else {
            $this->writeln("<info>Deploy does not support $provider, but we are working on it 🤧...</info>");
        }

        return 0;
    }

    protected function setupFlyDeployment()
    {
        $appDir = getcwd();
        $appName = $this->namify(basename($appDir), 'fly');

        if (!\Leaf\FS\File::exists("$appDir/fly.toml")) {
            $this->writeln('<info>Writing fly deploy files...</info>');

            if (
                \Leaf\FS\Directory::copy(
                    __DIR__ . '/themes/fly',
                    $appDir,
                    ['recursive' => true]
                )
            ) {
                $this->writeln('<info>Deployment files setup!</info>');

                $appRegion = $this->getEnvValue('APP_PROD_REGION', "$appDir/.env");
                $appName = $this->namify($this->getEnvValue('APP_NAME', "$appDir/.env"), 'fly');

                \Leaf\FS\File::create(
                    "$appDir/storage/deployments.yml",
                    "appName: $appName\nregion: $appRegion\ndeployed: false",
                    ['recursive' => true]
                );

                \Leaf\FS\File::write(
                    "$appDir/fly.toml",
                    function ($content) use ($appRegion, $appName) {
                        return str_replace(
                            ['LEAF-APP-NAME', 'LEAF-APP-REGION'],
                            [$appName, $appRegion],
                            $content
                        );
                    }
                );

                return true;
            } else {
                $this->writeln('<error>❌  Failed to write deployment files.</error>');
                return false;
            }
        }

        if (\Leaf\FS\File::exists("$appDir/storage/deployments.yml")) {
            $deployConfig = \Leaf\FS\File::read("$appDir/storage/deployments.yml");

            preg_match('/appName:\s*(.+)/', $deployConfig, $appNameMatch);
            preg_match('/region:\s*(.+)/', $deployConfig, $regionMatch);

            $appName = trim($appNameMatch[1] ?? '');
            $appRegion = trim($regionMatch[1] ?? '');

            if (strpos(\Leaf\FS\File::read("$appDir/storage/deployments.yml"), 'deployed: false') !== false) {
                if (
                    sprout()
                        ->process("fly launch --now --auto-confirm --copy-config --region $appRegion --name $appName")
                        ->setTimeout(null)
                        ->run() === 0
                ) {
                    \Leaf\FS\File::write("$appDir/storage/deployments.yml", function ($content) {
                        return str_replace('deployed: false', 'deployed: true', $content);
                    });

                    return true;
                }

                return false;
            }

            return sprout()
                ->process('fly deploy --yes')
                ->setTimeout(null)
                ->run() === 0 ? true : false;
        }

        return false;
    }

    protected function getEnvValue($key, $envPath = __DIR__ . '/.env')
    {
        if (!file_exists($envPath)) {
            return null;
        }

        $pattern = '/^' . preg_quote($key) . '\s*=\s*(.*)$/m';
        $envContent = file_get_contents($envPath);

        if (preg_match($pattern, $envContent, $matches)) {
            $value = trim($matches[1]);

            if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
                $value = substr($value, 1, -1);
            } elseif (str_starts_with($value, "'") && str_ends_with($value, "'")) {
                $value = substr($value, 1, -1);
            }

            return $value;
        }

        return null;
    }

    protected function namify($name, $provider)
    {
        switch (strtolower($provider)) {
            case 'fly':
            case 'fly.io':
                return strtolower(str_replace(['_', ' '], '-', $name));
            default:
                return strtolower($name);
        }
    }

    protected function isMVCApp()
    {
        $directory = getcwd();

        return is_dir("$directory/app/views") && file_exists("$directory/leaf") && is_dir("$directory/public");
    }
}
