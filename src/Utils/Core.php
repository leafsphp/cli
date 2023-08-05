<?php

declare(strict_types=1);

namespace Leaf\Console\Utils;

use Symfony\Component\Process\Process;

class Core
{
	/**
	 * Run a shell process with the output.
	 */
	public static function run(string $command, $output, string $cwd = null)
	{
		$process = Process::fromShellCommandline(
			$command,
			$cwd,
			null,
			null,
			null
		);

		$process->run(function ($type, $line) use ($output) {
			$output->write($line);
		});

		return $process->isSuccessful();
	}
	/**
	 * Get the composer command for the environment.
	 * @return string
	 */
	public static function findComposer(): string
	{
		$composerPath = getcwd() . '/composer.phar';

		if (file_exists($composerPath)) {
			return '"' . PHP_BINARY . '" ' . $composerPath;
		}

		return 'composer';
	}

	/**
	 * Get the git command for the environment.
	 * @return string
	 */
	public static function findGit(): string
	{
		$gitPath = getcwd() . '/git';

		if (file_exists($gitPath)) {
			return $gitPath;
		}

		return 'git';
	}

	/**
	 * Get the node command for the environment.
	 * @return string
	 */
	public static function findNodeJS(): string
	{
		$nodePath = getcwd() . '/node';

		if (file_exists($nodePath)) {
			return $nodePath;
		}

		return 'node';
	}

	/**
	 * Get the node command for the environment.
	 * @return string
	 */
	public static function findNpm(): string
	{
		$npmPath = getcwd() . '/npm';

		if (file_exists($npmPath)) {
			return $npmPath;
		}

		return 'npm';
	}

	/**
	 * Get the leaf CLI bin.
	 * @return string
	 */
	public static function findLeaf(): string
	{
		$leafPath = __DIR__ . '/../../bin/leaf';

		if (file_exists($leafPath)) {
			return '"' . PHP_BINARY . '" ' . $leafPath;
		}

		return 'leaf';
	}

	/**
	 * Get the leaf watcher bin.
	 * @return string
	 */
	public static function findWatcher(): string
	{
		$watcherPath = getcwd() . '/watcher/bin/watcher.js';

		if (file_exists($watcherPath)) {
			return $watcherPath;
		}

		return 'leaf-watcher';
	}

	/**
	 * Check if a system command exists
	 * @return bool
	 */
	public static function commandExists(string $cmd)
	{
		return !empty(shell_exec(sprintf("which %s", escapeshellarg($cmd))));
	}
}
