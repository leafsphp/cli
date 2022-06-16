<?php

declare(strict_types=1);

namespace Leaf\Console\Utils;

class Core {
	/**
	 * Get the composer command for the environment.
	 *
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
	 * Get the node command for the environment.
	 *
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
	 *
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
	 *
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
	 * Check if a system command exists
	 * 
	 * @return bool
	 */
	public static function commandExists(string $cmd)
	{
		return !empty(shell_exec(sprintf("which %s", escapeshellarg($cmd))));
	}
}
