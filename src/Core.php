<?php

namespace Leaf\Console;

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
}
