<?php

declare(strict_types=1);

namespace Leaf\Console;

use Leaf\Sprout\Command;

class ContextCommand extends Command
{
    protected $signature = 'context
        {--raw? : Print raw context without minification}';

    protected $description = 'Print compact project context for external AI assistants';

    protected function handle(): int
    {
        $directory = getcwd();
        $contextFile = null;

        if (file_exists("$directory/.leaf/context.md")) {
            $contextFile = "$directory/.leaf/context.md";
        } elseif (file_exists("$directory/.leaf/CONTEXT.md")) {
            $contextFile = "$directory/.leaf/CONTEXT.md";
        }

        $content = ($contextFile)
            ? file_get_contents($contextFile)
            : $this->generateContextMap($directory);

        if ($this->option('raw')) {
            $this->writeln($content);
            return 0;
        }

        $minified = $this->minifyContext($content);
        $this->writeln($minified);

        return 0;
    }

    /**
     * Minify project context for compact AI prompt handoff.
     *
     * @param string $content
     * @return string
     */
    protected function minifyContext(string $content): string
    {
        $content = preg_replace('/<!--(.*?)-->/s', '', $content);

        $lines = explode("\n", $content);
        $filtered = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (str_starts_with($trimmed, '[track your recent changes') || str_starts_with($trimmed, '[remove this line')) {
                continue;
            }

            $filtered[] = $trimmed;
        }

        $result = implode("\n", $filtered);
        $result = preg_replace("/\n{3,}/", "\n\n", $result);

        return trim($result);
    }

    /**
     * Dynamically generate a fallback context map for projects without .leaf/context.md.
     *
     * @param string $directory
     * @return string
     */
    protected function generateContextMap(string $directory): string
    {
        $appName = basename($directory);
        $composerFile = "$directory/composer.json";
        $modules = [];

        if (file_exists($composerFile)) {
            $composerData = json_decode(file_get_contents($composerFile), true) ?? [];
            $appName = $composerData['name'] ?? $appName;
            $requires = array_merge($composerData['require'] ?? [], $composerData['require-dev'] ?? []);

            foreach ($requires as $package => $version) {
                if (str_starts_with($package, 'leafs/')) {
                    $modules[] = $package;
                }
            }
        }

        $appType = 'lite';
        $directories = '';

        if (is_dir("$directory/app/controllers") || is_dir("$directory/app/routes")) {
            $appType = is_dir("$directory/app/views") ? 'mvc' : 'api';
            $directories = implode(', ', array_filter(scandir($directory), function ($item) use ($directory) {
                return $item !== '.' && $item !== '..' && is_dir("$directory/$item");
            }));
        }

        $modulesList = !empty($modules) ? implode(', ', $modules) : 'leafs/leaf';

        return <<<MARKDOWN
# Project Memory: {$appName}
App Type: {$appType}
Leaf Modules: {$modulesList}

## Structure
- Base Directory: {$directory}
- Key Folders: {$directories}
MARKDOWN;
    }
}
