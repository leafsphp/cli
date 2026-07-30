<?php

declare(strict_types=1);

namespace Leaf\Console;

use Leaf\Sprout\Command;

class ContextCommand extends Command
{
    protected $signature = 'context
        {--raw? : Print raw .leaf/CONTEXT.md without compacting it}
        {--path= : Project directory to inspect}';

    protected $description = 'Print compact project context for external AI assistants';

    protected function handle(): int
    {
        $directory = $this->findProjectRoot($this->option('path') ?: getcwd());

        if (!$directory) {
            $this->writeln('<error>No Leaf project found. Run this command from a project with composer.json or .leaf/CONTEXT.md.</error>');

            return 1;
        }

        $contextFile = $this->findContextFile($directory);

        if ($contextFile) {
            $content = file_get_contents($contextFile);

            if ($content === false) {
                $this->writeln("<error>Could not read $contextFile.</error>");

                return 1;
            }

            if ($this->option('raw')) {
                $this->writeln(rtrim($content));

                return 0;
            }

            $this->writeln($this->buildExternalHandoff($directory, $contextFile, $content));

            return 0;
        }

        $this->writeln($this->generateContextMap($directory));

        return 0;
    }

    protected function findProjectRoot(string $path): ?string
    {
        $directory = realpath($path);

        if (!$directory) {
            return null;
        }

        if (is_file($directory)) {
            $directory = dirname($directory);
        }

        while ($directory && $directory !== dirname($directory)) {
            if (file_exists("$directory/composer.json") || $this->findContextFile($directory)) {
                return $directory;
            }

            $directory = dirname($directory);
        }

        return null;
    }

    protected function findContextFile(string $directory): ?string
    {
        foreach (['CONTEXT.md', 'context.md'] as $file) {
            $path = "$directory/.leaf/$file";

            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    protected function buildExternalHandoff(string $directory, string $contextFile, string $content): string
    {
        $projectName = $this->projectName($directory);
        $source = $this->relativePath($directory, $contextFile);
        $body = $this->compactMarkdown($content);

        return trim(<<<MARKDOWN
# Leaf External Context Handoff

Project: {$projectName}
Source: {$source}

Use this with an external assistant that cannot access the project. Agents running inside the project should read `{$source}` directly, inspect the filesystem, and write useful project knowledge back to the shared context when they finish.

---

{$body}
MARKDOWN);
    }

    /**
     * Compact markdown while preserving code fences and nested list indentation.
     */
    protected function compactMarkdown(string $content): string
    {
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $content = preg_replace('/<!--.*?-->/s', '', $content) ?? $content;

        $lines = explode("\n", $content);
        $filtered = [];
        $blankLines = 0;
        $inFence = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (!$inFence && $this->isPlaceholderLine($trimmed)) {
                continue;
            }

            if (preg_match('/^\s*```/', $line)) {
                $inFence = !$inFence;
                $filtered[] = rtrim($line);
                $blankLines = 0;

                continue;
            }

            if ($inFence) {
                $filtered[] = rtrim($line);

                continue;
            }

            if ($trimmed === '') {
                $blankLines++;

                if ($blankLines <= 1) {
                    $filtered[] = '';
                }

                continue;
            }

            $blankLines = 0;
            $filtered[] = rtrim($line);
        }

        return trim(implode("\n", $filtered));
    }

    protected function isPlaceholderLine(string $line): bool
    {
        $line = strtolower($line);

        return str_starts_with($line, '[track your recent changes')
            || str_starts_with($line, '[remove this line')
            || str_starts_with($line, '[add decisions')
            || str_starts_with($line, '[add notes');
    }

    /**
     * Generate a compact handoff for projects without .leaf/CONTEXT.md.
     */
    protected function generateContextMap(string $directory): string
    {
        $projectName = $this->projectName($directory);
        $appType = $this->detectAppType($directory);
        $modules = $this->detectLeafModules($directory);
        $entryPoints = $this->detectEntryPoints($directory);
        $routes = $this->detectRouteFiles($directory);
        $folders = $this->detectImportantFolders($directory);
        $frontend = $this->detectFrontendStack($directory);

        return trim(<<<MARKDOWN
# Leaf External Context Handoff

Project: {$projectName}
Source: generated from project files

No `.leaf/CONTEXT.md` file was found, so this is a compact fallback map for an external assistant. Agents running inside the project should prefer the shared `.leaf/CONTEXT.md` file when it exists and keep it synced with useful project knowledge.

## Project
- App type: {$appType}
- Leaf modules: {$this->formatList($modules, 'none detected')}
- Frontend stack: {$this->formatList($frontend, 'not detected')}

## Entry points
{$this->formatBullets($entryPoints, '- No common Leaf entry point detected')}

## Routes
{$this->formatBullets($routes, '- No conventional route files detected')}

## Important folders
{$this->formatBullets($folders, '- No conventional app folders detected')}

## Notes for the assistant
- Treat this as a read-only handoff, not the shared project memory.
- Ask the user for missing files when a change depends on code not represented here.
- If the project later gains `.leaf/CONTEXT.md`, use `leaf context` again for a better external handoff.
MARKDOWN);
    }

    protected function projectName(string $directory): string
    {
        $composer = $this->composerData($directory);

        return $composer['name'] ?? basename($directory);
    }

    protected function detectAppType(string $directory): string
    {
        if (is_dir("$directory/app/console")) {
            return 'Leaf console app';
        }

        if (is_dir("$directory/app/controllers") && is_dir("$directory/app/routes")) {
            return is_dir("$directory/app/views") ? 'Leaf MVC app' : 'Leaf MVC API app';
        }

        if (file_exists("$directory/public/index.php") && file_exists("$directory/leaf")) {
            return 'Leaf MVC-style app';
        }

        if (file_exists("$directory/index.php")) {
            return 'Leaf lite app';
        }

        return 'Leaf app';
    }

    /**
     * @return string[]
     */
    protected function detectLeafModules(string $directory): array
    {
        $composer = $this->composerData($directory);
        $requires = array_merge($composer['require'] ?? [], $composer['require-dev'] ?? []);
        $modules = [];

        foreach ($requires as $package => $version) {
            if (str_starts_with((string) $package, 'leafs/')) {
                $modules[] = "$package ($version)";
            }
        }

        sort($modules);

        return $modules;
    }

    /**
     * @return string[]
     */
    protected function detectEntryPoints(string $directory): array
    {
        return $this->existingPaths($directory, [
            'index.php',
            'public/index.php',
            'leaf',
            'leaf.php',
            'bin/sprout',
        ]);
    }

    /**
     * @return string[]
     */
    protected function detectRouteFiles(string $directory): array
    {
        $routes = $this->existingPaths($directory, [
            'app/routes/index.php',
            'app/routes/_app.php',
            'app/routes/_frontend.php',
            'routes/index.php',
            'routes/web.php',
            'routes/api.php',
        ]);

        foreach (glob("$directory/app/routes/*.php") ?: [] as $file) {
            $routes[] = $this->relativePath($directory, $file);
        }

        $indexFile = "$directory/index.php";

        if (is_file($indexFile) && preg_match('/app\(\)->(get|post|put|patch|delete|any)\s*\(/i', file_get_contents($indexFile) ?: '')) {
            $routes[] = 'index.php (inline routes)';
        }

        return array_values(array_unique($routes));
    }

    /**
     * @return string[]
     */
    protected function detectImportantFolders(string $directory): array
    {
        return $this->existingPaths($directory, [
            'app',
            'app/controllers',
            'app/models',
            'app/views',
            'app/routes',
            'app/database/migrations',
            'app/database/seeds',
            'config',
            'public',
            'public/assets',
            'storage',
            'tests',
        ]);
    }

    /**
     * @return string[]
     */
    protected function detectFrontendStack(string $directory): array
    {
        $stack = [];

        if (file_exists("$directory/vite.config.js") || file_exists("$directory/vite.config.ts")) {
            $stack[] = 'Vite';
        }

        if (file_exists("$directory/tailwind.config.js") || file_exists("$directory/tailwind.config.ts")) {
            $stack[] = 'Tailwind CSS';
        }

        $packageFile = "$directory/package.json";

        if (is_file($packageFile)) {
            $package = json_decode(file_get_contents($packageFile) ?: '', true) ?: [];
            $dependencies = array_merge($package['dependencies'] ?? [], $package['devDependencies'] ?? []);

            foreach ([
                'react' => 'React',
                'vue' => 'Vue',
                'svelte' => 'Svelte',
                '@inertiajs/inertia' => 'Inertia',
                '@inertiajs/react' => 'Inertia React',
                '@inertiajs/vue3' => 'Inertia Vue',
                '@inertiajs/svelte' => 'Inertia Svelte',
            ] as $packageName => $label) {
                if (array_key_exists($packageName, $dependencies)) {
                    $stack[] = $label;
                }
            }
        }

        return array_values(array_unique($stack));
    }

    /**
     * @return string[]
     */
    protected function existingPaths(string $directory, array $paths): array
    {
        $found = [];

        foreach ($paths as $path) {
            if (file_exists("$directory/$path")) {
                $found[] = $path;
            }
        }

        return $found;
    }

    /**
     * @return array<string, mixed>
     */
    protected function composerData(string $directory): array
    {
        $composerFile = "$directory/composer.json";

        if (!is_file($composerFile)) {
            return [];
        }

        $data = json_decode(file_get_contents($composerFile) ?: '', true);

        return is_array($data) ? $data : [];
    }

    protected function relativePath(string $root, string $path): string
    {
        $root = rtrim(str_replace('\\', '/', $root), '/');
        $path = str_replace('\\', '/', $path);

        if (str_starts_with($path, "$root/")) {
            return substr($path, strlen($root) + 1);
        }

        return $path;
    }

    protected function formatList(array $items, string $fallback): string
    {
        return !empty($items) ? implode(', ', $items) : $fallback;
    }

    protected function formatBullets(array $items, string $fallback): string
    {
        if (empty($items)) {
            return $fallback;
        }

        return implode("\n", array_map(fn ($item) => "- `$item`", $items));
    }
}
