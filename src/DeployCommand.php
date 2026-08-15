<?php

declare(strict_types=1);

namespace Leaf\Console;

use Leaf\Sprout\Command;

class DeployCommand extends Command
{
    protected $signature = 'deploy
        {--to=fly : The provider to deploy to (fly, render)}
        {--name= : Name for your app on the provider (defaults to APP_NAME or the directory name)}
        {--region= : Region to deploy to (defaults to APP_PROD_REGION or the provider default)}';
    protected $description = 'Deploy your app to a provider of your choice';

    protected const PROVIDERS = ['fly', 'fly.io', 'render'];

    protected function handle(): int
    {
        if (!sprout()->composer()->json()) {
            $this->writeln('<error>No composer.json found in the current directory.</error>');

            return 1;
        }

        $provider = strtolower($this->option('to'));

        if (!in_array($provider, static::PROVIDERS)) {
            $this->writeln("<error>$provider is not supported yet.</error>");
            $this->writeln('Supported providers: fly, render');

            return 1;
        }

        if ($provider === 'render') {
            return $this->deployToRender();
        }

        return $this->deployToFly();
    }

    // -------------------- fly.io --------------------

    protected function deployToFly(): int
    {
        if (sprout()->process('fly version')->run(function () {
        }) !== 0) {
            $this->writeln('<error>The fly CLI is not installed.</error>');
            $this->writeln('Install it from https://fly.io/docs/flyctl/install/ then run this command again.');

            return 1;
        }

        if (sprout()->process('fly auth whoami')->run(function () {
        }) !== 0) {
            $this->writeln('<error>You are not logged in to Fly.io.</error>');
            $this->writeln('Run <info>fly auth login</info> then run this command again.');

            return 1;
        }

        $appDir = getcwd();
        $appRegion = $this->option('region') ?: ($this->getEnvValue('APP_PROD_REGION', "$appDir/.env") ?: 'iad');
        $appName = $this->namify(
            $this->option('name') ?: ($this->getEnvValue('APP_NAME', "$appDir/.env") ?: basename($appDir)),
            'fly'
        );

        if (!\Leaf\FS\File::exists("$appDir/fly.toml")) {
            $this->writeln('<info>Writing fly deploy files...</info>');

            if (!$this->writeDeployFiles($appDir)) {
                $this->writeln('<error>❌  Failed to write deployment files.</error>');

                return 1;
            }

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
        } else {
            // the app was configured on a previous run: fly.toml is the
            // source of truth for the name
            $flyConfig = \Leaf\FS\File::read("$appDir/fly.toml");

            if (preg_match('/^app\s*=\s*[\'"](.+)[\'"]/m', $flyConfig, $appNameMatch)) {
                $appName = trim($appNameMatch[1]);
            }
        }

        // older versions of this command tracked deploy state in a local
        // file; fly itself is the source of truth now
        if (\Leaf\FS\File::exists("$appDir/storage/deployments.yml")) {
            \Leaf\FS\File::delete("$appDir/storage/deployments.yml");
            $this->writeln('<comment>Removed storage/deployments.yml (no longer used; deploy state now comes from Fly directly).</comment>');
        }

        // fly itself knows whether this app exists; no local state file
        // to go stale when a teammate clones the repo
        $appExists = sprout()
            ->process('fly status --app ' . escapeshellarg($appName))
            ->run(function () {
            }) === 0;

        $exitCode = $appExists
            ? sprout()
                ->process('fly deploy --yes')
                ->setTimeout(null)
                ->run(function ($type, $buffer) {
                    echo $buffer;
                })
            : sprout()
                ->process(
                    'fly launch --now --copy-config --yes --region ' . escapeshellarg($appRegion) .
                        ' --name ' . escapeshellarg($appName)
                )
                ->setTimeout(null)
                ->run(function ($type, $buffer) {
                    echo $buffer;
                });

        if ($exitCode !== 0) {
            $this->writeln('<error>❌  Deployment failed.</error>');

            if (!$appExists) {
                $this->writeln("Fly app names are globally unique. If the name <info>$appName</info> is taken, run again with --name to pick another.");
            }

            return 1;
        }

        $this->writeln("\n<info>Deployed 🚀</info> https://$appName.fly.dev");
        $this->printSecretsHint("$appDir/.env", 'fly secrets set');

        return 0;
    }

    // -------------------- render --------------------

    protected function deployToRender(): int
    {
        $appDir = getcwd();
        $appName = $this->namify(
            $this->option('name') ?: ($this->getEnvValue('APP_NAME', "$appDir/.env") ?: basename($appDir)),
            'render'
        );

        if (!\Leaf\FS\File::exists("$appDir/Dockerfile")) {
            $this->writeln('<info>Writing deploy files...</info>');

            if (!$this->writeDeployFiles($appDir)) {
                $this->writeln('<error>❌  Failed to write deployment files.</error>');

                return 1;
            }

            // the docker image is provider-agnostic; only fly's own config
            // file has no business on render
            \Leaf\FS\File::delete("$appDir/fly.toml");
        }

        if (!\Leaf\FS\File::exists("$appDir/render.yaml")) {
            \Leaf\FS\File::create("$appDir/render.yaml", $this->renderBlueprint($appName, $this->option('region') ?: null));
        }

        $this->writeln('<info>Render deploy files ready (Dockerfile + render.yaml).</info>');
        $this->writeln('');
        $this->writeln('Render deploys straight from your git repository:');
        $this->writeln('  1. Commit and push these files to GitHub or GitLab');
        $this->writeln('  2. Open <info>https://dashboard.render.com</info> → New → Blueprint');
        $this->writeln('  3. Connect this repository. Render reads render.yaml and sets everything up');

        if (sprout()->process('git remote get-url origin')->run(function () {
        }) !== 0) {
            $this->writeln('');
            $this->writeln('<comment>This project has no git remote yet. Create a repository first, e.g. on https://github.com/new</comment>');
        }

        $this->printSecretsHint("$appDir/.env", null);
        $this->writeln('');
        $this->writeln('<comment>Note: free Render services sleep after 15 minutes without traffic and wake on the next request.</comment>');

        return 0;
    }

    protected function renderBlueprint(string $appName, ?string $region = null): string
    {
        // render regions: oregon, virginia, ohio, frankfurt, singapore
        $regionLine = $region ? "\n    region: $region" : '';

        return <<<YAML
services:
  - type: web
    name: $appName
    runtime: docker
    plan: free$regionLine
    dockerfilePath: ./Dockerfile
    healthCheckPath: /
    envVars:
      - key: APP_ENV
        value: production
      - key: APP_DEBUG
        value: 'false'

YAML;
    }

    // -------------------- shared --------------------

    /**
     * Copy the deployment files into the app, adjusting them for the
     * shape of the app being deployed
     */
    protected function writeDeployFiles(string $appDir): bool
    {
        if (
            !\Leaf\FS\Directory::copy(
                __DIR__ . '/themes/fly',
                $appDir,
                ['recursive' => true]
            )
        ) {
            return false;
        }

        if ($this->isLiteApp($appDir)) {
            $this->serveFromAppRoot($appDir);
        }

        return true;
    }

    /**
     * A lite app keeps index.php at the project root instead of in public/
     */
    protected function isLiteApp(string $appDir): bool
    {
        return !is_dir("$appDir/public") && file_exists("$appDir/index.php");
    }

    /**
     * Point the web server at the project root for lite apps, and keep
     * everything that isn't meant to be public out of reach
     */
    protected function serveFromAppRoot(string $appDir)
    {
        $nginxConfig = "$appDir/.fly/nginx/sites-available/default";

        if (!\Leaf\FS\File::exists($nginxConfig)) {
            return;
        }

        \Leaf\FS\File::write($nginxConfig, function ($content) {
            return str_replace(
                'root /var/www/html/public;',
                "root /var/www/html;\n\n" .
                    "    # the app root is the docroot here, so keep app internals\n" .
                    "    # out of the browser's reach\n" .
                    "    location ~ ^/(vendor|storage)/ {\n" .
                    "        deny all;\n" .
                    "    }\n\n" .
                    "    location ~ ^/(composer\.(json|lock)|package(-lock)?\.json)$ {\n" .
                    "        deny all;\n" .
                    '    }',
                $content
            );
        });
    }

    /**
     * Production needs the secrets from .env, but they are never part of
     * the image. Point them at the provider's secret store by key name
     * only; values stay out of the terminal history.
     */
    protected function printSecretsHint(string $envPath, ?string $setCommand)
    {
        $keys = $this->getSecretEnvKeys($envPath);

        if (empty($keys)) {
            return;
        }

        $this->writeln('');
        $this->writeln('<comment>Your .env has values your app will need in production:</comment>');

        if ($setCommand) {
            $this->writeln("  $setCommand " . implode('= ', $keys) . '=');
            $this->writeln('  (fill in the values; .env files are not uploaded with your app)');
        } else {
            $this->writeln('  ' . implode(', ', $keys));
            $this->writeln('  Add them under Environment in your service settings; .env files are not uploaded with your app.');
        }
    }

    protected function getSecretEnvKeys(string $envPath): array
    {
        if (!file_exists($envPath)) {
            return [];
        }

        $skip = ['APP_NAME', 'APP_ENV', 'APP_DEBUG', 'APP_URL', 'APP_PORT', 'APP_PROD_REGION'];
        $keys = [];

        foreach (preg_split('/\R/', (string) file_get_contents($envPath)) as $line) {
            if (preg_match('/^([A-Z0-9_]+)\s*=\s*(.+)$/', trim($line), $matches)) {
                if (!in_array($matches[1], $skip) && trim($matches[2], '"\' ') !== '') {
                    $keys[] = $matches[1];
                }
            }
        }

        return $keys;
    }

    protected function getEnvValue($key, $envPath)
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
        $name = strtolower(str_replace(['_', ' '], '-', $name));

        // both fly and render want dns-friendly names
        $name = preg_replace('/[^a-z0-9-]/', '', $name);

        return trim($name, '-') ?: 'leaf-app';
    }
}
